
<?php

header('Content-Type: application/json');

include("../mongo_connect.php");

if($conn){
    if(isset($_GET['notifid'])){
        $notifid = $_GET['notifid'];
      
        $notifications_col->deleteOne(array('_id'=>new MongoDB\BSON\ObjectID($notifid)));
    }
}


?>