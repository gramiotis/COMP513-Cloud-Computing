<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Search if specific product is already in cart
    if($conn){
        if(isset($_GET['field'])){
            $field = $_GET['field'];
            $value = $_GET['value'];
            $id = $_GET['id'];

            if($field == 'PRICE' || $field == 'soldout')
                $value = (int) $value;

            if($field == 'DATEOFWITHDRAWAL')
                $value = new MongoDB\BSON\UTCDateTime(strtotime($value)*1000);

            $answer = $products_col->updateOne(array('_id'=>new MongoDB\BSON\ObjectID($id)), array('$set'=>array($field=>$value)));
            echo json_encode($answer, JSON_PRETTY_PRINT);
        }
    }
    
?>