<?php

require_once ('lots.class.php');

$burialObj = new Lots_class();

if (isset($_GET['lot_id'])) {
    $lot_id = $_GET['lot_id'];
    
    if ($burialObj->deleteLot($lot_id)) {
        echo 'success';
    } else {
        echo 'failed';
    }
} else {
    echo 'No lot ID provided';
}
