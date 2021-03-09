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
$subject_token = $data->id;
$sql = "SELECT * from alpn_topics_linked_view where owner_id = ".$id." and subject_token = '".$subject_token."'";
$results = $wpdb->get_results($sql);
echo '<pre>';
print_r($results);
die;

if(!empty($array))
$response = array('success' => 1, 'message'=>'Success topics found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No contacts found.','data'=>"");

echo json_encode($response); 