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
    <title>Cart page</title>
    <link rel="stylesheet" href="css/tabs.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style>
#search_div {
    width: 25%;
    margin: 0 auto;
}
h3{
    text-align: center;
    margin-bottom: 0px;
}
tbody{
    display: block;
    width: 100%;
    overflow: auto;
    height: 300px;
}
th{
    position: sticky; top: 0;
    width: 1%;
}
thead{
    position: sticky; top: 0;
}
</style>
<body>
<?php
    if($_SESSION['role'] != "USER"){
        echo "<div class='form'>
            <h3>You are not Authorised to see this page.</h3><br/>
            <p class='link'>Click here to go to the <a href='welcome.php'>Welcome Page</a> again.</p>
            </div>";
    }else{
    $userid = $_SESSION['userid'];

    //get all products from cart with userid
    $rest_request = "http://ds-proxy:4001/api/api_get_cart_from_userid.php?userid=".$userid;
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
    <a href="products.php">Products</a>
</div>
<h2 style="text-align: center;">Products in Cart</h2>
<div id="search_div">
    <input class="login-input" type="text" id="search" placeholder="Type to search">
</div>
<div id="cart_table">
    <table id="data_table">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Product Code</th>
            <th>Date Of Insertion</th>
            <th></th>
        </tr>
        <?php
            $total = 0;
            if($result){
                foreach($result as $row){
                    $id = $row['_id']['$oid'];
                    $product_id = $row['PRODUCTID'];
                    $date_of_insertion = date("Y-m-d H:i:s", $row['DATEOFINSERTION']['$date']['$numberLong']/1000);;

                    //get product's info
                    $rest_request = "http://ds-proxy:4001/api/api_get_products.php?id=".$product_id;
                    $client = curl_init($rest_request);
                    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($client, CURLOPT_HTTPHEADER, array('X-Auth-Token: '.$_SESSION['oauthtoken']));
                    $response = curl_exec($client);
                    curl_close($client);
                    $product = json_decode($response,true);
                    if($product){
                        $price = $product['PRICE'];
                        $name = $product['NAME'];
                        $product_code = $product['PRODUCTCODE'];
                    }

                    $total += $price;

                    $delete_button = '<button onclick="delete_product(\''.$id.'\')">Delete</button>';

                    echo '<tr id="'.$id.'"><td>'.$name.'</td><td>'.$price.'$</td><td>'.$product_code.'</td><td>'.$date_of_insertion.'</td><td style ="text-align: center;vertical-align: middle;";>'.$delete_button.'</td></tr>';
                }
            }
        ?>
        <thead><tr><td id="total" colspan="8" style="font-size: large;">Total Amount: <?php echo $total ?>$</td></tr></thead>
        <?php
        }
        ?>
    </table>
</div>
</body>
<script>
    //delete product from database using its id
    function delete_product(id) { 
    $(document).ready(function(){
        $.ajax({
            url: 'scripts/handle_ajax.php',
            type: 'POST',
            data: {cart_id:id},
            success: function (result) {
                    //remove product from table
                    document.getElementById(id).remove();

                    var firstCells = document.querySelectorAll('td:nth-child(2)');
                    let total = 0;
                    firstCells.forEach(function(singleCell) {
                        total = total + parseInt(singleCell.textContent, 10);
                        console.log(total);
                    });
                    document.getElementById("total").textContent = "Total Amount: " + total + "$";
            }
        }); 
    }); 
}

//Search Function
var $rows = $('#cart_table tbody tr:not(:first-of-type)');
$('#search').keyup(function() {
    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
    
    $rows.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
    }).hide();
});
</script>
</html>