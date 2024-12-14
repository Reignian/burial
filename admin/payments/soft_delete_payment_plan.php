<?php
session_start();
require_once ('payments.class.php');
require_once ('../staffs/staffs.class.php');

$burialObj = new Payments_class();
$staffObj = new Staffs_class();

if (isset($_GET['id'])) {
    $payment_plan_id = $_GET['id'];
    
    // Get payment plan details before deletion
    $plan = $burialObj->getPaymentPlan($payment_plan_id);
    
    if ($burialObj->softDeletePaymentPlan($payment_plan_id)) {
        // Log the deletion
        $details = sprintf(
            "Deleted payment plan:\nPlan Name: %s\nDuration: %d months\nDown Payment: %s%%\nInterest Rate: %s%%",
            $plan['plan'],
            $plan['duration'],
            $plan['down_payment'],
            $plan['interest_rate']
        );
        $staffObj->addStaffLog($_SESSION['account']['account_id'], "Delete Payment Plan", $details);
        
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
