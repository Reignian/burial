<?php

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../staffs/staffs.class.php';

class Accounts_class {
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $username = '';
    public $password = '';
    public $email = '';
    public $phone_number = '';
    protected $db;
    protected $staffs;

    function __construct(){
        $this->db = new Database();
        $this->staffs = new Staffs_class();
    }

    function showALL_account(){
        $sql = "SELECT *, CASE WHEN is_banned = 1 THEN 'Banned' ELSE 'Active' END as status FROM account WHERE (is_customer = 1 AND is_admin = 0 AND is_deleted = 0) ORDER BY created_at DESC;";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    function toggleBanStatus($account_id, $reason) {
        $sql = "UPDATE account SET is_banned = NOT is_banned WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        if ($query->execute()) {
            // Get the updated status
            $statusSql = "SELECT is_banned, CONCAT(first_name, ' ', last_name) as full_name FROM account WHERE account_id = :account_id";
            $statusQuery = $this->db->connect()->prepare($statusSql);
            $statusQuery->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            $statusQuery->execute();
            $result = $statusQuery->fetch();
            
            $action = $result['is_banned'] ? "Banned customer account" : "Unbanned customer account";
            $details = "Customer: " . $result['full_name'] . "\nReason: " . $reason;
            
            // Log the action
            $this->staffs->addStaffLog($_SESSION['account']['account_id'], $action, $details);
            return true;
        }
        return false;
    }

    function createAccount(){
        $sql = "INSERT INTO account (first_name, middle_name, last_name, username, password, email, phone_number, is_customer) VALUES (:first_name, :middle_name, :last_name, :username, :password, :email, :phone_number, 1);";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':first_name', $this->first_name);
        $query->bindParam(':middle_name', $this->middle_name);
        $query->bindParam(':last_name', $this->last_name);
        $query->bindParam(':username', $this->username);
        $query->bindParam(':password', $this->password);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':phone_number', $this->phone_number);
        
        if ($query->execute()) {
            return $this->db->connect()->lastInsertId();
        }
        return false;
    }

    function hasActiveReservations($account_id) {
        $sql = "SELECT COUNT(*) as count FROM reservation WHERE account_id = ? AND request = 'Confirmed'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute([$account_id]);
        $result = $query->fetch();
        return $result['count'] > 0;
    }

    function deleteAccount($account_id, $reason) {
        if ($this->hasActiveReservations($account_id)) {
            return false;
        }
        
        // Get account details before deletion for logging
        $detailsSql = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM account WHERE account_id = ?";
        $detailsQuery = $this->db->connect()->prepare($detailsSql);
        $detailsQuery->execute([$account_id]);
        $accountDetails = $detailsQuery->fetch();

        $sql = "UPDATE account SET is_deleted = 1 WHERE account_id = ?";
        $query = $this->db->connect()->prepare($sql);
        if ($query->execute([$account_id])) {
            // Log the deletion with reason
            $details = "Deleted customer account: " . $accountDetails['full_name'] . "\nReason: " . $reason;
            $this->staffs->addStaffLog($_SESSION['account']['account_id'], "Deleted customer account", $details);
            return true;
        }
        return false;
    }

    function updateAccount($account_id, $first_name, $middle_name, $last_name, $email, $phone_number, $username) {
        try {
            // First check if username is already taken by another account
            $checkSql = "SELECT account_id FROM account WHERE username = ? AND account_id != ?";
            $checkQuery = $this->db->connect()->prepare($checkSql);
            $checkQuery->execute([$username, $account_id]);
            
            if ($checkQuery->rowCount() > 0) {
                throw new Exception("Username already exists");
            }

            $sql = "UPDATE account SET 
                    first_name = ?, 
                    middle_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone_number = ?,
                    username = ?
                    WHERE account_id = ?";
            
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([$first_name, $middle_name, $last_name, $email, $phone_number, $username, $account_id]);
        } catch (PDOException $e) {
            error_log("Error updating account: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error updating account: " . $e->getMessage());
            throw $e;
        }
    }
}