<?php
include "includes/settings.php";
include "includes/notloggedin.php";
include "includes/userinfo.php";
include $mainserverroot . "/allthebest/models/Products.php";
include $mainserverroot . "/allthebest/models/Category.php";

$request = 1;
if (isset($_POST['request'])) {
    $request = $_POST['request'];
}

// Upload file
if ($request == 1) {
    switch ($_FILES['file']['type']) {
        case 'image/jpeg':
            $extension = '.jpg';
            break;
        case 'image/png':
            $extension = '.png';
            break;
        case 'image/gif':
            $extension = '.gif';
            break;
        default:
            $error = true;
    }

    $newfilename = rand() . $extension;
    $target_dir = $mainserverroot . "/partnerwebsites/themes/".$partnertheme."/assets/products/";
   // echo $target_dir;
    $filename = $_FILES["file"]["tmp_name"];
    $filename2 = $_FILES["file"]["name"];

    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    
    $lastId = $_GET['id'];
    if(!$error){
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir.$_FILES['file']['name'])) {
            $insert = "INSERT INTO productsimage (partnerId, storeId, productId, image, status) VALUES ('$partnerId', '$partnerstoreId', $lastId, '$filename2', '1')";
            if ($dbhconnection->query($insert)) {
               // return 'success';
                echo "success";
            }
        }
    }
    else{
        echo "file format not supported";
    }
  
}

// Remove file
if ($request == 2) {
    $productId = $_POST['product_id'];
    $filedelete = $_POST['name'];
    $sql = "DELETE FROM productsimage WHERE productId='$productId' AND image='$filedelete'";
    if ($dbhconnection->query($sql)) {
        echo "Record deleted successfully";
        $target_dir = "../partnerwebsites/themes/".$partnertheme."/assets/products/";
        $filename = $target_dir.$filedelete;
        unlink($filename);
        exit;
    } else {
        echo "Error deleting record: " . $dbhconnection->errorInfo();
    }
}

if ($request == 3) {
    $productId = $_POST['productid'];
    $imagename = $_POST['imageurl'];
    $sql = "DELETE FROM productsimage WHERE productId='$productId' AND image='$imagename'";
     if ($dbhconnection->query($sql)) {
        echo "Record deleted successfully";
        $target_dir = "../partnerwebsites/themes/".$partnertheme."/assets/products/";
        $filename = $target_dir.$filedelete;
        unlink($filename);
        exit;
    } else {
        echo "Error deleting record: " . $dbhconnection->errorInfo();
    } 
   
}