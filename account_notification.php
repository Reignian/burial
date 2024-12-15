<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['account'])){
    header('location: sign/login.php?return_to=' . urlencode($_SERVER['PHP_SELF']));
    exit();
}

// If user is admin, always redirect to admin dashboard, no exceptions
if($_SESSION['account']['is_admin']){
    header('location: admin/dashboard.php');
    exit();
}

// If user is not a customer, redirect to index
if(!$_SESSION['account']['is_customer']){
    header('location: index.php');
    exit();
}

// Add cache control headers to prevent back-button issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include(__DIR__ . '/includes/header.php');
require_once __DIR__ . '/website/notification.class.php';

$notification = new Notification();
$notifications = $notification->getNotifications($_SESSION['account']['account_id']);

// Mark notification as read if clicked (AJAX endpoint)
if (isset($_POST['notification_id']) && isset($_POST['ajax'])) {
    $notification->markAsRead($_POST['notification_id']);
    echo json_encode(['success' => true]);
    exit();
}

// Legacy form submission handler
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
        .notification-container {
            max-width: 100%;
            padding: 0 20px;
        }
        .notification-item {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .notification-item.unread {
            background-color: #f8f9fa;
            border-left-color: #28a745;
        }
        .notification-item.payment-missed {
            border-left-color: #dc3545;
            background-color: #ffe9e9;
            color: #d32f2f;
        }
        .notification-item.payment-due-today {
            border-left-color: #ffc107;
            background-color: #fff8e3;
            color: #ff9e00;
        }
        .notification-item.payment-due-tomorrow {
            border-left-color: #ffc107;
        }
        .notification-time {
            font-size: 0.85rem;
            margin-top: 8px;
        }
        .notification-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .notification-message {
            font-size: 1rem;
            line-height: 1.5;
        }
        .no-notifications {
            text-align: center;
            padding: 60px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge.bg-success {
            background-color: #28a745 !important;
        }
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 3px solid #007bff;
            padding: 1rem 1.5rem;
        }
        .modal-title {
            color: #2d3748;
            font-weight: 600;
        }
        .notification-modal-content {
            padding: 1.5rem;
        }
        .notification-message-section {
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .notification-message-section .icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .notification-message-section.payment-due-today .icon {
            color: #ffc107;
        }
        .notification-message-section.payment-due-tomorrow .icon {
            color: #ffc107;
        }
        .notification-message-section.payment-missed .icon {
            color: #dc3545;
        }
        .notification-message-section.payment-success .icon {
            color: #28a745;
        }

        .notification-message-section .title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }
        .notification-message-section .message {
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .notification-message-section .timestamp {
            color: #718096;
            font-size: 0.875rem;
        }
        .details-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .details-section h6 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-item {
            display: flex;
            margin-bottom: 0.75rem;
            align-items: baseline;
        }
        .detail-item:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 500;
            color: #718096;
            width: 40%;
            font-size: 0.95rem;
        }
        .detail-value {
            color: #2d3748;
            width: 60%;
            font-size: 0.95rem;
        }
        .detail-value.highlight {
            font-weight: 600;
            color: #2563eb;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="notificationModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid notification-container py-5">
        <div class="row">
            <div class="col-12">
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
                        <?php 
                        $notificationClass = '';
                        if ($notif['type'] === 'payment_due_today') {
                            $notificationClass = 'payment-due-today';
                        } elseif ($notif['type'] === 'payment_due_tomorrow') {
                            $notificationClass = 'payment-due-tomorrow';
                        } elseif ($notif['type'] === 'payment_missed') {
                            $notificationClass = 'payment-missed';
                        } elseif (!$notif['is_read']) {
                            $notificationClass = 'unread';
                        }
                        ?>
                        <div class="notification-item <?php echo $notificationClass; ?>">
                            <button type="button" class="btn btn-link p-0 view-notification" 
                                data-notification-id="<?php echo $notif['notification_id']; ?>"
                                data-reference-id="<?php echo $notif['reference_id']; ?>"
                                data-type="<?php echo $notif['type']; ?>"
                                style="text-decoration: none; color: inherit; width: 100%; text-align: left;">
                                <div class="notification-title">
                                    <span>
                                        <?php if ($notif['type'] === 'payment_missed' || $notif['type'] === 'payment_due_tomorrow' || $notif['type'] === 'payment_due_today' ): ?>
                                            <i class="fas fa-exclamation-circle me-2" <?php if ($notif['type'] === 'payment_due_tomorrow'){ ?> style="color: #ffc107;" <?php } ?> ></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($notif['title']); ?>
                                    </span>
                                    <span>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="badge bg-success">New</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="notification-message">
                                    <?php echo htmlspecialchars($notif['message']); ?>
                                </div>
                                <div class="notification-time">
                                    <i class="far fa-clock me-1"></i>
                                    <?php 
                                    $date = new DateTime($notif['created_at']);
                                    echo $date->format('F j, Y g:i A'); 
                                    ?>
                                </div>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationButtons = document.querySelectorAll('.view-notification');
            const notificationModal = document.getElementById('notificationModal');
            
            // Add modal hidden event listener for page refresh
            notificationModal.addEventListener('hidden.bs.modal', function () {
                location.reload();
            });
            
            notificationButtons.forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const notificationId = this.dataset.notificationId;
                    const referenceId = this.dataset.referenceId;
                    const type = this.dataset.type;
                    const notificationItem = this.closest('.notification-item');

                    // Show modal first
                    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                    modal.show();

                    // Mark as read immediately when modal opens
                    try {
                        const formData = new FormData();
                        formData.append('notification_id', notificationId);
                        formData.append('ajax', true);
                        
                        const response = await fetch('account_notification.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            // Update UI to show notification as read
                            if (notificationItem) {
                                notificationItem.classList.remove('unread');
                            }
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }

                    // Fetch and display notification details
                    try {
                        const response = await fetch(`get_notification_details.php?notification_id=${notificationId}&reference_id=${referenceId}&type=${type}`);
                        
                        console.log('Response status:', response.status);
                        
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Server response:', errorText);
                            throw new Error(`HTTP error! status: ${response.status}, body: ${errorText}`);
                        }
                        
                        const data = await response.json();
                        
                        console.log('Received data:', data);
                        
                        if (data.error) {
                            console.error('Server error:', data.error);
                            if (data.debug_trace) {
                                console.error('Debug trace:', data.debug_trace);
                            }
                            alert(`Error loading notification details: ${data.error}`);
                            return;
                        }

                        // Verify we have the required data
                        if (!data.notification) {
                            throw new Error('No notification data received');
                        }

                        let iconClass = '';
                        if (data.notification.type === 'payment_due_today') {
                            iconClass = 'payment-due-today';
                        } else if (data.notification.type === 'payment_due_tomorrow') {
                            iconClass = 'payment-due-tomorrow';
                        } else if (data.notification.type === 'payment_missed') {
                            iconClass = 'payment-missed';
                        } else if (data.notification.type === 'payment_success') {
                            iconClass = 'payment-success';
                        }

                        let modalContent = `
                            <div class="notification-modal-content">
                                <div class="notification-message-section ${iconClass}">
                                    <div class="d-flex align-items-start">
                                        <div class="icon">
                                            <i class="fas fa-${data.notification.type.includes('payment') ? 'money-bill' : 'bell'}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="title">${data.notification.title}</div>
                                            <div class="message">${data.notification.message}
                                            ${data.notification.title === 'Reservation Pending' ? 
                                                'Please wait for us to review and confirm your request.<br><br>' : 
                                                data.notification.title === 'Reservation Confirmed' ? 
                                                `<br><br>Visit the Sto. Niño Parish Cemetery Office and pay for your down payment of ₱${parseFloat(data.reservation.balance * (data.reservation.pplan.match(/(\d+)%/)[1] / 100)).toLocaleString('en-PH', {minimumFractionDigits: 2})} on or before ${new Date(data.reservation.reservation_date).toLocaleDateString('en-PH', {month: 'long', day: 'numeric', year: 'numeric'})}<br><br>` : 
                                                ''}
                                            </div>
                                            <div class="timestamp">
                                                <i class="far fa-clock me-1"></i>
                                                ${data.notification.created_at}
                                            </div>
                                        </div>
                                    </div>
                                </div>`;

                        if (data.reservation) {
                            modalContent += `
                                <div class="details-section">
                                    <h6>Payment Information</h6>
                                    <div class="detail-item">
                                        <div class="detail-label">Monthly Payment</div>
                                        <div class="detail-value">₱${parseFloat(data.reservation.monthly_payment).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Balance</div>
                                        <div class="detail-value">₱${parseFloat(data.reservation.balance).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                                    </div>
                                    <div class="divider"></div>`;

                            if (data.lot) {
                                modalContent += `
                                    <h6>Reservation Details</h6>
                                    <div class="detail-item">
                                        <div class="detail-label">Lot</div>
                                        <div class="detail-value">${data.lot.lot_name} - ${data.lot.location}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Payment Plan</div>
                                        <div class="detail-value">${data.reservation.pplan}</div>
                                    </div>`;
                            }

                            modalContent += `</div>`;
                        }

                        modalContent += `</div>`;
                        
                        console.log('Setting modal content...');
                        document.getElementById('notificationModalBody').innerHTML = modalContent;
                        console.log('Modal displayed successfully');
                    } catch (error) {
                        console.error('Detailed error:', error);
                        console.error('Error stack:', error.stack);
                        alert(`Error loading notification details: ${error.message}`);
                    }
                });
            });
        });
    </script>
</body>
</html>
