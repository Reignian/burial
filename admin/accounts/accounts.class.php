<?php

require_once __DIR__ . '/../../database.php';

class Accounts_class{


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

}