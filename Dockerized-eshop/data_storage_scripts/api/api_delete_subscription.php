<?php
    include("../mongo_connect.php");

    if($conn){
        if(isset($_GET['orionid_single'])){

            $orion = $_GET['orionid_single'];
            $subscriptions_col->deleteOne(array('ORIONID'=>$orion));
        }else{
            $data = json_decode(file_get_contents("php://input"), true);

            echo $data;

            $orion = $data['orionid'];
            $user = $data['userid'];
            
            $subscriptions_col->deleteOne(array('ORIONID'=>$orion, 'USERID'=>$user));
        }
    }
?>