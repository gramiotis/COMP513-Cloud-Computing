<?php
session_start();

if(isset($_POST['field'])){
    $field = $_POST['field'];
    $value = $_POST['value'];
    $id = $_POST['id'];

    //update product's field with new value using its id
    $rest_request = "http://ds-proxy:4001/api/api_update_product.php?field=".$field."&value=".$value."&id=".$id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);

    $row = json_decode($response,true);
}
?>