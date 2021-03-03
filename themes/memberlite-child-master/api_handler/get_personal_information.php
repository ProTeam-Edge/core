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
$results = $wpdb->get_results($sql);
 echo '<pre>';
 print_r($results);
 die;
