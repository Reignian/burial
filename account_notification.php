<?php
include(__DIR__ . '/includes/header.php');
require_once __DIR__ . '/website/notification.class.php';

$notification = new Notification();
$notifications = $notification->getNotifications($_SESSION['account']['account_id']);

// Mark notification as read if clicked
if (isset($_POST['notification_id'])) {
    $notification->markAsRead($_POST['notification_id']);
    header('Location: account_notification.php');
    exit();
}

// Run the payment due check
$notification->checkDuePayments();

// Run the reservation status check
$notification->checkReservationStatus();

?>

    <style>
        .notification-item {
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .notification-item.unread {
            background-color: #f8f9fa;
            border-left-color: #28a745;
        }
        .notification-time {
            color: #6c757d;
            font-size: 0.85rem;
        }
        .notification-title {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 5px;
        }
        .notification-message {
            color: #6c757d;
        }
        .no-notifications {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">
                    <i class="fas fa-bell me-2"></i>
                    Notifications
                </h2>

                <?php if (empty($notifications)): ?>
                    <div class="no-notifications">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h4>No notifications yet</h4>
                        <p>When you receive notifications, they will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="notification_id" value="<?php echo $notif['notification_id']; ?>">
                                <button type="submit" class="btn btn-link p-0" style="text-decoration: none; color: inherit;">
                                    <div class="notification-title">
                                        <?php echo htmlspecialchars($notif['title']); ?>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="badge bg-success">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-message">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php 
                                        $date = new DateTime($notif['created_at']);
                                        echo $date->format('F j, Y g:i A'); 
                                        ?>
                                    </div>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
