<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Search if specific product is already in cart
    if($conn){
        if(isset($_GET['productid'])){
            $id = $_GET['productid'];
            $answer = $products_col->deleteOne(array('_id'=>new MongoDB\BSON\ObjectID($id)));
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }
    }
    
?>