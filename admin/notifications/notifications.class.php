<?php

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../website/notification.class.php';
require_once __DIR__ . '/../staffs/staffs.class.php';

class Notifications_class{

    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    function showrequest($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM reservation 
                ORDER BY 
                    CASE 
                        WHEN request = 'Pending' THEN 1 
                        ELSE 2 
                    END,
                    created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    function getTotalNotifications() {
        $sql = "SELECT COUNT(*) FROM reservation";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
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

    function fetchReservationRecord($recordID){
        $sql = "SELECT * FROM reservation WHERE reservation_id=:recordID;";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':recordID', $recordID);
        $data = null;

        if($query->execute()){
            $data = $query->fetch();
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

    public function fetchPayment_planRecord($payment_plan_id) {
        $sql = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_plan_id', $payment_plan_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function cancel_reservation($reservation_id) {
        $reservation = $this->fetchReservationRecord($reservation_id);
        if (!$reservation) {
            return false;
        }

        $sql = "UPDATE lots l
                JOIN reservation r ON l.lot_id = r.lot_id
                SET l.status = 'Available'
                WHERE r.reservation_id = :reservation_id;";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        
        if ($query->execute()) {
            $sqlupdate = "UPDATE reservation SET request = 'Cancelled' WHERE reservation_id = :reservation_id";
            $queryupdate = $this->db->connect()->prepare($sqlupdate);
            $queryupdate->bindParam(':reservation_id', $reservation_id);
    
            if ($queryupdate->execute()) {
                // Create notification for cancelled reservation
                $notificationObj = new Notification();
                $lotName = $this->account_lot($reservation_id);
                $accountName = $this->account($reservation_id);
                $lot = $this->fetchLotRecord($reservation['lot_id']);
                $payment_plan = $this->fetchPayment_planRecord($reservation['payment_plan_id']);
                $title = "Reservation Cancelled";
                $message = "Your reservation for lot " . $lotName . " has been cancelled.";
                $notificationObj->createNotification($reservation['account_id'], 'reservation_status', $title, $message, $reservation_id);
                
                // Add staff log with more details
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    $staffs = new Staffs_class();
                    $logDetails = sprintf(
                        "Cancelled Reservation Details:\n" .
                        "Reservation ID: #%d\n" .
                        "Client: %s\n" .
                        "Lot Details:\n" .
                        "- Name: %s\n" .
                        "- Location: %s\n" .
                        "- Size: %s sqm\n" .
                        "- Price: ₱%s\n" .
                        "Payment Plan: %s\n" .
                        "Reservation Date: %s\n" .
                        "Cancellation Date: %s",
                        $reservation_id,
                        $accountName,
                        $lot['lot_name'],
                        $lot['location'],
                        $lot['size'],
                        number_format($lot['price'], 2),
                        $payment_plan['plan'],
                        date('F j, Y', strtotime($reservation['reservation_date'])),
                        date('F j, Y g:i A')
                    );
                    $staffs->addStaffLog($_SESSION['account']['account_id'], 'Cancel Reservation', $logDetails);
                }
                
                return true;
            }
        }
        return false;
    }
    
    
    function confirm_reservation($reservation_id) {
        $reservation = $this->fetchReservationRecord($reservation_id);
        if (!$reservation) {
            return false;
        }

        $sql = "UPDATE lots l
                JOIN reservation r ON l.lot_id = r.lot_id
                SET l.status = 'Reserved'
                WHERE r.reservation_id = :reservation_id;";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':reservation_id', $reservation_id);
        
        if ($query->execute()) {
            $sqlupdate = "UPDATE reservation SET request = 'Confirmed' WHERE reservation_id = :reservation_id";
            $queryupdate = $this->db->connect()->prepare($sqlupdate);
            $queryupdate->bindParam(':reservation_id', $reservation_id);
    
            if ($queryupdate->execute()) {
                // Create notification for confirmed reservation
                $notificationObj = new Notification();
                $lotName = $this->account_lot($reservation_id);
                $accountName = $this->account($reservation_id);
                $lot = $this->fetchLotRecord($reservation['lot_id']);
                $payment_plan = $this->fetchPayment_planRecord($reservation['payment_plan_id']);
                $title = "Reservation Confirmed";
                $message = "Your reservation for lot " . $lotName . " has been confirmed.";
                $notificationObj->createNotification($reservation['account_id'], 'reservation_status', $title, $message, $reservation_id);
                
                // Add staff log with more details
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    $staffs = new Staffs_class();
                    $logDetails = sprintf(
                        "Confirmed Reservation Details:\n" .
                        "Reservation ID: #%d\n" .
                        "Client: %s\n" .
                        "Lot Details:\n" .
                        "- Name: %s\n" .
                        "- Location: %s\n" .
                        "- Size: %s sqm\n" .
                        "- Price: ₱%s\n" .
                        "Payment Plan: %s\n" .
                        "Monthly Payment: ₱%s\n" .
                        "Reservation Date: %s\n" .
                        "Confirmation Date: %s",
                        $reservation_id,
                        $accountName,
                        $lot['lot_name'],
                        $lot['location'],
                        $lot['size'],
                        number_format($lot['price'], 2),
                        $payment_plan['plan'],
                        number_format($reservation['monthly_payment'], 2),
                        date('F j, Y', strtotime($reservation['reservation_date'])),
                        date('F j, Y g:i A')
                    );
                    $staffs->addStaffLog($_SESSION['account']['account_id'], 'Confirm Reservation', $logDetails);
                }
                
                return true;
            }
        }
        return false;
    }

    function getPendingNotificationsCount() {
        $sql = "SELECT COUNT(*) FROM reservation WHERE request = 'Pending'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

}