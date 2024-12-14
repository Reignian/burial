<?php
session_start();
require_once ('accounts.class.php');

$burialObj = new Accounts_class();

if (isset($_POST['account_id']) && isset($_POST['reason'])) {
    $account_id = $_POST['account_id'];
    $reason = $_POST['reason'];
    
    if (!$burialObj->hasActiveReservations($account_id)) {
        if ($burialObj->deleteAccount($account_id, $reason)) {
            echo "<script>
                alert('Account has been deleted successfully.');
                window.location.href = '../accounts.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete account.');
                window.location.href = '../accounts.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Cannot delete account with active reservations.');
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
