<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/notifications/notifications.class.php');

    $burialObj = new Notifications_class();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $requestarray = $burialObj->showrequest($limit, $offset);
    $totalNotifications = $burialObj->getTotalNotifications();
    $hasMoreNotifications = $totalNotifications > ($offset + $limit);
?>

<link rel="stylesheet" href="css/notifications.css">

<div class="notifications-container">
    <div class="notifications-grid">
        <h1 class="notifications-header">Notifications</h1>

        <div id="notifications-list">
            <?php if (empty($requestarray)): ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash fa-2x mb-3"></i>
                    <p>No notifications at this time</p>
                </div>
            <?php else: ?>
                <?php foreach ($requestarray as $reqarr):
                    $accountname = $burialObj->account($reqarr['reservation_id']);
                    $account_lot = $burialObj->account_lot($reqarr['reservation_id']);
                    $status = strtolower($reqarr['request']);
                    
                    $statusText = $reqarr['request'] === 'Pending' ? 'New reservation request' : 
                                 ($reqarr['request'] === 'Confirmed' ? 'Reservation confirmed' : 'Reservation cancelled');
                ?>
                    <a href="notifications/reservation_request.php?reservation_id=<?= $reqarr['reservation_id'] ?>" 
                       class="notification-card <?= $status ?>">
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($hasMoreNotifications): ?>
            <div class="load-more-container">
                <button id="loadMoreBtn" class="load-more-btn" data-page="<?= $page + 1 ?>">
                    Show Previous Notifications
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('loadMoreBtn')?.addEventListener('click', function() {
    const button = this;
    const nextPage = button.dataset.page;
    
    fetch(`notifications/load_more.php?page=${nextPage}`)
        .then(response => response.text())
        .then(html => {
            if (html.trim()) {
                document.getElementById('notifications-list').insertAdjacentHTML('beforeend', html);
                button.dataset.page = parseInt(nextPage) + 1;
                
                // Check if we should hide the button
                if (html.includes('data-last-page="true"')) {
                    button.style.display = 'none';
                }
            } else {
                button.style.display = 'none';
            }
        });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
