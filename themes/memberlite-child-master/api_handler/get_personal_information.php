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
$sql = "SELECT * from alpn_topics where owner_id = ".$id." and special = 'user' and sync_id !=''";
$results = $wpdb->get_row($sql);
$topic_content = '';
$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		$array['person_givenname'] =$topic_content_response->person_givenname;
		$array['person_familyname'] =$topic_content_response->person_familyname;
		$array['person_jobtitle'] =$topic_content_response->person_jobtitle;
		$array['person_email'] =$topic_content_response->person_email;
		$array['person_url'] =$topic_content_response->person_url;
		$array['person_telephone'] =$topic_content_response->person_telephone;
		$array['person_faxnumber'] =$topic_content_response->person_faxnumber;
		$array['person_knowsabout'] =$topic_content_response->person_knowsabout;
		$array['person_description'] =$topic_content_response->person_description;
	}
}
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success topics found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No contacts found.','data'=>"");

echo json_encode($response); 