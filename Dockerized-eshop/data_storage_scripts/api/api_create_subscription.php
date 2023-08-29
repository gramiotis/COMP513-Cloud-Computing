<?php

include("../mongo_connect.php");

//Create subscription for specific user_id on specific concert_id
if($conn){
    if(isset($_GET['productid'])){

        $product=$_GET['productid'];
        $user =$_GET['userid'];
        $orion = $_GET['orionid'];
        $soldout = $_GET['soldout'];

        $subscriptions_col->insertOne(array('ORIONID'=>$orion,'USERID'=>$user,'PRODUCTID'=>$product,'SOLDOUT'=>(int)$soldout));
    }
}

?>