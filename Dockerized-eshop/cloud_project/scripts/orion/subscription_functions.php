<?php
session_start();

function add_orion_sub($id,$userid){
    $new_sub = '{
            "description": "Subscription for receiving updates on product with id '.$id.' for user '.$userid.'",
            "subject": {
            "entities": [
                {
                "id": "'.$id.'",
                "type": "product"
                }
            ],
            "condition": {
                "attrs": [
                "soldout"
                ]
            }
            },
            "notification": {
            "http": {
                "url": "http://172.18.1.7:80/api/api_handle_update.php"
            },
            "attrs": [
                "soldout"
            ]
            },
            "expires": "2050-04-05T14:00:00.00Z"
        }';

    $ch = curl_init();

    //send add subscriber request
    curl_setopt($ch, CURLOPT_URL, "http://orion:1026/v2/subscriptions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $new_sub);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Auth-Token: '.$_SESSION['oauthtoken']
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $headers = parse_curl($header);

    //retrieve sub id from the header of response
    $subid="";
    if(array_key_exists('Location', $headers)){
        $subid = $headers['Location'];
        $subid = substr($subid,18);
    }

    //acquire entity data to store to local database
    $entity=get_entity($id);

    $data=json_decode($entity,true);

    $soldout = $data['soldout'];

    if(empty($subid)){
        die;
    }else{

        //store subscribers to local db
        $rest_request = "http://ds-proxy:4001/api/api_create_subscription.php?productid=".$id."&userid=".$userid."&orionid=".$subid."&soldout=".$soldout;
        $client = curl_init($rest_request);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
        $response = curl_exec($client);

        curl_close($client);
    }
}

//retrieve entity's key values from id
function get_entity($id){

    $curl = curl_init();

    $rest_request = "http://orion-proxy:4002/v2/entities/".$id."?type=product&options=keyValues";

    curl_setopt_array($curl, array(
        CURLOPT_URL => $rest_request,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_HTTPHEADER => array('X-Auth-Token: '.$_SESSION['oauthtoken']),
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response,true);

    if(array_key_exists("error",$response)){

        echo json_encode(array("response"=>"notfound"));

    }elseif(array_key_exists("id",$response)){

        return json_encode($response);
       
    }
}

//checks if user is subscribed
function is_subscribed($id){
    $userid = $_SESSION['userid'];

    $rest_request = "http://ds-proxy:4001/api/api_get_subscription.php?userid=".$userid."&productid=".$id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    $result = json_decode($response,true);

    if(array_key_exists(0,$result)){
      if(array_key_exists("ORIONID",$result[0])){
        return true;
      }else{
        return false;
      }
    }else{
      return false;
    }
}

//retrive id of sub in orion from local db
function get_orion_id($productid){
    $userid = $_SESSION['userid'];
  
    $rest_request = "http://ds-proxy:4001/api/api_get_subscription.php?userid=".$userid."&productid=".$productid;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    $result = json_decode($response,true);
    return $result[0]["ORIONID"];
}


function remove_local_sub_entry($orionid){
    $userid = $_SESSION['userid'];

    $rest_request = "http://ds-proxy:4001/api/api_delete_subscription.php?orionid_single=".$orionid;
    $client = curl_init();
    curl_setopt($client, CURLOPT_URL,$rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    curl_exec($client);
    curl_close($client);
}

//remove subscription from orion and local db
function del_orion_sub($id){

    $ch = curl_init();
  
    $orionid = get_orion_id($id);
    $url = "http://orion-proxy:4002/v2/subscriptions/".$orionid;
  
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));  
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    
    curl_exec($ch);
    curl_close($ch);

    remove_local_sub_entry($orionid);
}

function add_new_entity($id){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://orion-proxy:4002/v2/entities?options=keyValues',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "id":"'.$id.'",
    "type": "product",
    "soldout": 0
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-Auth-Token: '.$_SESSION['oauthtoken']
    ),
    ));
    curl_exec($curl);
    curl_close($curl);
}

//update entity's soldout state
function update_entity($id, $soldout){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://orion-proxy:4002/v2/entities/".$id."/attrs/soldout");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"value\": ".$soldout.",
        \"type\": \"Number\"
    }");

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-Auth-Token: '.$_SESSION['oauthtoken'],
        'Content-Type: application/json'
    ));

    curl_exec($ch);
    curl_close($ch);

    echo json_encode(array("response"=>"success"));

}

//delete entity and its subscribers if there are any from orion and local db
function del_entity($id){

    $ch = curl_init();

    //delete entity
    curl_setopt($ch, CURLOPT_URL, "http://orion-proxy:4002/v2/entities/".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-Auth-Token: '.$_SESSION['oauthtoken'],
        'Content-Type:'
    ));

    curl_exec($ch);
    curl_close($ch);

    //get all subs from this entity
    $rest_request = "http://ds-proxy:4001/api/api_get_subscription.php?productid_multi=".$id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    $result = json_decode($response,true);

    foreach($result as $sub){

        //delete orion sub
        $ch = curl_init();
        $url = "http://orion-proxy:4002/v2/subscriptions/".$sub['ORIONID'];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));  
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_exec($ch);
        curl_close($ch);

        //delete sub from local db
        $rest_request = "http://ds-proxy:4001/api/api_delete_subscription.php?orionid_single=".$sub['ORIONID'];
        $client = curl_init($rest_request);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
        curl_exec($client);
        curl_close($client);
    }
}

//delete notification
function del_notif($id){

    $rest_request = "http://ds-proxy:4001/api/api_delete_notification.php?notifid=".$id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    curl_exec($client);
    curl_close($client);

}

//parse the headers of curl response and create associative array
function parse_curl($header){
    $headers = array();

    foreach (explode("\n", $header) as $i => $h) {
        $h = explode(':', $h, 2);
    
        if (isset($h[1])) {
            if(!isset($headers[$h[0]])) {
                $headers[$h[0]] = trim($h[1]);
            } else if(is_array($headers[$h[0]])) {
                $tmp = array_merge($headers[$h[0]],array(trim($h[1])));
                $headers[$h[0]] = $tmp;
            } else {
                $tmp = array_merge(array($headers[$h[0]]),array(trim($h[1])));
                $headers[$h[0]] = $tmp;
            }
        }
    }

    return $headers;
}
?>