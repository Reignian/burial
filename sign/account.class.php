<?php

require_once __DIR__ . '/../database.php';

date_default_timezone_set('Asia/Singapore');

class Account{
    public $account_id = '';
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $username = '';
    public $password = '';
    public $email = '';
    public $phone_number = '';
    public $is_customer = 1;
    public $is_admin = 0;

    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    function add (){

        $sql = "INSERT INTO account (first_name, middle_name, last_name, username, password, email, phone_number, is_customer, is_admin) VALUES (:first_name, :middle_name, :last_name, :username, :password, :email, :phone_number, :is_customer, :is_admin);";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':first_name', $this->first_name);
        $query->bindParam(':middle_name', $this->middle_name);
        $query->bindParam(':last_name', $this->last_name);
        $query->bindParam(':username', $this->username);
        $hashpassword = password_hash($this->password, PASSWORD_DEFAULT);
        $query->bindParam(':password', $hashpassword);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':phone_number', $this->phone_number);
        $query->bindParam(':is_customer', $this->is_customer);
        $query->bindParam(':is_admin', $this->is_admin);

        return $query->execute();
    }

    function usernameExist($username, $excludeID = null){
        $sql = "SELECT COUNT(*) FROM account WHERE username = :username";
        if ($excludeID){
            $sql .= " and account_id != :excludeID";
        }

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username);

        if ($excludeID){
            $query->bindParam(':excludeID', $excludeID);
        }

        $count = $query->execute() ? $query->fetchColumn() : 0;

        return $count > 0;
    }

    function login($username, $password){
        $sql = "SELECT * FROM account WHERE username = :username LIMIT 1;";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam('username', $username);

        if($query->execute()){
            $data = $query->fetch();
            if($data && password_verify($password, $data['password'])){
                return true;
            }
        }

        return false;
    }

    function fetch($username){
        $sql = "SELECT * FROM account WHERE username = :username LIMIT 1;";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam('username', $username);
        $data = null;
        if($query->execute()){
            $data = $query->fetch();
        }

        return $data;
    }

    function getAccountBanStatus($account_id) {
        $sql = "SELECT is_banned FROM account WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    function update($account_id) {
        $sql = "UPDATE account SET first_name = :first_name, middle_name = :middle_name, last_name = :last_name, 
                username = :username, email = :email, phone_number = :phone_number 
                WHERE account_id = :account_id";
        
        $query = $this->db->connect()->prepare($sql);
        
        $query->bindParam(':first_name', $this->first_name);
        $query->bindParam(':middle_name', $this->middle_name);
        $query->bindParam(':last_name', $this->last_name);
        $query->bindParam(':username', $this->username);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':phone_number', $this->phone_number);
        $query->bindParam(':account_id', $account_id);
        
        return $query->execute();
    }

    function changePassword($account_id, $current_password, $new_password) {
        // First verify the current password
        $sql = "SELECT password FROM account WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        
        if ($query->execute()) {
            $data = $query->fetch();
            if ($data && password_verify($current_password, $data['password'])) {
                // Current password is correct, update to new password
                $sql = "UPDATE account SET password = :password WHERE account_id = :account_id";
                $query = $this->db->connect()->prepare($sql);
                
                $hashpassword = password_hash($new_password, PASSWORD_DEFAULT);
                $query->bindParam(':password', $hashpassword);
                $query->bindParam(':account_id', $account_id);
                
                return $query->execute();
            }
        }
        return false;
    }

    function verifyPassword($account_id, $password) {
        $sql = "SELECT password FROM account WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id);
        
        if ($query->execute()) {
            $data = $query->fetch();
            if ($data && password_verify($password, $data['password'])) {
                return true;
            }
        }
        return false;
    }

    // Save reset token in database
    public function saveResetToken($email, $token, $expiry) {
        try {
            $sql = "UPDATE account SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([$token, $expiry, $email]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Validate reset token
    public function validateResetToken($token) {
        try {
            $sql = "SELECT reset_token_expiry FROM account WHERE reset_token = ? AND reset_token_expiry > NOW()";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([$token]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Reset password
    public function resetPassword($token, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE account SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([$hashedPassword, $token]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Invalidate reset token
    public function invalidateResetToken($token) {
        try {
            $sql = "UPDATE account SET reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Check if email exists
    public function emailExists($email) {
        $sql = "SELECT account_id FROM account WHERE email = :email";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

// $obj = new Account();

// $obj->add();