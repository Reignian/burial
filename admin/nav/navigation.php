<?php

  session_start();
  require_once __DIR__ . '/../notifications/notifications.class.php';

    if(isset($_SESSION['account'])){
        if(!$_SESSION['account']['is_admin']){
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
    <div class="sidebar-header">
        <h3>Admin</h3>
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
        <li><a href="../sign/logout.php"><i class="fas fa-sign-out-alt icon"></i><span class="menu-text">Logout</span></a></li>
    </ul>
</div>

<div class="content" id="content">
    <!-- Your page content goes here -->
</div>

<script>
    $(document).ready(function () {
        $('.sidebar-menu li').on('click', function () {
            $('.sidebar-menu li').removeClass('active');
            $(this).addClass('active');
        });
    });
</script>

<!-- Add Bootstrap JS for dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

