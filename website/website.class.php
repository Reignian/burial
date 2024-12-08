<?php

require_once __DIR__ . '/../database.php';

class CMS{

    function __construct(){
        $this->db = new Database();
    }

    public function Pubmat1(){
        $sql = "SELECT * FROM pubmat_1;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Pubmat2(){
        $sql = "SELECT * FROM pubmat_2;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function About(){
        $sql = "SELECT * FROM about;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function About_team(){
        $sql = "SELECT * FROM about_team;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function About_2(){
        $sql = "SELECT * FROM about_2;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function About_main(){
        $sql = "SELECT * FROM about_main;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }




}