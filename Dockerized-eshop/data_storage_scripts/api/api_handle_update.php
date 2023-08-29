
<?php
    date_default_timezone_set('Europe/Athens');

    $orion_notif = json_decode(file_get_contents("php://input"),true);

    $orionid = $orion_notif['subscriptionId'];

    $productid = $orion_notif['data'][0]['id'];

    print_r($orion_notif);

    //Find old values of subscription attributes
    $rest_request = "http://localhost:80/api/api_get_subscription.php?orionid=".$orionid;

    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($client);
    curl_close($client);
    $data = json_decode($response,true);

    print_r($data);

    $soldout_old = $data[0]['SOLDOUT'];
    $userid = $data[0]['USERID'];

    $soldout_new = $orion_notif['data'][0]['soldout']['value'];

    //Patch updates to local MongoDB
    $rest_request = "http://localhost:80/api/api_update_subscription.php?orionid=".$orionid."&soldout=".$soldout_new;
    $client = curl_init();
    curl_setopt($client, CURLOPT_URL,$rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($client);
    curl_close($client);

    //Get the concert name from REST API of Data-Storage-Service
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:80/api/api_get_products.php?id=".$productid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $product = json_decode($output,true);

    print_r($product);
    $name = $product['NAME'];

    //If the soldout state has changed inform user about new sold out state
    if(!$soldout_new){
        $message = "Product: '".$name."' is available again!!";
        $time = new DateTime();
        $timestamp = $time->format('y-m-d H:i:s');

    }else{
        $message = "Product: '".$name."' is now sold out!!";
        $time = new DateTime();
        $timestamp = $time->format('y-m-d H:i:s');
    }

    $notif = '{
        "message" :"'.$message.'",
        "timestamp":"'.$timestamp.'",
        "userid":"'.$userid.'"            
    }';

    //Produce notification to MongoDB
    $rest_request = "http://localhost:80/api/api_create_notification.php";
    $client = curl_init();
    curl_setopt($client, CURLOPT_URL,$rest_request);
    curl_setopt($client, CURLOPT_POST, true);
    curl_setopt($client, CURLOPT_POSTFIELDS, $notif);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($client);
    curl_close($client);

    echo $response;


?>