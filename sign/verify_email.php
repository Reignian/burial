<?php
require_once '../functions.php';
require_once 'account.class.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['temp_signup_data'])) {
    header('Location: signup.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verification_code'])) {
        $entered_code = clean_input($_POST['verification_code']);
        
        if ($entered_code == $_SESSION['verification_code']) {
            // Create the account
            $accountObj = new Account();
            $data = $_SESSION['temp_signup_data'];
            
            $accountObj->first_name = $data['first_name'];
            $accountObj->middle_name = $data['middle_name'];
            $accountObj->last_name = $data['last_name'];
            $accountObj->username = $data['username'];
            $accountObj->password = $data['password'];
            $accountObj->email = $data['email'];
            $accountObj->phone_number = $data['phone_number'];
            
            if ($accountObj->add()) {
                // Clear session data
                unset($_SESSION['temp_signup_data']);
                unset($_SESSION['verification_code']);
                
                // Show success message and redirect
                echo "<script>
                    alert('Email verification successful! Your account has been created.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            } else {
                $error = "Failed to create account. Please try again.";
            }
        } else {
            echo "<script>alert('Invalid verification code. Please try again.');</script>";
            $error = "Invalid verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');

        :root {
            --primary-dark: #006064;
            --primary-light: #e0f2f1;
            --secondary: #b2dfdb;
            --accent: #455a64;
            --text-dark: #263238;
            --text-light: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-light);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            background-color: var(--text-light);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: var(--primary-dark);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--secondary);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-dark);
        }

        .error {
            color: #d32f2f;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-dark);
            color: var(--text-light);
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #00838f;
        }

        .success {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 1rem;
        }

        .resend {
            text-align: center;
            margin-top: 1rem;
        }

        .resend a {
            color: var(--primary-dark);
            text-decoration: none;
        }

        .resend a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="error" style="text-align: center; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-bottom: 2rem;">
            Please enter the verification code sent to your email address.
        </p>

        <form method="POST" action="">
            <div class="input-group">
                <label for="verification_code">Verification Code</label>
                <input type="text" id="verification_code" name="verification_code" required>
            </div>
            <button type="submit">Verify Email</button>
        </form>

        <div class="resend">
            <a href="signup.php">Back to Signup</a>
        </div>
    </div>
</body>
</html>
