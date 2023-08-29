<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Search if specific product is already in cart
    if($conn){
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $userid = $_GET['user_id'];
            $answer = $carts_col->findOne(array(
                "PRODUCTID" => $id,
                "USERID" => $userid
            ));
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }
    }
    
?>