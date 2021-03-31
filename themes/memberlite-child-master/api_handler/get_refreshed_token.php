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
$identity = $data->identity;
$token = array();
if(!empty($source_key) && !empty($channelId) && !empty($id))
{
	if($source_key=='core_contact') {
		$sql = 'select u.device_token from alpn_topics as a JOIN alpn_topics as b on a.connected_topic_id = b.id JOIN wp_users as u on b.owner_id = u.ID where a.id='.$id.'';

		$result = $wpdb->get_row($sql);
		$token[0] = $result->device_token;
	}
	else {
		echo $sql = 'select u.device_token , u.ID from alpn_proteams as ap join alpn_topics as ato on ap.proteam_member_id=ato.id join wp_users as u on ato.connected_id=u.ID where ap.topic_id="'.$id.'"';
		$result = $wpdb->get_results($sql);
		foreach($result as $vals){
			if($vals->ID!=$identity) {
				$token[] = $vals->device_token
			}
		}
	}
}
$response = array('success' => 1, 'message'=>'success','data'=>$token);
echo json_encode($response); 