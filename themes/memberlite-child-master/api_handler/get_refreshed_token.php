<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$input = file_get_contents('php://input');
$data = json_decode($input);
$source_key = $data->source_key;
$channelId = $data->channelId;
$id = $data->id;
$token = '';
if(!empty($source_key) && !empty($channelId) && !empty($id))
{
	if($source_key=='core_contact') {
		$sql = 'select u.device_token from alpn_topics as a JOIN alpn_topics as b on a.connected_topic_id = b.id JOIN wp_users as u on b.owner_id = u.ID where a.id='.$id.'';

		$result = $wpdb->get_row($sql);
		$token = $result->device_token;
	}
	else {
		$sql = 'select u.ID as id,u.device_id as device_id from alpn_topics as a join alpn_proteams as ap on a.id=ap.topic_id join wp_users as u on ap.proteam_member_id=u.iD where a.channel_id="'.$channelId.'"';
		$result = $wpdb->get_row($sql);
		echo '<pre>';
		print_r($result);
		die;
	}
}
$response = array('success' => 1, 'message'=>'success','data'=>$token);
echo json_encode($response); 