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

/* $final_sql = 'SELECT l.subject_token, t.id,t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t JOIN alpn_topics_linked_view as l on t.id=l.connected_topic_id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = "user" WHERE t.owner_id =  '.$id.' and t.name!="" and (l.subject_token!="pte_place" and l.subject_token!="pte_organization"  and l.subject_token!="pte_notedigitaldocument" and l.subject_token!="pte_external")  UNION
SELECT "" AS subject_token, t.id,t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, "" AS connected_id, "" AS connected_image_handle, "" AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id <> "" AND p.wp_id = '.$id.''; */

$final_sql = "SELECT tt.source_type_key, tt.topic_class, t.id,t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = ".$id." and t.name!='' and tt.topic_class != 'link' 
UNION
SELECT tt.source_type_key, tt.topic_class, t.id, t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, '' AS connected_id, '' AS connected_image_handle, '' AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.channel_id <> '' AND p.wp_id = ".$id."";

$final_data = $wpdb->get_results($final_sql);

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
if(!empty($final_data)) {
	$c = $t = $u = 0;
		foreach($final_data as $val) {
			
				if($val->special=='topic') {
					$increment_variable = $t;
				}
				else if($val->special=='contact') {
					$increment_variable = $c;
				} 
				else {
					$increment_variable = $u;
				}
				$about = 'No about to show here';
			
				if(isset( $val->about) && !empty( $val->about)){
					$about = striptags($val->about);
				}
				
				if(isset($val->connected_id) && !empty($val->connected_id)) {
					$returned_name = $val->connected_name;
					if(isset($val->connected_image_handle) && !empty($val->connected_image_handle)) {
						$returned_contact_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val->connected_image_handle;
					} else {
						$returned_contact_image = $base_image;
					}
				}
				else {
					$returned_name = $val->name;
					if(isset($val->image_handle ) && !empty($val->image_handle )) {
						$returned_contact_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val->image_handle ;
					} else {
						$returned_contact_image = $base_image;
					}
				}
				$sql = 'select u.device_token from alpn_topics as a JOIN alpn_topics as b on a.connected_topic_id = b.id JOIN wp_users as u on b.owner_id = u.ID where a.id='.$val->id.'';
				$data = $wpdb->get_row($sql);
				if($data){
					$dId = $data->device_token;
				}
				else {
					$dId = '';
				}
				$array[$val->special][$increment_variable]['name'] = $returned_name;
				$array[$val->special][$increment_variable]['image'] = $returned_contact_image;
				$array[$val->special][$increment_variable]['channel_id'] = $val->channel_id;
				$array[$val->special][$increment_variable]['about'] = $about;
				$array[$val->special][$increment_variable]['device_id'] = $dId;
				$array[$val->special][$increment_variable]['id'] = $val->id;
				$array[$val->special][$increment_variable]['source_type_key'] = $val->source_type_key;
				if($val->special=='topic'){
					$t++;
				}
				else if($val->special=='contact'){
					$c++;
				}
				else {
					$u++;
				}
		}

	 if(isset($final_data[0]->image_handle)) {
		$user_image = 'https://storage.googleapis.com/pte_media_store_1/'.$final_data[0]->image_handle;
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