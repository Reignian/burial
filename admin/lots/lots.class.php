<?php

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../staffs/staffs.class.php';

class Lots_class{

    public $lot_id = '';
    public $lot_name = '';
    public $lot_image = '';
    public $location = '';
    public $size = '';
    public $price = '';
    public $description = '';


    protected $db;
    protected $staffs;

    function __construct(){
        $this->db = new Database();
        $this->staffs = new Staffs_class();
    }

    function showALL_lots() {
        $sql = "SELECT * FROM lots WHERE is_deleted = 0;";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function lotExists($lot_name, $location) {
        $sql = "SELECT COUNT(*) as count FROM lots WHERE lot_name = :lot_name AND location = :location AND is_deleted = 0";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':lot_name', $lot_name);
        $query->bindParam(':location', $location);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    function addLot(){
        try {
            // Check if lot with same name and location already exists
            if ($this->lotExists($this->lot_name, $this->location)) {
                error_log("Lot with name '{$this->lot_name}' and location '{$this->location}' already exists");
                return ['success' => false, 'message' => 'A lot with this name and location already exists'];
            }

            $this->db->connect()->beginTransaction();

            $sql = "INSERT INTO lots (lot_name, location, size, price, lot_image, description) VALUES (:lot_name, :location, :size, :price, :lot_image, :description);";

            $query = $this->db->connect()->prepare($sql);

            $query->bindParam(':lot_name', $this->lot_name);
            $query->bindParam(':location', $this->location);
            $query->bindParam(':size', $this->size);
            $query->bindParam(':price', $this->price);
            $query->bindParam(':lot_image', $this->lot_image);
            $query->bindParam(':description', $this->description);

            if($query->execute()){
                // Log the action
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    error_log("Session account ID: " . $_SESSION['account']['account_id']);
                    $logDetails = sprintf(
                        "Added new lot:\n" .
                        "Lot Name: %s\n" .
                        "Location: %s\n" .
                        "Size: %s\n" .
                        "Price: ₱%s",
                        $this->lot_name,
                        $this->location,
                        $this->size,
                        number_format($this->price, 2)
                    );
                    error_log("Log details: " . $logDetails);

                    $result = $this->staffs->addStaffLog(
                        $_SESSION['account']['account_id'],
                        'Add Lot',
                        $logDetails
                    );
                    error_log("Staff log result: " . ($result ? "success" : "failed"));
                } else {
                    error_log("No session account data found in addLot");
                }

                $this->db->connect()->commit();
                return ['success' => true];
            } else {
                $this->db->connect()->rollBack();
                return ['success' => false, 'message' => 'Failed to add lot'];
            }
        } catch (Exception $e) {
            error_log("Error in addLot: " . $e->getMessage());
            $this->db->connect()->rollBack();
            return ['success' => false, 'message' => 'An error occurred while adding the lot'];
        }
    }

    public function fetchLotRecord($lot_id) {
        $sql = "SELECT * FROM lots WHERE lot_id = :lot_id AND is_deleted = 0";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':lot_id', $lot_id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function editlot() {
        try {
            $this->db->connect()->beginTransaction();

            // Get old lot details for logging
            $oldLot = $this->fetchLotRecord($this->lot_id);

            $sql = "UPDATE lots SET lot_name = :lot_name, location = :location, size = :size, price = :price, lot_image = :lot_image, description = :description WHERE lot_id = :lot_id;";

            $query = $this->db->connect()->prepare($sql);

            $query->bindParam(':lot_name', $this->lot_name);
            $query->bindParam(':location', $this->location);
            $query->bindParam(':size', $this->size);
            $query->bindParam(':price', $this->price);
            $query->bindParam(':lot_image', $this->lot_image);
            $query->bindParam(':description', $this->description);
            $query->bindParam(':lot_id', $this->lot_id);

            if($query->execute()) {
                // Log the action
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    $changes = [];
                    if ($oldLot['lot_name'] != $this->lot_name) $changes[] = "Name: {$oldLot['lot_name']} → {$this->lot_name}";
                    if ($oldLot['location'] != $this->location) $changes[] = "Location: {$oldLot['location']} → {$this->location}";
                    if ($oldLot['size'] != $this->size) $changes[] = "Size: {$oldLot['size']} → {$this->size}";
                    if ($oldLot['price'] != $this->price) $changes[] = sprintf("Price: ₱%s → ₱%s", 
                        number_format($oldLot['price'], 2), 
                        number_format($this->price, 2)
                    );
                    if ($oldLot['description'] != $this->description) $changes[] = "Description updated";
                    if ($oldLot['lot_image'] != $this->lot_image) $changes[] = "Image updated";

                    if (!empty($changes)) {
                        $logDetails = sprintf(
                            "Edited lot (%s):\n%s",
                            $oldLot['lot_name'],
                            implode("\n", $changes)
                        );

                        $this->staffs->addStaffLog(
                            $_SESSION['account']['account_id'],
                            'Edit Lot',
                            $logDetails
                        );
                    }
                }

                $this->db->connect()->commit();
                return true;
            } else {
                $this->db->connect()->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return false;
        }
    }

    function deleteLot($lot_id) {
        try {
            $this->db->connect()->beginTransaction();

            // Get lot details first for logging
            $sql = "SELECT * FROM lots WHERE lot_id = :lot_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':lot_id', $lot_id);
            $query->execute();
            $lotDetails = $query->fetch(PDO::FETCH_ASSOC);

            // Check if the lot has any confirmed reservations
            $sql = "SELECT r.reservation_id, 
                          CONCAT(a.last_name, ', ', a.first_name, ' ', a.middle_name) as client_name,
                          l.lot_name,
                          l.location
                   FROM reservation r 
                   JOIN account a ON r.account_id = a.account_id 
                   JOIN lots l ON r.lot_id = l.lot_id 
                   WHERE r.lot_id = :lot_id AND r.request = 'Confirmed'";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':lot_id', $lot_id, PDO::PARAM_INT);
            $query->execute();
            $confirmedReservation = $query->fetch(PDO::FETCH_ASSOC);

            if ($confirmedReservation) {
                $message = sprintf(
                    "Cannot delete lot. It has a confirmed reservation:\n" .
                    "Lot: %s - %s\n" .
                    "Reserved by: %s",
                    $confirmedReservation['lot_name'],
                    $confirmedReservation['location'],
                    $confirmedReservation['client_name']
                );
                $this->db->connect()->rollBack();
                return ['success' => false, 'message' => $message];
            }

            // Check if the lot has any pending/cancelled reservations
            $sql = "SELECT COUNT(*) as reservation_count FROM reservation WHERE lot_id = :lot_id AND request != 'Confirmed'";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':lot_id', $lot_id, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $isHardDelete = $result['reservation_count'] == 0;
            if ($result['reservation_count'] > 0) {
                $sql = "UPDATE lots SET is_deleted = 1 WHERE lot_id = :lot_id";
            } else {
                $sql = "DELETE FROM lots WHERE lot_id = :lot_id";
            }
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':lot_id', $lot_id, PDO::PARAM_INT);
            
            if ($query->execute()) {
                // Log the action
                if(isset($_SESSION['account']) && isset($_SESSION['account']['account_id'])) {
                    error_log("Session account ID: " . $_SESSION['account']['account_id']);
                    $logDetails = sprintf(
                        "Deleted lot:\n" .
                        "Lot Name: %s\n" .
                        "Location: %s\n" .
                        "Price: ₱%s",
                        $lotDetails['lot_name'],
                        $lotDetails['location'],
                        number_format($lotDetails['price'], 2)
                    );
                    error_log("Log details: " . $logDetails);

                    $result = $this->staffs->addStaffLog(
                        $_SESSION['account']['account_id'],
                        'Delete Lot',
                        $logDetails
                    );
                    error_log("Staff log result: " . ($result ? "success" : "failed"));
                } else {
                    error_log("No session account data found");
                }

                $this->db->connect()->commit();
                return ['success' => true];
            }
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            return ['success' => false, 'message' => 'An error occurred while deleting the lot.'];
        }
    }

}