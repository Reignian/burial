<?php

require_once '../database.php';

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

    function add(){

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

}

// $obj = new Account();

// $obj->add();