<?php
session_start();
require_once __DIR__ . '/accounts.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_SESSION['account']['account_id'];
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $username = trim($_POST['username']);

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number) || empty($username)) {
        echo "<script>alert('All required fields must be filled out.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    // Validate phone number (11 digits)
    if (!preg_match('/^[0-9]{11}$/', $phone_number)) {
        echo "<script>alert('Phone number must be 11 digits.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    $accounts = new Accounts_class();
    try {
        if ($accounts->updateAccount($account_id, $first_name, $middle_name, $last_name, $email, $phone_number, $username)) {
            // Update session data
            $_SESSION['account']['first_name'] = $first_name;
            $_SESSION['account']['middle_name'] = $middle_name;
            $_SESSION['account']['last_name'] = $last_name;
            $_SESSION['account']['email'] = $email;
            $_SESSION['account']['phone_number'] = $phone_number;
            $_SESSION['account']['username'] = $username;

            echo "<script>alert('Account information updated successfully.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        } else {
            echo "<script>alert('Failed to update account information.'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('" . addslashes($e->getMessage()) . "'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
    }
    exit();
} else {
    header('Location: ../dashboard.php');
    exit();
}
