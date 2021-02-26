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
$sql = 'select * from alpn_topics where special = "contact" and connected_topic_id = "'.$id.'"';
$data = $wpdb->get_results($sql);
echo '<pre>';
print_r($data);