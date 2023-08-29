<?php

header('Content-Type: application/json');

include("../mongo_connect.php");

//Return notifications of a specific user 
if($conn){
    if(isset($_GET['userid'])){
        $userid = $_GET['userid'];
        
        $answer = $notifications_col->find(array('USERID'=>$userid))->toArray();

        echo json_encode($answer,JSON_PRETTY_PRINT);
        
    }
}

?>