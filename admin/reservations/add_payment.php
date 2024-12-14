<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once ('../../functions.php');
require_once ('reservations.class.php');

$reservation_id = $amount_paid = '';
$reservation_idErr = $amount_paidErr = '';

$burialObj = new Reservation_class();

$reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
$name = isset($_GET['name']) ? urldecode($_GET['name']) : '';
$lot = isset($_GET['lot']) ? urldecode($_GET['lot']) : '';

// Get payment plan and balance if reservation_id is set
$payment_plan = '';
$balance = 0;
if (!empty($reservation_id)) {
    $result = $burialObj->getReservationDetails($reservation_id);
    if ($result) {
        $payment_plan = $result['plan'];
        $balance = $result['balance'];
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $reservation_id = clean_input(($_POST['reservation_id']));
    $amount_paid = clean_input(($_POST['amount_paid']));

    if(empty($reservation_id)){
        $reservation_idErr = 'Reservation is required';
    }

    if(empty($amount_paid)){
        $amount_paidErr = 'Payment is required';
    } else if (!is_numeric($amount_paid)){
        $amount_paidErr = 'Payment should be a number';
    } else if ($amount_paid < 1){
        $amount_paidErr = 'Payment must be greater than 0';
    }

    if(empty($reservation_idErr) && empty($amount_paidErr)){
        $burialObj->reservation_id = $reservation_id;
        $burialObj->amount_paid = $amount_paid;

        if($burialObj->addPayment($reservation_id, $amount_paid)){
            header('location: ../reservations.php');
            exit;
        } else {
            echo 'Something went wrong when adding new product';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment</title>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <style>
        :root {
            --deep-teal: #006064;
            --light-teal: #e0f2f1;
            --medium-teal: #b2dfdb;
            --blue-grey: #455a64;
            --dark-blue-grey: #263238;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-teal);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .payment-container {
            background-color: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .form-title {
            color: var(--deep-teal);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .required-notice {
            color: #dc3545;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: var(--blue-grey);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--medium-teal);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: var(--deep-teal);
        }

        input[type="text"][readonly] {
            background-color: var(--light-teal);
            cursor: not-allowed;
        }

        .date-display {
            text-align: center;
            color: var(--blue-grey);
            font-size: 1.1rem;
            margin: 1.5rem 0;
            padding: 0.75rem;
            background-color: var(--light-teal);
            border-radius: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background-color: var(--deep-teal);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: var(--dark-blue-grey);
            transform: translateY(-2px);
        }

        .error-field {
            border-color: #dc3545 !important;
        }

        .info-text {
            display: block;
            color: #455a64;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Custom styling for jQuery UI Autocomplete */
        .ui-autocomplete {
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--medium-teal);
        }

        .ui-menu-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .ui-menu-item:hover {
            background-color: var(--light-teal);
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1 class="form-title">Add Payment</h1>
        
        <form action="" method="post">

            <div class="form-group">
                <label for="reservation_id">Name</label>
                <input type="text" 
                       name="reservation_id_display" 
                       id="reservation_autocomplete" 
                       value="<?= htmlspecialchars($name) ?>" 
                       readonly
                       class="<?= !empty($reservation_idErr) ? 'error-field' : '' ?>">
                <input type="hidden" 
                       name="reservation_id" 
                       id="reservation_id_hidden" 
                       value="<?= htmlspecialchars($reservation_id) ?>">
            </div>

            <div class="form-group">
                <label for="amount_paid">Amount <span class="error">*</span></label>
                <input type="number" 
                       name="amount_paid" 
                       value="<?= ($payment_plan === 'Spot Cash') ? $balance : (isset($_POST['amount_paid']) ? $amount_paid : '') ?>"
                       class="<?= !empty($amount_paidErr) ? 'error-field' : '' ?>"
                       <?= ($payment_plan === 'Spot Cash') ? 'readonly' : '' ?>
                       placeholder="Enter payment amount">
                <?php if (!empty($amount_paidErr)): ?>
                    <span class="error"><?= $amount_paidErr ?></span>
                <?php endif; ?>
                <?php if ($payment_plan === 'Spot Cash'): ?>
                    <span class="info-text">Full payment required for Spot Cash plan</span>
                <?php endif; ?>
            </div>

            <div class="date-display">
                <?= date("F j, Y") ?>
            </div>

            <button type="submit" class="submit-btn">Add Payment</button>
        </form>
    </div>
</body>
</html>