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

$get_user_sql = "SELECT * from alpn_topics where owner_id = ".$id." and special = 'user' and sync_id !=''";
$get_user_data = $wpdb->get_row($get_user_sql);

$get_contacts_sql = 'select  topics.logo_handle as image ,topics.about as about, topics.channel_id as channel_id, topics.special as type, users.user_login as name , users.ID as id from alpn_topics as topics inner join wp_users as users on topics.connected_id=users.ID where topics.owner_id = "'.$id.'" and topics.special = "contact" ';
$get_contacts_data = $wpdb->get_results($get_contacts_sql);

$get_topic_sql = 'select logo_handle as image ,name, about, channel_id, id from alpn_topics where owner_id = "'.$id.'" and special = "topic" and name!="" ';
$get_topic_data = $wpdb->get_results($get_topic_sql);

$final_sql = 'SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = "user" WHERE t.owner_id = '.$id.' UNION
SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, "" AS connected_id, "" AS connected_image_handle, "" AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id <> "" AND p.wp_id = '.$id.' ';
$final_data = $wpdb->get_results($final_sql);
echo '<pre>';
print_r($final_data);
die;
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
$base_image = 'https://storage.googleapis.com/pte_media_store_1/2020/03/f7491f5d-cropped-36a6c22c-globe650x650-e1585629698318.png';
if(!empty($get_contacts_data) || !empty($get_topic_data)) {
	$i = 0;
	if(!empty($get_contacts_data)) {
		foreach($get_contacts_data as $val) {
			$about = 'No about to show here';
			
			if(isset( $val->about) && !empty( $val->about)){
				$about = striptags($val->about);
			}
			if(isset($val->image) && !empty($val->image)) {
				$contact_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val->image;
			}
			else {
				$contact_image = $base_image;
			}
			$array['contact'][$i]['name'] = $val->name;
			$array['contact'][$i]['image'] = $contact_image;
			$array['contact'][$i]['channel_id'] = $val->channel_id;
			$array['contact'][$i]['about'] = $about;
			$array['contact'][$i]['id'] = $val->id;
			$i++;
		}
	}
	$count = count($array);
	
	if(!empty($get_topic_data)) {
		foreach($get_topic_data as $val1) {
			$m = 0;
			$about1 = 'No about to show here';
			if(isset( $val1->about) && !empty( $val1->about))
			{
				$about1 = striptags($val1->about);
			}
			if(isset($val1->image) && !empty($val1->image)) {
				$topic_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val1->image;
			}
			else {
				$topic_image = $base_image;
			}
			$array['topic'][$m]['name'] = $val1->name;
			$array['topic'][$m]['image'] = $topic_image;
			$array['topic'][$m]['channel_id'] = $val1->channel_id;
			$array['topic'][$m]['about'] = $about1;
			$array['topic'][$m]['id'] = $val1->id;
			$m++;
		}
	}

	 if(isset($get_user_data->logo_handle)) {
		$user_image = 'https://storage.googleapis.com/pte_media_store_1/'.$get_user_data->logo_handle;
	}
	else {
		$user_image = $base_image;
	}
	$array['user_image'] = $user_image; 
	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response); 