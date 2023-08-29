<?php
    include("../mongo_connect.php");
    
    if($conn){
        if(isset($_POST['orionid'])){

            $orion = $_POST['orionid'];
            $soldout = $_POST['soldout'];

            $subscriptions_col->updateOne(array('ORIONID'=>$orion),array('$set' => array('SOLDOUT'=>(int)$soldout)));
        }
        
    }

?>