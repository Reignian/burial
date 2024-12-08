<?php

require_once __DIR__ . '/../../database.php';

class WebsiteSettings {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Pubmat 1 (Hero Carousel) Functions
    public function getPubmat1() {
        $sql = "SELECT * FROM pubmat_1";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertPubmat1($heading, $text, $image) {
        $sql = "INSERT INTO pubmat_1 (heading, text, image) VALUES (?, ?, ?)";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$heading, $text, $image]);
    }

    public function deletePubmat1($id) {
        // First get the image path to delete the file
        $sql = "SELECT image FROM pubmat_1 WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['image']) {
            $imagePath = __DIR__ . '/../../' . $result['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Then delete the record
        $sql = "DELETE FROM pubmat_1 WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$id]);
    }

    public function updatePubmat1($id, $heading, $text, $image = null) {
        if ($image) {
            $sql = "UPDATE pubmat_1 SET heading = ?, text = ?, image = ? WHERE id = ?";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([$heading, $text, $image, $id]);
        } else {
            $sql = "UPDATE pubmat_1 SET heading = ?, text = ? WHERE id = ?";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([$heading, $text, $id]);
        }
    }

    // Pubmat 2 (Products Section) Functions
    public function getPubmat2() {
        $sql = "SELECT * FROM pubmat_2";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertPubmat2($heading, $text, $image) {
        $sql = "INSERT INTO pubmat_2 (heading, text, image) VALUES (?, ?, ?)";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$heading, $text, $image]);
    }

    public function updatePubmat2($id, $heading, $text, $image) {
        $sql = "UPDATE pubmat_2 SET heading = ?, text = ?, image = ? WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$heading, $text, $image, $id]);
    }

    public function deletePubmat2($id) {
        // First get the image path
        $sql = "SELECT image FROM pubmat_2 WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['image'])) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/burial/' . $result['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $sql = "DELETE FROM pubmat_2 WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$id]);
    }

    // About Page Functions
    public function getAboutMain() {
        $sql = "SELECT * FROM about_main";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }






    public function getAbout() {
        $sql = "SELECT * FROM about";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAbout1($section_title1, $sub_title1) {
        $sql = "UPDATE about SET section_title = ?, sub_title = ? WHERE id = 1";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$section_title1, $sub_title1]);
    }

    public function updateAbout2($section_title2, $sub_title2) {
        $sql = "UPDATE about SET section_title = ?, sub_title = ? WHERE id = 2";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$section_title2, $sub_title2]);
    }

    public function updateAbout3($section_title3, $sub_title3) {
        $sql = "UPDATE about SET section_title = ?, sub_title = ? WHERE id = 3";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$section_title3, $sub_title3]);
    }

    public function updateAbout4($section_title4, $sub_title4) {
        $sql = "UPDATE about SET section_title = ?, sub_title = ? WHERE id = 4";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$section_title4, $sub_title4]);
    }


    public function getAbout2() {
        $sql = "SELECT * FROM about_2";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCard1($card_title1, $card_text1, $card_icon1) {
        $sql = "UPDATE about_2 SET card_title = ?, card_text = ?, card_icon = ? WHERE id = 1";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$card_title1, $card_text1, $card_icon1]);
    }

    public function updateCard2($card_title2, $card_text2, $card_icon2) {
        $sql = "UPDATE about_2 SET card_title = ?, card_text = ?, card_icon = ? WHERE id = 2";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$card_title2, $card_text2, $card_icon2]);
    }

    public function updateCard3($card_title3, $card_text3, $card_icon3) {
        $sql = "UPDATE about_2 SET card_title = ?, card_text = ?, card_icon = ? WHERE id = 3";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$card_title3, $card_text3, $card_icon3]);
    }

    public function updateCard4($card_title4, $card_text4, $card_icon4) {
        $sql = "UPDATE about_2 SET card_title = ?, card_text = ?, card_icon = ? WHERE id = 4";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$card_title4, $card_text4, $card_icon4]);
    }

    public function updateCard5($card_title5, $card_text5, $card_icon5) {
        $sql = "UPDATE about_2 SET card_title = ?, card_text = ?, card_icon = ? WHERE id = 5";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$card_title5, $card_text5, $card_icon5]);
    }
    

    public function getAboutTeam() {
        $sql = "SELECT * FROM about_team";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTeamMember($name, $position, $image) {
        $sql = "INSERT INTO about_team (name, position, image) VALUES (?, ?, ?)";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$name, $position, $image]);
    }

    public function updateTeamMember($id, $name, $position, $image = null) {
        if ($image) {
            $sql = "UPDATE about_team SET name = ?, position = ?, image = ? WHERE id = ?";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([$name, $position, $image, $id]);
        } else {
            $sql = "UPDATE about_team SET name = ?, position = ? WHERE id = ?";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([$name, $position, $id]);
        }
    }

    public function deleteTeamMember($id) {
        $sql = "DELETE FROM about_team WHERE id = ?";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$id]);
    }

    // Contact Information Functions
    public function getContact() {
        $sql = "SELECT * FROM contact";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function updateContact($phone, $email, $address) {
        $sql = "UPDATE contact SET phone = ?, email = ?, address = ? WHERE id = 1";
        $query = $this->db->connect()->prepare($sql);
        return $query->execute([$phone, $email, $address]);
    }

    // Handle Image Upload
    public function uploadImage($file, $target_dir) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $file['tmp_name'];
            $name = basename($file['name']);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            // Allow certain file formats
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($extension, $allowed_types)) {
                return false;
            }

            // Generate unique filename
            $new_filename = uniqid() . '.' . $extension;
            $upload_path = $target_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                return $upload_path;
            }
        }
        return false;
    }
}
