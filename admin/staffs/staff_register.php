<?php
session_start();
require_once __DIR__ . '/staffs.class.php';
require_once __DIR__ . '/../../functions.php';
require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        // Generate verification code
        $verification_code = sprintf("%06d", mt_rand(1, 999999));
        
        // Store staff data in session
        $_SESSION['temp_staff_data'] = [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'phone_number' => $phone_number
        ];
        $_SESSION['staff_verification_code'] = $verification_code;

        // Send verification email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'stoninoparishcemetery@gmail.com';
            $mail->Password = 'vbfq umvs ibff xxjv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('stoninoparishcemetery@gmail.com', 'Sto. Nino Parish Cemetery Office');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Staff Email Verification - Sto. Nino Parish Cemetery";
            $mail->Body = "
                <h2>Staff Email Verification</h2>
                <p>Hello " . htmlspecialchars($first_name) . ",</p>
                <p>You have been registered as a staff member. Please use the following verification code to complete your registration:</p>
                <h1 style='font-size: 24px; color: #006064; letter-spacing: 2px;'>" . $verification_code . "</h1>
                <p>This code will expire in 30 minutes.</p>
                <p>If you did not expect this registration, please contact the administrator.</p>
                <p>Best regards,<br>Sto. Nino Parish Cemetery Office</p>";

            $mail->send();
            header('Location: verify_staff_email.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to send verification email. Please try again.";
            header('Location: ../staff.php');
            exit;
        }
    } else {
        $_SESSION['error_message'] = implode(', ', $errors);
        header('Location: ../staff.php');
        exit;
    }
}

header('Location: ../staff.php');
exit();
?>
