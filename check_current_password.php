<?php
session_start();
require_once __DIR__ . '/sign/account.class.php';

if (!isset($_SESSION['account']) || !isset($_SESSION['account']['account_id'])) {
    echo 'invalid';
    exit;
}

if (!isset($_POST['current_password'])) {
    echo 'invalid';
    exit;
}

$account = new Account();
$current_password = $_POST['current_password'];
$username = $_SESSION['account']['username'];

// Use the login method to verify the password
if ($account->login($username, $current_password)) {
    echo 'valid';
} else {
    echo 'invalid';
}
