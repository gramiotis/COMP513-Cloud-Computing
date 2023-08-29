<?php
session_start();

include_once("db_connect.php");

//handle live edit of user table from admin page
if(isset($_POST['field']))
{
    $field = $_POST['field'];
    $value = $_POST['value'];
    $id = $_POST['id'];

    if($field == 'name')
    {
        // Prepare an update statement
        $data = array("user"=>array(
            'description' => $value
        ));
    }
    else
    {
        $data = array("user"=>array(
            $field => $value
        ));
    }

    //request to update new value
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/users/".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Auth-token:".$_SESSION['xtoken']
    ));

    $response = curl_exec($ch);
    curl_close($ch);
}

//special treatment for role updates
if(isset($_POST['role']))
{
    $field = $_POST['role'];
    $value = $_POST['value'];
    $id = $_POST['id'];

    if($value != 'ADMIN')
        $admin = 0;
    else
        $admin = 1;

    $data = array("user"=>array(
        $field => $value,
    ));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/users/".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Auth-token:".$_SESSION['xtoken']
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $query = "UPDATE user SET admin= " . $admin . " WHERE id='" . $id . "'";

    mysqli_query($con, $query);

    echo json_encode($data);
}

//special treatment for enabled updates
if(isset($_POST['confirm_value']))
{
    $value = $_POST['confirm_value'];
    $id = $_POST['id'];

    $query = "UPDATE user SET enabled= '" . $value . "' WHERE id='" . $id . "'";

    mysqli_query($con, $query);
}
?>