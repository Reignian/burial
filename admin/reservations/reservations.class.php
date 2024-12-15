<?php

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../website/notification.class.php';
require_once __DIR__ . '/../staffs/staffs.class.php';

class Reservation_class{

    public $reservation_id = '';
    public $amount_paid = '';
    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    function showALL_reservation(){

        $sql = "SELECT * FROM reservation WHERE request = 'Confirmed';";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    function hasPayments($reservationID) {
        $sql = "SELECT COUNT(*) FROM payment WHERE reservation_id = :reservation_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservationID);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    function getReservationDetails($reservation_id) {
        $sql = "SELECT pp.plan, r.balance 
                FROM reservation r 
                JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id 
                WHERE r.reservation_id = :reservation_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function cancelReservation($reservationID) {
        try {
            $this->db->connect()->beginTransaction();

            // Get reservation details before cancellation
            $sql = "SELECT r.*, 
                   CONCAT(a.last_name, ', ', a.first_name, ' ', COALESCE(a.middle_name, '')) as client_name,
                   CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                   pp.plan as payment_plan,
                   (SELECT COUNT(*) FROM payment p WHERE p.reservation_id = r.reservation_id) as payment_count,
                   (SELECT SUM(amount_paid) FROM payment p WHERE p.reservation_id = r.reservation_id) as total_paid
            FROM reservation r
            JOIN account a ON r.account_id = a.account_id
            JOIN lots l ON r.lot_id = l.lot_id
            JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
            WHERE r.reservation_id = :reservation_id";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservationID);
            $query->execute();
            $reservationDetails = $query->fetch(PDO::FETCH_ASSOC);

            // Update reservation status
            $sql = "UPDATE reservation SET request = 'Cancelled' WHERE reservation_id = :reservation_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservationID);

            if($query->execute()){
                // Create detailed log
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    $logDetails = sprintf(
                        "Cancelled Reservation #%d:\n" .
                        "Client: %s\n" .
                        "Lot: %s\n" .
                        "Payment Plan: %s\n" .
                        "Reservation Date: %s\n" .
                        "Total Payments Made: %d\n" .
                        "Total Amount Paid: ₱%s\n" .
                        "Remaining Balance: ₱%s\n" .
                        "Cancellation Date: %s",
                        $reservationID,
                        $reservationDetails['client_name'],
                        $reservationDetails['lot_details'],
                        $reservationDetails['payment_plan'],
                        date('F j, Y', strtotime($reservationDetails['reservation_date'])),
                        $reservationDetails['payment_count'],
                        number_format($reservationDetails['total_paid'], 2),
                        number_format($reservationDetails['balance'], 2),
                        date('F j, Y g:i A')
                    );
                    
                    $staffs = new Staffs_class();
                    $staffs->addStaffLog(
                        $_SESSION['account']['account_id'],
                        'Cancel Reservation',
                        $logDetails
                    );
                }

                $this->db->connect()->commit();
                return true;
            }
            
            $this->db->connect()->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return false;
        }
    }

    function account($reservation_id) {
        $sql = "SELECT CONCAT(c.last_name, ', ', c.first_name, ' ', c.middle_name) AS account_name
                FROM reservation r
                JOIN account c ON r.account_id = c.account_id
                WHERE r.reservation_id = :reservation_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        return $query->fetchColumn();
    }

    function account_lot($reservation_id) {
        $sql = "SELECT CONCAT(l.lot_name, ' - ', l.location) as lot_details
                FROM reservation r
                JOIN lots l ON r.lot_id = l.lot_id
                WHERE r.reservation_id = :reservation_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        return $query->fetchColumn();
    }

    function Balance($reservation_id){
        $sql = "SELECT (r.balance - COALESCE(SUM(p.amount_paid), 0)) AS updated_balance 
            FROM reservation r 
            LEFT JOIN payment p ON r.reservation_id = p.reservation_id 
            WHERE r.reservation_id = :reservation_id;";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        $balance = $query->fetchColumn();

        // If balance is 0 or less, we consider it paid, but we don't update any status
        return max(0, $balance); // Ensure we don't return negative balance
    }

    function pp($reservation_id) {
        $sql = "SELECT p.plan
                FROM reservation r
                JOIN payment_plan p ON r.payment_plan_id = p.payment_plan_id
                WHERE r.reservation_id = :reservation_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        return $query->fetchColumn();
    }

    private function calculatePenalty($reservation_id, $monthly_payment, $days_late) {
        // Fetch the payment plan for this reservation
        $sql = "SELECT pp.* FROM reservation r JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id WHERE r.reservation_id = :reservation_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        $paymentPlan = $query->fetch(PDO::FETCH_ASSOC);

        if ($paymentPlan['duration'] == 0) {
            // Spot cash, no penalty
            return 0;
        } else {
            // Get penalty rate from penalty table
            $sql = "SELECT penalty_amount FROM penalty WHERE penalty_id = 1";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $penalty = $query->fetch(PDO::FETCH_ASSOC);
            
            // Apply penalty rate (stored as percentage in database)
            return $monthly_payment * ($penalty['penalty_amount'] / 100);
        }
    }

    function applyPenaltyAndUpdateBalance($reservation_id, $penalty_amount, $due_date) {
        // Ensure $due_date is a DateTime object
        if (!($due_date instanceof DateTime)) {
            $due_date = new DateTime($due_date);
        }

        // Start a transaction
        $conn = $this->db->connect();
        $conn->beginTransaction();

        try {
            // Format the due date
            $formatted_due_date = $due_date->format('Y-m-d H:i:s');
            $formatted_date_only = $due_date->format('Y-m-d');

            // Check if a penalty has already been applied for this month
            $sqlCheckPenalty = "SELECT COUNT(*) FROM penalty_log 
                                WHERE reservation_id = :reservation_id 
                                AND DATE(penalty_date) = :check_date";
            $queryCheckPenalty = $conn->prepare($sqlCheckPenalty);
            $queryCheckPenalty->bindParam(':reservation_id', $reservation_id);
            $queryCheckPenalty->bindParam(':check_date', $formatted_date_only);
            
            if (!$queryCheckPenalty->execute()) {
                throw new Exception("Failed to check existing penalty");
            }
            
            $penaltyExists = $queryCheckPenalty->fetchColumn();

            if ($penaltyExists == 0) {
                // Add the penalty to the balance
                $sqlUpdateBalance = "UPDATE reservation SET balance = balance + :penalty_amount WHERE reservation_id = :reservation_id";
                $queryUpdateBalance = $conn->prepare($sqlUpdateBalance);
                $queryUpdateBalance->bindParam(':penalty_amount', $penalty_amount);
                $queryUpdateBalance->bindParam(':reservation_id', $reservation_id);
                
                if (!$queryUpdateBalance->execute()) {
                    throw new Exception("Failed to update balance");
                }

                // Log the penalty
                $sqlLogPenalty = "INSERT INTO penalty_log (reservation_id, penalty_amount, penalty_date) VALUES (:reservation_id, :penalty_amount, :penalty_date)";
                $queryLogPenalty = $conn->prepare($sqlLogPenalty);
                $queryLogPenalty->bindParam(':reservation_id', $reservation_id);
                $queryLogPenalty->bindParam(':penalty_amount', $penalty_amount);
                $queryLogPenalty->bindParam(':penalty_date', $formatted_due_date);
                
                if (!$queryLogPenalty->execute()) {
                    throw new Exception("Failed to log penalty");
                }

                // Commit the transaction
                $conn->commit();
                return true;
            } else {
                // Penalty already applied this month
                $conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $conn->rollBack();
            return false;
        }
    }


    function Duedate($reservation_id) {
        // First, check if the balance is 0
        if ($this->Balance($reservation_id) <= 0) {
            return "<span style='color: green;'>Paid</span>";
        }

        // Fetch reservation date, count of payments, monthly payment amount, and payment plan months
        $sql = "SELECT 
                    r.reservation_id,
                    r.reservation_date,
                    r.monthly_payment,
                    COUNT(p.payment_id) AS payment_count,
                    pp.duration AS plan_months
                FROM 
                    reservation r
                LEFT JOIN
                    payment p ON r.reservation_id = p.reservation_id
                JOIN
                    payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                WHERE
                    r.reservation_id = :reservation_id
                GROUP BY
                    r.reservation_id, r.reservation_date, r.monthly_payment, pp.duration;";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return 'Reservation not found';
        }
        
        // Set timezone to your local timezone
        date_default_timezone_set('Asia/Manila'); // Adjust this to your timezone
        
        $reservation_date = new DateTime($result['reservation_date']);
        $payment_count = (int)$result['payment_count'];
        $monthly_payment = (float)$result['monthly_payment'];
        $plan_months = (int)$result['plan_months'];
        
        // Calculate next due date
        $next_due_date = (clone $reservation_date)->modify("+{$payment_count} month");
        $next_due_date->setTime(0, 0, 0); // Reset time to start of day
        
        // Current date with time set to start of day
        $current_date = new DateTime();
        $current_date->setTime(0, 0, 0);
        
        // Calculate the number of days left
        $interval = $current_date->diff($next_due_date);
        $days_left = $interval->days;
        $is_future = $interval->invert === 0;

        // Check if the current date is before, on, or after the due date
        if ($current_date < $next_due_date) {
            if ($days_left == 1) {
                return "<span style='color: black;'>Due tomorrow</span>";
            }
            return "<span style='color: black;'>{$days_left} day(s) left before monthly due</span>";
        } elseif ($current_date == $next_due_date) {
            return "<span style='color: orange;'>Due today</span>";
        } else {
            if ($days_left > 0) {
                $penalty_amount = $this->calculatePenalty($reservation_id, $monthly_payment, $days_left);
                if ($penalty_amount > 0) {
                    $penalty_applied = $this->applyPenaltyAndUpdateBalance($reservation_id, $penalty_amount, $next_due_date);
                    if ($penalty_applied) {
                        return "<span style='color: red;'>{$days_left} day(s) passed since monthly due (Penalty of ₱{$penalty_amount} applied)</span>";
                    } else {
                        return "<span style='color: red;'>{$days_left} day(s) passed since monthly due (Penalty already applied this month)</span>";
                    }
                } else {
                    return "<span style='color: red;'>{$days_left} day(s) passed since monthly due (No penalty for spot cash)</span>";
                }
            }
        }
    }

    function getRowClass($reservation_id) {
        $balance = $this->Balance($reservation_id);
        
        if ($balance <= 0) {
            return "table-secondary"; // Class for paid reservations
        }

        $dueInfo = $this->Duedate($reservation_id);

        // Check for due status
        if (strpos($dueInfo, 'passed since monthly due') !== false) {
            return "table-danger"; // Class for passed due date
        } elseif (strpos($dueInfo, 'Due today') !== false) {
            return "table-warning"; // Class for due today
        }
        return ""; // Default for no color change
    }

    private function createPaymentSuccessNotification($account_id, $reservation_id, $amount_paid) {
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
            $sql = "INSERT INTO notifications (account_id, type, title, message, reference_id) 
                    VALUES (:account_id, :type, :title, :message, :reference_id)";
            
            $type = 'payment_success';
            $title = 'Payment Successful';
            $message = "Your payment of ₱" . number_format($amount_paid, 2) . " was successfully processed. Thank you!";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':account_id', $account_id);
            $query->bindParam(':type', $type);
            $query->bindParam(':title', $title);
            $query->bindParam(':message', $message);
            $query->bindParam(':reference_id', $reservation_id);
            
            return $query->execute();
        }
        return true;
    }

    public function addPayment($reservation_id, $amount_paid) {
        try {
            $this->db->connect()->beginTransaction();

            // Get reservation details before payment
            $sql = "SELECT r.*, 
                   CONCAT(a.last_name, ', ', a.first_name, ' ', COALESCE(a.middle_name, '')) as client_name,
                   CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                   pp.plan as payment_plan,
                   r.monthly_payment
            FROM reservation r
            JOIN account a ON r.account_id = a.account_id
            JOIN lots l ON r.lot_id = l.lot_id
            JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
            WHERE r.reservation_id = :reservation_id";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->execute();
            $reservationDetails = $query->fetch(PDO::FETCH_ASSOC);

            // Get current balance before payment
            $current_balance = $this->Balance($reservation_id);
            
            // Add payment record
            $payment_date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO payment (reservation_id, amount_paid, payment_date) VALUES (:reservation_id, :amount_paid, :payment_date)";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->bindParam(':amount_paid', $amount_paid);
            $query->bindParam(':payment_date', $payment_date);
            
            if($query->execute()){
                // Create detailed log
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    // Calculate new balance after payment
                    $new_balance = $this->Balance($reservation_id);
                    
                    $logDetails = sprintf(
                        "Added payment for Reservation #%d:\n" .
                        "Client: %s\n" .
                        "Lot: %s\n" .
                        "Payment Plan: %s\n" .
                        "Amount Paid: ₱%s\n" .
                        "Previous Balance: ₱%s\n" .
                        "New Balance: ₱%s\n" .
                        "Monthly Payment: ₱%s\n" .
                        "Payment Date: %s",
                        $reservation_id,
                        $reservationDetails['client_name'],
                        $reservationDetails['lot_details'],
                        $reservationDetails['payment_plan'],
                        number_format($amount_paid, 2),
                        number_format($current_balance, 2),
                        number_format($new_balance, 2),
                        number_format($reservationDetails['monthly_payment'], 2),
                        date('F j, Y g:i A', strtotime($payment_date))
                    );
                    
                    $staffs = new Staffs_class();
                    $staffs->addStaffLog(
                        $_SESSION['account']['account_id'],
                        'Add Payment',
                        $logDetails
                    );
                }

                // Create notification for the client
                $this->createPaymentSuccessNotification(
                    $reservationDetails['account_id'],
                    $reservation_id,
                    $amount_paid
                );

                $this->db->connect()->commit();
                return true;
            }
            
            $this->db->connect()->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return false;
        }
    }

    public function editReservation($reservation_id, $reservation_date, $payment_plan_id) {
        try {
            $this->db->connect()->beginTransaction();

            // Get reservation details before update
            $sql = "SELECT r.*, 
                   CONCAT(a.last_name, ', ', a.first_name, ' ', COALESCE(a.middle_name, '')) as client_name,
                   CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                   pp_old.plan as old_payment_plan,
                   pp_new.plan as new_payment_plan,
                   l.price as lot_price
            FROM reservation r
            JOIN account a ON r.account_id = a.account_id
            JOIN lots l ON r.lot_id = l.lot_id
            JOIN payment_plan pp_old ON r.payment_plan_id = pp_old.payment_plan_id
            JOIN payment_plan pp_new ON pp_new.payment_plan_id = :new_payment_plan_id
            WHERE r.reservation_id = :reservation_id";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->bindParam(':new_payment_plan_id', $payment_plan_id);
            $query->execute();
            $oldDetails = $query->fetch(PDO::FETCH_ASSOC);

            // Get new payment plan details
            $sql = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':payment_plan_id', $payment_plan_id);
            $query->execute();
            $new_plan = $query->fetch();
            
            // Calculate new payment details
            if ($new_plan['duration'] > 0) {
                $principal = $oldDetails['lot_price'] * (1 - ($new_plan['down_payment'] / 100));
                if ($new_plan['interest_rate'] > 0) {
                    $interest = $principal * ($new_plan['interest_rate'] / 100);
                    $total_amount = $principal + $interest;
                } else {
                    $total_amount = $principal;
                }
                $monthly_payment = $total_amount / $new_plan['duration'];
            } else {
                // Spot cash
                $monthly_payment = 0;
                $total_amount = $oldDetails['lot_price'];
            }

            // Update reservation
            $sql = "UPDATE reservation SET 
                   reservation_date = :reservation_date, 
                   payment_plan_id = :payment_plan_id,
                   monthly_payment = :monthly_payment,
                   balance = :balance 
                   WHERE reservation_id = :reservation_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->bindParam(':reservation_date', $reservation_date);
            $query->bindParam(':payment_plan_id', $payment_plan_id);
            $query->bindParam(':monthly_payment', $monthly_payment);
            $query->bindParam(':balance', $total_amount);

            if($query->execute()){
                // Create detailed log
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    $logDetails = sprintf(
                        "Updated Reservation #%d:\n" .
                        "Client: %s\n" .
                        "Lot: %s\n" .
                        "Old Payment Plan: %s\n" .
                        "New Payment Plan: %s\n" .
                        "Old Reservation Date: %s\n" .
                        "New Reservation Date: %s\n" .
                        "New Monthly Payment: ₱%s\n" .
                        "New Balance: ₱%s",
                        $reservation_id,
                        $oldDetails['client_name'],
                        $oldDetails['lot_details'],
                        $oldDetails['old_payment_plan'],
                        $oldDetails['new_payment_plan'],
                        date('F j, Y', strtotime($oldDetails['reservation_date'])),
                        date('F j, Y', strtotime($reservation_date)),
                        number_format($monthly_payment, 2),
                        number_format($total_amount, 2)
                    );
                    
                    $staffs = new Staffs_class();
                    $staffs->addStaffLog(
                        $_SESSION['account']['account_id'],
                        'Edit Reservation',
                        $logDetails
                    );
                }

                $this->db->connect()->commit();
                return true;
            }
            
            $this->db->connect()->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return false;
        }
    }

    public function transferReservation($reservationId, $newAccountId, $isNewAccount = false) {
        $this->db->connect()->beginTransaction();
        
        try {
            // Get current reservation details
            $sql = "SELECT r.account_id, r.lot_id, r.balance, r.monthly_payment, r.payment_plan_id, r.reservation_date,
                          CONCAT(a_old.last_name, ', ', a_old.first_name, ' ', a_old.middle_name) as old_client_name,
                          CONCAT(a_new.last_name, ', ', a_new.first_name, ' ', a_new.middle_name) as new_client_name,
                          CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                          pp.plan as payment_plan
                   FROM reservation r 
                   JOIN account a_old ON r.account_id = a_old.account_id 
                   JOIN account a_new ON a_new.account_id = :new_account_id
                   JOIN lots l ON r.lot_id = l.lot_id
                   JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                   WHERE r.reservation_id = :reservation_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservationId);
            $query->bindParam(':new_account_id', $newAccountId);
            $query->execute();
            $currentReservation = $query->fetch(PDO::FETCH_ASSOC);
            
            if (!$currentReservation) {
                throw new Exception("Reservation not found.");
            }

            // Update the reservation with new account
            $sqlUpdate = "UPDATE reservation 
                         SET account_id = :new_account_id 
                         WHERE reservation_id = :reservation_id";
            $queryUpdate = $this->db->connect()->prepare($sqlUpdate);
            $queryUpdate->bindParam(':new_account_id', $newAccountId);
            $queryUpdate->bindParam(':reservation_id', $reservationId);
            
            if (!$queryUpdate->execute()) {
                throw new Exception("Failed to transfer reservation.");
            }

            // Create notifications for both old and new account holders
            $notificationObj = new Notification();
            $lotName = $this->account_lot($reservationId);
            
            // Notify previous owner
            $title = "Reservation Transferred";
            $message = "Your reservation for lot " . $lotName . " has been transferred to another account.";
            $notificationObj->createNotification($currentReservation['account_id'], 'reservation_status', $title, $message, $reservationId);
            
            // Notify new owner
            $title = "New Reservation Received";
            $message = "A reservation for lot " . $lotName . " has been transferred to your account.";
            $notificationObj->createNotification($newAccountId, 'reservation_status', $title, $message, $reservationId);

            // Log the transfer action
            if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                require_once __DIR__ . '/../staffs/staffs.class.php';
                $staffs = new Staffs_class();
                
                // Create a detailed log message
                $logDetails = sprintf(
                    "Transfer Details:\n" .
                    "Lot: %s\n" .
                    "From: %s\n" .
                    "To: %s (%s)\n" .
                    "Payment Plan: %s\n" .
                    "Balance: ₱%.2f\n" .
                    "Monthly Payment: ₱%.2f",
                    $currentReservation['lot_details'],
                    $currentReservation['old_client_name'],
                    $currentReservation['new_client_name'],
                    $isNewAccount ? 'New Account' : 'Existing Account',
                    $currentReservation['payment_plan'],
                    $currentReservation['balance'],
                    $currentReservation['monthly_payment']
                );

                $staffs->addStaffLog(
                    $_SESSION['account']['account_id'],
                    'Transfer Reservation',
                    $logDetails
                );
            }

            $this->db->connect()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return $e->getMessage();
        }
    }

    public function getReservationById($reservationId) {
        $sql = "SELECT * FROM reservation WHERE reservation_id = :reservation_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservationId);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function getPaymentPlans() {
        $sql = "SELECT * FROM payment_plan WHERE is_deleted = 0 ORDER BY duration";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
}