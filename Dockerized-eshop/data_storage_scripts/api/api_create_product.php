<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Searches for one specific product or return all products
    if($conn){
        if(isset($_GET['name'])){
            $name=$_GET['name'];
            $code=$_GET['code'];
            $price=$_GET['price'];
            $datetime=$_GET['datetime'];
            $category=$_GET['category'];
            $sellername=$_GET['sellername'];

            $new_doc = array( 
                "ID" => "NULL", 
                "NAME" => $name,
                "PRODUCTCODE" => $code,
                "PRICE" => (int)$price,
                "DATEOFWITHDRAWAL" => new MongoDB\BSON\UTCDateTime(strtotime($datetime)*1000),
                "SELLERNAME" => $sellername,
                "CATEGORY" => $category,
                "soldout" => 0
            );

            $answer = $products_col->insertOne($new_doc);
            
            echo json_encode($new_doc, JSON_PRETTY_PRINT);
            die;
        }
    }
    
?>