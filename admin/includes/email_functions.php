<?php
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendReservationSummaryEmail($details) {
    global $db;
    $mail = new PHPMailer(true);

    try {
        // Get contact information from database
        $contact_query = "SELECT * FROM contact WHERE id = 1";
        $stmt = $db->connect()->prepare($contact_query);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'stoninoparishcemetery@gmail.com';
        $mail->Password = 'vbfq umvs ibff xxjv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('stoninoparishcemetery@gmail.com', 'Sto. Nino Parish Cemetery Office');
        $mail->addAddress($details['account']['email'], $details['account']['first_name'] . ' ' . $details['account']['last_name']);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Your Reservation Summary - Sto. Nino Parish Cemetery';

        // Email body
        $body = '<h2>Sto. Nino Parish Cemetery</h2>';
        $body .= '<h3>Reservation Summary</h3>';
        $body .= '<p>Date: ' . date('F d, Y') . '</p>';
        
        $body .= '<h4>Account Information</h4>';
        $body .= '<p>Name: ' . $details['account']['first_name'] . ' ' . 
                ($details['account']['middle_name'] ? $details['account']['middle_name'] . ' ' : '') . 
                $details['account']['last_name'] . '</p>';
        $body .= '<p>Email: ' . $details['account']['email'] . '</p>';
        $body .= '<p>Phone: ' . $details['account']['phone_number'] . '</p>';
        
        if (!empty($details['account']['username'])) {
            $body .= '<div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            $body .= '<h4 style="color: #856404; margin-top: 0;">Important: Your Login Credentials</h4>';
            $body .= '<p>Username: ' . $details['account']['username'] . '</p>';
            $body .= '<p>Password: ' . $details['account']['password'] . '</p>';
            $body .= '<p><strong>Security Notice:</strong> For your security, please change your password immediately after logging in. You can do this by:</p>';
            $body .= '<ol>';
            $body .= '<li>Visit our website and log in with the credentials above</li>';
            $body .= '<li>Go to your Account Settings</li>';
            $body .= '<li>Click on "Change Password"</li>';
            $body .= '<li>Choose a strong, unique password</li>';
            $body .= '</ol>';
            $body .= '</div>';
        }
        
        $body .= '<h4>Payment Details</h4>';
        $body .= '<p>Lot Price: PHP ' . number_format($details['lot']['price'], 2) . '</p>';
        $body .= '<p>Down Payment: PHP ' . number_format($details['payment']['down_payment'], 2) . '</p>';
        $body .= '<p>Monthly Payment: PHP ' . number_format($details['payment']['monthly_payment'], 2) . '</p>';
        $body .= '<p>Interest Rate: ' . $details['payment']['interest_rate'] . '%</p>';
        $body .= '<p>Payment Duration: ' . $details['payment']['payment_duration'] . ' months</p>';
        $body .= '<p>Total Balance: PHP ' . number_format($details['payment']['total_balance'], 2) . '</p>';

        $body .= '<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">';
        $body .= '<h4>Thank You!</h4>';
        $body .= '<p>Thank you for choosing Sto. Nino Parish Cemetery. We appreciate your trust in our services.</p>';
        $body .= '<p>If you have any questions or concerns, please don\'t hesitate to contact us:</p>';
        $body .= '<ul>';
        $body .= '<li>Email: stoninoparishcemetery@gmail.com</li>';
        if ($contact) {
            $body .= '<li>Phone: ' . $contact['phone'] . '</li>';
            $body .= '<li>Address: ' . $contact['address'] . '</li>';
        }
        $body .= '</ul>';
        $body .= '</div>';

        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}
