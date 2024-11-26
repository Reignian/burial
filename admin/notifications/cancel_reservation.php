<?php

require_once('notifications.class.php');

$burialObj = new Notifications_class();
$reservation_id = '';

if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
}

if ($burialObj->cancel_reservation($reservation_id)) {
    echo 'success';
} else {
    echo 'failed';
}
?>
