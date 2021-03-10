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
$limit = 1;
if( $data->current_page==0) {
$offset = 0;
$incremented_current_page = 1;
}
else {
	$incremented_current_page = $data->current_page+1;
	$offset = $increment_current_page*$limit;
	
}

$subject_token = $data->subject_token;
$sql = "SELECT * from alpn_topics_linked_view where owner_id = ".$id." and subject_token = '".$subject_token."' LIMIT ".$limit." OFFSET ".$offset."";
$results = $wpdb->get_results($sql);
$count = count($results);

$sql1 = "SELECT COUNT(*) as total from alpn_topics_linked_view where owner_id = ".$id." and subject_token = '".$subject_token."'";
$results1 = $wpdb->get_row($sql1);
if(!empty($results)) {
	$i = 0; 
	foreach($results as $vals) {
		$array['items'][$i]['connected_topic_id'] =$vals->connected_topic_id;
		$array['items'][$i]['name'] =$vals->name;
		$i++;
	}
}

$array['rows_count'] = $count;
$array['total_count'] = $results1->total;
$array['current_page'] = $incremented_current_page;
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 