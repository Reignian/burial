<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Start output buffering to prevent any unwanted output
ob_start();

try {
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/website/notification.class.php';
    require_once __DIR__ . '/website/lots.class.php';  // Add this line to include the Lots class

    // Log incoming parameters
    error_log("Received parameters: " . json_encode($_GET));

    // Initialize response array
    $response = [
        'notification' => null,
        'reservation' => null,
        'lot' => null,
        'error' => null,
        'debug' => [] // Add debug information
    ];

    if (!isset($_GET['notification_id']) || !isset($_GET['reference_id'])) {
        throw new Exception('Missing required parameters: ' . json_encode($_GET));
    }

    $db = new Database();
    $conn = $db->connect();
    $notification = new Notification();
    $reservation = new Reservation();  // Create an instance of the Reservation class

    // Get notification details
    $sql = "SELECT * FROM notifications WHERE notification_id = :notification_id";
    $query = $conn->prepare($sql);
    $query->bindParam(':notification_id', $_GET['notification_id']);
    $query->execute();
    $notificationData = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$notificationData) {
        throw new Exception('Notification not found for ID: ' . $_GET['notification_id']);
    }
    
    $response['notification'] = $notificationData;
    $response['debug'][] = 'Successfully retrieved notification data';

    // If there's a reference_id and it's a payment-related notification
    if ($_GET['reference_id'] && isset($_GET['type']) !== false) {
        // Get reservation details with payment plan
        $sql = "SELECT r.*, CONCAT(pp.plan, ' (', pp.down_payment, '% down payment with ', pp.interest_rate, '% interest rate)') AS pplan
            FROM reservation r 
            LEFT JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id 
            WHERE r.reservation_id = :reservation_id";
        $query = $conn->prepare($sql);
        $query->bindParam(':reservation_id', $_GET['reference_id']);
        $query->execute();
        $reservationData = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($reservationData) {
            try {
                // Get the updated balance using the Balance function from Reservation class
                $response['debug'][] = 'Calculating balance for reservation ID: ' . $_GET['reference_id'];
                $updated_balance = $reservation->Balance($_GET['reference_id']);  // Use the reservation instance instead
                $response['debug'][] = 'Calculated balance: ' . $updated_balance;
                
                $reservationData['balance'] = $updated_balance;
                $response['reservation'] = $reservationData;
                $response['debug'][] = 'Successfully added reservation data';

                // Get lot details if lot_id exists
                if (isset($reservationData['lot_id'])) {
                    $sql = "SELECT * FROM lots WHERE lot_id = :lot_id";
                    $query = $conn->prepare($sql);
                    $query->bindParam(':lot_id', $reservationData['lot_id']);
                    $query->execute();
                    $response['lot'] = $query->fetch(PDO::FETCH_ASSOC);
                    $response['debug'][] = 'Successfully added lot data';
                }
            } catch (Exception $e) {
                error_log('Error in Balance calculation: ' . $e->getMessage());
                throw new Exception('Error calculating balance: ' . $e->getMessage());
            }
        } else {
            throw new Exception('Reservation not found for ID: ' . $_GET['reference_id']);
        }
    }

    // Clear any output buffers
    ob_clean();
    
    // Set headers
    header('Content-Type: application/json');
    header('HTTP/1.1 200 OK');
    
    // Log response before sending
    error_log('Sending response: ' . json_encode($response));
    
    // Send response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_notification_details.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Clear any output buffers
    ob_clean();
    
    // Set headers
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    
    // Send error response
    $errorResponse = [
        'error' => $e->getMessage(),
        'notification' => null,
        'reservation' => null,
        'lot' => null,
        'debug_trace' => $e->getTraceAsString()
    ];
    
    echo json_encode($errorResponse);
}

// End output buffering
ob_end_flush();
