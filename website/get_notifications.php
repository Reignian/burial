<?php
session_start();
require_once 'notification.class.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account']) || !isset($_SESSION['account']['account_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$account_id = $_SESSION['account']['account_id'];
$notificationObj = new Notification();

// Get notifications and unread count
$notifications = $notificationObj->getNotifications($account_id);
$unread_count = $notificationObj->getUnreadCount($account_id);

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);
?>
