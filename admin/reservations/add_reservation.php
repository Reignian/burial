<?php
session_start();
require_once __DIR__ . '/reservations.class.php';
require_once __DIR__ . '/../../database.php';

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

        // Get lot price
        $sql = "SELECT price FROM lots WHERE lot_id = :lot_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':lot_id' => $_POST['lot_id']]);
        $lot_price = $query->fetchColumn();

        // Get payment plan details
        $sql = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':payment_plan_id' => $_POST['payment_plan']]);
        $payment_plan = $query->fetch(PDO::FETCH_ASSOC);

        // Calculate monthly payment and balance
        $down_payment_percentage = $payment_plan['down_payment'] / 100;
        $interest_rate = $payment_plan['interest_rate'] / 100;
        $duration = $payment_plan['duration'];

        $down_payment = $lot_price * $down_payment_percentage;
        $principal = $lot_price - $down_payment;
        $interest = $principal * $interest_rate;
        $total = $principal + $interest;
        
        $monthly_payment = $duration > 0 ? $total / $duration : 0;
        $balance = $lot_price + $interest;

        // Create reservation
        $sql = "INSERT INTO reservation (account_id, lot_id, payment_plan_id, reservation_date, monthly_payment, balance, request) 
                VALUES (:account_id, :lot_id, :payment_plan_id, NOW(), :monthly_payment, :balance, 'Pending')";
        $query = $db->connect()->prepare($sql);
        $query->execute([
            ':account_id' => $account_id,
            ':lot_id' => $_POST['lot_id'],
            ':payment_plan_id' => $_POST['payment_plan'],
            ':monthly_payment' => $monthly_payment,
            ':balance' => $balance
        ]);

        // Update lot status
        $sql = "UPDATE lots SET status = 'On Request' WHERE lot_id = :lot_id";
        $query = $db->connect()->prepare($sql);
        $query->execute([':lot_id' => $_POST['lot_id']]);

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
                'price' => $lot_price
            ],
            'payment' => [
                'down_payment' => $down_payment,
                'monthly_payment' => $monthly_payment,
                'total_balance' => $balance,
                'interest_rate' => $interest_rate,
                'payment_duration' => $duration
            ]
        ];
        
        $name = $_POST['account_type'] === 'new' ? $_POST['first_name'] . ' ' . $_POST['last_name'] : $existing_account['first_name'] . ' ' . $existing_account['last_name'];
        echo "<script>alert('Reservation created successfully!'); window.location.href='reservation_summary.php';</script>";
        exit();
    } catch (Exception $e) {
        $db->connect()->rollBack();
        echo "<script>alert('Error creating reservation: " . $e->getMessage() . "'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }
}

if (isset($_SESSION['show_summary']) && $_SESSION['show_summary']) {
    $details = $_SESSION['reservation_details'];
    unset($_SESSION['show_summary']); // Clear flag after showing
?>
    <div class="modal fade" id="summaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reservation Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4>Garden of Eternal Life</h4>
                        <p>Reservation Details</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Account Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Name:</th>
                                    <td><?php echo $details['account']['first_name'] . ' ' . $details['account']['middle_name'] . ' ' . $details['account']['last_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo $details['account']['email']; ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo $details['account']['phone_number']; ?></td>
                                </tr>
                                <tr class="table-warning">
                                    <th>Username:</th>
                                    <td><?php echo $details['account']['username']; ?></td>
                                </tr>
                                <tr class="table-warning">
                                    <th>Password:</th>
                                    <td><?php echo $details['account']['password']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Payment Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Lot Price:</th>
                                    <td>₱<?php echo number_format($details['lot']['price'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Down Payment:</th>
                                    <td>₱<?php echo number_format($details['payment']['down_payment'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Monthly Payment:</th>
                                    <td>₱<?php echo number_format($details['payment']['monthly_payment'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Interest Rate:</th>
                                    <td><?php echo $details['payment']['interest_rate']; ?>%</td>
                                </tr>
                                <tr>
                                    <th>Payment Duration:</th>
                                    <td><?php echo $details['payment']['payment_duration']; ?> months</td>
                                </tr>
                                <tr>
                                    <th>Total Balance:</th>
                                    <td>₱<?php echo number_format($details['payment']['total_balance'], 2); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-center"><small>Please keep this information secure.</small></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('#summaryModal').modal('show');
            
            // After modal is closed, redirect to reservations page
            $('#summaryModal').on('hidden.bs.modal', function () {
                window.location.href = '../reservations.php';
            });
        });
    </script>
<?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reservation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid px-4 py-4">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Add New Reservation</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <!-- Account Selection -->
                            <div class="mb-4">
                                <label class="form-label">Account Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="account_type" id="new_account" value="new" checked>
                                    <label class="btn btn-outline-primary" for="new_account">Create New Account</label>
                                    
                                    <input type="radio" class="btn-check" name="account_type" id="existing_account" value="existing">
                                    <label class="btn btn-outline-primary" for="existing_account">Select Existing Account</label>
                                </div>
                            </div>

                            <!-- New Account Form -->
                            <div id="new_account_form">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="middle_name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Account Selection -->
                            <div id="existing_account_form" style="display: none;">
                                <div class="mb-3">
                                    <label for="existing_account" class="form-label">Select Account</label>
                                    <select class="form-select" id="existing_account_select" name="existing_account">
                                        <option value="">Select an account</option>
                                        <?php foreach ($existing_accounts as $account): ?>
                                            <option value="<?= $account['account_id'] ?>"><?= $account['full_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Lot Selection -->
                            <div class="mb-3">
                                <label for="lot_id" class="form-label">Select Available Lot</label>
                                <select class="form-select" id="lot_id" name="lot_id" required>
                                    <option value="">Select a lot</option>
                                    <?php foreach ($available_lots as $lot): ?>
                                        <option value="<?= $lot['lot_id'] ?>" data-price="<?= $lot['price'] ?>">
                                            <?= $lot['lot_name'] ?> - <?= $lot['location'] ?> (₱<?= number_format($lot['price'], 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Payment Plan -->
                            <div class="mb-3">
                                <label for="payment_plan" class="form-label">Payment Plan</label>
                                <select class="form-select" id="payment_plan" name="payment_plan" required>
                                    <option value="">Select Payment Plan</option>
                                    <?php foreach ($payment_plans as $plan): ?>
                                        <option value="<?= $plan['payment_plan_id'] ?>" 
                                                data-duration="<?= $plan['duration'] ?>"
                                                data-downpayment="<?= $plan['down_payment'] ?>"
                                                data-interest="<?= $plan['interest_rate'] ?>">
                                            <?= $plan['plan'] . ' (' . $plan['down_payment'] . '% DP, ' . $plan['interest_rate'] . '% Interest)'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Payment Details Preview -->
                            <div id="payment_details" class="mb-3" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Payment Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">Lot Price: <span id="lot_price">₱0.00</span></p>
                                                <p class="mb-1">Down Payment: <span id="down_payment">₱0.00</span></p>
                                                <p class="mb-1">Interest Rate: <span id="interest">0</span>%</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">Monthly Payment: <span id="monthly">₱0.00</span></p>
                                                <p class="mb-1">Duration: <span id="duration">0</span> months</p>
                                                <p class="mb-1">Total Balance: <span id="total">₱0.00</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="../reservations.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Reservation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#existing_account_select, #lot_id, #payment_plan').select2();

            // Handle account type toggle
            $('input[name="account_type"]').change(function() {
                if ($(this).val() === 'new') {
                    $('#new_account_form').show();
                    $('#existing_account_form').hide();
                    $('#existing_account_select').prop('required', false);
                    $('#new_account_form input').prop('required', true);
                } else {
                    $('#new_account_form').hide();
                    $('#existing_account_form').show();
                    $('#existing_account_select').prop('required', true);
                    $('#new_account_form input').prop('required', false);
                }
            });

            // Form validation before submit
            $('form').on('submit', function(e) {
                const accountType = $('input[name="account_type"]:checked').val();
                
                if (accountType === 'new') {
                    // Check if all required new account fields are filled
                    const firstName = $('#first_name').val().trim();
                    const lastName = $('#last_name').val().trim();
                    const email = $('#email').val().trim();
                    const phone = $('#phone_number').val().trim();
                    
                    if (!firstName || !lastName || !email || !phone) {
                        e.preventDefault();
                        alert('Please fill in all required account information fields');
                        return false;
                    }
                } else {
                    // Check if existing account is selected
                    if (!$('#existing_account_select').val()) {
                        e.preventDefault();
                        alert('Please select an existing account');
                        return false;
                    }
                }

                // Check if lot and payment plan are selected
                if (!$('#lot_id').val() || !$('#payment_plan').val()) {
                    e.preventDefault();
                    alert('Please select both lot and payment plan');
                    return false;
                }
            });

            // Calculate payment details
            function updatePaymentDetails() {
                const lotSelect = $('#lot_id');
                const planSelect = $('#payment_plan');
                
                if (lotSelect.val() && planSelect.val()) {
                    const lotPrice = parseFloat(lotSelect.find(':selected').data('price'));
                    const duration = parseInt(planSelect.find(':selected').data('duration'));
                    const downPaymentPercentage = parseFloat(planSelect.find(':selected').data('downpayment'));
                    const interestRate = parseFloat(planSelect.find(':selected').data('interest'));

                    const downPayment = (lotPrice * downPaymentPercentage) / 100;
                    const principal = lotPrice - downPayment;
                    const interest = principal * (interestRate / 100);
                    const total = principal + interest;
                    const monthly = duration > 0 ? total / duration : 0;

                    $('#lot_price').text('₱' + lotPrice.toFixed(2));
                    $('#down_payment').text('₱' + downPayment.toFixed(2));
                    $('#interest').text(interestRate);
                    $('#monthly').text('₱' + monthly.toFixed(2));
                    $('#duration').text(duration);
                    $('#total').text('₱' + (lotPrice + interest).toFixed(2));

                    $('#payment_details').show();
                } else {
                    $('#payment_details').hide();
                }
            }

            $('#lot_id, #payment_plan').change(updatePaymentDetails);
        });
    </script>
</body>
</html>