<?php
session_start();
require_once 'website.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $websiteSettings = new WebsiteSettings();
    $message = '';
    $type = '';


    // Handle Add Hero Carousel Item
    if (isset($_POST['add_pubmat1'])) {
        $heading = htmlspecialchars($_POST['heading']);
        $text = htmlspecialchars($_POST['text']);
        $image = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $uploadDir = '../../assets/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'assets/images/' . $filename;
                }
            }
        }

        if ($websiteSettings->insertPubmat1($heading, $text, $image)) {
            $message = 'Hero carousel item added successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to add hero carousel item.';
            $type = 'danger';
        }
    }

    // Handle Delete Hero Carousel Item
    if (isset($_POST['delete_pubmat1'])) {
        $id = $_POST['id'];
        
        if ($websiteSettings->deletePubmat1($id)) {
            $message = 'Hero carousel item deleted successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to delete hero carousel item.';
            $type = 'danger';
        }
    }

    // Handle Update Hero Carousel Item
    if (isset($_POST['update_pubmat1'])) {
        $id = $_POST['id'];
        $heading = htmlspecialchars($_POST['heading']);
        $text = htmlspecialchars($_POST['text']);
        $image = null;
        
        // Handle image upload if new image is selected
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $uploadDir = '../../assets/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'assets/images/' . $filename;
                    
                    // Delete old image if exists
                    if (!empty($_POST['current_image'])) {
                        $oldImagePath = __DIR__ . '/../../' . $_POST['current_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
        }

        if ($websiteSettings->updatePubmat1($id, $heading, $text, $image)) {
            $message = 'Hero carousel item updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update hero carousel item.';
            $type = 'danger';
        }
    }





    if (isset($_POST['update_main'])) {
        $text = htmlspecialchars($_POST['text']);
        $image = null;
        
        // Handle image upload if new image is selected
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $uploadDir = '../../assets/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'assets/images/' . $filename;
                    
                    // Delete old image if exists
                    if (!empty($_POST['current_image'])) {
                        $oldImagePath = __DIR__ . '/../../' . $_POST['current_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
        }

        if ($websiteSettings->updateAboutMain($text, $image)) {
            $message = 'Hero carousel item updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update hero carousel item.';
            $type = 'danger';
        }
    }



    // Handle About Section Updates
    if (isset($_POST['update_about1'])) {
        $section_title1 = htmlspecialchars($_POST['section_title1']);
        $sub_title1 = htmlspecialchars($_POST['sub_title1']);
        
        if ($websiteSettings->updateAbout1($section_title1, $sub_title1)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_about2'])) {
        $section_title2 = htmlspecialchars($_POST['section_title2']);
        $sub_title2 = htmlspecialchars($_POST['sub_title2']);
        
        if ($websiteSettings->updateAbout2($section_title2, $sub_title2)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_about3'])) {
        $section_title3 = htmlspecialchars($_POST['section_title3']);
        $sub_title3 = htmlspecialchars($_POST['sub_title3']);
        
        if ($websiteSettings->updateAbout3($section_title3, $sub_title3)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_about4'])) {
        $section_title4 = htmlspecialchars($_POST['section_title4']);
        $sub_title4 = htmlspecialchars($_POST['sub_title4']);
        
        if ($websiteSettings->updateAbout4($section_title4, $sub_title4)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }








    // Handle card Section Updates
    if (isset($_POST['update_card1'])) {
        $card_title1 = htmlspecialchars($_POST['card_title1']);
        $card_text1 = htmlspecialchars($_POST['card_text1']);
        $card_icon1 = htmlspecialchars($_POST['card_icon1']);
        
        if ($websiteSettings->updateCard1($card_title1, $card_text1, $card_icon1)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_card2'])) {
        $card_title2 = htmlspecialchars($_POST['card_title2']);
        $card_text2 = htmlspecialchars($_POST['card_text2']);
        $card_icon2 = htmlspecialchars($_POST['card_icon2']);
        
        if ($websiteSettings->updateCard2($card_title2, $card_text2, $card_icon2)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_card3'])) {
        $card_title3 = htmlspecialchars($_POST['card_title3']);
        $card_text3 = htmlspecialchars($_POST['card_text3']);
        $card_icon3 = htmlspecialchars($_POST['card_icon3']);
        
        if ($websiteSettings->updateCard3($card_title3, $card_text3, $card_icon3)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_card4'])) {
        $card_title4 = htmlspecialchars($_POST['card_title4']);
        $card_text4 = htmlspecialchars($_POST['card_text4']);
        $card_icon4 = htmlspecialchars($_POST['card_icon4']);
        
        if ($websiteSettings->updateCard4($card_title4, $card_text4, $card_icon4)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_card5'])) {
        $card_title5 = htmlspecialchars($_POST['card_title5']);
        $card_text5 = htmlspecialchars($_POST['card_text5']);
        $card_icon5 = htmlspecialchars($_POST['card_icon5']);
        
        if ($websiteSettings->updateCard5($card_title5, $card_text5, $card_icon5)) {
            $message = 'updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update.';
            $type = 'danger';
        }
    }

    // Handle Team Members
    if (isset($_POST['add_team_member'])) {
        $name = htmlspecialchars($_POST['name']);
        $position = htmlspecialchars($_POST['position']);
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = 'assets/images/' . $filename;
            }
        }
        
        if ($websiteSettings->addTeamMember($name, $position, $image)) {
            $message = 'Team member added successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to add team member.';
            $type = 'danger';
        }
    }

    if (isset($_POST['update_team_member'])) {
        $id = $_POST['id'];
        $name = htmlspecialchars($_POST['name']);
        $position = htmlspecialchars($_POST['position']);
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = 'assets/images/' . $filename;
            }
        }
        
        if ($websiteSettings->updateTeamMember($id, $name, $position, $image)) {
            $message = 'Team member updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update team member.';
            $type = 'danger';
        }
    }

    if (isset($_POST['delete_team_member'])) {
        $id = $_POST['id'];
        
        if ($websiteSettings->deleteTeamMember($id)) {
            $message = 'Team member deleted successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to delete team member.';
            $type = 'danger';
        }
    }






    // Handle Contact Information Updates
    if (isset($_POST['update_contact'])) {
        $address = htmlspecialchars($_POST['address']);
        $email = htmlspecialchars($_POST['email']);
        $phone = htmlspecialchars($_POST['phone']);
        
        if ($websiteSettings->updateContact($phone, $email, $address)) {
            $message = 'Contact information updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update contact information.';
            $type = 'danger';
        }
    }

    // Handle Add Pubmat2
    if (isset($_POST['add_pubmat2'])) {
        $heading = htmlspecialchars($_POST['heading']);
        $text = htmlspecialchars($_POST['text']);
        
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = '../../assets/images/' . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'assets/images/' . $filename;
                    if ($websiteSettings->insertPubmat2($heading, $text, $image)) {
                        $message = 'Sample lot added successfully!';
                        $type = 'success';
                    } else {
                        $message = 'Failed to add sample lot.';
                        $type = 'danger';
                    }
                }
            }
        }
    }

    // Handle Update Pubmat2
    if (isset($_POST['update_pubmat2'])) {
        $id = $_POST['id'];
        $heading = htmlspecialchars($_POST['heading']);
        $text = htmlspecialchars($_POST['text']);
        $current_image = $_POST['current_image'];
        
        $image = $current_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = '../../assets/images/' . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'assets/images/' . $filename;
                    
                    // Delete old image
                    if ($current_image && file_exists('../../' . $current_image)) {
                        unlink('../../' . $current_image);
                    }
                }
            }
        }
        
        if ($websiteSettings->updatePubmat2($id, $heading, $text, $image)) {
            $message = 'Sample lot updated successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to update sample lot.';
            $type = 'danger';
        }
    }

    // Handle Delete Pubmat2
    if (isset($_POST['delete_pubmat2'])) {
        $id = $_POST['id'];
        if ($websiteSettings->deletePubmat2($id)) {
            $message = 'Sample lot deleted successfully!';
            $type = 'success';
        } else {
            $message = 'Failed to delete sample lot.';
            $type = 'danger';
        }
    }

    // Set message in session
    if ($message && $type) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
}

// Redirect back to the settings page
header('Location: ../website_settings.php');
exit();