<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/reservations.class.php';

if (!isset($_GET['reservation_id']) || !is_numeric($_GET['reservation_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation ID']);
    exit();
}

try {
    $reservation_id = intval($_GET['reservation_id']);
    $reservationObj = new Reservation_class();
    
    // Log the reservation ID we're trying to fetch
    error_log("Fetching details for reservation ID: " . $reservation_id);
    
    $details = $reservationObj->getFullReservationDetails($reservation_id);
    
    if ($details) {
        // Log successful data retrieval
        error_log("Successfully retrieved details for reservation ID: " . $reservation_id);
        
        // Ensure proper data types for numeric values
        $numeric_fields = ['price', 'monthly_payment', 'balance'];
        foreach ($numeric_fields as $field) {
            if (isset($details[$field])) {
                $details[$field] = floatval($details[$field]);
            }
        }
        
        // Format dates
        if (isset($details['reservation_date'])) {
            $details['reservation_date'] = date('Y-m-d', strtotime($details['reservation_date']));
        }
        
        // Format payment dates
        if (isset($details['payments']) && is_array($details['payments'])) {
            foreach ($details['payments'] as &$payment) {
                if (isset($payment['payment_date'])) {
                    $payment['payment_date'] = date('Y-m-d', strtotime($payment['payment_date']));
                }
                if (isset($payment['amount'])) {
                    $payment['amount'] = floatval($payment['amount']);
                }
            }
        }
        
        echo json_encode($details, JSON_PRETTY_PRINT);
    } else {
        error_log("No details found for reservation ID: " . $reservation_id);
        http_response_code(404);
        echo json_encode(['error' => 'Reservation not found']);
    }
} catch (Exception $e) {
    $error_message = "Error in get_reservation_details.php: " . $e->getMessage() . "\n" . 
                    "Stack trace: " . $e->getTraceAsString();
    error_log($error_message);
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'details' => 'Check server error log for more details'
    ]);
}
