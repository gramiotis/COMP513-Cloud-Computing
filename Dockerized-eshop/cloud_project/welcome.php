<?php 
session_start();

if(!isset($_SESSION['loggedIn']))//redirect if not logged in
    header("Location: index.php");

if(isset($_POST['logout'])){
    header("Location: index.php");
    session_destroy();
}
?>
<style>
.column {
  flex: 50%;
}
.row {
  display: flex;
  margin-left: 300px;
  margin-right: 300px;
}
</style>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
    <title>Welcome to our Shop</title>
    <link rel="stylesheet" href="css/welcome.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<body>

<div class="topnav">
    <form method="post">
        <input type="submit" name="logout" value="Log Out"/>
    </form>
    <p><?php echo $_SESSION['username'].'('.$_SESSION['role'].')';?></p>
    <a href="products.php">Products</a>
    <a href="cart.php">Cart</a>
    <a href="seller.php">Sellers Page</a>
    <a href="administration.php">Admins Page</a>
</div>
<?php
//if role user display tables with notifications and subscriptions else display welcome message 
if($_SESSION['role'] == 'USER'){
?>
<div class="row">
<div id="table1" class="column">
    <table>
        <thead>
            <tr>
                <th>Notifications</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Message</th>
                <th>Timestamp</th>
                <th></th>
            </tr>
        <?php
            //get all notifications from local db
            $rest_request = "http://ds-proxy:4001/api/api_get_notifications.php?userid=".$_SESSION['userid'];
            $client = curl_init($rest_request);
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
            $response = curl_exec($client);
            curl_close($client);
            $result = json_decode($response,true);

            if(count($result)>0){
                foreach($result as $notif){
                    $message = $notif['MESSAGE'];
                    $time = $notif['TIMESTAMP'];
                    $id = $notif['_id']['$oid'];

                    $delete_button = '<button onclick="delete_notif(\''.$id.'\')">Delete</button>';

                    echo '<tr id="'.$id.'"><td>'.$message.'</td><td>'.$time.'</td><td>'.$delete_button.'</td></tr>';
                }
            }else{
                echo '<tr><td>You have no notifications.</td><td></td><td></td></tr>';
            }
        ?>
        </tbody>
    </table>
</div>
<div id="table2" class="column">
    <table>
        <thead>
            <tr>
                <th>Subscriptions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Product</th>
                <th></th>
            </tr>
        <?php
            //get all subscriptions from local db
            $rest_request = "http://ds-proxy:4001/api/api_get_subscription.php?userid_multi=".$_SESSION['userid'];
            $client = curl_init($rest_request);
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
            $response = curl_exec($client);

            curl_close($client);
            $result = json_decode($response,true);

            if(count($result)>0){
                foreach($result as $sub){
                    $product = $sub['PRODUCTID'];

                    $rest_request = "http://ds-proxy:4001/api/api_get_products.php?id=".$product;
                    $client = curl_init($rest_request);
                    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
                    $response = curl_exec($client);
                    curl_close($client);
                    $result = json_decode($response,true);

                    $name = $result['NAME'];

                    $cancel_sub_button = '<button id=sub_"'.$product.'" onclick="unsubscribe(\''.$product.'\')">Cancel Subscription</button>';
                    

                    echo '<tr id="'.$product.'"><td>'.$name.'</td><td>'.$cancel_sub_button.'</td></tr>';
                }
            }else{
                echo '<tr><td>You have no subscriptions.</td><td></td></tr>';
            }
        ?>
        </tbody>
    </table>
</div>
</div>
<?php
}else{
?>
<div>
    <h1>Welcome To The Shop</h1>
    <h2>Hope you find the website pleasant</h2>
</div>
<?php
}
?>

</body>
<script>
    function unsubscribe(id){
        $(document).ready(function(){
            $.ajax({
                url: 'scripts/orion/handle_sub.php',
                type: 'POST',
                data: {entityid_delete_sub:id},
                success: function (result) {
                        document.getElementById(id).remove();
                }
            }); 
        });
    }

    function delete_notif(id){
        $(document).ready(function(){
            $.ajax({
                url: 'scripts/orion/handle_sub.php',
                type: 'POST',
                data: {notifid:id},
                success: function (result) {
                        document.getElementById(id).remove();
                }
            }); 
        });
    }
</script>
</html>
