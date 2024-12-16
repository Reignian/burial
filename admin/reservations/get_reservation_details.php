<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/reservations.class.php';

if (!isset($_GET['reservation_id']) || !is_numeric($_GET['reservation_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation ID']);
    exit();
}

try {
    $reservation_id = intval($_GET['reservation_id']);
    $reservationObj = new Reservation_class();
    $details = $reservationObj->getFullReservationDetails($reservation_id);
    
    if ($details) {
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
        http_response_code(404);
        echo json_encode(['error' => 'Reservation not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
