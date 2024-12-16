<?php
session_start();
require_once __DIR__ . '/accounts.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_SESSION['account']['account_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('All password fields are required.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    // Validate password strength (at least 8 characters)
    if (strlen($new_password) < 8) {
        echo "<script>alert('New password must be at least 8 characters long.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    $accounts = new Accounts_class();
    try {
        if ($accounts->changePassword($account_id, $current_password, $new_password)) {
            echo "<script>alert('Password changed successfully.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        } else {
            echo "<script>alert('Failed to change password.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('" . addslashes($e->getMessage()) . "'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
    }
    exit();
} else {
    header('Location: ../dashboard.php');
    exit();
}
