<?php
    $host = '172.18.1.3';  
    $user = 'keyrock';  
    $password = 'keyrock'; 
    $dbname = 'idm'; 

    // Enter your host name, database username, password, and database name.
    // If you have not set database password on localhost then set empty.
    $con = mysqli_connect($host, $user, $password, $dbname);
    // Check connection
    if (mysqli_connect_errno()){
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
?>