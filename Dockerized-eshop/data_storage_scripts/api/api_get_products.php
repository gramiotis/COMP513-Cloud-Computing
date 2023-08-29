<?php

    header('Content-Type: application/json');

    include("../mongo_connect.php");

    //Searches for one specific product or return all products
    if($conn){
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $answer = $products_col->findOne(array('_id'=>new MongoDB\BSON\ObjectID($id)));
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }else if(isset($_GET['sellerid'])){
            $sellerid = $_GET['sellerid'];
            $answer = $products_col->find(array('SELLERNAME'=>$sellerid))->toArray();
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }else if(isset($_GET['sellername'])){
            $sellerid = $_GET['sellername'];
            $answer = $products_col->find(array('SELLERNAME'=>$sellerid), array('sort'=>array('_id'=>-1), 'limit'=>1))->toArray();
            echo json_encode($answer, JSON_PRETTY_PRINT);
            die;
        }else{
            $answer = $products_col->find()->toArray();
            echo json_encode($answer, JSON_PRETTY_PRINT) ;            
            die;
        }
    }
    
?>