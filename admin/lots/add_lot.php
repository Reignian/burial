<?php
session_start();
require_once ('../../functions.php');
require_once ('lots.class.php');

$lot_name = $location = $size = $price = $description = '';
$lot_nameErr = $locationErr = $sizeErr = $lot_imageErr = $priceErr = $descriptionErr = '';
$lot_image = '';
$alert_message = '';

$burialObj = new Lots_class();

if(isset($_POST['add_lot'])){
    $lot_name = clean_input(($_POST['lot_name']));
    $location = clean_input(($_POST['location']));
    $size = clean_input(($_POST['size']));
    $price = clean_input(($_POST['price']));
    $description = clean_input(($_POST['description']));

    // Validate lot name
    if(empty($lot_name)){
        $lot_nameErr = 'Lot name is required';
    }

    // Validate location
    if(empty($location)){
        $locationErr = 'Location is required';
    }

    // Validate size
    if(empty($size)){
        $sizeErr = 'Size is required';
    } else if (!is_numeric($size)){
        $sizeErr = 'Size should be a number';
    }

    // Validate price
    if(empty($price)){
        $priceErr = 'Price is required';
    } else if (!is_numeric($price)){
        $priceErr = 'Price should be a number';
    } else if ($price < 1){
        $priceErr = 'Price must be greater than 0';
    }

    // Validate description
    if(empty($description)){
        $descriptionErr = 'Description is required';
    }

    // Image handling
    if(isset($_FILES['lot_image']) && $_FILES['lot_image']['error'] === 0) {
        $file = $_FILES['lot_image'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png');

        if(in_array($file_ext, $allowed)){
            if($file_error === 0){
                if($file_size <= 10485760){ // 10MB limit (10 * 1024 * 1024)
                    // Generate unique filename
                    $file_name_parts = pathinfo($file_name);
                    $unique_filename = $file_name_parts['filename'];
                    $counter = 1;
                    
                    // Keep checking and incrementing counter until we find a unique filename
                    while(file_exists("lots_images/" . $unique_filename . "." . $file_ext)) {
                        $unique_filename = $file_name_parts['filename'] . '_' . $counter;
                        $counter++;
                    }
                    
                    // Create directory if it doesn't exist
                    if (!file_exists("lots_images")) {
                        mkdir("lots_images", 0777, true);
                    }
                    
                    $folder = "lots_images/" . $unique_filename . "." . $file_ext;
                    $destination = $folder;

                    if(move_uploaded_file($file_tmp, $destination)){
                        $lot_image = $folder;
                    } else {
                        $lot_imageErr = 'Error uploading file';
                    }
                } else {
                    $lot_imageErr = 'File size must be less than 10MB';
                }
            } else {
                $lot_imageErr = 'Error uploading file';
            }
        } else {
            $lot_imageErr = 'Invalid file type. Only JPG, JPEG, and PNG files are allowed';
        }
    } else {
        $lot_imageErr = 'Image is required';
    }

    if($lot_nameErr == '' && $locationErr == '' && $sizeErr == '' && $lot_imageErr == '' && $priceErr == '' && $descriptionErr == ''){
        $burialObj->lot_name = $lot_name;
        $burialObj->location = $location;
        $burialObj->size = $size;
        $burialObj->price = $price;
        $burialObj->lot_image = $lot_image;
        $burialObj->description = $description;
        
        $result = $burialObj->addLot();
        
        if($result['success']){
            header('location: ../lots.php?status=created');
            exit;
        } else {
            $alert_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f2f1;
            color: #263238;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #006064;
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            display: grid;
            gap: 20px;
        }
        label {
            font-weight: bold;
            color: #455a64;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #b2dfdb;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #006064;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #00838f;
        }
        .error {
            color: red;
            font-size: 0.8em;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        #imagePreview {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            object-fit: contain;
        }
        .preview-container {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(!empty($alert_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($alert_message); ?>
            </div>
        <?php endif; ?>
        <h1>Add New Lot</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div>
                <label for="lot_name">Lot Name <span class="required">*</span></label>
                <input type="text" name="lot_name" id="lot_name" value="<?= $lot_name ?>">
                <?php if(!empty($lot_nameErr)): ?>
                    <span class="error"><?= $lot_nameErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="location">Location <span class="required">*</span></label>
                <input type="text" name="location" id="location" value="<?= $location ?>">
                <?php if(!empty($locationErr)): ?>
                    <span class="error"><?= $locationErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="size">Size <span class="required">*</span></label>
                <input type="text" name="size" id="size" value="<?= $size ?>">
                <?php if(!empty($sizeErr)): ?>
                    <span class="error"><?= $sizeErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="price">Price <span class="required">*</span></label>
                <input type="number" name="price" id="price" value="<?= $price ?>">
                <?php if(!empty($priceErr)): ?>
                    <span class="error"><?= $priceErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="lot_image">Image <span class="required">*</span></label>
                <input type="file" name="lot_image" id="lot_image" accept=".jpg,.jpeg,.png" onchange="previewImage(this);">
                <?php if(!empty($lot_imageErr)): ?>
                    <span class="error"><?php echo $lot_imageErr; ?></span>
                <?php endif; ?>
                <div class="preview-container">
                    <img id="imagePreview" src="#" alt="Image preview" />
                </div>
            </div>

            <div>
                <label for="description">Description <span class="required">*</span></label>
                <textarea name="description" id="description" rows="4"><?= $description ?></textarea>
                <?php if(!empty($descriptionErr)): ?>
                    <span class="error"><?= $descriptionErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <input type="submit" name="add_lot" value="Add Lot">
            </div>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>