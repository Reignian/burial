<?php
require_once __DIR__ . '/notifications.class.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$burialObj = new Notifications_class();
$requestarray = $burialObj->showrequest($limit, $offset);
$totalNotifications = $burialObj->getTotalNotifications();
$hasMoreNotifications = $totalNotifications > ($offset + $limit);

if (!empty($requestarray)) {
    foreach ($requestarray as $reqarr):
        $accountname = $burialObj->account($reqarr['reservation_id']);
        $account_lot = $burialObj->account_lot($reqarr['reservation_id']);
        $status = strtolower($reqarr['request']);
        
        $statusText = $reqarr['request'] === 'Pending' ? 'New reservation request' : 
                     ($reqarr['request'] === 'Confirmed' ? 'Reservation confirmed' : 'Reservation cancelled');
    ?>
        <a href="notifications/reservation_request.php?reservation_id=<?= $reqarr['reservation_id'] ?>" 
           class="notification-card <?= $status ?>"
           <?= !$hasMoreNotifications ? 'data-last-page="true"' : '' ?>>
            <div class="notification-status">
                <?= $statusText ?>
            </div>
            <div class="notification-content">
                <strong><?= htmlspecialchars($accountname) ?></strong> has requested a reservation for <strong><?= htmlspecialchars($account_lot) ?></strong>
            </div>
            <div class="notification-time">
                <i class="far fa-clock me-1"></i>
                <?= date('F j, Y, g:i a', strtotime($reqarr['created_at'])) ?>
            </div>
        </a>
    <?php
    endforeach;
}
?> 