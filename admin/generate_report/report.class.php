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
                    CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) as client_name,
                    CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                    p.amount_paid,
                    CONCAT(pp.plan, ' (', pp.down_payment, '% down payment with ', pp.interest_rate, '% interest rate)') as payment_plan
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
                    CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                    l.status,
                    CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) as reserved_by,
                    r.reservation_date,
                    GREATEST(0, r.balance - COALESCE(SUM(p.amount_paid), 0)) as balance
                FROM lots l
                LEFT JOIN reservation r ON l.lot_id = r.lot_id AND r.request = 'Confirmed'
                LEFT JOIN account a ON r.account_id = a.account_id
                LEFT JOIN payment p ON r.reservation_id = p.reservation_id
                GROUP BY l.lot_name, l.location, l.status, a.last_name, a.first_name, a.middle_name, r.reservation_date, r.balance
                ORDER BY l.lot_name";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generatePaymentStatusReport() {
        $sql = "SELECT 
                    CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) as client_name,
                    CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                    r.reservation_date,
                    CONCAT(pp.plan, ' (', pp.down_payment, '% down payment with ', pp.interest_rate, '% interest rate)') as payment_plan,
                    r.monthly_payment,
                    GREATEST(0, r.balance - COALESCE(SUM(p.amount_paid), 0)) as balance,
                    COALESCE((
                        SELECT SUM(penalty_amount)
                        FROM penalty_log pl
                        WHERE pl.reservation_id = r.reservation_id
                    ), 0) as total_penalties,
                    (
                        SELECT MAX(penalty_date)
                        FROM penalty_log pl
                        WHERE pl.reservation_id = r.reservation_id
                    ) as last_penalty_date,
                    CASE 
                        WHEN (r.balance - COALESCE(SUM(p.amount_paid), 0)) <= 0 THEN 'Fully Paid'
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
                LEFT JOIN payment p ON r.reservation_id = p.reservation_id
                WHERE r.request = 'Confirmed'
                GROUP BY r.reservation_id, a.last_name, a.first_name, a.middle_name, l.lot_name, l.location, 
                         r.reservation_date, pp.plan, pp.down_payment, pp.interest_rate, r.monthly_payment, r.balance
                ORDER BY 
                    CASE 
                        WHEN (r.balance - COALESCE(SUM(p.amount_paid), 0)) <= 0 THEN 3
                        WHEN DATEDIFF(CURDATE(), r.reservation_date) > 30 
                            AND NOT EXISTS (
                                SELECT 1 FROM payment 
                                WHERE reservation_id = r.reservation_id 
                                AND MONTH(payment_date) = MONTH(CURRENT_DATE)
                            ) THEN 1
                        ELSE 2
                    END,
                    r.reservation_date DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generatePenaltyReport() {
        $sql = "SELECT 
                    CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) as client_name,
                    CONCAT(l.lot_name, ' - ', l.location) as lot_details,
                    GROUP_CONCAT(
                        DATE_FORMAT(pl.penalty_date, '%b %d, %Y')
                        ORDER BY pl.penalty_date DESC
                        SEPARATOR '\n'
                    ) as penalty_dates,
                    COUNT(pl.penalty_id) as penalty_count,
                    SUM(pl.penalty_amount) as total_penalty_amount,
                    CONCAT(pp.plan, ' (', pp.down_payment, '% down payment with ', pp.interest_rate, '% interest rate)') as payment_plan,
                    r.monthly_payment,
                    GREATEST(0, r.balance - COALESCE(
                        (SELECT SUM(amount_paid) 
                         FROM payment 
                         WHERE reservation_id = r.reservation_id)
                    , 0)) as current_balance,
                    r.reservation_date
                FROM reservation r
                JOIN account a ON r.account_id = a.account_id
                JOIN lots l ON r.lot_id = l.lot_id
                JOIN payment_plan pp ON r.payment_plan_id = pp.payment_plan_id
                JOIN penalty_log pl ON r.reservation_id = pl.reservation_id
                WHERE r.request = 'Confirmed'
                GROUP BY r.reservation_id, a.last_name, a.first_name, a.middle_name, 
                         l.lot_name, l.location, pp.plan, pp.down_payment, pp.interest_rate,
                         r.monthly_payment, r.balance, r.reservation_date
                ORDER BY COUNT(pl.penalty_id) DESC, MAX(pl.penalty_date) DESC";
        
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
        $sql = "SELECT COALESCE(SUM(GREATEST(0, r.balance - COALESCE(p_sum.total_paid, 0))), 0) as total_balance 
                FROM reservation r 
                LEFT JOIN (
                    SELECT reservation_id, SUM(amount_paid) as total_paid 
                    FROM payment 
                    GROUP BY reservation_id
                ) p_sum ON r.reservation_id = p_sum.reservation_id
                WHERE r.request = 'Confirmed'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $totalBalance = $query->fetchColumn();

        // Payment Status Counts
        $sql = "SELECT 
                    COUNT(CASE WHEN (r.balance - COALESCE(p_sum.total_paid, 0)) <= 0 THEN 1 END) as fully_paid,
                    COUNT(CASE WHEN (r.balance - COALESCE(p_sum.total_paid, 0)) > 0 THEN 1 END) as pending_payment
                FROM reservation r
                LEFT JOIN (
                    SELECT reservation_id, SUM(amount_paid) as total_paid 
                    FROM payment 
                    GROUP BY reservation_id
                ) p_sum ON r.reservation_id = p_sum.reservation_id
                WHERE r.request = 'Confirmed'";
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