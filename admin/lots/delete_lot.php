<?php
session_start();
require_once ('lots.class.php');

$burialObj = new Lots_class();

if (isset($_GET['lot_id'])) {
    $lot_id = $_GET['lot_id'];
    
    $result = $burialObj->deleteLot($lot_id);
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No lot ID provided']);
}
