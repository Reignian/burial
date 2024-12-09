<?php
session_start();
require_once 'sign/account.class.php';
require_once 'functions.php';

if (!isset($_SESSION['account']) || !isset($_SESSION['account']['account_id'])) {
    header('location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: account_profile.php');
    exit;
}

$accountObj = new Account();
$account_id = $_SESSION['account']['account_id'];

// Check if this is a password change request
if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
    $current_password = clean_input($_POST['current_password']);
    $new_password = clean_input($_POST['new_password']);
    $confirm_new_password = clean_input($_POST['confirm_new_password']);
    
    // Validate passwords
    $errors = [];
    
    if (empty($current_password)) {
        echo "Current password is required";
        exit;
    }
    
    if (empty($new_password)) {
        echo "New password is required";
        exit;
    } elseif (strlen($new_password) < 8) {
        echo "Password must be at least 8 characters long";
        exit;
    }
    
    if (empty($confirm_new_password)) {
        echo "Please confirm your new password";
        exit;
    } elseif ($new_password !== $confirm_new_password) {
        echo "Passwords do not match";
        exit;
    }
    
    if ($accountObj->changePassword($account_id, $current_password, $new_password)) {
        echo "Password changed successfully";
        exit;
    } else {
        echo "Current password is incorrect";
        exit;
    }
}

// Handle account information update
$first_name = clean_input($_POST['first_name']);
$middle_name = clean_input($_POST['middle_name']);
$last_name = clean_input($_POST['last_name']);
$username = clean_input($_POST['username']);
$email = clean_input($_POST['email']);
$phone_number = clean_input($_POST['phone_number']);

// Validate inputs
$errors = [];

if (empty($first_name)) {
    echo "First name is required";
    exit;
}

if (empty($last_name)) {
    echo "Last name is required";
    exit;
}

if (empty($username)) {
    echo "Username is required";
    exit;
} elseif ($accountObj->usernameExist($username, $account_id)) {
    echo "Username already exists";
    exit;
}

if (empty($email)) {
    echo "Email is required";
    exit;
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format";
    exit;
}

if (empty($phone_number)) {
    echo "Phone number is required";
    exit;
} elseif (!preg_match('/^(09|\+639)\d{9}$/', $phone_number)) {
    echo "Invalid phone number format";
    exit;
}

$accountObj->first_name = $first_name;
$accountObj->middle_name = $middle_name;
$accountObj->last_name = $last_name;
$accountObj->username = $username;
$accountObj->email = $email;
$accountObj->phone_number = $phone_number;

if ($accountObj->update($account_id)) {
    // Update session data
    $_SESSION['account']['first_name'] = $first_name;
    $_SESSION['account']['middle_name'] = $middle_name;
    $_SESSION['account']['last_name'] = $last_name;
    $_SESSION['account']['username'] = $username;
    $_SESSION['account']['email'] = $email;
    $_SESSION['account']['phone_number'] = $phone_number;
    
    echo "Account updated successfully";
    exit;
} else {
    echo "Failed to update account";
    exit;
}
