<?php
require_once 'notification.class.php';

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Prevent timeout
ignore_user_abort(true); // Keep running even if client disconnects

// Set timezone
date_default_timezone_set('Asia/Manila');

// Function to send SSE message
function sendSSEMessage($data) {
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}

// Function to check client connection
function isClientConnected() {
    return !connection_aborted();
}

try {
    // Create notification object
    $notificationObj = new Notification();

    while (isClientConnected()) {
        try {
            // Check for due payments
            $notificationObj->checkDuePayments();
            
            // Send a keepalive ping
            sendSSEMessage([
                'status' => 'alive', 
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Sleep for 30 seconds before next check (reduced from 5 minutes for better responsiveness)
            sleep(30);
        } catch (Exception $e) {
            sendSSEMessage([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            sleep(5); // Wait before retrying
        }
    }
} catch (Exception $e) {
    error_log("SSE Fatal Error: " . $e->getMessage());
    sendSSEMessage([
        'status' => 'fatal_error',
        'message' => 'Server error occurred'
    ]);
}
