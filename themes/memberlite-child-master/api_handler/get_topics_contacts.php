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

echo $sql = 'select  topics.about as about, topics.channel_id as channel_id, topics.special as type, users.user_login as name , users.ID as id from alpn_topics as topics left join wp_users as users on topics.connected_id=users.ID where topics.owner_id = "'.$id.'" and topics.special = "contact" || topics.special = "topic"';
die;
$result = $wpdb->get_results($sql);

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
if(!empty($result)) {
	$i = 0;
	foreach($result as $val) {
		$about = 'No about to show here';
		if(isset( $val->about) && !empty( $val->about))
		{
			$about = striptags($val->about);
		}
		$array[$val->type][$i]['name'] = $val->name;
		$array[$val->type][$i]['channel_id'] = $val->channel_id;
		$array[$val->type][$i]['about'] = $val->about;
		$array[$val->type][$i]['id'] = $val->id;
		$i++;
	}
	$response = array('success' => 1, 'message'=>'Contacts found.','data'=>$array);
} else {
	$response = array('success' => 0, 'message'=>'No contacts found.','data'=>$array);
}
echo json_encode($response); 