<?php
    include("scripts/db_connect.php");

    session_start();

    if(!isset($_SESSION['loggedIn']))
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
    <title>Seller page</title>
    <link rel="stylesheet" href="css/tabs.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="js/product_table_edit.js"></script>
<style>
.entry{
    width: 300px;
    height: 50px;
    margin: 25vh auto;
    margin-top: 10px;
    padding: 30px 25px;
    margin-bottom: 10px;
    border: 2px white solid;
    border-radius: 16px;
    height: 300px;
}
.entry-input{
    font-size: 15px;
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 4px;
    width: calc(100% - 23px);
    display: inline;
    border-radius: 20px;
}
.entry-button{
    color: #fff;
    background: #069;
    border: 0;
    outline: 0;
    width: 200px;
    height: 30px;
    font-size: 16px;
    text-align: center;
    margin: 10px 50px 2px;
    cursor: pointer;
    border-radius: 16px;
}
#add-btn{
    color: #fff;
    background: #069;
    font-size: 16px;
    border: 0;
    outline: 0;
    width: 200px;
    height: 30px;
    cursor: pointer;
    border-radius: 16px;
    margin-left: 100vh;
    margin-bottom: 20px;
}
input[type="datetime-local"] {
     border-radius: 16px;
}
.form {
    box-shadow: 0 15px 25px rgba(0,0,0,.6);
    margin: 25vh auto;
    width: 300px;
    padding: 30px 25px;
    background: white;
    border-radius: 16px;
}
.link {
    color: blue;
    font-size: 15px;
    text-align: center;
    margin-bottom: 0px;
}
.link a{
    color: blue;
}
</style>
<body>
<?php
    if($_SESSION['role'] != "PRODUCTSELLER"){
        echo "<div class='form'>
            <h3>You are not Authorised to see this page.</h3><br/>
            <p class='link'>Click here to go to the <a href='welcome.php'>Welcome Page</a> again.</p>
            </div>";
    }else{
        $username = $_SESSION['username'];

        //get all products of seller
        $rest_request = "http://ds-proxy:4001/api/api_get_products.php?sellerid=".$username;
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
</div>
<h2 style="text-align: center;">Your Products</h2>
<div id="search_div">
    <input class="login-input" type="text" id="search" placeholder="Type to search">
</div>
<input class="add" id="add-btn" onclick="show_form()" type="button" value="Add New Entry">
<div id="add_new">
    <form id="new_entry" class="entry" method="post" name="add_new_entry" hidden>
        <input id="name" type="text" class="entry-input" name="Name" placeholder="Name" autofocus="true" required/>
        <input id="product_code" type="text" class="entry-input" name="product_code" placeholder="Product Code(4 digit)" required/>
        <input id="price" type="text" class="entry-input" name="price" placeholder="Price" required/>
        <input id="category" type="text" class="entry-input" name="category" placeholder="Category" required/>
        <label style="color: white; margin: 0px 40px 10px;font-size: 15px;font-weight: 500;text-align: center;" for="date_of_withdrawal">Choose a time of withdrawal:</label>
        <input id="datetime" style="margin: 0px 40px 10px;" type="datetime-local" id="date_of_withdrawal" name="date_of_withdrawal" value="2020-11-11T19:30" min="2020-11-11T19:30" max="2024-06-14T00:00">
        <input id="submit" onclick="add_entry()" type="button" value="Add New Entry" class="entry-button">
        <input id="cancel" onclick="show_form()" type="button" value="Cancel" class="entry-button"> 
    </form>
</div>
    <table id="data_table">
    <thead>
    <tr><td colspan="8">*Click on the field to edit</td></tr>
    <tr><td colspan="8">*Press ENTER to register or ESC to cancel</td></tr>
    </thead>
    <tbody id="table_body">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Product Code</th>
            <th>Price</th>
            <th>Date Of Withdrawal</th>
            <th>Category</th>
            <th>Soldout</th>
            <th></th>
        </tr>
        <?php
        if($result){
            foreach($result as $row){
                $id = $row['_id']['$oid'];
                $name = $row['NAME'];
                $product_code = $row['PRODUCTCODE'];
                $price = $row['PRICE'];
                $date_of_withdrawal = date("Y-m-d H:i:s", $row['DATEOFWITHDRAWAL']['$date']['$numberLong']/1000);
                $category = $row['CATEGORY'];

                //build checkbox button for soldout
                $checkbox = '<label class="switch">
                <input type="checkbox" id="confirmed" onclick="check(\''.$id.'\', this)"';

                $append = '>
                <span class="slider round"></span>
                </label>';

                $checkbox = $row['soldout']==1 ? $checkbox.'checked = "checked"':$checkbox;
                $checkbox .= $append;

                $delete_button = '<button onclick="delete_product(\''.$id.'\')">Delete</button>';

                echo '<tr id="'.$id.'"><td>'.$id.'</td>
                <td><div class="edit" >'.$name.'</div><input type="text" class="txtedit" value="'.$name.'" id="NAME"></td>
                <td><div class="edit" >'.$product_code.'</div><input type="text" class="txtedit" value="'.$product_code.'" id="PRODUCTCODE"></td>
                <td><div class="edit" >'.$price.'</div><input type="text" class="txtedit" value="'.$price.'" id="PRICE"></td>
                <td><input type="datetime-local" id="DATEOFWITHDRAWAL" name="date_of_withdrawal" value="'.$date_of_withdrawal.'" min="2020-11-11T19:30" max="2024-06-14T00:00"></td>
                <td><div class="edit" >'.$category.'</div><input type="text" class="txtedit" value="'.$category.'" id="CATEGORY"></td>
                <td style ="text-align: middle;><div class="edit" ></div>'.$checkbox.'</td>
                <td style ="text-align: center;vertical-align: middle";>'.$delete_button.'</td></tr>';
            }
        }
    }
        ?>
    </tbody>
    </table> 
</div>
</body>

<script>
//Search Function
var $rows = $('#data_table tbody tr:not(:first-of-type)');
$('#search').keyup(function() {
    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
    
    $rows.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
    }).hide();
});
</script>
</html>