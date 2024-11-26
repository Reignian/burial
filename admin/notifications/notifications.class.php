<?php

require_once __DIR__ . '/../../database.php';

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
    
            $queryupdate->execute();
        
            return true;
        } else {
            return false;
        }
    }
    
    
    function confirm_reservation($reservation_id) {
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
    
            $queryupdate->execute();
        
            return true;
        } else {
            return false;
        }
    }

    function getPendingNotificationsCount() {
        $sql = "SELECT COUNT(*) FROM reservation WHERE request = 'Pending'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

}