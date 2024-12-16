<?php

  session_start();
  require_once __DIR__ . '/../notifications/notifications.class.php';

    if(isset($_SESSION['account'])){
        if(!($_SESSION['account']['is_admin'] || $_SESSION['account']['is_staff'])){
            header('location: ../sign/login.php');
        }
    }else{
        header('location: ../sign/login.php');
    }

    $notifObj = new Notifications_class();
    $pendingCount = $notifObj->getPendingNotificationsCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #006064;
            padding-top: 20px;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            color: white;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 15px 20px;
            transition: all 0.3s;
        }

        .sidebar-menu li:hover {
            background-color: #00838f;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .sidebar-menu .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
            transition: all 0.3s;
            width: calc(100% - 250px);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                height: 100vh;
                position: fixed;
            }
            .content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }

        /* New media query for mobile devices */
        @media (max-width: 576px) {
            .sidebar {
                width: 70px;
                height: 100vh;
                position: fixed;
            }
            .content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
            .sidebar-header {
                padding: 10px 5px;
            }
            .sidebar-header h3 {
                display: none;
            }
            .menu-text {
                display: none;
            }
            .sidebar-menu li {
                padding: 15px 0;
                display: flex;
                justify-content: center;
            }
            .sidebar-menu .icon {
                margin-right: 0;
                font-size: 20px;
            }
            
            .payments-menu .submenu {
                position: absolute;
                left: 100%;
                top: 0;
                width: 200px;
                z-index: 1000;
            }
            
            .payments-menu .submenu li {
                padding: 15px;
                justify-content: flex-start;
            }
            
            .payments-menu .submenu .menu-text {
                display: inline;
                margin-left: 10px;
            }
            
            .notification-badge {
                position: absolute;
                top: 5px;
                right: 5px;
                transform: none;
            }
            
            .menu-item-wrapper {
                justify-content: center;
            }
        }

        .payments-menu {
            position: relative;
        }

        .payments-menu .submenu {
            display: none;
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #00838f;
            position: relative;
        }

        .payments-menu:hover .submenu {
            display: block;
        }

        .payments-menu .submenu li {
            padding: 10px 20px 10px 40px;
        }

        .payments-menu .submenu li:hover {
            background-color: #006064;
        }

        .payments-menu .submenu a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .payments-menu .submenu .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Remove all previous dropdown-related styles */
        .nav-item.dropdown,
        .dropdown-menu,
        .dropdown-item,
        .payments-dropdown,
        .dropdown-icon {
            all: unset;
        }

        .notification-badge {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .menu-item-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        @media (max-width: 768px) {
            .notification-badge {
                position: absolute;
                top: 0;
                right: 50%;
                transform: translate(150%, 0);
            }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="sidebar-header">
        <h3 style="display: inline-block;">
            <?= $_SESSION['account']['first_name'], ' ', $_SESSION['account']['middle_name'], ' ', $_SESSION['account']['last_name']?>
            <a href="#" data-bs-toggle="modal" data-bs-target="#editAccountModal" style="color: inherit; text-decoration: none;">
                <i class="fas fa-pencil" style="font-size: 12px;"></i>
            </a>
        </h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" id="dashboard-link" class="nav-link"><i class="fas fa-tachometer-alt icon"></i><span class="menu-text">Dashboard</span></a></li>
        <li><a href="reservations.php" id="reservations-link" class="nav-link"><i class="fas fa-calendar-check icon"></i><span class="menu-text">Reservations</span></a></li>
        <li><a href="lots.php" id="lots-link" class="nav-link"><i class="fas fa-map-marker-alt icon"></i><span class="menu-text">Lots</span></a></li>
        <li><a href="accounts.php" id="accounts-link" class="nav-link"><i class="fas fa-users icon"></i><span class="menu-text">Accounts</span></a></li>
        <li class="nav-item payments-menu">
            <a href="payments.php" class="nav-link">
                <i class="fas fa-money-bill-wave icon"></i>
                <span class="menu-text">Payments</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="payment_plans.php">
                        <i class="fas fa-file-invoice-dollar icon"></i>
                        <span class="menu-text">Payment Plans</span>
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="notifications.php" id="notifications-link" class="nav-link">
                <div class="menu-item-wrapper">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-bell icon"></i>
                        <span class="menu-text">Notifications</span>
                    </div>
                    <?php if ($pendingCount > 0): ?>
                        <span class="notification-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </div>
            </a>
        </li>
        <li><a href="generate_report.php"><i class="fas fa-file-lines icon"></i><span class="menu-text">Generate Report</span></a></li>
        <li><a href="website_settings.php"><i class="fas fa-gear icon"></i><span class="menu-text">Website Settings</span></a></li>
        
        
        <?php if($_SESSION['account']['is_admin']): ?>
        <li class="nav-item payments-menu">
            <a href="staff.php" class="nav-link">
                <i class="fas fa-user-tie icon"></i>
                <span class="menu-text">Staffs</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="staff_log.php">
                        <i class="fas fa-user-clock icon"></i>
                        <span class="menu-text">Staff Logs</span>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; ?>


        <li><a href="../sign/logout.php"><i class="fas fa-sign-out-alt icon"></i><span class="menu-text">Logout</span></a></li>
    </ul>
</div>

<div class="content" id="content">
    <!-- Your page content goes here -->
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">Edit Account Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAccountForm" action="../admin/accounts/update_account.php" method="POST">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" value="<?= $_SESSION['account']['first_name'] ?>" required>
                        <div class="invalid-feedback" id="firstNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="middleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middleName" name="middle_name" value="<?= $_SESSION['account']['middle_name'] ?>">
                        <div class="invalid-feedback" id="middleNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" value="<?= $_SESSION['account']['last_name'] ?>" required>
                        <div class="invalid-feedback" id="lastNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $_SESSION['account']['username'] ?>" required>
                        <div class="invalid-feedback" id="usernameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $_SESSION['account']['email'] ?>" required>
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?= $_SESSION['account']['phone_number'] ?>" required>
                        <div class="invalid-feedback" id="phoneError"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn" style="background-color: #6c757d; color: white;" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
                        <button type="submit" class="btn btn" style="background-color: #006064; color: white;" >Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" action="../admin/accounts/change_password.php" method="POST">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        <div class="invalid-feedback" id="currentPasswordError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        <div class="invalid-feedback" id="newPasswordError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        <div class="invalid-feedback" id="confirmPasswordError"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn" style="background-color: #006064; color: white;">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.sidebar-menu li').on('click', function () {
            $('.sidebar-menu li').removeClass('active');
            $(this).addClass('active');
        });
    });
</script>

<script>
// Only run the form validation if the form exists
const editAccountForm = document.getElementById('editAccountForm');
if (editAccountForm) {
    editAccountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        
        // Clear previous error messages
        document.querySelectorAll('.invalid-feedback').forEach(div => div.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
        
        // Validate First Name
        const firstName = document.getElementById('firstName');
        if (firstName && !firstName.value.trim()) {
            document.getElementById('firstNameError').textContent = 'First name is required';
            document.getElementById('firstNameError').style.display = 'block';
            firstName.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate Last Name
        const lastName = document.getElementById('lastName');
        if (lastName && !lastName.value.trim()) {
            document.getElementById('lastNameError').textContent = 'Last name is required';
            document.getElementById('lastNameError').style.display = 'block';
            lastName.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate Email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && (!email.value.trim() || !emailRegex.test(email.value.trim()))) {
            document.getElementById('emailError').textContent = 'Please enter a valid email address';
            document.getElementById('emailError').style.display = 'block';
            email.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate Phone Number
        const phoneNumber = document.getElementById('phone_number');
        const phoneRegex = /^[0-9]{11}$/;
        if (phoneNumber && (!phoneNumber.value.trim() || !phoneRegex.test(phoneNumber.value.trim()))) {
            document.getElementById('phoneError').textContent = 'Please enter a valid 11-digit phone number';
            document.getElementById('phoneError').style.display = 'block';
            phoneNumber.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate Username
        const username = document.getElementById('username');
        if (username && !username.value.trim()) {
            document.getElementById('usernameError').textContent = 'Username is required';
            document.getElementById('usernameError').style.display = 'block';
            username.classList.add('is-invalid');
            isValid = false;
        }
        
        if (isValid) {
            this.submit();
        }
    });
}
</script>

<script>
// Add password form validation
const changePasswordForm = document.getElementById('changePasswordForm');
if (changePasswordForm) {
    changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        
        // Clear previous error messages
        document.querySelectorAll('.invalid-feedback').forEach(div => div.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
        
        // Validate Current Password
        const currentPassword = document.getElementById('currentPassword');
        if (currentPassword && !currentPassword.value.trim()) {
            document.getElementById('currentPasswordError').textContent = 'Current password is required';
            document.getElementById('currentPasswordError').style.display = 'block';
            currentPassword.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate New Password
        const newPassword = document.getElementById('newPassword');
        if (newPassword && !newPassword.value.trim()) {
            document.getElementById('newPasswordError').textContent = 'New password is required';
            document.getElementById('newPasswordError').style.display = 'block';
            newPassword.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate Confirm Password
        const confirmPassword = document.getElementById('confirmPassword');
        if (confirmPassword && !confirmPassword.value.trim()) {
            document.getElementById('confirmPasswordError').textContent = 'Please confirm your new password';
            document.getElementById('confirmPasswordError').style.display = 'block';
            confirmPassword.classList.add('is-invalid');
            isValid = false;
        } else if (newPassword && confirmPassword && newPassword.value !== confirmPassword.value) {
            document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            document.getElementById('confirmPasswordError').style.display = 'block';
            confirmPassword.classList.add('is-invalid');
            isValid = false;
        }
        
        if (isValid) {
            this.submit();
        }
    });
}
</script>

<!-- Add Bootstrap JS for dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Add SSE for payment checks -->
<script src="__DIR__ . '/../../website/js/check-payments.js"></script>

</html>
