<?php
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$input = file_get_contents('php://input');
$data = json_decode($input);
$source_key = $data->source_key;
$channelId = $data->channelId;
$id = $data->id;
if($source_key=='core_contact') {
	$sql = 'select u.device_token from alpn_topics as a JOIN alpn_topics as b on a.connected_topic_id = b.id JOIN wp_users as u on b.owner_id = u.ID where a.id='.$id.'';
	$data = $wpdb->get_row($sql);
	$token = $data->device_token;
}

$response = array('success' => 1, 'message'=>'No token found.','data'=>$token);
echo json_encode($response); 