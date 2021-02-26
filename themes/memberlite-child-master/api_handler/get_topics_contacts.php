<?php
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];

$input = file_get_contents('php://input');
$data = json_decode($input);

echo '<pre>';
print_r($data);
die;