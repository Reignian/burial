<?php

require_once __DIR__ . '/../../database.php';

class Report_class{

    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    public function generateRevenueReport($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    p.payment_date,
                    CONCAT(a.last_name, ', ', a.first_name) as client_name,
                    l.lot_name,
                    p.amount_paid,
                    pp.plan as payment_plan
                FROM payment p
                JOIN reservation r ON p.reservation_id = r.reservation_id
                JOIN account a ON r.account_id = a.account_id
                JOIN lots l ON r.lot_id = l.lot_id
                JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE DATE(p.payment_date) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }
        
        $sql .= " ORDER BY p.payment_date DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateLotStatusReport() {
        $sql = "SELECT 
                    l.lot_name,
                    l.status,
                    CONCAT(a.last_name, ', ', a.first_name) as reserved_by,
                    r.reservation_date,
                    r.balance
                FROM lots l
                LEFT JOIN reservation r ON l.lot_id = r.lot_id AND r.request = 'Confirmed'
                LEFT JOIN account a ON r.account_id = a.account_id
                ORDER BY l.lot_name";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generatePaymentStatusReport() {
        $sql = "SELECT 
                    CONCAT(a.last_name, ', ', a.first_name) as client_name,
                    l.lot_name,
                    r.reservation_date,
                    pp.plan as payment_plan,
                    r.monthly_payment,
                    r.balance,
                    CASE 
                        WHEN r.balance <= 0 THEN 'Fully Paid'
                        WHEN DATEDIFF(CURDATE(), r.reservation_date) > 30 
                            AND NOT EXISTS (
                                SELECT 1 FROM payment 
                                WHERE reservation_id = r.reservation_id 
                                AND MONTH(payment_date) = MONTH(CURRENT_DATE)
                            ) THEN 'Late Payment'
                        ELSE 'Current'
                    END as payment_status
                FROM reservation r
                JOIN account a ON r.account_id = a.account_id
                JOIN lots l ON r.lot_id = l.lot_id
                JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                WHERE r.request = 'Confirmed'
                ORDER BY 
                    CASE payment_status 
                        WHEN 'Late Payment' THEN 1
                        WHEN 'Current' THEN 2
                        WHEN 'Fully Paid' THEN 3
                    END,
                    r.reservation_date DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportSummary($startDate = null, $endDate = null) {
        $params = [];
        $dateCondition = "";
        if ($startDate && $endDate) {
            $dateCondition = " AND DATE(p.payment_date) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }

        // Total Revenue
        $sql = "SELECT COALESCE(SUM(amount_paid), 0) as total_revenue 
                FROM payment p 
                WHERE 1=1" . $dateCondition;
        $query = $this->db->connect()->prepare($sql);
        $query->execute($params);
        $totalRevenue = $query->fetchColumn();

        // Total Outstanding Balance
        $sql = "SELECT COALESCE(SUM(balance), 0) as total_balance 
                FROM reservation 
                WHERE request = 'Confirmed'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $totalBalance = $query->fetchColumn();

        // Payment Status Counts
        $sql = "SELECT 
                    COUNT(CASE WHEN balance <= 0 THEN 1 END) as fully_paid,
                    COUNT(CASE WHEN balance > 0 THEN 1 END) as pending_payment
                FROM reservation 
                WHERE request = 'Confirmed'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $paymentCounts = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'total_revenue' => $totalRevenue,
            'total_balance' => $totalBalance,
            'fully_paid' => $paymentCounts['fully_paid'],
            'pending_payment' => $paymentCounts['pending_payment']
        ];
    }

}