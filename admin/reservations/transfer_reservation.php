<?php
session_start();
require_once __DIR__ . '/reservations.class.php';
require_once __DIR__ . '/../accounts/accounts.class.php';

if(isset($_SESSION['account'])){
    if(!($_SESSION['account']['is_admin'] || $_SESSION['account']['is_staff'])){
        header('location: ../sign/login.php');
    }
}else{
    header('location: ../sign/login.php');
}

$burialObj = new Reservation_class();
$accountObj = new Accounts_class();

// Get reservation details
$reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : null;
$accountname = isset($_GET['name']) ? $_GET['name'] : '';
$lot = isset($_GET['lot']) ? $_GET['lot'] : '';

// Get current reservation's account ID
$currentReservation = $burialObj->getReservationById($reservation_id);
$currentAccountId = $currentReservation['account_id'] ?? null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['transfer_reservation'])) {
        $newAccountId = $_POST['new_account_id'];
        $result = $burialObj->transferReservation($_POST['reservation_id'], $newAccountId);
        
        if ($result === true) {
            echo "<script>
                    alert('Reservation transferred successfully.');
                    window.location.href = '../reservations.php';
                  </script>";
        } else {
            echo "<script>alert('Error: " . addslashes($result) . "');</script>";
        }
    } elseif (isset($_POST['create_and_transfer'])) {
        // Set account properties
        $accountObj->first_name = $_POST['first_name'];
        $accountObj->middle_name = $_POST['middle_name'];
        $accountObj->last_name = $_POST['last_name'];
        $accountObj->phone_number = $_POST['phone_number'];
        $accountObj->email = $_POST['email'];
        $accountObj->username = $_POST['username'];
        $accountObj->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $newAccountId = $accountObj->createAccount();
            if ($newAccountId) {
                // Transfer the reservation to the new account
                $result = $burialObj->transferReservation($_POST['reservation_id'], $newAccountId);
                if ($result === true) {
                    echo "<script>
                            alert('Account created and reservation transferred successfully.');
                            window.location.href = '../reservations.php';
                          </script>";
                } else {
                    echo "<script>alert('Error transferring reservation: " . addslashes($result) . "');</script>";
                }
            } else {
                echo "<script>alert('Error creating account.');</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Get all accounts for dropdown
$accounts = $accountObj->showALL_account();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background-color: #f5f5f5;">

<div class="container-fluid p-4" style="max-width: 1400px; margin: 0 auto;">
    <section id="transfer-reservation">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase">Transfer Reservation</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <!-- Current Reservation Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Reservation Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Account</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($accountname) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lot</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($lot) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer Type Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transfer Type</h5>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="transfer_type" id="existing_account" value="existing" checked>
                            <label class="btn btn-outline-primary" for="existing_account">Existing Account</label>
                            
                            <input type="radio" class="btn-check" name="transfer_type" id="new_account" value="new">
                            <label class="btn btn-outline-primary" for="new_account">New Account</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Existing Account Form -->
            <div class="col-md-6">
                <div class="card h-100" id="existing_account_card">
                    <div class="card-body">
                        <h5 class="card-title">Transfer to Existing Account</h5>
                        <form method="POST" id="existing_account_form">
                            <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($reservation_id) ?>">
                            <div class="mb-3">
                                <label for="new_account_id" class="form-label">Select Existing Account</label>
                                <select name="new_account_id" id="new_account_id" class="form-select" required>
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account): 
                                        // Skip the current account holder
                                        if ($account['account_id'] == $currentAccountId) continue;
                                    ?>
                                        <option value="<?= $account['account_id'] ?>">
                                            <?= htmlspecialchars($account['first_name'] . ' ' . $account['middle_name'] . ' ' . $account['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="transfer_reservation" class="btn btn-primary">
                                    Transfer to Existing Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- New Account Form -->
            <div class="col-md-6">
                <div class="card h-100" id="new_account_card">
                    <div class="card-body">
                        <h5 class="card-title">Create New Account</h5>
                        <form method="POST" id="new_account_form">
                            <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($reservation_id) ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" name="phone_number" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="create_and_transfer" class="btn btn-primary">
                                    Create Account and Transfer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <a href="../reservations.php" class="btn btn-secondary w-100">Cancel</a>
            </div>
        </div>
    </section>
</div>

<style>
:root {
    --primary-color: #006064;
    --primary-hover: #00838f;
    --primary-light: #00acc1;
}

body {
    min-height: 100vh;
    background-color: #f5f5f5;
}

.display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: var(--primary-color) !important;
}

.btn-primary {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.btn-primary:hover {
    background-color: var(--primary-light) !important;
    border-color: var(--primary-light) !important;
}

.btn-outline-primary {
    color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.btn-outline-primary:hover,
.btn-check:checked + .btn-outline-primary {
    color: #fff !important;
    background-color: var(--primary-color) !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: none;
    margin-bottom: 1rem;
}

.card-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.form-control:focus, 
.form-select:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 0.25rem rgba(0, 96, 100, 0.25);
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem !important;
    }
    
    .row {
        margin: 0 !important;
    }
    
    .col-md-6 {
        padding: 0 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const transferType = document.getElementsByName('transfer_type');
    const existingForm = document.getElementById('existing_account_form');
    const newForm = document.getElementById('new_account_form');
    const existingCard = document.getElementById('existing_account_card');
    const newCard = document.getElementById('new_account_card');

    function toggleForms() {
        const selectedValue = document.querySelector('input[name="transfer_type"]:checked').value;
        if (selectedValue === 'existing') {
            existingCard.style.opacity = '1';
            newCard.style.opacity = '0.5';
            existingForm.style.display = 'block';
            newForm.style.display = 'none';
            newForm.querySelectorAll('input, textarea').forEach(input => input.required = false);
            existingForm.querySelectorAll('select').forEach(select => select.required = true);
        } else {
            existingCard.style.opacity = '0.5';
            newCard.style.opacity = '1';
            existingForm.style.display = 'none';
            newForm.style.display = 'block';
            newForm.querySelectorAll('input[required], textarea[required]').forEach(input => input.required = true);
            existingForm.querySelectorAll('select').forEach(select => select.required = false);
        }
    }

    transferType.forEach(radio => {
        radio.addEventListener('change', toggleForms);
    });

    // Initialize form state
    toggleForms();
});
</script>
</body>
</html>
