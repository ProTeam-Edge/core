<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$input = file_get_contents('php://input');
$data = json_decode($input);
//$id = $data->id;
$id = $_REQUEST['id'];
$sql = 'select * from alpn_topics where owner_id = "'.$id.'" and special = "contact"';
$data = $wpdb->get_results($sql,ARRAY_A);
$array = array();
if(!empty($data) {
	foreach($data as $val) {
		$array[$val['id']]['name'] = $val['name'];
	}
	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response);