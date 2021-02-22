<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
echo $root.'/wp-content/themes/memberlite-child-master/api_handler/twillo_chat_sdk/vendor/autoload.php';
die;
