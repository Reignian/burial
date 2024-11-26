<?php

require_once __DIR__ . '/../../database.php';

class Dashboard_class{

    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    public function getTotalLots() {
        $sql = "SELECT COUNT(*) FROM lots";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getTotalAvailableLots() {
        $sql = "SELECT COUNT(*) FROM lots WHERE status = 'Available'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getTotalReservations() {
        $sql = "SELECT COUNT(*) FROM reservation WHERE request = 'Confirmed'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getTotalRevenue($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(amount_paid) FROM payment";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " WHERE payment_date BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        } elseif ($startDate) {
            $sql .= " WHERE payment_date >= :startDate";
            $params[':startDate'] = $startDate;
        } elseif ($endDate) {
            $sql .= " WHERE payment_date <= :endDate";
            $params[':endDate'] = $endDate;
        }

        $query = $this->db->connect()->prepare($sql);
        $query->execute($params);
        return $query->fetchColumn() ?: 0;
    }

    public function getPendingPaymentsThisMonth() {
        $sql = "SELECT COUNT(DISTINCT r.reservation_id) 
                FROM reservation r
                LEFT JOIN payment p ON r.reservation_id = p.reservation_id
                WHERE r.request = 'Confirmed'
                AND r.balance > 0
                AND (
                    -- No payments made this month
                    NOT EXISTS (
                        SELECT 1 
                        FROM payment 
                        WHERE reservation_id = r.reservation_id 
                        AND YEAR(payment_date) = YEAR(CURDATE()) 
                        AND MONTH(payment_date) = MONTH(CURDATE())
                    )
                    OR
                    -- Last payment was before this month
                    (SELECT MAX(payment_date) 
                     FROM payment 
                     WHERE reservation_id = r.reservation_id) < DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE())-1 DAY)
                )";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getLatePayments() {
        $sql = "SELECT COUNT(*) FROM reservation r
                WHERE r.request = 'Confirmed'
                AND r.balance > 0
                AND DATE_ADD(r.reservation_date, INTERVAL (
                    SELECT COUNT(*) FROM payment p WHERE p.reservation_id = r.reservation_id
                ) MONTH) < CURDATE()";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getRecentReservations($limit = 10) {
        $sql = "SELECT l.lot_name, r.reservation_date, CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) AS reserved_by
                FROM reservation r
                JOIN lots l ON r.lot_id = l.lot_id
                JOIN account a ON r.account_id = a.account_id
                WHERE r.request = 'Confirmed'
                ORDER BY r.reservation_date DESC
                LIMIT :limit";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}