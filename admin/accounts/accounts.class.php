<?php

require_once __DIR__ . '/../../database.php';

class Accounts_class{
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $username = '';
    public $password = '';
    public $email = '';
    public $phone_number = '';
    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    function showALL_account(){
        $sql = "SELECT *, CASE WHEN is_banned = 1 THEN 'Banned' ELSE 'Active' END as status FROM account WHERE (is_customer = 1 AND is_admin = 0);";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    function toggleBanStatus($account_id) {
        $sql = "UPDATE account SET is_banned = NOT is_banned WHERE account_id = :account_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        return $query->execute();
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
}