<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Searches for one specific product or return all products
    if($conn){
        if(isset($_GET['userid'])){
            $userid = $_GET['userid'];
            $productid = $_GET['productid'];
            $date = $_GET['date'];
            $answer = $carts_col->insertOne(array( 
                "USERID" => $userid, 
                "PRODUCTID" => $productid,
                "DATEOFINSERTION" => new MongoDB\BSON\UTCDateTime(strtotime($date)*1000)
            ));
            echo $answer;
        }
    }
    
?>