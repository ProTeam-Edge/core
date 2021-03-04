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

$get_contacts_sql = 'select  topics.image_handle as image ,topics.about as about, topics.channel_id as channel_id, topics.special as type, users.user_login as name , users.ID as id from alpn_topics as topics inner join wp_users as users on topics.connected_id=users.ID where topics.owner_id = "'.$id.'" and topics.special = "contact" ';
$get_contacts_data = $wpdb->get_results($get_contacts_sql);

$get_topic_sql = 'select image_handle as image ,name, about, channel_id, id from alpn_topics where owner_id = "'.$id.'" and special = "topic" and name!="" ';
$get_topic_data = $wpdb->get_results($get_topic_sql);

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
$final_image = 'https://storage.googleapis.com/pte_media_store_1/2020/03/f7491f5d-cropped-36a6c22c-globe650x650-e1585629698318.png';
if(!empty($result) || !empty($result1)) {
	$i = 0;
	if(!empty($result)) {
		foreach($result as $val) {
			$about = 'No about to show here';
			
			if(isset( $val->about) && !empty( $val->about)){
				$about = striptags($val->about);
			}
			if(isset($val->image)) {
				$final_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val->image;
			}
			$array['contact'][$i]['name'] = $val->name;
			$array['contact'][$i]['image'] = $final_image;
			$array['contact'][$i]['channel_id'] = $val->channel_id;
			$array['contact'][$i]['about'] = $about;
			$array['contact'][$i]['id'] = $val->id;
			$i++;
		}
	}
	$count = count($array);
	
	if(!empty($result1)) {
		foreach($result1 as $val1) {
			$m = 0;
			$about1 = 'No about to show here';
			if(isset( $val1->about) && !empty( $val1->about))
			{
				$about1 = striptags($val1->about);
			}
			if(isset($val1->image)) {
				$final_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val1->image;
			}
			$array['topic'][$m]['name'] = $val1->name;
			$array['topic'][$m]['image'] = $final_image;
			$array['topic'][$m]['channel_id'] = $val1->channel_id;
			$array['topic'][$m]['about'] = $about1;
			$array['topic'][$m]['id'] = $val1->id;
			$m++;
		}
	}
	echo '<pre>';
	print_r($get_user_data);
	/* if(isset($get_user_data->image_handle)) {
		$final_image = 'https://storage.googleapis.com/pte_media_store_1/'.$get_user_data->image_handle;
	}
	$array['user_image'] = $final_image; */
	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response); 