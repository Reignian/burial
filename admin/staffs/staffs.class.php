<?php

require_once __DIR__ . '/../../database.php';

class Staffs_class {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    function showALL_staff() {
        $sql = "SELECT *, CASE WHEN is_banned = 1 THEN 'Banned' ELSE 'Active' END as status 
                FROM account 
                WHERE is_staff = 1";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()) {
            $data = $query->fetchAll();
        }

        return $data;
    }

    function toggleBanStatus($account_id) {
        $sql = "UPDATE account SET is_banned = NOT is_banned WHERE account_id = :account_id AND is_staff = 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        return $query->execute();
    }

    function usernameExist($username) {
        $sql = "SELECT COUNT(*) as count FROM account WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();
        return $result['count'] > 0;
    }

    function addStaff($first_name, $middle_name, $last_name, $username, $password, $email, $phone_number) {
        $sql = "INSERT INTO account (first_name, middle_name, last_name, username, password, email, phone_number, is_staff) 
                VALUES (:first_name, :middle_name, :last_name, :username, :password, :email, :phone_number, 1)";
        
        $query = $this->db->connect()->prepare($sql);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $query->bindParam(':middle_name', $middle_name, PDO::PARAM_STR);
        $query->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        
        return $query->execute();
    }
}