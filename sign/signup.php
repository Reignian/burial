<?php
    require_once '../functions.php';
    require_once 'account.class.php';
    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    session_start();

    $first_name = $middle_name = $last_name = $username = $password = $confirm_password = $email = $phone_number = '';
    $first_nameErr = $last_nameErr = $usernameErr = $passwordErr = $confirm_passwordErr = $emailErr = $phone_numberErr = '';
    $accountObj = new Account();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $first_name = clean_input(($_POST['first_name']));
        $middle_name = clean_input(($_POST['middle_name']));
        $last_name = clean_input(($_POST['last_name']));
        $username = clean_input(($_POST['username']));
        $password = clean_input($_POST['password']);
        $confirm_password = clean_input($_POST['confirm_password']);
        $email = clean_input($_POST['email']);
        $phone_number = clean_input($_POST['phone_number']);

        if(empty($first_name)){
            $first_nameErr = 'First Name is required';
        }

        if(empty($last_name)){
            $last_nameErr = 'Last Name is required';
        }

        if(empty($username)){
            $usernameErr = 'Username is required';
        } else if ($accountObj->usernameExist($username)){
            $usernameErr = 'Username already exists';
        }

        if (empty($password)) {
            $passwordErr = 'Password is required';
        } elseif (strlen($password) < 8) {
            $passwordErr = 'Password must be at least 8 characters long.';
        }

        if (empty($confirm_password)) {
            $confirm_passwordErr = 'Confirm password is required';
        } else if ($password !== $confirm_password) {
            $confirm_passwordErr = 'Passwords do not match';
        }

        if (empty($email)) {
            $emailErr = 'Email is required';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = 'The email address is invalid.';
        } else if ($accountObj->emailExists($email)) {
            $emailErr = 'This email is already registered';
        }

        $phone = preg_match('/^(09|\+639)\d{9}$/',$phone_number);

        if(empty($phone_number)){
            $phone_numberErr = 'Phone number is required';
        } else if (!$phone){
            $phone_numberErr = 'Invalid phone number';
        }

        if(empty($first_nameErr) && empty($last_nameErr) && empty($usernameErr) && empty($passwordErr) && empty($confirm_passwordErr) && empty($emailErr) && empty($phone_numberErr)){
            // Generate verification code
            $verification_code = sprintf("%06d", mt_rand(1, 999999));
            
            // Store signup data in session
            $_SESSION['temp_signup_data'] = [
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'phone_number' => $phone_number
            ];
            $_SESSION['verification_code'] = $verification_code;

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
                $mail->Subject = "Email Verification - Sto. Nino Parish Cemetery";
                $mail->Body = "
                    <h2>Email Verification</h2>
                    <p>Hello " . htmlspecialchars($first_name) . ",</p>
                    <p>Thank you for signing up. Please use the following verification code to complete your registration:</p>
                    <h1 style='font-size: 24px; color: #006064; letter-spacing: 2px;'>" . $verification_code . "</h1>
                    <p>This code will expire in 30 minutes.</p>
                    <p>If you did not request this verification, please ignore this email.</p>
                    <p>Best regards,<br>Sto. Nino Parish Cemetery Office</p>";

                $mail->send();
                header('Location: verify_email.php');
                exit;
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
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
            margin-top: 2vw;
            margin-bottom: 2vw;
        }

        .container {
            background-color: var(--text-light);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }

        form {
            position: relative;
            z-index: 1;
        }

        h2 {
            text-align: center;
            color: var(--primary-dark);
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            position: absolute;
            left: 0;
            top: 10px;
            color: var(--accent);
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 2px solid var(--secondary);
            background-color: transparent;
            font-size: 16px;
            color: var(--text-dark);
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="text"]:not(:placeholder-shown),
        input[type="password"]:not(:placeholder-shown),
        input[type="email"]:not(:placeholder-shown),
        input[type="tel"]:not(:placeholder-shown) {
            outline: none;
            border-color: var(--primary-dark);
        }

        input[type="text"]:focus + label,
        input[type="password"]:focus + label,
        input[type="email"]:focus + label,
        input[type="tel"]:focus + label,
        input[type="text"]:not(:placeholder-shown) + label,
        input[type="password"]:not(:placeholder-shown) + label,
        input[type="email"]:not(:placeholder-shown) + label,
        input[type="tel"]:not(:placeholder-shown) + label {
            top: -20px;
            font-size: 12px;
            color: var(--primary-dark);
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-dark);
            color: var(--text-light);
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        input[type="submit"]:hover {
            background-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .error {
            color: #d32f2f;
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="" method="post">
            <h2>SIGNUP</h2>
            <div class="input-group">
                <input type="text" name="first_name" id="first_name" value="<?=$first_name?>" required placeholder=" ">
                <label for="first_name">First Name <?php if (!empty($first_nameErr)): ?>
                <span class="error"><?= $first_nameErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="text" name="middle_name" id="middle_name" value="<?=$middle_name?>" placeholder=" ">
                <label for="middle_name">Middle Name</label>
            </div>

            <div class="input-group">
                <input type="text" name="last_name" id="last_name" value="<?=$last_name?>" required placeholder=" ">
                <label for="last_name">Last Name <?php if (!empty($last_nameErr)): ?>
                <span class="error"><?= $last_nameErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="text" name="username" id="username" value="<?=$username?>" required placeholder=" ">
                <label for="username">Username <?php if (!empty($usernameErr)): ?>
                <span class="error"><?= $usernameErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" value="<?=$password?>" required placeholder=" ">
                <label for="password">Password <?php if (!empty($passwordErr)): ?>
                <span class="error"><?= $passwordErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" value="<?=$confirm_password?>" required placeholder=" ">
                <label for="confirm_password">Confirm Password <?php if (!empty($confirm_passwordErr)): ?>
                <span class="error"><?= $confirm_passwordErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="email" name="email" id="email" value="<?=$email?>" required placeholder=" ">
                <label for="email">Email <?php if (!empty($emailErr)): ?>
                <span class="error"><?= $emailErr ?></span>
            <?php endif; ?></label>
            </div>

            <div class="input-group">
                <input type="tel" name="phone_number" id="phone_number" value="<?=$phone_number?>" required placeholder=" ">
                <label for="phone_number">Phone Number <?php if (!empty($phone_numberErr)): ?>
                <span class="error"><?= $phone_numberErr ?></span>
            <?php endif; ?></label>
            </div>

            <input type="submit" value="Signup" name="Signup">
        </form>

        <a href="login.php" class="login-link">Already have an account? Log in</a>
    </div>
</body>
</html>
