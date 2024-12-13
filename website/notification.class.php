<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/lots.class.php';

class Notification {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new notification
    public function createNotification($account_id, $type, $title, $message, $reference_id = null) {
        $sql = "INSERT INTO notifications (account_id, type, title, message, reference_id) 
                VALUES (:account_id, :type, :title, :message, :reference_id)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->bindParam(':type', $type);
        $query->bindParam(':title', $title);
        $query->bindParam(':message', $message);
        $query->bindParam(':reference_id', $reference_id);
        
        return $query->execute();
    }

    // Get all notifications for a user
    public function getNotifications($account_id) {
        $sql = "SELECT * FROM notifications 
                WHERE account_id = :account_id 
                ORDER BY created_at DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get unread notification count
    public function getUnreadCount($account_id) {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE account_id = :account_id AND is_read = 0";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC)['count'];
    }

    // Mark notification as read
    public function markAsRead($notification_id) {
        $sql = "UPDATE notifications SET is_read = 1 
                WHERE notification_id = :notification_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':notification_id', $notification_id);
        
        return $query->execute();
    }

    // Create payment success notification
    public function createPaymentSuccessNotification($account_id, $reservation_id, $amount_paid) {
        // Check if we already sent a notification for this payment today
        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE account_id = :account_id 
                AND reference_id = :reservation_id 
                AND type = 'payment_success'
                AND DATE(created_at) = CURRENT_DATE()";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        
        if ($query->fetchColumn() == 0) {
            $this->createNotification(
                $account_id,
                'payment_success',
                'Payment Successful',
                "Your payment of ₱" . number_format($amount_paid, 2) . " was successfully processed. Thank you!",
                $reservation_id
            );
        }
    }

    // Check and create due payment notifications
    public function checkDuePayments() {
        // Set timezone to your local timezone
        date_default_timezone_set('Asia/Manila');

        $reservationObj = new Reservation();

        $sql = "SELECT 
                    r.reservation_id, r.account_id, r.monthly_payment, r.balance,
                    r.reservation_date, r.payment_plan_id,
                    COUNT(p.payment_id) AS payment_count
                FROM reservation r
                LEFT JOIN payment p ON r.reservation_id = p.reservation_id
                WHERE r.request = 'Confirmed' 
                AND r.balance > 0
                GROUP BY r.reservation_id, r.account_id, r.monthly_payment, r.balance, r.reservation_date, r.payment_plan_id";

        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $duePayments = $query->fetchAll(PDO::FETCH_ASSOC);

        $today = new DateTime();
        $today->setTime(0, 0, 0); // Set time to start of day for accurate comparison

        foreach ($duePayments as $payment) {
            // Calculate next payment date
            $reservation_date = new DateTime($payment['reservation_date']);
            $payment_count = (int)$payment['payment_count'];
            $next_payment_date = clone $reservation_date;
            $next_payment_date->modify("+{$payment_count} month");
            $next_payment_date->setTime(0, 0, 0);

            // For missed payments, check and apply penalty
            if ($next_payment_date < $today) {
                // Calculate penalty using the public method
                $days_late = $today->diff($next_payment_date)->days;
                $penalty_amount = $reservationObj->calculatePenalty(
                    $payment['reservation_id'], 
                    $payment['monthly_payment'],
                    $days_late
                );
                
                if ($penalty_amount > 0) {
                    // Try to apply the penalty
                    $penalty_applied = $reservationObj->applyPenaltyAndUpdateBalance(
                        $payment['reservation_id'], 
                        $penalty_amount, 
                        $next_payment_date
                    );
                    
                    if ($penalty_applied) {
                        // Create penalty notification
                        $this->createNotification(
                            $payment['account_id'],
                            'payment_missed',
                            'Missed Payment Due',
                            "A penalty of ₱" . number_format($penalty_amount, 2) . " has been applied to your balance due to late payment.",
                            $payment['reservation_id']
                        );
                    }
                }
            }

            // Get the current month and year
            $currentMonth = $today->format('Y-m');
            $dueMonth = $next_payment_date->format('Y-m');

            // For payments due today
            if ($next_payment_date == $today) {
                // Check if we already sent a notification today
                $sql = "SELECT COUNT(*) FROM notifications 
                        WHERE account_id = :account_id 
                        AND reference_id = :reservation_id 
                        AND type = 'payment_due_today'
                        AND DATE(created_at) = CURRENT_DATE()";
                
                $query = $this->db->connect()->prepare($sql);
                $query->bindParam(':account_id', $payment['account_id']);
                $query->bindParam(':reservation_id', $payment['reservation_id']);
                $query->execute();
                
                if ($query->fetchColumn() == 0) {
                    $this->createNotification(
                        $payment['account_id'],
                        'payment_due_today',
                        'Payment Due Today',
                        "You have a payment of ₱" . number_format($payment['monthly_payment'], 2) . " due today",
                        $payment['reservation_id']
                    );
                }
            }
            // For payments due tomorrow
            elseif ($next_payment_date > $today) {
                $tomorrow = clone $today;
                $tomorrow->modify('+1 day');
                
                if ($next_payment_date == $tomorrow) {
                    // Check if we already sent a notification for tomorrow
                    $sql = "SELECT COUNT(*) FROM notifications 
                            WHERE account_id = :account_id 
                            AND reference_id = :reservation_id 
                            AND type = 'payment_due_tomorrow'
                            AND DATE(created_at) = CURRENT_DATE()";
                    
                    $query = $this->db->connect()->prepare($sql);
                    $query->bindParam(':account_id', $payment['account_id']);
                    $query->bindParam(':reservation_id', $payment['reservation_id']);
                    $query->execute();
                    
                    if ($query->fetchColumn() == 0) {
                        $this->createNotification(
                            $payment['account_id'],
                            'payment_due_tomorrow',
                            'Payment Due Tomorrow',
                            "You have a payment of ₱" . number_format($payment['monthly_payment'], 2) . " due tomorrow",
                            $payment['reservation_id']
                        );
                    }
                }
            }
            // For missed payments notification
            elseif ($next_payment_date < $today) {
                // Check if we already sent a missed payment notification for this month
                $sql = "SELECT COUNT(*) FROM notifications 
                        WHERE account_id = :account_id 
                        AND reference_id = :reservation_id 
                        AND type = 'payment_missed'
                        AND DATE_FORMAT(created_at, '%Y-%m') = :current_month";
                
                $query = $this->db->connect()->prepare($sql);
                $query->bindParam(':account_id', $payment['account_id']);
                $query->bindParam(':reservation_id', $payment['reservation_id']);
                $query->bindParam(':current_month', $dueMonth);
                $query->execute();
                
                if ($query->fetchColumn() == 0) {
                    $this->createNotification(
                        $payment['account_id'],
                        'payment_missed',
                        'Missed Payment',
                        "You have missed a payment of ₱" . number_format($payment['monthly_payment'], 2) . 
                        " that was due on " . $next_payment_date->format('F j, Y'),
                        $payment['reservation_id']
                    );
                }
            }
        }
    }

    // Check and create notifications for reservation status changes
    public function checkReservationStatus() {
        $sql = "SELECT r.reservation_id, r.account_id, r.request, l.lot_name, l.location 
                FROM reservation r
                JOIN lots l ON r.lot_id = l.lot_id
                WHERE r.request IN ('Pending', 'Confirmed', 'Cancelled')";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $reservations = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $reservation) {
            // Check if notification already exists for this status
            $sql = "SELECT COUNT(*) as count FROM notifications 
                    WHERE account_id = :account_id 
                    AND reference_id = :reference_id 
                    AND type = 'reservation_status'
                    AND title LIKE :status_title";
            
            $statusTitle = "Reservation " . $reservation['request'];
            $titlePattern = $statusTitle . "%";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':account_id', $reservation['account_id']);
            $query->bindParam(':reference_id', $reservation['reservation_id']);
            $query->bindParam(':status_title', $titlePattern);
            $query->execute();
            $exists = $query->fetch(PDO::FETCH_ASSOC)['count'] > 0;

            if ($exists) {
                continue; // Skip if notification already exists for this status
            }

            // Create status-specific message
            $lotInfo = $reservation['lot_name'] . " - " . $reservation['location'];
            if ($reservation['request'] == 'Pending') {
                $message = "Your reservation request for $lotInfo is currently pending.";
            } elseif ($reservation['request'] == 'Confirmed') {
                $message = "Your reservation request for $lotInfo has been confirmed.";
            } else { // Cancelled
                $message = "Your reservation request for $lotInfo has been cancelled.";
            }

            $this->createNotification(
                $reservation['account_id'],
                'reservation_status',
                $statusTitle,
                $message,
                $reservation['reservation_id']
            );
        }
    }

}
?>