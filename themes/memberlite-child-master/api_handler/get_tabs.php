<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$array = array();
$input = file_get_contents('php://input');
$data = json_decode($input);
$id = $data->id;


$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
$sql = "SELECT topic_content from alpn_topics where id = ".$id."";
$results = $wpdb->get_row($sql);
$array = array();
$topicMeta = json_decode($topicData->topic_type_meta, true);
echo '<pre>';
print_r($topicMeta);
die;
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 