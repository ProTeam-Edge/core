<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$input = file_get_contents('php://input');
$data = json_decode($input);
$device_token = $data->device_token;

$update_sql= "update wp_users set device_token ='".$device_token."'  where ID =".$id." ";
$update_data = $wpdb->query($update_sql);
if($update_data)
	$response = array('success' => 1, 'message'=>'Updated token successfully.','data'=>'1');
else
$response = array('success' => 0, 'message'=>'Updation failed. Please try again later','data'=>'0');
echo json_encode($response); 