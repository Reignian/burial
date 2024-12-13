<?php
session_start();
require_once __DIR__ . '/staffs.class.php';
require_once __DIR__ . '/../../functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_staff'])) {
    $staffObj = new Staffs_class();
    
    // Store the form data in session for repopulating the form
    $_SESSION['staff_form_data'] = $_POST;
    
    $first_name = clean_input($_POST['first_name']);
    $middle_name = clean_input($_POST['middle_name']);
    $last_name = clean_input($_POST['last_name']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    $email = clean_input($_POST['email']);
    $phone_number = clean_input($_POST['phone_number']);

    $errors = [];

    if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || 
        empty($confirm_password) || empty($email) || empty($phone_number)) {
        $errors[] = 'All fields are required';
    }

    if ($staffObj->usernameExist($username)) {
        $errors[] = 'Username already exists';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (!preg_match('/^(09|\+639)\d{9}$/', $phone_number)) {
        $errors[] = 'Invalid phone number format (09XXXXXXXXX or +639XXXXXXXXX)';
    }

    if (empty($errors)) {
        if ($staffObj->addStaff($first_name, $middle_name, $last_name, $username, $password, $email, $phone_number)) {
            $_SESSION['success_message'] = 'Staff member added successfully';
            unset($_SESSION['staff_form_data']);
            unset($_SESSION['staff_errors']);
        } else {
            $_SESSION['error_message'] = 'Failed to add staff member';
        }
    } else {
        $_SESSION['error_message'] = implode(', ', $errors);
    }
}

header('Location: ../staff.php');
exit();
?>
