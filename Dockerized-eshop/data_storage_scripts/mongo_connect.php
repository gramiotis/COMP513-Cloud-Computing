<?php
    require 'config/vendor/autoload.php';
    $conn = new MongoDB\Client("mongodb://admin:admin@mongo:27017");
    $db = $conn->eshop;

    $products_col = $db->Products;
    $carts_col = $db->Carts;
    $subscriptions_col = $db->Subscriptions;
    $notifications_col = $db->Notifications;
?>