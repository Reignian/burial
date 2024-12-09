<?php
require_once '../functions.php';
require_once 'account.class.php';

session_start();

$error = '';
$success = '';
$accountObj = new Account();
$token = isset($_GET['token']) ? clean_input($_GET['token']) : '';

// Validate token
if (empty($token)) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    
    // Validate passwords
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if token is valid and not expired
        if ($accountObj->validateResetToken($token)) {
            // Update password
            if ($accountObj->resetPassword($token, $password)) {
                $success = "Password has been reset successfully. You can now login with your new password.";
                // Invalidate token
                $accountObj->invalidateResetToken($token);
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Invalid or expired reset token. Please request a new password reset.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: var(--primary-dark);
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-size: 14px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-dark);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #00796b;
        }

        .error-message {
            color: #d32f2f;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message {
            color: #388e3c;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-dark);
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . htmlspecialchars($token); ?>">
            <h2>Reset Password</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php else: ?>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            <?php endif; ?>
        </form>
        <a href="login.php" class="back-link">Back to Login</a>
    </div>
</body>
</html>
