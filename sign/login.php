<?php

   require_once '../functions.php';
    require_once 'account.class.php';

    session_start();

    $username = $password = '';
    $accountObj = new Account();
    $loginErr = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = clean_input($_POST['username']);
        $password = clean_input($_POST['password']);

        if (empty($username) && empty($password)) {
            $loginErr = 'Username and password is required';
        } elseif (empty($username)) {
            $loginErr = 'Username is required';
        } elseif (empty($password)) {
            $loginErr = 'Password is required';
        }

        elseif ($accountObj->login($username, $password)) {
            $data = $accountObj->fetch($username);
            
            // Check if the account is banned
            if ($accountObj->getAccountBanStatus($data['account_id'])) {
                $loginErr = 'This account has been banned. Please contact the administrator.';
            } else {
                $_SESSION['account'] = $data;

                // Redirect based on account type
                if ($_SESSION['account']['is_admin']) {
                    header('location: ../admin/dashboard.php');
                } elseif ($_SESSION['account']['is_customer']) {
                    header('location: ../index.php');
                }
                exit();
            }
        } else {
            $loginErr = 'Invalid username/password';
        }
    } else {
        // If user is already logged in, redirect accordingly
        if (isset($_SESSION['account'])) {
            if ($_SESSION['account']['is_customer']) {
                header('location: ../index.php');
                exit();
            } elseif ($_SESSION['account']['is_admin']) {
                header('location: ../admin/dashboard.php');
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        input[type="password"] {
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
        input[type="text"]:not(:placeholder-shown),
        input[type="password"]:not(:placeholder-shown) {
            outline: none;
            border-color: var(--primary-dark);
        }

        input[type="text"]:focus + label,
        input[type="password"]:focus + label,
        input[type="text"]:not(:placeholder-shown) + label,
        input[type="password"]:not(:placeholder-shown) + label {
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
            text-align: center;
            font-size: 14px;
            margin-top: 15px;
        }

        .signup-link {
            display: block;
            text-align: start;
            margin-top: 20px;
            color: var(--accent);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 14px;
        }

        .signup-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h2>Login</h2>
            <div class="input-group">
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" placeholder=" ">
                <label for="username">Username</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" value="<?php echo $password; ?>" placeholder=" ">
                <label for="password">Password</label>
                <?php if (!empty($loginErr)): ?>
                <div class="error"><?php echo $loginErr; ?></div>
            <?php endif; ?>
            <a href="forgot_password.php" class="signup-link" style="text-align: end;">Forgot Password?</a>

            </div>
            <input type="submit" value="Log In" name="login">
            <a href="signup.php" class="signup-link">Doesn't have an account? Sign Up</a>
        </form>
    </div>
</body>
</html>
