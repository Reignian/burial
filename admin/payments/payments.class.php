<?php

require_once __DIR__ . '/../../database.php';

class Payments_class{

    public $payment_id = '';
    public $payment_plan_id = '';
    public $plan = '';
    public $duration = '';
    public $down_payment = '';
    public $interest_rate = '';


    protected $db;

    function __construct(){
        $this->db = new Database();
    }
    function showALL_payments(){

        $sql = "SELECT * FROM payment ORDER by payment_date DESC;";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    public function showPaymentPlans() {
        $sql = "SELECT * FROM payment_plan WHERE is_deleted = 0";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    function payer($payment_id) {
        $sql = "SELECT CONCAT(c.last_name, ', ', c.first_name, ' ', c.middle_name) AS account_name
                FROM payment p
                JOIN reservation r ON p.reservation_id = r.reservation_id
                JOIN account c ON r.account_id = c.account_id
                WHERE p.payment_id = :payment_id";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_id', $payment_id);
        $query->execute();
        return $query->fetchColumn();
    }

    function payerlot($payment_id) {
        $sql = "SELECT l.lot_name
                FROM payment p
                JOIN reservation r ON p.reservation_id = r.reservation_id
                JOIN lots l ON r.lot_id = l.lot_id
                WHERE p.payment_id = :payment_id";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_id', $payment_id);
        $query->execute();
        return $query->fetchColumn();
    }

    function balhistory($payment_id) {
        $sql = "SELECT r.balance - COALESCE(SUM(p.amount_paid), 0) AS remaining_balance
                FROM reservation r
                LEFT JOIN payment p ON r.reservation_id = p.reservation_id
                WHERE r.reservation_id = (
                    SELECT reservation_id FROM payment WHERE payment_id = :payment_id1
                )
                AND p.payment_date <= (
                    SELECT payment_date FROM payment WHERE payment_id = :payment_id2
                )
                GROUP BY r.balance;"; 
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_id1', $payment_id);
        $query->bindParam(':payment_id2', $payment_id);
        $query->execute();
        
        return $query->fetchColumn();
    }

    function addpayment_plan() {
        $sql = "INSERT INTO payment_plan (plan, duration, down_payment, interest_rate) VALUES (:plan, :duration, :down_payment, :interest_rate)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':plan',$this->plan);
        $query->bindParam(':duration',$this->duration);
        $query->bindParam(':down_payment',$this->down_payment);
        $query->bindParam(':interest_rate',$this->interest_rate);
    
        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaymentPlan($payment_plan_id) {
        $sql = "SELECT * FROM payment_plan WHERE payment_plan_id = :payment_plan_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_plan_id', $payment_plan_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePaymentPlan($payment_plan_id, $plan, $duration, $down_payment, $interest_rate) {
        $sql = "UPDATE payment_plan SET plan = :plan, duration = :duration, down_payment = :down_payment, interest_rate = :interest_rate WHERE payment_plan_id = :payment_plan_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':plan', $plan);
        $query->bindParam(':duration', $duration);
        $query->bindParam(':down_payment', $down_payment);
        $query->bindParam(':interest_rate', $interest_rate);
        $query->bindParam(':payment_plan_id', $payment_plan_id, PDO::PARAM_INT);
        
        return $query->execute();
    }

    public function softDeletePaymentPlan($payment_plan_id) {
        $sql = "UPDATE payment_plan SET is_deleted = 1 WHERE payment_plan_id = :payment_plan_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':payment_plan_id', $payment_plan_id, PDO::PARAM_INT);
        
        return $query->execute();
    }

    function deletePayment($recordID) {
        $sql = "DELETE FROM payment WHERE payment_id = :recordID";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':recordID', $recordID, PDO::PARAM_INT);
    
        return $query->execute();
    }

}