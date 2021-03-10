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

/* $final_sql = 'SELECT t.id,t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = "user" WHERE t.owner_id = '.$id.' and t.name!=""  UNION
SELECT t.id,t.about, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, "" AS connected_id, "" AS connected_image_handle, "" AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id <> "" AND p.wp_id = '.$id.' '; */

$final_sql = "SELECT tt.topic_class, t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id inner join alpn_topics_linked_view as linked on linked.owner_topic_id = t.id WHERE t.owner_id = ".$id." and t.name!='' and (linked.subject_token!='pte_organization' && linked.subject_token!='pte_places')
UNION
SELECT tt.topic_class, t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, '' AS connected_id, '' AS connected_image_handle, '' AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id inner join alpn_topics_linked_view as linked on linked.owner_topic_id = t.id WHERE t.channel_id <> '' AND p.wp_id = ".$id." (linked.subject_token!='pte_organization' && linked.subject_token!='pte_places')";
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
			if(isset($val->image_handle) && !empty($val->image_handle)) {
				$contact_image = 'https://storage.googleapis.com/pte_media_store_1/'.$val->image_handle;
			}
			else {
				$contact_image = $base_image;
			}
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
				$array[$val->special][$increment_variable]['name'] = $val->name;
				$array[$val->special][$increment_variable]['image'] = $contact_image;
				$array[$val->special][$increment_variable]['channel_id'] = $val->channel_id;
				$array[$val->special][$increment_variable]['about'] = $about;
				$array[$val->special][$increment_variable]['id'] = $val->id;
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