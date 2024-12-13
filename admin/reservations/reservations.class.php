<?php

require_once __DIR__ . '/../../database.php';

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
        $this->db->connect()->beginTransaction();

        try {
            // Check if the reservation exists and get its details
            $sqlCheckReservation = "SELECT r.reservation_id, r.reservation_date, r.balance, l.lot_id 
                                    FROM reservation r
                                    JOIN lots l ON r.lot_id = l.lot_id
                                    WHERE r.reservation_id = :reservation_id";
            $queryCheckReservation = $this->db->connect()->prepare($sqlCheckReservation);
            $queryCheckReservation->bindParam(':reservation_id', $reservationID);
            $queryCheckReservation->execute();
            $reservationDetails = $queryCheckReservation->fetch(PDO::FETCH_ASSOC);

            if (!$reservationDetails) {
                throw new Exception("Reservation not found.");
            }

            // Check for payments and time passed (for warning purposes)
            $hasPayments = $this->hasPayments($reservationID);
            $reservationDate = new DateTime($reservationDetails['reservation_date']);
            $now = new DateTime();
            $daysPassed = $now->diff($reservationDate)->days;

            // Update reservation status to 'Cancelled'
            $sqlUpdateReservation = "UPDATE reservation SET request = 'Cancelled' WHERE reservation_id = :reservation_id";
            $queryUpdateReservation = $this->db->connect()->prepare($sqlUpdateReservation);
            $queryUpdateReservation->bindParam(':reservation_id', $reservationID);
            $queryUpdateReservation->execute();

            // Update the lot status back to 'Available'
            $sqlUpdateLot = "UPDATE lots SET status = 'Available' WHERE lot_id = :lot_id";
            $queryUpdateLot = $this->db->connect()->prepare($sqlUpdateLot);
            $queryUpdateLot->bindParam(':lot_id', $reservationDetails['lot_id']);
            $queryUpdateLot->execute();

            $this->db->connect()->commit();

            // Return a warning message if applicable
            if ($hasPayments || $daysPassed > 1) {
                return "Warning: Reservation cancelled, but it had existing payments or was older than 24 hours.";
            }

            return true;
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return $e->getMessage();
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
        $sql = "SELECT l.lot_name
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
            // For all other plans, apply penalty
            return $monthly_payment * 0.03; // 3% penalty
        }
    }

    function applyPenaltyAndUpdateBalance($reservation_id, $penalty_amount, $due_date) {
        // Start a transaction
        $this->db->connect()->beginTransaction();

        try {
            // Format the due date
            $formatted_due_date = $due_date->format('Y-m-d');

            // Check if a penalty has already been applied for this month
            $sqlCheckPenalty = "SELECT COUNT(*) FROM penalty_log 
                                WHERE reservation_id = :reservation_id 
                                AND YEAR(penalty_date) = YEAR(:due_date) 
                                AND MONTH(penalty_date) = MONTH(:due_date)";
            $queryCheckPenalty = $this->db->connect()->prepare($sqlCheckPenalty);
            $queryCheckPenalty->bindParam(':reservation_id', $reservation_id);
            $queryCheckPenalty->bindParam(':due_date', $formatted_due_date);
            $queryCheckPenalty->execute();
            $penaltyExists = $queryCheckPenalty->fetchColumn();

            if ($penaltyExists == 0) {
                // Add the penalty to the balance
                $sqlUpdateBalance = "UPDATE reservation SET balance = balance + :penalty_amount WHERE reservation_id = :reservation_id";
                $queryUpdateBalance = $this->db->connect()->prepare($sqlUpdateBalance);
                $queryUpdateBalance->bindParam(':penalty_amount', $penalty_amount);
                $queryUpdateBalance->bindParam(':reservation_id', $reservation_id);
                $queryUpdateBalance->execute();

                // Log the penalty
                $sqlLogPenalty = "INSERT INTO penalty_log (reservation_id, penalty_amount, penalty_date) VALUES (:reservation_id, :penalty_amount, :due_date)";
                $queryLogPenalty = $this->db->connect()->prepare($sqlLogPenalty);
                $queryLogPenalty->bindParam(':reservation_id', $reservation_id);
                $queryLogPenalty->bindParam(':penalty_amount', $penalty_amount);
                $queryLogPenalty->bindParam(':due_date', $formatted_due_date);
                $queryLogPenalty->execute();

                // Commit the transaction
                $this->db->connect()->commit();
                return true;
            } else {
                // Penalty already applied this month
                $this->db->connect()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
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

    function addPayment($reservation_id, $amount_paid) {
        // Start transaction
        $this->db->connect()->beginTransaction();

        try {
            // Get account_id for the reservation
            $sql = "SELECT account_id FROM reservation WHERE reservation_id = :reservation_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->execute();
            $account_id = $query->fetchColumn();

            // Insert payment
            $sql = "INSERT INTO payment (reservation_id, amount_paid) VALUES (:reservation_id, :amount_paid)";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':reservation_id', $reservation_id);
            $query->bindParam(':amount_paid', $amount_paid);
            
            if ($query->execute()) {
                // Create notification
                $this->createPaymentSuccessNotification($account_id, $reservation_id, $amount_paid);

                $this->db->connect()->commit();
                return true;
            } else {
                $this->db->connect()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return false;
        }
    }
}