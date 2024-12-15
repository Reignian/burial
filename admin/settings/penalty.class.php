<?php

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../staffs/staffs.class.php';

class Penalty {
    private $db;
    private $staffLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = new Database();
        $this->staffLog = new Staffs_class();
    }

    public function getPenaltyRate() {
        $sql = "SELECT penalty_amount FROM penalty WHERE penalty_id = 1";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC)['penalty_amount'];
    }

    public function updatePenaltyRate($newRate) {
        $sql = "UPDATE penalty SET penalty_amount = :rate WHERE penalty_id = 1";
        $query = $this->db->connect()->prepare($sql);
        $success = $query->execute(['rate' => $newRate]);

        if ($success) {
            // Log the change
            $staffId = isset($_SESSION['account']['account_id']) ? $_SESSION['account']['account_id'] : null;
            if ($staffId) {
                $this->staffLog->addStaffLog(
                    $staffId,
                    'UPDATE',
                    "Updated penalty rate to {$newRate}%"
                );
            }
        }

        return $success;
    }
}
