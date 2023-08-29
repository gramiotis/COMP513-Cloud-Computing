<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Searches for one specific product or return all products
    if($conn){
        if(isset($_GET['userid'])){
            $userid = $_GET['userid'];
            $answer = $carts_col->find(array("USERID"=>$userid))->toArray();
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }
    }
?>