<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    if($conn){
        if(isset($_GET['userid'])){
            
            $user = $_GET['userid'];
            $product = $_GET['productid'];
            
            $answer = $subscriptions_col->find(array('USERID'=>$user,'PRODUCTID'=>$product))->toArray();
            echo json_encode($answer);
        }elseif (isset($_GET['orionid'])) {
            $id = $_GET['orionid'];
            
            $answer = $subscriptions_col->find(array('ORIONID'=>$id))->toArray();
            echo json_encode($answer);
        }elseif(isset($_GET['productid_multi'])){
            $id = $_GET['productid_multi'];
            
            $answer = $subscriptions_col->find(array('PRODUCTID'=>$id))->toArray();
            echo json_encode($answer);
        }elseif(isset($_GET['userid_multi'])){
            $id = $_GET['userid_multi'];

            $answer = $subscriptions_col->find(array('USERID'=>$id))->toArray();
            echo json_encode($answer);
        }
    }
?>