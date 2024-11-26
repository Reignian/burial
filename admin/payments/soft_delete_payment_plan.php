<?php
require_once ('payments.class.php');

$burialObj = new Payments_class();

if (isset($_GET['id'])) {
    $payment_plan_id = $_GET['id'];
    
    if ($burialObj->softDeletePaymentPlan($payment_plan_id)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
