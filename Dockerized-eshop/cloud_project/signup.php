<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Registration</title>
    <link rel="stylesheet" href="css/login_signup.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<body>
<style>
h3{
    text-align: center;
    margin-bottom: 0px;
}
</style>

<?php
    include("scripts/db_connect.php");

    //ID, name, surname, username, password, email, role
    // When form submitted, insert values into the database.
    if (isset($_REQUEST['username'])) {
        // removes backslashes
        //escapes special characters in a string
        $name    = stripslashes($_REQUEST['name']);
        $name    = mysqli_real_escape_string($con, $name);
        $surname    = stripslashes($_REQUEST['surname']);
        $surname    = mysqli_real_escape_string($con, $surname);
        $username = stripslashes($_REQUEST['username']);
        $username = mysqli_real_escape_string($con, $username);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con, $password);
        $email    = stripslashes($_REQUEST['email']);
        $email    = mysqli_real_escape_string($con, $email);
        $role    = stripslashes($_REQUEST['role']);
        $role    = mysqli_real_escape_string($con, $role);
        
        //check if email is valid
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo "<div class='form'>
                <h3>Email needs to be in a@b.c format.</h3><br/>
                <p class='link'>Click here to <a href='signup.php'>Signup</a> again.</p>
                </div>";
        }

        if($role == "ADMIN")
            $admin = TRUE;
        else
            $admin = FALSE;

        //data for adding new user
        $new_data = array("user"=>array("email"=>"$email","username"=>"$username","password"=>"$password","description"=>"$name"." "."$surname","website"=>"$role","enabled"=>TRUE,"admin"=>$admin));
        
        //admin data to get token to add new user
        $user_creds = array("name"=>"gramiotis@tuc.gr","password"=>"admin");


        $curl_req = curl_init();
        
        //request to get a token from admin data
        curl_setopt($curl_req, CURLOPT_URL, "http://keyrock:3005/v1/auth/tokens");
        curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_req, CURLOPT_HEADER, 1);
        curl_setopt($curl_req, CURLOPT_POST, TRUE);
        curl_setopt($curl_req, CURLOPT_POSTFIELDS, json_encode($user_creds));
        curl_setopt($curl_req, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
        
        //get the token
        $response = curl_exec($curl_req);
        $header_size = curl_getinfo($curl_req, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $parsed_header = parse_curl($header);
        curl_close($curl_req);

        $x_token="";
        try{
            if(array_key_exists('X-Subject-Token', $parsed_header))
                $x_token = $parsed_header['X-Subject-Token'];
            else
                $x_token="";
        }catch(Exception $ex){
            $x_token="";
        } 

        if(!empty($x_token)){

            //get a list with all users to check first if username and email that were submitted already exist
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/users");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-Auth-token:".$x_token
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            $users = $result['users'];

            $exists = 0;

            foreach($users as $user){
                if($user['email'] == $email){
                    echo "<div class='form'>
                    <h3>Email already exists.</h3><br/>
                    <p class='link'>Click here to <a href='signup.php'>Signup</a> again.</p>
                    </div>";
                    $exists = 1;
                }
                if($user['username'] == $username){
                    echo "<div class='form'>
                    <h3>Username already exists.</h3><br/>
                    <p class='link'>Click here to <a href='signup.php'>Signup</a> again.</p>
                    </div>";
                    $exists = 1;
                }
            }


            if(!$exists){
                //request to add new user
                $curl_req = curl_init();
                
                curl_setopt($curl_req, CURLOPT_URL, "http://keyrock:3005/v1/users");
                curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl_req, CURLOPT_HEADER, FALSE);
                curl_setopt($curl_req, CURLOPT_POST, TRUE);
                curl_setopt($curl_req, CURLOPT_POSTFIELDS, json_encode($new_data));
                curl_setopt($curl_req, CURLOPT_HTTPHEADER, array("Content-Type:application/json","X-Auth-token:".$x_token));
                $response = curl_exec($curl_req);
                curl_close($curl_req);

                $new_info = json_decode($response,true);
                $keyrock_id = $new_info["user"]["id"];

                //set enabled to false by default
                $query = "UPDATE user SET enabled=0 WHERE id='" . $keyrock_id . "'";
                mysqli_query($con,$query);

                echo "<div class='form'>
                    <h3>You are registered successfully.</h3><br/>
                    <p class='link'>Click here to <a href='index.php'>Login</a></p>
                    </div>";
            }
        }else{
            //Error in authentication
            echo "<div class='form'>
            <h3>Something went wrong with the request for authentication please try again.</h3><br/>
            <p class='link'>Click here to <a href='signup.php'>Signup</a>.</p>
            </div>";
        }
    } else {
?>
    <form class="form" style="margin: 12.5vh auto;" action="" method="post">
        <h1 class="login-title">Registration</h1>
        <input type="text" class="login-input" name="name" placeholder="Name" required />
        <input type="text" class="login-input" name="surname" placeholder="Surname" required />
        <input type="text" class="login-input" name="username" placeholder="Username" required />
        <input type="password" class="login-input" name="password" placeholder="Password" required>
        <input type="email" class="login-input" name="email" placeholder="Email Adress" required>
        <select class="box" name="role" id="role">
            <option value="USER">User</option>
            <option value="PRODUCTSELLER">Product Seller</option>
            <option value="ADMIN">Admin</option>
        </select> 
        <input type="submit" name="submit" value="Register" class="login-button">
        <p class="link"><a href="index.php">Click to Login</a></p>
    </form>
<?php
    }
?>
</body>
</html>

<?php 
//parse headers of curl response and create associative array
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