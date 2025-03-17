<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/notification.class.php';

class Reservation{

    public $reservation_date = '';
    public $payment_plan_id = '';
    public $account_id = '';
    public $lot_id = '';
    public $balance = '';
    protected $db;

    function __construct(){
        $this->db = new Database();
    }
        
    public function displayAvailable_lots(){

        $sql = "SELECT * FROM lots WHERE status LIKE 'Available';";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    public function fetchLotRecord($lot_id) {
        $sql = "SELECT * FROM lots WHERE lot_id = :lot_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':lot_id', $lot_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAccountRecord($account_id) {
        $sql = "SELECT * FROM account WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchPayment_plan() {
        $sql = "SELECT payment_plan_id, CONCAT(plan, ' (', down_payment, '% down payment with ', interest_rate, '% interest rate)') AS pplan FROM payment_plan ;";
    
        $query = $this->db->connect()->prepare($sql);
        $data = null;
        if ($query->execute()) {
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    function addReservation() {
        // Fetch lot price
        $sqlPrice = "SELECT price FROM lots WHERE lot_id = :lot_id";
        $queryPrice = $this->db->connect()->prepare($sqlPrice);
        $queryPrice->bindParam(':lot_id', $this->lot_id);
        $queryPrice->execute();
        $result = $queryPrice->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $lotPrice = $result['price'];
    
            // Fetch payment plan details
            $sqlPlan = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
            $queryPlan = $this->db->connect()->prepare($sqlPlan);
            $queryPlan->bindParam(':payment_plan_id', $this->payment_plan_id);
            $queryPlan->execute();
            $paymentPlan = $queryPlan->fetch(PDO::FETCH_ASSOC);
    
            if ($paymentPlan) {
                // Calculate amortization
                $amortizationDetails = $this->calculateAmortization($lotPrice, $paymentPlan);
                
                // Set balance and monthly payment
                $this->balance = $amortizationDetails['totalBalance'];
                $monthlyPayment = $amortizationDetails['monthlyPayment'];
                // Insert the reservation
                $sql = "INSERT INTO reservation (account_id, lot_id, reservation_date, payment_plan_id, balance, monthly_payment) 
                        VALUES (:account_id, :lot_id, :reservation_date, :payment_plan_id, :balance, :monthly_payment)";
                
                $query = $this->db->connect()->prepare($sql);
    
                $query->bindParam(':account_id', $this->account_id);
                $query->bindParam(':lot_id', $this->lot_id);
                $query->bindParam(':reservation_date', $this->reservation_date);
                $query->bindParam(':payment_plan_id', $this->payment_plan_id);
                $query->bindParam(':balance', $this->balance);
                $query->bindParam(':monthly_payment', $monthlyPayment);
    
                if ($query->execute()) {
                    // Get the last inserted reservation ID
                    $reservation_id = $this->db->connect()->lastInsertId();
                    
                    // Update lot status
                    $sql = "UPDATE lots SET status = 'On Request' WHERE lot_id = :lot_id";
                    $queryUpdateStatus = $this->db->connect()->prepare($sql);
                    $queryUpdateStatus->bindParam(':lot_id', $this->lot_id);
                    $queryUpdateStatus->execute();
                    
                    // Create notification for pending reservation
                    $notificationObj = new Notification();
                    $lotRecord = $this->fetchLotRecord($this->lot_id);
                    $title = "Reservation Pending";
                    $message = "Your reservation for lot " . $lotRecord['lot_name'] . " has been submitted and is pending approval.";
                    $notificationObj->createNotification($this->account_id, 'reservation_status', $title, $message, $reservation_id);
    
                    return true;
                } else {
                    return false;
                }
            } else {
                echo 'Payment plan not found';
                return false;
            }
        } else {
            echo 'Lot not found';
            return false;
        }
    }

    private function calculateAmortization($lotPrice, $paymentPlan) {
        // Calculate down payment
        $downPaymentAmount = ($paymentPlan['down_payment'] / 100) * $lotPrice;
        $principalAmount = $lotPrice - $downPaymentAmount;

        // Convert annual interest rate to monthly
        $monthlyInterestRate = ($paymentPlan['interest_rate'] / 100) / 12;
        $numberOfPayments = $paymentPlan['duration'];
// For spot cash, set monthly payment to total amount (no amortization needed)
        if ($paymentPlan['plan'] === 'Spot Cash' || $paymentPlan['duration'] == 1) {
            $monthlyPayment = $principalAmount;
            $numberOfPayments = 1;  // Single payment for spot cash
        } else {
            // Amortization formula for installment plans
            if ($monthlyInterestRate > 0) {
                $monthlyPayment = $principalAmount * 
                                ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $numberOfPayments)) / 
                                (pow(1 + $monthlyInterestRate, $numberOfPayments) - 1);
            } else {
                // If interest rate is 0, just divide the principal by the number of payments
                $monthlyPayment = $principalAmount / $numberOfPayments;
            }
        }

        // Calculate total balance
        $totalBalance = ($monthlyPayment * $numberOfPayments) + $downPaymentAmount;

        return [
            'totalBalance' => $totalBalance,
            'monthlyPayment' => $monthlyPayment,
            'downPayment' => $downPaymentAmount
        ];
    }

    function getReservationsByAccountId($account_id) {
        $sql = "SELECT r.reservation_id, l.*, r.reservation_date, r.monthly_payment, pp.plan, r.balance, r.request
                FROM reservation r
                JOIN lots l ON r.lot_id = l.lot_id
                JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                WHERE r.account_id = :account_id AND r.request = 'Confirmed'
                ORDER BY r.reservation_date DESC";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    

    function getPayments($reservation_id) {
        $sql = "SELECT p.*
                FROM payment p
                JOIN reservation r ON p.reservation_id = r.reservation_id
                WHERE r.reservation_id = :reservation_id
                ORDER BY p.payment_date DESC";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
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

    function getNextPaymentSchedule($reservation_id) {
        // First check if there's any remaining balance
        $balance = $this->Balance($reservation_id);
        if ($balance <= 0) {
            return "Fully Paid";
        }

        // Get reservation details including payment count and plan
        $sql = "SELECT 
                    r.reservation_date,
                    r.monthly_payment,
                    COUNT(p.payment_id) AS payment_count,
                    pp.duration AS plan_months,
                    pp.plan
                FROM 
                    reservation r
                LEFT JOIN
                    payment p ON r.reservation_id = p.reservation_id
                JOIN
                    payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                WHERE
                    r.reservation_id = :reservation_id
                GROUP BY
                    r.reservation_date, r.monthly_payment, pp.duration, pp.plan";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return "Schedule not found";
        }

        // If it's spot cash, handle differently
        if ($result['plan'] === 'Spot Cash') {
            return "Due immediately (Spot Cash)";
        }

        // Calculate the next payment date based on reservation date and number of payments made
        $reservation_date = new DateTime($result['reservation_date']);
        $payment_count = (int)$result['payment_count'];
        $next_payment_date = (clone $reservation_date)->modify("+{$payment_count} month");

        // Format the date
        return $next_payment_date->format('F d, Y');
    }

    public function calculatePenalty($reservation_id, $monthly_payment, $days_late) {
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
            // Get all months between the due date and current date
            $current_date = new DateTime();
            $current_date->setTime(0, 0, 0);
            $check_date = clone $due_date;
            $check_date->setTime(0, 0, 0);
            
            $months_missed = [];
            while ($check_date <= $current_date) {
                $months_missed[] = clone $check_date;
                $check_date->modify('+1 month');
            }

            $total_penalties = 0;
            $penalties_applied = false;

            // Get account_id for the reservation
            $sqlGetAccount = "SELECT account_id FROM reservation WHERE reservation_id = :reservation_id";
            $queryGetAccount = $conn->prepare($sqlGetAccount);
            $queryGetAccount->bindParam(':reservation_id', $reservation_id);
            $queryGetAccount->execute();
            $account_id = $queryGetAccount->fetchColumn();

            foreach ($months_missed as $missed_date) {
                $formatted_date = $missed_date->format('Y-m-d H:i:s');
                $formatted_month = $missed_date->format('Y-m');

                // Check if a penalty has already been applied for this month
                $sqlCheckPenalty = "SELECT COUNT(*) FROM penalty_log 
                                    WHERE reservation_id = :reservation_id 
                                    AND DATE_FORMAT(penalty_date, '%Y-%m') = :check_month";
                $queryCheckPenalty = $conn->prepare($sqlCheckPenalty);
                $queryCheckPenalty->bindParam(':reservation_id', $reservation_id);
                $queryCheckPenalty->bindParam(':check_month', $formatted_month);
                
                if (!$queryCheckPenalty->execute()) {
                    throw new Exception("Failed to check existing penalty");
                }
                
                $penaltyExists = $queryCheckPenalty->fetchColumn();

                if ($penaltyExists == 0) {
                    // Log the penalty for this month
                    $sqlLogPenalty = "INSERT INTO penalty_log (reservation_id, penalty_amount, penalty_date) 
                                    VALUES (:reservation_id, :penalty_amount, :penalty_date)";
                    $queryLogPenalty = $conn->prepare($sqlLogPenalty);
                    $queryLogPenalty->bindParam(':reservation_id', $reservation_id);
                    $queryLogPenalty->bindParam(':penalty_amount', $penalty_amount);
                    $queryLogPenalty->bindParam(':penalty_date', $formatted_date);
                    
                    if (!$queryLogPenalty->execute()) {
                        throw new Exception("Failed to log penalty");
                    }

                    // Create notification for missed payment
                    require_once(__DIR__ . '/notification.class.php');
                    $notification = new Notification();
                    $notification->createNotification(
                        $account_id,
                        'payment_missed',
                        'Missed Payment Penalty Applied',
                        "A penalty of ₱" . number_format($penalty_amount, 2) . " has been applied to your account for missing the payment due on " . $missed_date->format('F j, Y'),
                        $reservation_id
                    );

                    $total_penalties += $penalty_amount;
                    $penalties_applied = true;
                }
            }

            if ($penalties_applied) {
                // Add all penalties to the balance
                $sqlUpdateBalance = "UPDATE reservation 
                                   SET balance = balance + :total_penalties 
                                   WHERE reservation_id = :reservation_id";
                $queryUpdateBalance = $conn->prepare($sqlUpdateBalance);
                $queryUpdateBalance->bindParam(':total_penalties', $total_penalties);
                $queryUpdateBalance->bindParam(':reservation_id', $reservation_id);
                
                if (!$queryUpdateBalance->execute()) {
                    throw new Exception("Failed to update balance");
                }

                // Commit the transaction
                $conn->commit();
                return true;
            }

            // No new penalties to apply
            $conn->rollBack();
            return false;

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
                JOIN
                    payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                LEFT JOIN
                    payment p ON r.reservation_id = p.reservation_id
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
        date_default_timezone_set('Asia/Manila');
        
        $reservation_date = new DateTime($result['reservation_date']);
        $payment_count = (int)$result['payment_count'];
        $monthly_payment = (float)$result['monthly_payment'];
        $plan_months = (int)$result['plan_months'];
        
        // Calculate next due date
        $next_due_date = (clone $reservation_date)->modify("+{$payment_count} month");
        $next_due_date->setTime(0, 0, 0);
        
        // Current date with time set to start of day
        $current_date = new DateTime();
        $current_date->setTime(0, 0, 0);
        
        // Calculate the number of days left
        $interval = $current_date->diff($next_due_date);
        $days_left = $interval->days;
        $is_past_due = $interval->invert === 1;

        // Check if the current date is before, on, or after the due date
        if ($current_date == $next_due_date) {
            return "<span style='color: orange;'>Due today</span>";
        } elseif ($is_past_due) {
            $penalty_amount = $this->calculatePenalty($reservation_id, $monthly_payment, $days_left);
            
            if ($penalty_amount > 0) {
                $penalty_applied = $this->applyPenaltyAndUpdateBalance($reservation_id, $penalty_amount, $next_due_date);
                
                if ($penalty_applied) {
                    return "<span style='color: red;'>{$days_left} day(s) passed since monthly due (Penalty of ₱{$penalty_amount} applied)</span>";
                } else {
                    return "<span style='color: red;'>{$days_left} day(s) passed since monthly due</span>";
                }
            } else {
                return "<span style='color: red;'>{$days_left} day(s) passed since monthly due (No penalty for spot cash)</span>";
            }
        } else {
            if ($days_left == 1) {
                return "<span>Due tomorrow</span>";
            }
            return "<span>{$days_left} day(s) left before monthly due</span>";
        }
    }

    function getPaymentStatus($reservation_id) {
        // First check if there's any remaining balance
        $balance = $this->Balance($reservation_id);
        if ($balance <= 0) {
            return 'paid';
        }

        // Get reservation details
        $sql = "SELECT 
                    r.reservation_date,
                    COUNT(p.payment_id) AS payment_count,
                    pp.plan
                FROM 
                    reservation r
                LEFT JOIN
                    payment p ON r.reservation_id = p.reservation_id
                JOIN
                    payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                WHERE
                    r.reservation_id = :reservation_id
                GROUP BY
                    r.reservation_date, pp.plan";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return 'error';
        }

        // If it's spot cash, it's overdue if not paid
        if ($result['plan'] === 'Spot Cash') {
            return 'overdue';
        }

        // Calculate the next payment date
        $reservation_date = new DateTime($result['reservation_date']);
        $payment_count = (int)$result['payment_count'];
        $next_payment_date = (clone $reservation_date)->modify("+{$payment_count} month");
        $next_payment_date->setTime(0, 0, 0);

        // Get current date
        $current_date = new DateTime();
        $current_date->setTime(0, 0, 0);

        // Compare dates
        if ($current_date == $next_payment_date) {
            return 'due_today';
        } elseif ($current_date > $next_payment_date) {
            return 'overdue';
        } else {
            return 'upcoming';
        }
    }
}