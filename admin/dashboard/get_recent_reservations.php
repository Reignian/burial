<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display but still log them

header('Content-Type: application/json'); // Set proper JSON header

try {
    require_once 'dashboard.class.php';
    
    $class = new Dashboard_class();
    $recentReservations = $class->getRecentReservations(10);
    
    // Format dates and ensure proper encoding
    foreach ($recentReservations as &$reservation) {
        $reservation['reservation_date'] = date('M d, Y', strtotime($reservation['reservation_date']));
        // Ensure proper UTF-8 encoding
        array_walk_recursive($reservation, function(&$item) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });
    }
    
    echo json_encode($recentReservations, JSON_THROW_ON_ERROR);
    
} catch (Exception $e) {
    // Return a valid JSON response even in case of error
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching reservations',
        'debug' => $e->getMessage()
    ]);
}
