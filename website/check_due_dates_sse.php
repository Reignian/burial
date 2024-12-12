<?php
// Set error handling first
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler
function errorHandler($errno, $errstr, $errfile, $errline) {
    $error = [
        'status' => 'error',
        'type' => 'php_error',
        'message' => "$errstr in $errfile on line $errline",
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo "data: " . json_encode($error) . "\n\n";
    flush();
    return true;
}
set_error_handler("errorHandler");

// Clear any existing output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
header('Access-Control-Allow-Origin: *');

// Set script execution parameters
set_time_limit(0);
ignore_user_abort(true);
date_default_timezone_set('Asia/Manila');

// Function to send SSE message with retry
function sendSSEMessage($data) {
    $id = uniqid();
    echo "id: $id\n";
    echo "retry: 1000\n";
    echo "data: " . json_encode($data) . "\n\n";
    flush();
}

try {
    // Include required files
    if (!file_exists('../database.php')) {
        throw new Exception("Database file not found");
    }
    if (!file_exists('lots.class.php')) {
        throw new Exception("Lots class file not found");
    }
    
    require_once '../database.php';
    require_once 'lots.class.php';
    
    // Send initial message
    sendSSEMessage([
        'status' => 'connected',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    // Create database connection
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }

    // Create reservation object
    $reservationObj = new Reservation();

    // Send connection success
    sendSSEMessage([
        'status' => 'initialized',
        'message' => 'Database connection successful',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    // Main loop
    while (true) {
        if (connection_aborted()) {
            exit();
        }

        try {
            // Get all active reservations with balance
            $sql = "SELECT 
                        r.reservation_id,
                        r.reservation_date,
                        r.monthly_payment,
                        COUNT(p.payment_id) AS payment_count,
                        pp.duration AS plan_months,
                        pp.plan,
                        r.balance
                    FROM 
                        reservation r
                    JOIN
                        payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                    LEFT JOIN
                        payment p ON r.reservation_id = p.reservation_id
                    WHERE
                        r.balance > 0 AND r.request = 'Confirmed'
                    GROUP BY
                        r.reservation_id, r.reservation_date, r.monthly_payment, pp.duration, pp.plan, r.balance";
            
            $query = $conn->prepare($sql);
            if (!$query) {
                throw new Exception("Failed to prepare query: " . implode(" ", $conn->errorInfo()));
            }
            
            if (!$query->execute()) {
                throw new Exception("Failed to execute query: " . implode(" ", $query->errorInfo()));
            }
            
            $reservations = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($reservations === false) {
                throw new Exception("Failed to fetch results: " . implode(" ", $query->errorInfo()));
            }
            
            $current_date = new DateTime();
            $current_date->setTime(0, 0, 0);
            $penalties_applied = 0;
            $reservations_checked = count($reservations);
            
            foreach ($reservations as $res) {
                try {
                    // Skip spot cash plans
                    if ($res['plan'] === 'Spot Cash') {
                        continue;
                    }

                    $reservation_date = new DateTime($res['reservation_date']);
                    $payment_count = (int)$res['payment_count'];
                    $monthly_payment = (float)$res['monthly_payment'];
                    
                    // Calculate next due date
                    $next_due_date = (clone $reservation_date)->modify("+{$payment_count} month");
                    $next_due_date->setTime(0, 0, 0);
                    
                    // If payment is late, calculate and apply penalty
                    if ($current_date > $next_due_date) {
                        $interval = $current_date->diff($next_due_date);
                        $days_late = $interval->days;
                        
                        $penalty_amount = $reservationObj->calculatePenalty($res['reservation_id'], $monthly_payment, $days_late);
                        if ($penalty_amount > 0) {
                            if ($reservationObj->applyPenaltyAndUpdateBalance($res['reservation_id'], $penalty_amount, $next_due_date)) {
                                $penalties_applied++;
                            }
                        }
                    }
                } catch (Exception $e) {
                    sendSSEMessage([
                        'status' => 'error',
                        'type' => 'reservation_processing_error',
                        'message' => "Error processing reservation {$res['reservation_id']}: " . $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    continue;
                }
            }
            
            // Send a status update
            sendSSEMessage([
                'status' => 'check_complete',
                'timestamp' => date('Y-m-d H:i:s'),
                'reservations_checked' => $reservations_checked,
                'penalties_applied' => $penalties_applied
            ]);

        } catch (Exception $e) {
            sendSSEMessage([
                'status' => 'error',
                'type' => 'main_loop_error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        sleep(30);
    }

} catch (Exception $e) {
    sendSSEMessage([
        'status' => 'fatal_error',
        'type' => 'initialization_error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
