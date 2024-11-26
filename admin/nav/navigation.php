<?php

  session_start();

    if(isset($_SESSION['account'])){
        if(!$_SESSION['account']['is_admin']){
            header('location: ../sign/login.php');
        }
    }else{
        header('location: ../sign/login.php');
    }
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
            margin-left: 250px;
            padding: 0;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
            .sidebar-menu {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .sidebar-menu li {
                padding: 10px;
            }
            .sidebar-menu a {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .sidebar-menu .icon {
                margin-right: 0;
                margin-bottom: 5px;
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
        <li><a href="notifications.php" id="notifications-link" class="nav-link"><i class="fas fa-bell icon"></i><span class="menu-text">Notifications</span></a></li>
        <li><a href="generate_report.php"><i class="fas fa-file-lines icon"></i><span class="menu-text">Generate Report</span></a></li>
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

