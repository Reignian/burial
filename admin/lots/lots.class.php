<?php

require_once __DIR__ . '/../../database.php';

class Lots_class{

    public $lot_id = '';
    public $lot_name = '';
    public $lot_image = '';
    public $location = '';
    public $size = '';
    public $price = '';
    public $description = '';


    protected $db;

    function __construct(){
        $this->db = new Database();
    }

    function showALL_lots(){

        $sql = "SELECT * FROM lots;";
        $query = $this->db->connect()->prepare($sql);
        $data = null;

        if($query->execute()){
            $data = $query->fetchAll();
        }

        return $data;
    }

    function addLot(){
        $sql = "INSERT INTO lots (lot_name, location, size, price, lot_image, description) VALUES (:lot_name, :location, :size, :price, :lot_image, :description);";

        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':lot_name', $this->lot_name);
        $query->bindParam(':location', $this->location);
        $query->bindParam(':size', $this->size);
        $query->bindParam(':price', $this->price);
        $query->bindParam(':lot_image', $this->lot_image);
        $query->bindParam(':description', $this->description);

        if($query->execute()){
            return true;
        }else{
            return false;
        }
    }

    public function fetchLotRecord($lot_id) {
        $sql = "SELECT * FROM lots WHERE lot_id = :lot_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':lot_id', $lot_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function editlot() {
        $sql = "UPDATE lots SET lot_name = :lot_name, location = :location, size = :size, price = :price, lot_image = :lot_image, description = :description WHERE lot_id = :lot_id;";

        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':lot_name', $this->lot_name);
        $query->bindParam(':location', $this->location);
        $query->bindParam(':size', $this->size);
        $query->bindParam(':price', $this->price);
        $query->bindParam(':lot_image', $this->lot_image);
        $query->bindParam(':description', $this->description);
        $query->bindParam(':lot_id', $this->lot_id);

        return $query->execute();
    }

    function deleteLot($lot_id) {
        $sql = "DELETE FROM lots WHERE lot_id = :lot_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':lot_id', $lot_id, PDO::PARAM_INT);
        
        return $query->execute();
    }



}