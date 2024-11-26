<?php
  session_start();

    if(isset($_SESSION['account'])){
        if(!$_SESSION['account']['is_customer']){
            header('location: ./sign/login.php');
        }
    }else{
        header('location: ./sign/login.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burial Space Management System | Sto. Niño Parish Cemetery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<header>
    <ul>
        <li><a href="index.php" class="nav">HOME</a></li>
        <li><a href="browse_lots.php" class="nav">LOTS</a></li>
        <li><a href="about.php" class="nav">ABOUT</a></li>
        <li><a href="contact.php" class="nav">CONTACT</a></li>
    </ul>


    <div class="btn-group">
            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end">
                <a href="account_profile.php" class="dropdown-item">Account</a>
                <a href="./sign/logout.php" class="dropdown-item">Logout</a>

            </div>
        </div>
    </div>
</header>