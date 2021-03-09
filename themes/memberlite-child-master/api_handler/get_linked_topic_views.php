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
$subject_token = $data->id;
$offset = $data->offset;
$sql = "SELECT * from alpn_topics_linked_view where owner_id = ".$id." and subject_token = '".$subject_token."' LIMIT 10 OFFSET ".$offset."";
$results = $wpdb->get_results($sql);

$sql1 = "SELECT COUNT(*) as total from alpn_topics_linked_view where owner_id = ".$id." and subject_token = '".$subject_token."'";
$results1 = $wpdb->get_results($sql1);
if(!empty($results)) {
	$i = 0;
	foreach($results as $vals) {
		$array['items'][$i]['connected_topic_id'] =$vals->connected_topic_id;
		$array['items'][$i]['name'] =$vals->name;
		$i++;
	}
}
$array['count'] = $results1->total;
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 