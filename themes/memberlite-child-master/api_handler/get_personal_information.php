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
$sql = "SELECT * from alpn_topics where owner_id = ".$id." and special = 'user' and sync_id !=''";
$results = $wpdb->get_row($sql);
$topic_content = '';
if(isset($results->topic_content) && !empty($results->topic_content))
$topic_content = $results->topic_content;
if(!empty($topic_content))
$response = array('success' => 1, 'message'=>'Success topics found.','data'=>$topic_content);
else
$response = array('success' => 0, 'message'=>'No contacts found.','data'=>"");

echo json_encode($response); 