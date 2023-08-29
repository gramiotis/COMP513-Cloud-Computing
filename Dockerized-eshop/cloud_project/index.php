<?php
    include("scripts/db_connect.php");

    session_start();
    $auth_error = $enabled_error = false;

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

    // When form submitted, check and create user session.
    if (isset($_POST['username'])) {
        $username = stripslashes($_REQUEST['username']);    // removes backslashes
        $username = mysqli_real_escape_string($con, $username);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con, $password);
        
        //send request for x-subject-token with user's creds 
        $user_info = '{
            "name":"'.$username.'",
            "password":"'.$password.'"}';

        $curl_req = curl_init();

        //construct the request for keyrock
        curl_setopt($curl_req, CURLOPT_URL, "http://keyrock:3005/v1/auth/tokens");
        curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_req, CURLOPT_HEADER, 1);
        curl_setopt($curl_req, CURLOPT_POST, TRUE);
        curl_setopt($curl_req, CURLOPT_POSTFIELDS, $user_info);
        curl_setopt($curl_req, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

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

        //check if user is enabled or just wrong info
        $result = mysqli_query($con, "select enabled from user where email='".$username."'");

        $enabled = mysqli_fetch_row($result);
        
        if(empty($x_token)){
            //error in authentication
            if($enabled && !$enabled[0])
                $enabled_error = true;
            else
                $auth_error = true;
        }else{
            //get token info and check if user is enabled and get his id
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://keyrock:3005/v1/auth/tokens");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-Auth-token:".$x_token,
                "X-Subject-token:".$x_token
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            $user_data = $result['User'];

            if($user_data['enabled']==0){
                $enabled_error = true;
            }else{

                //send request to keyrock to aqcuire user info
                $curl_req = curl_init();
                curl_setopt($curl_req, CURLOPT_URL, "http://keyrock:3005/v1/users/".$user_data['id']);
                curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl_req, CURLOPT_HTTPHEADER, array("X-Auth-Token: ".$x_token));
                $answer = curl_exec($curl_req);
                curl_close($curl_req);

                $user_info = json_decode($answer,true);

                //get oauth2 token for pep proxy
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://keyrock:3005/oauth2/token',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'grant_type=password&username='.$username.'&password='.$password.'',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded',
                        'Authorization: Basic OGQ4ZjQ2OWYtNDM4ZC00MWQ2LTlmZDAtYzg1Mzg4ZWEwMDc3OmJjZDI3NmQ1LTI2MzYtNDQwNi05MDE4LWM2NjdjNTgyZjVhOA=='
                    ),
                ));
        
                $response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($response);
                $oauthtoken = $result->access_token;
                
                //assign session variables
                $_SESSION['oauthtoken'] = $oauthtoken;
                $_SESSION['xtoken'] = $x_token;
                $_SESSION['username'] = $user_info['user']['username'];
                $_SESSION['email'] = $user_info['user']['email'];
                $_SESSION['userid'] = $user_info['user']['id'];
                $_SESSION['loggedIn'] = TRUE;
            
                $_SESSION['confirmed'] = $user_data['enabled'];

                $fullname = $user_info['user']['description'];
                $sliced = explode(" ",$fullname);
                $_SESSION['firstname'] = $sliced[0];
                $_SESSION['lastname'] = $sliced[1];

                $_SESSION['role'] = $user_info['user']['website'];

                header("Location: welcome.php");
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Login</title>
    <link rel="stylesheet" href="css/login_signup.css"/>
    <link rel="icon" href="logo.jpeg" type="image/icon type">
</head>
<style>
h3{
    text-align: center;
    margin-bottom: 0px;
}
</style>
<body>
    <?php
        if(!$auth_error){
            if(!$enabled_error){
                echo '<form class="form" method="post" name="login">
                     <h1 class="login-title">Login</h1>
                     <input type="text" class="login-input" name="username" placeholder="Email" autofocus="true"/>
                     <input type="password" class="login-input" name="password" placeholder="Password"/>
                     <input type="submit" value="Login" name="submit" class="login-button"/>
                     <p class="link"><a href="signup.php">New Registration</a></p>
                     </form>';
            }else{
                echo "<div class='form'>
                     <h3>Your registration is not confirmed by the Admin.</h3><br/>
                     <p class='link'>Click here to <a href='index.php'>Login</a>.</p>
                     </div>";
            }
        }
        else{
            echo "<div class='form'>
                 <h3>Incorrect Email/password.</h3><br/>
                 <p class='link'>Click here to <a href='index.php'>Login</a> again.</p>
                 </div>";
        }
    ?>
</body>
</html>
