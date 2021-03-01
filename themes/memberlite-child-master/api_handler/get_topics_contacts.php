<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$input = file_get_contents('php://input');
$data = json_decode($input);
$id = $data->id;

$sql = 'select users.user_login as name , users.ID as id from alpn_topics as topics inner join wp_users as users on topics.owner_id=users.ID where topics.owner_id = "'.$id.'" and topics.special = "contact"';
$result = $wpdb->get_results($sql);

$array = $response= array();
if(!empty($result)) {
	$i = 0;
	foreach($result as $val) {
		$array[$i]['name'] = $val->name;
		$array[$i]['id'] = $val->id;
		$i++;
	}
	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response); 