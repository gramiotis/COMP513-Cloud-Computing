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
    <title>Admin page</title>
    <link rel="stylesheet" href="css/tabs.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="js/user_table_edit.js"></script>
<style>
</style>
<body>
<?php
if($_SESSION['role'] != "ADMIN"){
    echo "<div class='form'>
        <h3>You are not Authorised to see this page.</h3><br/>
        <p class='link'>Click here to go to the <a href='welcome.php'>Welcome Page</a> again.</p>
        </div>";
}else{

    $ch = curl_init();

    //get all users
    curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/users");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Auth-token:".$_SESSION['xtoken']
    ));
    curl_close($ch);

    $response = curl_exec($ch);
    $users = json_decode($response,true);
    $result = $users['users'];
?>
<div class="topnav">
    <form method="post">
        <input type="submit" name="logout" value="Log Out"/>
    </form>
    <p><?php echo $_SESSION['username'].'('.$_SESSION['role'].')';?></p>
    <a href="welcome.php">Home</a>
</div>
<h2 style="text-align: center;">Users</h2>
<div id="search_div">
    <input class="login-input" type="text" id="search" placeholder="Type to search">
</div>
<div id="admin">
    <table id="data_table">
    <thead>
    <tr><td colspan="8">*Click on the field to edit</td></tr>
    <tr><td colspan="8">*Press ENTER to register or ESC to cancel</td></tr>
    </thead>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Confirmed</th>
            <th></th>
        </tr>
        <?php
            //echo all user columns
            foreach($result as $row)
            {
                $id = $row['id'];
                if($row['description'] != NULL)
                {
                    $nameSlices = explode(" ", $row['description']);
                    $name = $nameSlices[0];
                    $surname = $nameSlices[1];
                    $fullname = $name." ".$surname;
                }
                else
                {
                    $name = "";
                    $surname = ""; 
                    $fullname = " ";
                }
                $username = $row['username'];
                $email = $row['email'];
                $role = $row['website'];

                if($username == 'admin')
                    continue;

                //build checkbox for enabled 
                $checkbox = '<label class="switch">
                <input type="checkbox" id="confirmed" onclick="check(\''.$id.'\', this)"';

                $append = '>
                <span class="slider round"></span>
                </label>';

                $checkbox = $row['enabled']==1 ? $checkbox.'checked = "checked"':$checkbox;
                $checkbox .= $append;

                $confirmed = $row['enabled'];
                
                $delete_button = '<button onclick="delete_user(\''.$id.'\')">Delete</button>';

                //build selection box for roles
                $roles = ['USER','ADMIN','PRODUCTSELLER'];
                $all_options = '';

                foreach($roles as $tmp){
                    if($tmp == $role){
                        $option = '<option value="'.$tmp.'" selected>'.$tmp.'</option>';
                    }else{
                        $option = '<option value="'.$tmp.'">'.$tmp.'</option>';
                    }
                    $all_options .= $option;
                }

                echo '<tr id='.$id.'><td>'.$id.'</td>
                <td><div class="edit" >'.$fullname.'</div><input type="text" class="txtedit" value="'.$fullname.'" id="name"></td>
                <td><div class="edit" >'.$username.'</div><input type="text" class="txtedit" value='.$username.' id="username"></td>
                <td><div class="edit" >'.$email.'</div><input type="email" class="txtedit" value='.$email.' id="email"></td>
                <td><div class="edit" ></div><select id="role" class="box" name="role">'.$all_options.'</select></td>
                <td style ="text-align: center;><div class="edit" ></div>'.$checkbox.'</td>
                <td style ="text-align: center;vertical-align: middle";>'.$delete_button.'</td></tr>';
            }
        }
        ?>
    </table> 
</div>
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