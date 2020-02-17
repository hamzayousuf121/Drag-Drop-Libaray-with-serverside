<?php

  $pagetitle = "Add Products";
  include "includes/settings.php";
  include "includes/notloggedin.php";
  include "includes/userinfo.php";
  include $mainserverroot . "/allthebest/models/Products.php";
  include $mainserverroot . "/allthebest/models/Category.php";
  include "includes/header.php";

 // *** GET DATA OF CATEGORIES *** \\

 $query = $dbhconnection->prepare("select * from maincategories where  storeId=$partnerstoreId and partnerId=$partnerId and status=1");
 $query->execute();
 $results = $query->fetchAll(PDO::FETCH_OBJ);

 // *** GET DATA OF BRANDS *** \\
  $query = $dbhconnection->prepare("SELECT * FROM `brands` WHERE partnerId=$partnerId and status=1");
  $query->execute();
  $brands = $query->fetchAll(PDO::FETCH_OBJ);

if (isset($_POST["id"]) && $_POST['id'] == "") {
    // form submit
    if (isset($_POST['submit'])) {

        $categoryId = $_POST['maincategoryId'];
        $brandId = $_POST['brandId'];
        $productname = $_POST['name'];
        $description = $_POST['description'];
        $stock = $_POST['stock'];
        $productcode = $_POST['productcode'];
        $prevprice = $_POST['prevprice'];
        $type = $_POST['type'];
        $price = $_POST['price'];
        $barcode = $_POST['barcode'];
        $status = 1;
        // prepare sql and bind parameters

        $sql = "insert into " . $partnerproductstable . "(partnerId, storeId, name, ipaddress, categoryId, brandId, description, stock, barcode, productcode, prevprice, type, price)VALUES (:partnerId,:storeId,  :productname, :ipaddress, :categoryId, :brandId, :description, :stock, :barcode, :productcode,:prevprice, :type,:price)";

        $stmt = $dbhconnection->prepare($sql);
        $stmt->bindParam(":productname", $productname);
        $stmt->bindParam(":ipaddress", $ipaddress);
        $stmt->bindParam(":categoryId", $categoryId);

        $stmt->bindParam(":brandId", $brandId);

        $stmt->bindParam(":barcode", $barcode);

        $stmt->bindParam(":description", $description);

        $stmt->bindParam(":stock", $stock);

        $stmt->bindParam(":productcode", $productcode);

        $stmt->bindParam(":prevprice", $prevprice);

        $stmt->bindParam(":type", $type);

        $stmt->bindParam(":price", $price);

        $stmt->bindParam(":partnerId", $partnerId);

        $stmt->bindParam(":storeId", $partnerstoreId);

        if($stmt->execute()) {
            $lastId = $dbhconnection->lastInsertId();
           
        } else {

            $dbhconnection->errorInfo();
            
            echo `<div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <strong>Data!</strong>  Data not Inserted
            </div>`;

        }
        //Image Upload

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



        $newfilename = round(microtime(true)) . $extension;

        $uploadDirectory = $mainserverroot . "/partnerwebsites/themes/".$partnertheme."/assets/products/";

        $destination = $uploadDirectory . $newfilename;

        $filename = $_FILES['file']['tmp_name'];



        if (!$error) {

            if (move_uploaded_file($filename, $destination)) {

                $sth = "UPDATE `$partnerproductstable` SET `image`='{$newfilename}' WHERE `id`={$lastId}";

                $dbhconnection->query($sth);

            }

        }

        if ($type == 'multisize') {

            $newPrice = 0;

            $newName = "";

            $quantity = "";


            $sql = "insert into " . $partnerproductstable . "(partnerId, storeId, name, ipaddress, categoryId, brandId, description, stock, barcode, productcode, prevprice, type, price, image)VALUES (:partnerId, :storeId, :productname, :ipaddress, :categoryId, :brandId, :description, :stock, :barcode, :productcode,:prevprice, :type,:price, :image)";

            $stmt = $dbhconnection->prepare($sql);

            $stmt->bindParam(":productname", $newName);

            $stmt->bindParam(":ipaddress", $ipaddress);

            $stmt->bindParam(":categoryId", $categoryId);

            $stmt->bindParam(":brandId", $brandId);

            $stmt->bindParam(":barcode", $barcode);

            $stmt->bindParam(":description", $description);

            $stmt->bindParam(":stock", $quantity);

            $stmt->bindParam(":productcode", $productcode);

            $stmt->bindParam(":prevprice", $prevprice);

            $stmt->bindParam(":type", $type);

            $stmt->bindParam(":price", $newPrice);

            $stmt->bindParam(":partnerId", $partnerId);

            $stmt->bindParam(":storeId", $partnerstoreId);

            $stmt->bindParam(":image", $newfilename);
       
            // ProductSizes Tables Query

    $sql1 = "Insert into productsize (partnerId, ipaddress, storeId, productId, parentproductId, name, quantity, addonprice,status)VALUES (:partnerId, :ipaddress, :storeId, :productId, :parentproductId, :name, :quantity,:addonprice,:status)";

            $sizename = null;

            $sizeprice = 0;

            $quantity = 0;

            $stmt2 = $dbhconnection->prepare($sql1);

            $stmt2->bindParam(":partnerId", $partnerId);

            $stmt2->bindParam(":ipaddress", $ipaddress);

            $stmt2->bindParam(":storeId", $partnerstoreId);

            $stmt2->bindParam(":productId", $lastId);

            $stmt2->bindParam(":parentproductId", $parentid);

            $stmt2->bindParam(":name", $sizename);

            $stmt2->bindParam(":quantity", $quantity);

            $stmt2->bindParam(":addonprice", $sizeprice);

            $stmt2->bindParam(":status", $status);

            $sum = 0;

            foreach ($_POST["productsize"] as $key => $productsizes) {

                $productsize = $_POST['productsize'];

                $addonprice = $_POST['addonprice'];

                $status = 1;

                $qty = $_POST['quantity'];

                $quantity = $qty[$key];

                $newPrice = $price + $addonprice[$key];

                $newName = $productname . " " . $productsize[$key];

                $sizename = $productsize[$key];

                $sizeprice = $addonprice[$key];

                $stmt->execute();

                $parentid = $dbhconnection->lastInsertId();

                if ($stmt2->execute()) {
                    //do Nothing
                } else {
                    $dbhconnection->errorInfo();
                }

                $sum += intval($quantity);

            }

            $dbhconnection->query("Update products set stock = " . $sum . " where id = " . $lastId);

        }

    }

}

// **** UPDATE PRODUCTS ****

if (isset($_GET['id']) && !empty($_GET['id'])) {

    global $newfilename;

    $pagetitle = "Edit Product";

    // prepare sql and bind parameters

    if (isset($_POST['submit'])) {

        $categoryId = $_POST['maincategoryId'];

        $brandId = $_POST['brandId'];

        $description = $_POST['description'];

        $stock = $_POST['stock'];

        $productcode = $_POST['productcode'];

        $prevprice1 = $_POST['prevprice'];

        $type = $_POST['type'];

        $price = $_POST['price'];

        $id = $_GET['id'];

        $barcode = $_POST['barcode'];

        $productname = $_POST['name'];

        $subcatid = null;

        $childid = null;

        $categoryid = null;

        $productsize = $_POST['productsize'];

         //Image Upload

         $extension = null;

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

    

            $newfilename = round(microtime(true)) . $extension;

            $uploadDirectory = $mainserverroot . "/partnerwebsites/themes/".$partnertheme."/assets/products/";

            $destination = $uploadDirectory . $newfilename;

            $filename = $_FILES['file']['tmp_name'];

            $id = $_GET['id'];

            if (!$error) {

                if (move_uploaded_file($filename, $destination)) {

                //     $query = "UPDATE `$partnerproductstable` SET `image`='{$newfilename}' WHERE `id`='{$_POST['id']}'";



                //   $dbhconnection->execute($query);

                }

            }

            if($error){

                $newfilename = $_POST['oldimage'];

            }

        $sql4 = "update " . $partnerproductstable . " SET image ='$newfilename',name ='$productname', prevprice='$prevprice1',brandId='$brandId',categoryId='$categoryid', ipaddress='$ipaddress', stock='$stock',subcategoryId='$subcatid', childcategoryId='$childid',barcode='$barcode', description='$description', price='$price',productcode='$productcode', type='$type' where id='$id'";

        $updateStmt = $dbhconnection->query($sql4);

        $sum = 0;

        if ($type == 'multisize') {

            foreach ($_POST["productsize"] as $key => $psize) {

                $productsize = $_POST["productsize"];

                $quantity = $_POST['quantity'];

                $newquantity = $quantity[$key];

                $sum += intval($newquantity);

                $newproductsize = $productsize[$key];

                $addonprice = $_POST['addonprice'];

                if (isset($_POST['pid'][$key])) {

                    $addonprice = $addonprice[$key];

                    $userId = $_POST['pid'];

                    $ppid = $_POST['ppid'];

                    $pid = $userId[$key];

                    $ppid2 = $ppid[$key];

                    $newproductsize = $productsize[$key];

                    $newPrice = $price + $addonprice[$key];

                    $newName = $productname . " " . $productsize[$key];

            $sql5 = "update productsize SET name ='$newproductsize', quantity='$newquantity',addonprice='$addonprice' where id={$pid}";

                    $stmt2 = $dbhconnection->query($sql5);

        $sql6 = "update " . $partnerproductstable . " SET image='" . $newfilename . "', name ='$newName', stock='$newquantity' ,price='$newPrice' where id={$ppid2}";

                    $stmt2 = $dbhconnection->query($sql6);

                } else {

                    $categoryId = $_POST['maincategoryId'];

                    $brandId = $_POST['brandId'];

                    $description = $_POST['description'];

                    $productcode = $_POST['productcode'];

                    $prevprice = $_POST['prevprice'];

                    $type = $_POST['type'];

                    $price = $_POST['price'];

                    $id = $_GET['id'];

                    $barcode = $_POST['barcode'];

                    $productname = $_POST['name'];

                    $subcatid = null;

                    $childid = null;

                    $categoryid = null;

                    $imagepath = $newfilename;

                    $status = 1;



                    //insert Products Query

        $sql = "insert into " . $partnerproductstable . "(partnerId, storeId, name, ipaddress, categoryId, brandId, description, barcode, productcode, prevprice, type, price, image, status)VALUES ('$partnerId',
            '$partnerstoreId', '$productname', '$ipaddress', '$categoryId', '$brandId', '$description', '$barcode', '$productcode',
            '$prevprice', '$type','$price', '$imagepath', '$status')";

            
                    $stmt = $dbhconnection->query($sql);

                    $id = $_GET['id'];

                    $parentid = $dbhconnection->lastInsertId();

                    //insert productSize
$sql1="Insert into productsize (partnerId, storeId, name, quantity, addonprice, productId, parentproductId, status)VALUES ('$partnerId', '$partnerstoreId', '$newproductsize', '$newquantity','$addonprice[$key]', '$id', $parentid, $status)";

                    $stmt3 = $dbhconnection->query($sql1);

                   }

                $dbhconnection->query("Update products set stock = " . $sum . " where id = " . $id);

            }

        }

    }

    // GET DATA FROM PRODUCTS

    $id = $_GET['id'];

    $query = $dbhconnection->prepare("SELECT * FROM  " . $partnerproductstable . " WHERE  storeId=$partnerstoreId and partnerId='$partnerId' and status=1 and id=$id");

    $query->execute();

    $update = $query->fetch(PDO::FETCH_ASSOC);

    $sth = $dbhconnection->prepare("SELECT * FROM subcategories WHERE partnerId=$partnerId ");

    $sth->execute();

    $updateSub = $sth->fetchAll(PDO::FETCH_ASSOC);



    $sth = $dbhconnection->prepare("SELECT * FROM childcategories WHERE partnerId=$partnerId ");

    $sth->execute();

    $updateChild = $sth->fetchAll(PDO::FETCH_ASSOC);

      // GET Images  FROM PRODUCTIMAGES TABLE

      $productIdImage = $_GET['id'];

      $productImagequery = $dbhconnection->prepare("SELECT * FROM  productsImage WHERE productId=$productIdImage and status=1");

      $productImagequery->execute();

      $productImageResult = $productImagequery->fetchAll(PDO::FETCH_ASSOC);



    if ($update['type'] == 'multisize') {

        $query = $dbhconnection->prepare("SELECT * FROM productsize WHERE partnerId = ? and productId = ?");

        $id = $_GET['id'];

        $query->execute([$partnerId, $_GET['id']]);

        $multisizes = $query->fetchAll(PDO::FETCH_ASSOC);

    }

}

?>

<div class="page-content-wrapper">

    <!-- BEGIN CONTENT BODY -->

    <div class="page-content">

        <!-- BEGIN PAGE HEADER-->

        <!-- BEGIN THEME PANEL -->



        <!-- END THEME PANEL -->

        <h3 class="page-title">Products

        </h3>

        <div class="page-bar">

            <ul class="page-breadcrumb">

                <li>

                    <i class="icon-home"></i>

                    <a href="index.html">Home</a>

                    <i class="fa fa-angle-right"></i>

                </li>

                <li>

                    <span><?php echo $pagetitle; ?></span>

                </li>

            </ul>

        </div>

        <div class="main-content">

        <!-- END PAGE HEADER-->

        <!-- BEGIN DASHBOARD STATS 1-->

        <div class="row">

            <div class="col-md-12 col-sm-12">

                <!-- page content comes here !-->

                <div>

                <?php

                if($updateStmt && $_GET['id']) {?>

                <div id="update message">

                </div>

                <div>

                    <div class="row">

                     <?php

                     foreach($productImageResult as $multiImages){

                         ?>

                        <div class="col-md-3" id="<?php echo $multiImages['image'];?>'">

                            <img class="img img-responsive border border-dark" src="<?php echo "../partnerwebsites/themes/".$partnertheme."/assets/products/".$multiImages['image'];?>" />

            <button id="delete" style="width: 100%;margin:5px 0 5px 0" onClick="deleteImg('<?php echo $multiImages['productId'];?>' , '<?php echo $multiImages['image'];?>' );" class="btn btn-danger">Delete Image</button>

                        </div>

                     <?php 

                    }?> 

                    </div>

                    <label>Add Multiple Images*</label>

                    <form action="dropzone.php?id='<?= $_GET['id']; ?>'" product_id="<?php echo $_GET['id']; ?>"

                        class="dropzone " id="myAwesomeDropzone">

                    </form><br>

                    

                   <?php  

                //    echo '<div class="alert alert-success alert-dismissible ">

                //                 <button type="button" class="close" data-dismiss="alert" value="Dismiss">Dismiss;</button>

                //                 <strong>Update!</strong>  Record SuccessFully

                //           </div>';



                    } 

                ?>             



                  

                <?php

                     if($lastId) {

                     ?>   

                    <div id="insertMessage">

                    </div>

    

                    <label>Add Multiple Images*</label>

                    <form action="dropzone.php?id='<?= $lastId; ?>'" product_id="<?php echo $lastId; ?>"

                        class="dropzone " id="myAwesomeDropzone">

                    </form>

                    <?php

} else {

    ?>  



                    <form method="POST" class="form-horizontal" enctype="multipart/form-data">



                        <input type="hidden" name="id" value="<?=$_GET["id"]?>" />



                        <div class="portlet box green">

                            <div class="portlet-title">

                                <div class="caption">

                                    <?php echo $pagetitle; ?> </div>

                                <div class="tools">

                                    <a href="javascript:;" class="collapse"> </a>

                                    <a href="#portlet-config" data-toggle="modal" class="config"> </a>

                                    <a href="javascript:;" class="reload"> </a>

                                    <a href="javascript:;" class="remove"> </a>

                                </div>

                            </div>

                            <div class="portlet-body form">

                                <br>

                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Product Name*</label>

                                    <div class="col-sm-6">

                                        <input type="text" name="name" placeholder="Enter Product name" required

                                            class="form-control" value="<?=$update['name'];?>">

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Brand*</label>

                                    <div class="col-sm-6">

                                        <select class="form-control" name="brandId">

                                            <option value="">Select Brands</option>

                                            <?php foreach ($brands as $brand) {

                                            if ($brand->id == $update['brandId']) {

                                                echo '<option value="' . $brand->id . '" selected>' . $brand->name . '</option>';

                                            } else {

                                                echo '<option value="' . $brand->id . '">' . $brand->name . '</option>';

                                            }

                                        }?>



                                        </select>

                                    </div>

                                </div>

                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Category*</label>

                                    <div class="col-sm-6">

                                        <select id="catid" class="form-control" name="maincategoryId">

                                            <option value="">Select Category</option>

                                            <?php foreach ($results as $row) {

                                                if ($row->id == $update['maincategoryId']) {

                                                    echo '<option value="' . $row->id . '" selected>' . $row->name . '</option>';

                                                } else {

                                                    echo '<option value="' . $row->id . '">' . $row->name . '</option>';

                                                }

                                            }?>

                                        </select>

                                    </div>

                                </div>

                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Sub Category*</label>

                                    <div class="col-sm-6">

                                        <select id="subcat" class="form-control" name="subcategoryId"

                                            <?=isset($_GET["id"]) ? "" : "disabled";?>>

                                            <option value="">Select Sub Category</option>

                                            <?php

                                                foreach ($updateSub as $sub) {

                                                        if ($sub["id"] == $subcatid) {

                                                            ?>

                                            <option value="<?=$sub["id"]?> " selected><?=$sub["name"]?></option>

                                            <?php

} else {

            ?>

                                            <option value="<?=$sub["id"]?>"><?=$sub["name"]?></option>

                                            <?php

}

    }?>

                                        </select>

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Child Category*</label>

                                    <div class="col-sm-6">

                                        <select id="childcat" class="form-control" name="childcategoryId"

                                            <?=isset($_GET["id"]) ? "" : "disabled";?>>

                                            <option value="0">Select Child</option>



                                            <?php

foreach ($updateChild as $child) {

        if ($child["id"] == $childid) {

            ?>

                                            <option value="<?=$child["id"]?> " selected><?=$child["name"]?></option>

                                            <?php

} else {

            ?>

                                            <option value="<?=$exitchild["id"]?>"><?=$child["name"]?></option>

                                            <?php

}

    }?>

                                        </select>

                                    </div>

                                </div>

                                <div class="form-group">

                                    <?php if($update['image']){?>

                                    <label class="col-sm-4" style="padding-left: 200px;">

                                        <!-- yaha pr chanegs krni hai kal say -->
                                        <img src="<?php echo '../partnerwebsites/themes/".<?=$partnertheme?>."/assets/products/<?=$update['image'];?>"

                                            alt="NO Image Available" style="width: 130px; height: 130px;"></label>

                                    <?php }?>

                                    <div class="col-sm-6">

                                     <input type="hidden" name="oldimage" value="<?=$update['image'];?>" />

                                        <input style="padding-left:<?php echo($update['image']) ? '0px':'366px' ?>"

                                            id="file" type="file" name="file" />

                                        <p style="padding-left: <?php echo($update['image']) ? '0px':'366px' ?>">

                                            Thumbnail image Prefered Size: (600x600) </p>

                                    </div>

                                </div>

                                <hr>



                                <div class="form-group">

                                    <label class="col-sm-4" id="barcode" style="padding-left: 200px;">Bar Code*</label>

                                    <div class="col-sm-6">

                                        <input type="text" class="form-control" required

                                            value="<?=$update['barcode'];?>" name="barcode" id="barcode"

                                            placeholder="Scan bar Code ">

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" for="textarea" style="padding-left: 200px;">Product

                                        Description*</label>

                                    <div class="col-sm-6">

                                        <!-- id="summernote"  style="resize: vertical;" this id and style exist in textarea -->

                                        <textarea class="form-control" row="5" required id="textarea" name="description"

                                            placeholder="Enter Profile Description"><?=$update['description'];?></textarea>

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Product Current Price*

                                        <span></span></label>

                                    <div class="col-sm-6">

                                        <input class="form-control" name="price" required id="blood_group_display_name"

                                            placeholder="e.g 20" value="<?=$update['price'];?>" type="number">

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Type </label>

                                    <div class="col-sm-6">

                                        <select class="col-sm-12 form-control" id="dropdownsize" name="type"

                                            onchange="multisizes();">

                                            <option value="onesize">One size</option>

                                            <option value="multisize" <?php if ($update['type'] === 'multisize') {

        echo 'selected';

    }?>>Multi size</option>

                                        </select>

                                    </div>

                                </div>



                                <div id="multiform">

                                    <?php

if ($update['type'] === 'multisize') {?>

                                    <div class="form-group" id="sizedesc">

                                        <label class="col-sm-4" style="padding-left: 200px;"></label>

                                        <div class="col-sm-6">

                                            <div class="row form-group">

                                                <div class="col-sm-4"><label class="col-sm-4 "></label>Sizes</div>

                                                <div class="col-sm-4"><label class="col-sm-4"></label>Addon Price</div>

                                                <div class="col-sm-4"><label class="col-sm-4 "></label>Quantity</div>

                                            </div>

                                        </div>

                                        <!-- Foreach Start -->

                                        <?php foreach ($multisizes as $multisize) {

        ?>

                                        <div class="form-group">

                                            <div class="form-group">

                                                <label class="col-sm-4" style="padding-left: 200px;"></label>

                                                <div class="col-sm-6">

                                                    <div class="row form-group" style="padding: 0px 20px 0px 10px">

                                                        <div class="col-sm-4"><input type="text"

                                                                value="<?=$multisize["name"];?>" class="form-control"

                                                                placeholder=" size" required name="productsize[]"></div>

                                                        <div class="col-sm-4"><input type="text"

                                                                value="<?=$multisize["addonprice"];?>"

                                                                class="form-control" placeholder=" price" required

                                                                name="addonprice[]"></div>

                                                        <div class="col-sm-4"><input type="text"

                                                                value="<?=$multisize["quantity"];?>"

                                                                class="form-control" placeholder=" quantity" required

                                                                name="quantity[]"></div>

                                                        <input type="text" style="display:none; visibility:hidden;"

                                                            name="pid[]" value="<?=$multisize["id"]?>">



                                                        <input type="text" style="display:none; visibility:hidden;"

                                                            name="ppid[]" value="<?=$multisize["parentproductId"]?>">

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <?php

}?>

                                        <!-- foreach end -->

                                    </div>

                                    <div class="form-group">

                                        <label class="col-sm-4" style="padding-left: 200px;"></label>

                                        <div class="col-sm-6 float-right">

                                            <button type="button" class="btn btn-success mb-5"

                                                style="position:relative;bottom: 25px;" onclick="addField()">Add Field

                                            </button></div>

                                    </div>

                                    <?php }?>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Product Previous Price*

                                        <span>(Leave Blank ifnot )</span></label>

                                    <div class="col-sm-6">

                                        <input class="form-control" name="prevprice" id="blood_group_display_name2"

                                            placeholder="e.g 25" value="<?=$update['prevprice'];?>" type="number">

                                    </div>

                                </div>



                                <div class="form-group">

                                    <label class="col-sm-4" style="padding-left: 200px;">Product Code*

                                        <span></span></label>

                                    <div class="col-sm-6">

                                        <input class="form-control" required name="productcode"

                                            id="blood_group_display_name3" placeholder="e.g SKU-Shirt"

                                            value="<?=$update['productcode'];?>" type="text">

                                    </div>

                                </div>

                                <span id="notStock">

                                    <?php if($update['type'] == 'multisize' && isset($_GET['id'])) {



    } else {?>

                                    <div class="form-group" id="stock">

                                        <label class="col-sm-4" style="padding-left: 200px;">Stock*

                                            <span></span></label>

                                        <div class="col-sm-6">

                                            <input class="form-control"  name="stock"

                                                value="<?=$update['stock'];?>" placeholder="10,20" type="number">

                                        </div>

                                    </div>

                                    <?php }?>

                                </span>



                                <div class="form-actions right">

                                    <div><button type="submit" class="btn btn-primary" name="submit">Submit</button>

                                    </div>

                                </div>

                            </div>

                        </div>





                        <?php

}?>

                </div>

</div>

            </div>

        </div>

        <div class="clearfix"></div>

        <!-- END DASHBOARD STATS 1-->

    </div>



    <!-- END CONTENT BODY -->

</div>



<?php include "includes/footer.php";?>

<script type="text/javascript" src="js/bootstrap-tagsinput.js"></script>

<script type="text/javascript" src="js/dropzone.js"></script>

<!-- <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js"></script>
 -->


<script>

const deleteImg = (id, imgurl) => {

    var data = imgurl;

    var selector = '#'+data;

    // alert(selector);

        $.ajax({

                type: 'POST',

                url: 'dropzone.php',

                data: {

                    productid: id,

                    imageurl: imgurl,

                    request: 3

                },

                success: function(data) 

                {
                    // $('#delete').closest(selector).remove();
                   var dataa =  $('.col-md-3').html();

                    alert(dataa);

                    console.log($(selector).html());

                }
            });
            return false;
    }



var subCategoryId = "<?php echo $subcatid ?>";

const catFunction = () => {
    document.getElementById('dropdownsize').value;
    alert("Page is loaded");
}

function multisizes() {
    var dropdownvalue =  document.getElementById('dropdownsize').value;
    if (dropdownvalue === 'multisize') {
        if($('#stock').length) 
        {
           document.getElementById('stock').style.display = "none";
           document.getElementById('stock').style.visibility = "hidden";
        }
       document.getElementById('multiform').innerHTML = `<div class="form-group" id="sizedesc">
            <label class="col-sm-4"  style="padding-left: 200px;" ></label>
            <div class="col-sm-6">
            <div class="row form-group" >
              <div class="col-sm-4"><label  class="col-sm-4 "></label>Sizes</div>
              <div class="col-sm-4"><label  class="col-sm-4"></label>Addon Price</div>
              <div class="col-sm-4"><label  class="col-sm-4 "></label>Quantity</div>
            </div>
            </div>
          </div>
            </div>
          </div>
          <div class="form-group">
          <label class="col-sm-4"  style="padding-left: 200px;" ></label>
            <div class="col-sm-6 float-right">
              <button type="button" class="btn btn-success mb-5" style="position:relative;bottom: 25px;" onclick="addField()">Add Field</a>
            </div>
          </div>`;
        addField();
    } else {
        if(!$('#stock').length || $('#stock') == 'undefined') {

            var html = `<div class="form-group" id="stock">
                    <label class="col-sm-4" style="padding-left: 200px;">Stock* <span></span></label>
                    <div class="col-sm-6">
                      <input class="form-control" name="stock" value="<?=$update['stock'];?>" placeholder="10,20" type="number">
                    </div>
                  </div>`;
            $('#notStock').html(html);
        }
        document.getElementById('stock').style.display = "block";
        document.getElementById('stock').style.visibility = "visible";
        document.getElementById('multiform').innerHTML = "";
    };
}

const addField = () => {

    var formfields = `

    <div class="form-group">

          <div class="form-group">

          <label class="col-sm-4"  style="padding-left: 200px;" ></label>

            <div class="col-sm-6">

            <div class="row form-group" style="padding: 0px 20px 0px 10px">

              <div class="col-sm-4"><input type="text" class="form-control" placeholder=" size" name="productsize[]"></div>

              <div class="col-sm-4"><input type="text" class="form-control" placeholder=" price" name="addonprice[]"></div>

              <div class="col-sm-4"><input type="text"class="form-control"  placeholder=" quantity" name="quantity[]"></div>

           </div>

      </div>

    </div>`;

    $('#sizedesc').append(formfields);
}


//$('#summernote').summernote();

$(document).on('change', '#catid', function() {

    var subCategoryId = "<?php echo $subcatid ?>";

    var id = $(this).val();

    $.ajax({
        url: 'ajax/getcategorylist.php',

        type: 'GET',

        data: {
            cateid: id
        },

        dataType: 'json',

        success: function(response) {

            console.log(response);

            // console.table(response);

            var subcat = response.length;

            $('#subcat').removeAttr("disabled");



            $("#subcat").empty();



            for (var i = 0; i < subcat; i++) {



                var id = response[i]['id'];

                var name = response[i]['name'];



                // if(id == subCategoryId){



                // $("#subcat1").append("<option value='"+id+"' selected>"+name+"</option>");

                // }else{

                $("#subcat").append("<option value='" + id + "'>" + name + "</option>");

                // }

            }

        }
    })
});

$(document).ready(function() {

    Dropzone.autoDiscover = false;

    $(".dropzone").dropzone({

        success: function(file, response) {

            if(response) {

                setTimeout(function() {

                    var insertmsg = `<div class="alert alert-success alert-dismissible">

                        <strong>Success! Insert Data Successfully. </strong><a href="addproducts.php" class="float-right btn btn-success">Add Another Product</a>

                      </div>`;
                      const queryString = window.location.search;
                     // console.log(queryString);
                      if(queryString ==''){
                        document.getElementById('insertMessage').innerHTML = insertmsg;
                    }

                }, 500);

            }

        },

        addRemoveLinks: true,

        removedfile: function(file) {

            var name = file.name;

            var product_id = $('#myAwesomeDropzone').attr('product_id');

            $.ajax({

                type: 'POST',

                url: 'dropzone.php',

                data: {

                    name: name,

                    product_id: product_id,

                    request: 2

                },

                success: function(data) {

                    setTimeout(function() {

                        var insertmsg = `<div class="alert alert-danger fade in alert-dismissible show">

              <button type="button" class="close" data-dismiss="alert" aria-label="Close">

                  <span aria-hidden="true" style="font-size:30px">Delete</span>

                </button><strong> Delete! Data Successfully. </strong><a href="addproducts.php" class="float-right btn btn-danger">Add Another Product</a>

              </div>`;
              const queryString = window.location.search;
                    //console.log(typeof(queryString));
                    if(queryString ==''){
                        document.getElementById('insertMessage').innerHTML = insertmsg;
                    }

                    }, 500);

                }

            });

            var _ref;

            return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file

                .previewElement) : void 0;

        }

    });


    $(document).on('change', '#subcat', function() {

        var id = $(this).val();

        $.ajax({

            url: 'ajax/getcategorylist.php',
            type: 'GET',
            data: {
                subid: id
            },
            dataType: 'json',

            success: function(response) {

                console.log(response);

                var childcat = response.length;

                $('#childcat').removeAttr("disabled");



                $("#childcat").empty();



                for (var i = 0; i < childcat; i++) {



                    var id = response[i]['childcatid'];

                    var name = response[i]['childcategoryname'];

                    $("#childcat").append("<option value='" + id + "'>" + name +

                        "</option>");

            
          }

            }

        })



    });


});

</script>