<?php
session_start();
require_once __DIR__ . '/reservations.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : null;
    $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : null;
    $payment_plan_id = isset($_POST['payment_plan_id']) ? $_POST['payment_plan_id'] : null;

    if (!$reservation_id || !$reservation_date || !$payment_plan_id) {
        $_SESSION['error'] = "Missing required fields";
        header("Location: ../reservations.php");
        exit();
    }

    $burialObj = new Reservation_class();
    
    if ($burialObj->editReservation($reservation_id, $reservation_date, $payment_plan_id)) {
        $_SESSION['success'] = "Reservation updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update reservation";
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

header("Location: ../reservations.php");
exit();
