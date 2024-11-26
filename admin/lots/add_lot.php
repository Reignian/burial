<?php

require_once ('../../functions.php');
require_once ('lots.class.php');

$lot_name = $location = $size = $price = $description = '';
$lot_nameErr = $locationErr = $sizeErr = $lot_imageErr = $priceErr = $descriptionErr = '';
$lot_image = '';

$burialObj = new Lots_class();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $lot_name = clean_input(($_POST['lot_name']));
    $location = clean_input(($_POST['location']));
    $size = clean_input(($_POST['size']));
    $price = clean_input(($_POST['price']));
    $description = clean_input(($_POST['description']));
    
    if (!empty($_FILES['lot_image']['name'])) {
        $allowed_types = ['jpg', 'jpeg', 'png'];  // Allowed image types
        $file_ext = strtolower(pathinfo($_FILES['lot_image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $lot_imageErr = 'Only JPG, JPEG, PNG formats are allowed';
        } elseif ($_FILES['lot_image']['size'] > 10000000) {  // 10MB file size limit
            $lot_imageErr = 'Image size should not exceed 5MB';
        } else {
            $lot_image = $_FILES['lot_image']['name'];
            $tempname = $_FILES['lot_image']['tmp_name'];
            $folder = 'lots_images/' . $lot_image;

            // Step 2: Move the uploaded file
            if (!move_uploaded_file($tempname, $folder)) {
                $lot_imageErr = 'Failed to upload image';
            }
        }
    } else {
        $lot_imageErr = 'Image is required';
    }

    if(empty($lot_name)){
        $lot_nameErr = 'Lot name is required';
    }

    if(empty($location)){
        $locationErr = 'Location is required';
    }

    if(empty($size)){
        $sizeErr = 'Size is required';
    }else if (!is_numeric($size)){
        $sizeErr = 'Size should be a number';
    }

    if(empty($price)){
        $priceErr = 'Price is required';
    } else if (!is_numeric($price)){
        $priceErr = 'Price should be a number';
    } else if ($price < 1){
        $priceErr = 'Price must be greater than 0';
    }

    if(empty($description)){
        $descriptionErr = 'Description is required';
    }


    if(empty($lot_nameErr) && empty($priceErr) && empty($locationErr) && empty($sizeErr) && empty($statusErr) && empty($descriptionErr) && empty($lot_imageErr)){

        $burialObj->lot_name = $lot_name;
        $burialObj->location = $location;
        $burialObj->price = $price;
        $burialObj->size = $size;
        $burialObj->description = $description;
        $burialObj->lot_image = $folder;

        if($burialObj->addlot()){
            header('location: ../lots.php');
        } else {
            echo 'Something went wrong when adding new lot';
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
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .required {
            color: #d32f2f;
        }
        #imagePreview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
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
                <input type="number" name="price" id="price" value="<?= isset($_POST['price'])? $price: '' ?>">
                <?php if(!empty($priceErr)): ?>
                    <span class="error"><?= $priceErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="lot_image">Image <span class="required">*</span></label>
                <input type="file" name="lot_image" id="lot_image" accept="image/*" onchange="previewImage(this);">
                <?php if (!empty($lot_imageErr)): ?>
                    <span class="error"><?= $lot_imageErr ?></span>
                <?php endif; ?>
                <img id="imagePreview" src="#" alt="Image preview" />
            </div>

            <div>
                <label for="description">Description <span class="required">*</span></label>
                <textarea name="description" id="description" rows="4"><?= $description ?></textarea>
                <?php if(!empty($descriptionErr)): ?>
                    <span class="error"><?= $descriptionErr ?></span>
                <?php endif; ?>
            </div>

            <div>
                <input type="submit" value="Add Lot">
            </div>
        </form>
    </div>

    <script>
        function previewImage(input) {
            var preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
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