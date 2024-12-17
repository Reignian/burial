<?php
session_start();
require_once __DIR__ . '/reservations.class.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/email_functions.php';

if(!isset($_SESSION['account']) || !($_SESSION['account']['is_admin'] || $_SESSION['account']['is_staff'])){
    header('location: ../../sign/login.php');
    exit();
}

$burialObj = new Reservation_class();
$payment_plans = $burialObj->getPaymentPlans();

// Initialize Database connection for additional queries
$db = new Database();

// Get all available lots
$sql = "SELECT * FROM lots WHERE status = 'Available' ORDER BY lot_name";
$query = $db->connect()->prepare($sql);
$query->execute();
$available_lots = $query->fetchAll(PDO::FETCH_ASSOC);

// Get existing accounts
$sql = "SELECT account_id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name, '')) as full_name, 
        first_name, middle_name, last_name, email, phone_number 
        FROM account 
        WHERE is_customer = 1 AND is_deleted = 0
        ORDER BY last_name, first_name";
$query = $db->connect()->prepare($sql);
$query->execute();
$existing_accounts = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->connect()->beginTransaction();
        
        $account_id = null;
        $existing_account = null;
        
        if ($_POST['account_type'] === 'new') {
            // Generate username from email
            $username = strtolower(explode('@', $_POST['email'])[0]);
            
            // Generate random password
            $password = bin2hex(random_bytes(4)); // 8 characters
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Create new account
            $sql = "INSERT INTO account (username, password, first_name, middle_name, last_name, email, phone_number, is_customer) 
                    VALUES (:username, :password, :first_name, :middle_name, :last_name, :email, :phone_number, 1)";
            $query = $db->connect()->prepare($sql);
            $query->execute([
                ':username' => $username,
                ':password' => $hashed_password,
                ':first_name' => $_POST['first_name'],
                ':middle_name' => $_POST['middle_name'],
                ':last_name' => $_POST['last_name'],
                ':email' => $_POST['email'],
                ':phone_number' => $_POST['phone_number']
            ]);
            $account_id = $db->connect()->lastInsertId();

            // Log new account creation
            $log_details = sprintf(
                "Created new account for reservation:\nName: %s %s %s\nEmail: %s\nPhone: %s",
                $_POST['first_name'],
                $_POST['middle_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone_number']
            );
            
            $sql = "INSERT INTO staff_logs (staff_id, action, details) 
                    VALUES (:staff_id, 'Create Account', :details)";
            $query = $db->connect()->prepare($sql);
            $query->execute([
                ':staff_id' => $_SESSION['account']['account_id'],
                ':details' => $log_details
            ]);
            
            // Store credentials to show later
            $_SESSION['temp_credentials'] = [
                'username' => $username,
                'password' => $password
            ];
        } else {
            $account_id = $_POST['existing_account'];
            $existing_account = array_filter($existing_accounts, function($account) use ($account_id) {
                return $account['account_id'] == $account_id;
            });
            $existing_account = reset($existing_account);
        }

        // Get lot details
        $sql = "SELECT * FROM lots WHERE lot_id = :lot_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':lot_id' => $_POST['lot_id']]);
        $lot_details = $query->fetch(PDO::FETCH_ASSOC);

        // Get payment plan details
        $sql = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':payment_plan_id' => $_POST['payment_plan']]);
        $payment_plan = $query->fetch(PDO::FETCH_ASSOC);

        // Calculate monthly payment and balance
        $down_payment_percentage = $payment_plan['down_payment'] / 100;
        $interest_rate = $payment_plan['interest_rate'] / 100;
        $duration = $payment_plan['duration'];
        $down_payment = $lot_details['price'] * $down_payment_percentage;
        $principal = $lot_details['price'] - $down_payment;
        $interest = $principal * $interest_rate;
        $total = $principal + $interest;
        
        $monthly_payment = $duration > 0 ? $total / $duration : 0;
        $balance = $lot_details['price'] + $interest;

        // Create reservation
        $sql = "INSERT INTO reservation (account_id, lot_id, payment_plan_id, reservation_date, monthly_payment, balance, request) 
                VALUES (:account_id, :lot_id, :payment_plan_id, NOW(), :monthly_payment, :balance, 'Confirmed')";
        $query = $db->connect()->prepare($sql);
        $query->execute([
            ':account_id' => $account_id,
            ':lot_id' => $_POST['lot_id'],
            ':payment_plan_id' => $_POST['payment_plan'],
            ':monthly_payment' => $monthly_payment,
            ':balance' => $balance
        ]);
        $reservation_id = $db->connect()->lastInsertId();

        // Update lot status
        $sql = "UPDATE lots SET status = 'Reserved' WHERE lot_id = :lot_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':lot_id' => $_POST['lot_id']]);

        // Log reservation creation
        $client_name = $_POST['account_type'] === 'new' 
            ? $_POST['last_name'] . ', ' . $_POST['first_name'] . ' ' . $_POST['middle_name']
            : $existing_account['full_name'];

        $log_details = sprintf(
            "Created new reservation #%d:\n" .
            "Client: %s\n" .
            "Lot: %s (%s)\n" .
            "Payment Plan: %s\n" .
            "Monthly Payment: ₱%s\n" .
            "Total Balance: ₱%s",
            $reservation_id,
            $client_name,
            $lot_details['lot_name'],
            $lot_details['location'],
            $payment_plan['plan'],
            number_format($monthly_payment, 2),
            number_format($balance, 2)
        );

        $sql = "INSERT INTO staff_logs (staff_id, action, details) 
                VALUES (:staff_id, 'Create Reservation', :details)";
        $query = $db->connect()->prepare($sql);
        $query->execute([
            ':staff_id' => $_SESSION['account']['account_id'],
            ':details' => $log_details
        ]);

        // Create notification for the customer
        $notification_message = "Your reservation for " . $lot_details['lot_name'] . " has been created successfully.";
        $sql = "INSERT INTO notifications (account_id, reference_id, type, message, created_at) 
                VALUES (:account_id, :reference_id, 'reservation', :message, NOW())";
        $query = $db->connect()->prepare($sql);
        $query->execute([
            ':account_id' => $account_id,
            ':reference_id' => $reservation_id,
            ':message' => $notification_message
        ]);

        $db->connect()->commit();
        
        // Store all details to show later
        $_SESSION['reservation_details'] = [
            'account' => [
                'username' => isset($username) ? $username : '',
                'password' => isset($password) ? $password : '',
                'first_name' => $_POST['account_type'] === 'new' ? $_POST['first_name'] : $existing_account['first_name'],
                'middle_name' => $_POST['account_type'] === 'new' ? $_POST['middle_name'] : $existing_account['middle_name'],
                'last_name' => $_POST['account_type'] === 'new' ? $_POST['last_name'] : $existing_account['last_name'],
                'email' => $_POST['account_type'] === 'new' ? $_POST['email'] : $existing_account['email'],
                'phone_number' => $_POST['account_type'] === 'new' ? $_POST['phone_number'] : $existing_account['phone_number']
            ],
            'lot' => [
                'lot_id' => $_POST['lot_id'],
                'price' => $lot_details['price']
            ],
            'payment' => [
                'down_payment' => $down_payment,
                'monthly_payment' => $monthly_payment,
                'total_balance' => $balance,
                'interest_rate' => $interest_rate,
                'payment_duration' => $duration
            ]
        ];

        // Send email for new accounts
        if ($_POST['account_type'] === 'new') {
            sendReservationSummaryEmail($_SESSION['reservation_details']);
        }
        
        echo "<script>alert('Reservation created successfully!'); window.location.href='reservation_summary.php';</script>";
        exit();
    } catch (Exception $e) {
        $db->connect()->rollBack();
        $error_message = addslashes($e->getMessage());
        echo "<script>alert('Error creating reservation: " . $error_message . "'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }
}

if (isset($_SESSION['show_summary']) && $_SESSION['show_summary']) {
    $details = $_SESSION['reservation_details'];
    unset($_SESSION['show_summary']); // Clear flag after showing
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reservation - Sto. Nino Parish Cemetery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #006064;
            --primary-light: #428e92;
            --primary-dark: #00363a;
            --accent-color: #ffd54f;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .main-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary-color) !important;
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .card-header h4 {
            color: white;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            color: var(--primary-dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.2rem rgba(0, 96, 100, 0.25);
        }
        
        .btn-group {
            gap: 0.5rem;
        }
        
        .btn-group .btn {
            border-radius: 8px !important;
            flex: 1;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .account-form, .payment-form {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        #existingAccountForm, #newAccountForm {
            display: none;
        }
        
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: rgba(0, 96, 100, 0.05);
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .table-warning {
            background-color: #fff3e0 !important;
        }
        
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                width: 100%;
                margin: 0.25rem 0;
            }
        }
        
        /* Select2 Custom Styles */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da;
        }
        .select2-container--bootstrap-5 .select2-selection--single {
            padding-top: 2px;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #ced4da;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
        }
        .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
        }
        .select2-container--bootstrap-5 .select2-selection__rendered {
            padding-left: 5px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="main-card">
            <div class="card-header">
                <h4><i class="fas fa-plus-circle me-2"></i>Add New Reservation</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- Account Selection -->
                    <div class="account-form">
                        <h5 class="section-title"><i class="fas fa-user me-2"></i>Account Information</h5>
                        <div class="mb-4">
                            <label class="form-label">Select Account Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="account_type" id="existing_account_radio" value="existing" checked>
                                <label class="btn btn-outline-primary" for="existing_account_radio">
                                    <i class="fas fa-users me-2"></i>Existing Account
                                </label>
                                
                                <input type="radio" class="btn-check" name="account_type" id="new_account_radio" value="new">
                                <label class="btn btn-outline-primary" for="new_account_radio">
                                    <i class="fas fa-user-plus me-2"></i>New Account
                                </label>
                            </div>
                        </div>
                        
                        <!-- Existing Account Form -->
                        <div id="existingAccountForm">
                            <div class="mb-3">
                                <label for="existing_account" class="form-label">Select Existing Account</label>
                                <select class="form-select searchable" id="existing_account" name="existing_account" data-placeholder="Search for an account...">
                                    <option value=""></option>
                                    <?php foreach ($existing_accounts as $account): ?>
                                        <option value="<?php echo $account['account_id']; ?>">
                                            <?php echo $account['full_name'] . ' (' . $account['email'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- New Account Form -->
                        <div id="newAccountForm">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lot and Payment Information -->
                    <div class="payment-form">
                        <h5 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Lot and Payment Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lot_id" class="form-label">Select Lot</label>
                                <select class="form-select searchable" id="lot_id" name="lot_id" required data-placeholder="Search for a lot...">
                                    <option value=""></option>
                                    <?php foreach ($available_lots as $lot): ?>
                                        <option value="<?php echo $lot['lot_id']; ?>" 
                                                data-price="<?php echo $lot['price']; ?>">
                                            <?php echo $lot['lot_name'] . ' - ' . $lot['location'] . 
                                                    ' (₱' . number_format($lot['price'], 2) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_plan" class="form-label">Payment Plan</label>
                                <select class="form-select searchable" id="payment_plan" name="payment_plan" required data-placeholder="Search for a payment plan...">
                                    <option value=""></option>
                                    <?php foreach ($payment_plans as $plan): ?>
                                        <option value="<?php echo $plan['payment_plan_id']; ?>"
                                                data-down="<?php echo $plan['down_payment']; ?>"
                                                data-interest="<?php echo $plan['interest_rate']; ?>"
                                                data-duration="<?php echo $plan['duration']; ?>">
                                            <?php echo $plan['plan'] . ' (' . $plan['down_payment'] . '% DP, ' . 
                                                    $plan['interest_rate'] . '% interest)'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="payment_summary" class="mt-4" style="display: none;">
                            <h6 class="mb-3">Payment Summary</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Lot Price:</th>
                                        <td id="lot_price_display">PHP 0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Down Payment:</th>
                                        <td id="down_payment_display">PHP 0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Monthly Payment:</th>
                                        <td id="monthly_payment_display">PHP 0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Total Balance:</th>
                                        <td id="total_balance_display">PHP 0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Create Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for searchable dropdowns
            $('.searchable').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                minimumInputLength: 0
            });
            
            // Show/hide account forms based on selection
            $('input[name="account_type"]').change(function() {
                if ($(this).val() === 'existing') {
                    $('#existingAccountForm').slideDown();
                    $('#newAccountForm').slideUp();
                    // Clear new account form
                    $('#newAccountForm input').val('');
                } else {
                    $('#newAccountForm').slideDown();
                    $('#existingAccountForm').slideUp();
                    // Clear existing account selection
                    $('#existing_account').val('');
                }
            });

            // Trigger initial state
            $('input[name="account_type"]:checked').change();

            // Calculate payments when lot or payment plan changes
            $('#lot_id, #payment_plan').change(function() {
                calculatePayments();
            });

            function calculatePayments() {
                var lot_price = parseFloat($('#lot_id option:selected').data('price')) || 0;
                var down_payment_percent = parseFloat($('#payment_plan option:selected').data('down')) || 0;
                var interest_rate = parseFloat($('#payment_plan option:selected').data('interest')) || 0;
                var duration = parseInt($('#payment_plan option:selected').data('duration')) || 0;

                if (lot_price && down_payment_percent && duration) {
                    var down_payment = lot_price * (down_payment_percent / 100);
                    var principal = lot_price - down_payment;
                    var interest = principal * (interest_rate / 100);
                    var total = principal + interest;
                    var monthly_payment = duration > 0 ? total / duration : 0;

                    $('#lot_price_display').text('PHP ' + lot_price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#down_payment_display').text('PHP ' + down_payment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#monthly_payment_display').text('PHP ' + monthly_payment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#total_balance_display').text('PHP ' + (lot_price + interest).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    
                    $('#payment_summary').slideDown();
                } else {
                    $('#payment_summary').slideUp();
                }
            }

            // Form validation
            $('form').submit(function(e) {
                var isValid = true;
                var account_type = $('input[name="account_type"]:checked').val();

                if (account_type === 'existing') {
                    if (!$('#existing_account').val()) {
                        alert('Please select an existing account');
                        isValid = false;
                    }
                } else {
                    // Validate new account fields
                    if (!$('#first_name').val() || !$('#last_name').val() || !$('#email').val() || !$('#phone_number').val()) {
                        alert('Please fill in all required fields for the new account');
                        isValid = false;
                    }
                }

                if (!$('#lot_id').val()) {
                    alert('Please select a lot');
                    isValid = false;
                }

                if (!$('#payment_plan').val()) {
                    alert('Please select a payment plan');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>