<?php 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$input = file_get_contents('php://input');
$data = json_decode($input);
$action = $data->action;
function Authenticate($data) {
}
$action
?>