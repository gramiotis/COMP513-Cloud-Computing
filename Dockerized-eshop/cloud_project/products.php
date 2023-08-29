<?php
    include("scripts/db_connect.php");

    session_start();

    if(!isset($_SESSION['loggedIn']))//redirect to index.php if not logged in
    header("Location: index.php");

    if(isset($_POST['logout'])){
        header("Location: index.php");
        session_destroy();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Products page</title>
    <link rel="stylesheet" href="css/tabs.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<body>
<?php
    if($_SESSION['role'] != "USER"){
        echo "<div class='form'>
            <h3>You are not Authorised to see this page.</h3><br/>
            <p class='link'>Click here to go to the <a href='welcome.php'>Welcome Page</a> again.</p>
            </div>";
    }else{
        //get all products
        $rest_request = "http://ds-proxy:4001/api/api_get_products.php";
        $client = curl_init($rest_request);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
        $response = curl_exec($client);
        curl_close($client);
        $result = json_decode($response,true);
?>

<div class="topnav">
    <form method="post">
        <input type="submit" name="logout" value="Log Out"/>
    </form>
    <p><?php echo $_SESSION['username'].'('.$_SESSION['role'].')';?></p>
    <a href="welcome.php">Home</a>
    <a href="cart.php">Cart</a>
</div>
<h2 style="text-align: center;">Products</h2>
<div id="search_div">
    <input class="login-input" type="text" id="search" placeholder="Type to search">
</div>
<div id="products_table">
    <table>
        <tbody>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Price</th>
            <th>Date Of Withdrawal</th>
            <th>Seller Name</th>
            <th>Category</th>
            <th></th>
            <th></th>
        </tr>
        <?php
        if($result){
            $userid = $_SESSION['userid'];
            foreach($result as $row){
                $id = $row['_id']['$oid'];
                $name = $row['NAME'];
                $code = $row['PRODUCTCODE'];
                $price = $row['PRICE'];
                $date = date("Y-m-d H:i:s", $row['DATEOFWITHDRAWAL']['$date']['$numberLong']/1000);
                $sellername = $row['SELLERNAME'];
                $category = $row['CATEGORY'];
                $soldout = $row['soldout'];

                //check if products is in cart, if true display correct message
                $rest_request = "http://ds-proxy:4001/api/api_get_product_in_cart.php?id=".$id."&user_id=".$userid;
                $client = curl_init($rest_request);
                curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
                $response = curl_exec($client);
                curl_close($client);
                $exist_result = json_decode($response,true);

                //check if is subscribed to product, if true display correct message
                $rest_request = "http://ds-proxy:4001/api/api_get_subscription.php?userid=".$userid."&productid=".$id;
                $client = curl_init($rest_request);
                curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
                $response = curl_exec($client);
                $result = json_decode($response,true);

                if(array_key_exists(0,$result)){
                    if(array_key_exists("ORIONID",$result[0])){
                        $sub = true;
                    }else{
                        $sub = false;
                    }
                }else{
                    $sub = false;
                }
                

                //$cart_result = mysqli_query($con, "select * from Carts where PRODUCTID='$id' and USERID='$userid'");
                if($exist_result){
                    $add_button = '<button id="'.$id.'">Added to Cart</button>';
                }else{
                    $add_button = '<button id="'.$id.'" onclick="add_to_cart(\''.$id.'\', \''.$userid.'\')">Add to Cart</button>';
                }

                if(!$sub)
                    $subscribe_button = '<button id="sub_'.$id.'" onclick="subscribe(\''.$id.'\', \''.$userid.'\')">Subscribe</button>';
                else
                    $subscribe_button = '<button id="sub_'.$id.'">Subscribed</button>';

                echo '<tr><td>'.$name.'</td><td>'.$code.'</td><td>'.$price.'$</td><td>'.$date.'</td><td>'.$sellername.'</td><td>'.$category.'</td><td style ="text-align: center;vertical-align: middle;width: 90px;";>'.$add_button.'</td><td style ="text-align: center;vertical-align: middle;";>'.$subscribe_button.'</td></tr>';
            }
        }
    }
    ?>
    </tbody>
    </table> 
</div>
</body>
<script>
//add product to cart
function add_to_cart(id, userid) { 
    $(document).ready(function(){
        $.ajax({
            url: 'scripts/handle_ajax.php',
            type: 'POST',
            data: {add_cart_id:id, userid:userid},
            success: function (result) {
                    //alter button add to cart
                    document.getElementById(id).onclick = null;
                    document.getElementById(id).textContent = "Added to Cart";
            }
        }); 
    });
}

function subscribe(id,userid){
    $(document).ready(function(){
        $.ajax({
            url: 'scripts/orion/handle_sub.php',
            type: 'POST',
            data: {entityid_add:id,userid:userid},
            success: function (result) {
                    //alter button add to cart
                    document.getElementById(id).onclick = null;
                    document.getElementById('sub_'+id).textContent = "Subscribed";
            }
        }); 
    });
}

//Search Function
var $rows = $('#products_table tbody tr:not(:first-of-type)');
$('#search').keyup(function() {
    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
    
    $rows.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
    }).hide();
});
</script>
</html>