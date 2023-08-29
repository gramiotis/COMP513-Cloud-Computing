<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    if($conn){
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $message=$data['message'];
        $time=$data['timestamp'];
        $userid=$data['userid'];

        $notifications_col->insertOne(array('MESSAGE'=>$message,'TIMESTAMP'=>$time,'USERID'=>$userid));
    }
?>