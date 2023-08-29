<?php
include('db_connect.php');

session_start();

//delete user from admin page
if (isset($_POST['id'])) {
    $user_id = $_POST['id'];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/users/".$user_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Auth-token:".$_SESSION['xtoken']
    ));

    curl_exec($ch);
    curl_close($ch);
}

//delete product of seller
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $rest_request = "http://ds-proxy:4001/api/api_delete_product_of_seller.php?productid=".$product_id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);
}

//delete product from cart
if (isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];

    $rest_request = "http://ds-proxy:4001/api/api_delete_product_from_cart.php?cart_id=".$cart_id;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);
}

//add product to cart
if (isset($_POST['add_cart_id'])) {
    $add_cart_id = $_POST['add_cart_id'];
    $user_id = $_POST['userid'];

    //Get current date in Athen's timezone
    $tz = 'Europe/Athens';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz));
    $dt->setTimestamp($timestamp);
    $date = $dt->format("Y-m-d%20H:i:s");

    $rest_request = "http://ds-proxy:4001/api/api_insert_product_to_cart.php?userid=".$user_id."&productid=".$add_cart_id."&date=".$date;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);

    echo $rest_request;
}

//add new product of seller
if (isset($_POST['name_new'])) {
   //Fetching Values from URL
    $name=$_POST['name_new'];
    $code=$_POST['code_new'];
    $price=$_POST['price_new'];
    $datetime=$_POST['datetime_new'];
    $category=$_POST['category_new'];
    $sellername=$_SESSION['username'];

    $rest_request = "http://ds-proxy:4001/api/api_create_product.php?name=".$name."&code=".$code."&price=".$price."&datetime=".$datetime."&category=".$category."&sellername=".$sellername;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);

    echo $response;
}

//Reload Table request which gets the newest entry(highest id)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sellername = $_SESSION['username'];

    $rest_request = "http://ds-proxy:4001/api/api_get_products.php?sellername=".$sellername;
    $client = curl_init($rest_request);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
    $response = curl_exec($client);
    curl_close($client);

    $row = json_decode($response,true);

    $id = $row[0]['_id']['$oid'];
    $name = $row[0]['NAME'];
    $code = $row[0]['PRODUCTCODE'];
    $price = $row[0]['PRICE'];
    $date = date("Y-m-d H:i:s", $row[0]['DATEOFWITHDRAWAL']['$date']['$numberLong']/1000);
    $category = $row[0]['CATEGORY'];

    $return_arr = array("id" => $id,
                    "name" => $name,
                    "code" => $code,
                    "price" => $price,
                    "date" => $date,
                    "sellername" => $sellername,
                    "category" => $category);

    echo json_encode($return_arr);
}
?>