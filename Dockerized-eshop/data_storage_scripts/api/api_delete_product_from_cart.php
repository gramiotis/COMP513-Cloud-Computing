<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Search if specific product is already in cart
    if($conn){
        if(isset($_GET['cart_id'])){
            $id = $_GET['cart_id'];
            $answer = $carts_col->deleteOne(array('_id'=>new MongoDB\BSON\ObjectID($id)));
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }
    }
    
?>