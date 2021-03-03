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

$sql = 'select  topics.about as about, topics.channel_id as channel_id, topics.special as type, users.user_login as name , users.ID as id from alpn_topics as topics inner join wp_users as users on topics.connected_id=users.ID where topics.owner_id = "'.$id.'" and topics.special = "contact" ';
$result = $wpdb->get_results($sql);

$sql1 = 'select about as about, channel_id as channel_id, special as type from alpn_topics where owner_id = "'.$id.'" and special = "topic" ';
$result1 = $wpdb->get_results($sql1);

$array = $response= array();
function striptags($string) {
	$string = strip_tags($string);
if (strlen($string) > 50) {

    // truncate string
    $stringCut = substr($string, 0, 50);
    $endPoint = strrpos($stringCut, ' ');

    //if the string doesn't contain any space then it will cut without word basis.
    $string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
    $string .= '...';
}
return $string;
}
if(!empty($result) || !empty($result1)) {
	$i = 0;
	if(!empty($result)) {
		foreach($result as $val) {
			$about = 'No about to show here';
			if(isset( $val->about) && !empty( $val->about))
			{
				$about = striptags($val->about);
			}
			$array['contact'][$i]['name'] = $val->name;
			$array['contact'][$i]['channel_id'] = $val->channel_id;
			$array['contact'][$i]['about'] = $val->about;
			$array['contact'][$i]['id'] = $val->id;
			$i++;
		}
	}
	$count = count($array);

	if(!empty($result1)) {
		foreach($result1 as $val1) {
			$m = 0;
			$about = 'No about to show here';
			if(isset( $val1->about) && !empty( $val1->about))
			{
				$about = striptags($val1->about);
			}
			$array['topic'][$count]['name'] = $val1->name;
			$array['topic'][$count]['channel_id'] = $val1->channel_id;
			$array['topic'][$count]['about'] = $val1->about;
			$array['topic'][$count]['id'] = $val1->id;
			$m++;
		}
	}

	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response); 