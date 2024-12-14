<?php
session_start();
require_once ('accounts.class.php');

$burialObj = new Accounts_class();

if (isset($_POST['account_id']) && isset($_POST['reason'])) {
    $account_id = $_POST['account_id'];
    $reason = $_POST['reason'];
    
    if ($burialObj->toggleBanStatus($account_id, $reason)) {
        echo "<script>
            alert('Account status has been updated successfully.');
            window.location.href = '../accounts.php';
        </script>";
    } else {
        echo "<script>
            alert('Failed to update account status.');
            window.location.href = '../accounts.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Missing required information.');
        window.location.href = '../accounts.php';
    </script>";
}
?>
