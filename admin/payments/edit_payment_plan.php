<?php
session_start();
require_once ('../../functions.php');
require_once ('payments.class.php');
require_once ('../staffs/staffs.class.php');

$burialObj = new Payments_class();
$staffObj = new Staffs_class();

$payment_plan_id = isset($_GET['id']) ? $_GET['id'] : null;
$plan = $duration = $down_payment = $interest_rate = '';
$planErr = $durationErr = $down_paymentErr = $interest_rateErr = '';

if (!$payment_plan_id) {
    die('No payment plan specified');
}

$paymentPlan = $burialObj->getPaymentPlan($payment_plan_id);

if (!$paymentPlan) {
    die('Payment plan not found');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plan = clean_input($_POST['plan']);
    $duration = clean_input($_POST['duration']);
    $down_payment = clean_input($_POST['down_payment']);
    $interest_rate = clean_input($_POST['interest_rate']);

    if (empty($plan)) {
        $planErr = 'Plan name is required';
    }

    if ($duration === '') {
        $durationErr = 'Duration is required';
    } else if (!is_numeric($duration) || $duration < 0) {
        $durationErr = 'Duration should be a non-negative number';
    }

    if ($down_payment === '') {
        $down_paymentErr = 'Down Payment is required';
    } else if (!is_numeric($down_payment) || $down_payment < 0) {
        $down_paymentErr = 'Down Payment should be a non-negative number';
    } else if ($down_payment > 100) {
        $down_paymentErr = 'Down Payment should not be greater than 100%';
    }

    if ($interest_rate === '') {
        $interest_rateErr = 'Interest rate is required';
    } else if (!is_numeric($interest_rate) || $interest_rate < 0) {
        $interest_rateErr = 'Interest rate should be a non-negative number';
    } else if ($interest_rate > 100) {
        $interest_rateErr = 'Interest rate should not be greater than 100%';
    }

    if (empty($planErr) && empty($durationErr) && empty($down_paymentErr) && empty($interest_rateErr)) {
        if ($burialObj->updatePaymentPlan($payment_plan_id, $plan, $duration, $down_payment, $interest_rate)) {
            // Track what fields were changed
            $changes = [];
            if ($paymentPlan['plan'] != $plan) {
                $changes[] = "Plan Name: {$paymentPlan['plan']} → {$plan}";
            }
            if ($paymentPlan['duration'] != $duration) {
                $changes[] = "Duration: {$paymentPlan['duration']} months → {$duration} months";
            }
            if ($paymentPlan['down_payment'] != $down_payment) {
                $changes[] = "Down Payment: {$paymentPlan['down_payment']}% → {$down_payment}%";
            }
            if ($paymentPlan['interest_rate'] != $interest_rate) {
                $changes[] = "Interest Rate: {$paymentPlan['interest_rate']}% → {$interest_rate}%";
            }

            if (!empty($changes)) {
                $logDetails = sprintf(
                    "Edited payment plan (%s):\n%s",
                    $paymentPlan['plan'],
                    implode("\n", $changes)
                );
                $staffObj->addStaffLog($_SESSION['account']['account_id'], "Edit Payment Plan", $logDetails);
            }
            
            header('location: ../payment_plans.php');
            exit;
        } else {
            echo 'Something went wrong when updating the payment plan';
        }
    }
} else {
    $plan = $paymentPlan['plan'];
    $duration = $paymentPlan['duration'];
    $down_payment = $paymentPlan['down_payment'];
    $interest_rate = $paymentPlan['interest_rate'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment Plan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f2f1;
            color: #263238;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #006064;
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            display: grid;
            gap: 20px;
        }
        label {
            font-weight: bold;
            color: #455a64;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #b2dfdb;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #006064;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #00838f;
        }
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .required {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Payment Plan</h1>
        <form action="" method="post">
            <div>
                <label for="plan">Plan Name <span class="required">*</span></label>
                <input type="text" name="plan" id="plan" value="<?= htmlspecialchars($plan) ?>">
                <?php if(!empty($planErr)): ?>
                    <span class="error"><?= $planErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="duration">Duration (months)<span class="required">*</span></label>
                <input type="number" name="duration" id="duration" value="<?= htmlspecialchars($duration) ?>" min="0">
                <?php if(!empty($durationErr)): ?>
                    <span class="error"><?= $durationErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="down_payment">Down Payment (%)<span class="required">*</span></label>
                <input type="number" name="down_payment" id="down_payment" value="<?= htmlspecialchars($down_payment) ?>" min="0" max="100" step="0.01">
                <?php if(!empty($down_paymentErr)): ?>
                    <span class="error"><?= $down_paymentErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="interest_rate">Interest rate (%)<span class="required">*</span></label>
                <input type="number" name="interest_rate" id="interest_rate" value="<?= htmlspecialchars($interest_rate) ?>" min="0" max="100" step="0.01">
                <?php if(!empty($interest_rateErr)): ?>
                    <span class="error"><?= $interest_rateErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <input type="submit" value="Update Payment Plan">
            </div>
        </form>
    </div>
</body>
</html>
