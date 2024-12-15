<?php
session_start();
require_once 'penalty.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_penalty_rate'])) {
    $penalty = new Penalty();
    $newRate = floatval($_POST['new_penalty_rate']);
    
    if ($penalty->updatePenaltyRate($newRate)) {
        $_SESSION['success_message'] = "Penalty rate updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update penalty rate.";
    }
}

header('Location: ../payment_plans.php');
exit();
