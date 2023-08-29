<?php
include('subscription_functions.php');

if(isset($_POST['entityid_add'])){
    $id = $_POST['entityid_add'];
    $userid = $_POST['userid'];

    add_orion_sub($id, $userid);
}

if(isset($_POST['entityid_check'])){
    $id = $_POST['entityid_check'];

    $response = is_subscribed($id);

    echo $response;
}

if(isset($_POST['entityid_delete_sub'])){
    $id = $_POST['entityid_delete_sub'];

    del_orion_sub($id);
}

if(isset($_POST['entityid_new'])){
    $id = $_POST['entityid_new'];

    add_new_entity($id);
}

if(isset($_POST['entityid_delete'])){
    $id = $_POST['entityid_delete'];

    $response = del_entity($id);
}

if(isset($_POST['entityid_update'])){

    $id = $_POST['entityid_update'];
    $soldout = $_POST['soldout'];

    update_entity($id, $soldout);
}

if(isset($_POST['notifid'])){

    $id = $_POST['notifid'];

    del_notif($id);
}
?>

