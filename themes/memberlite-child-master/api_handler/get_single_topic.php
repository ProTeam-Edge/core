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
$subject_token = $data->subject_token;
$offset = $data->offset;
$sql = "SELECT * from alpn_topics where id = ".$id."";
$results = $wpdb->get_row($sql);
$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		echo '<pre>';
		print_r($topic_content_response);
		die;
		/* $job_title = $job_email = $job_url = $telephone = $faxnumber = $knowsabout = $carrier =  $description = 'None' ;
		if(isset($topic_content_response->person_hasoccupation_occupation_occupationalcategory) && !empty($topic_content_response->person_hasoccupation_occupation_occupationalcategory)){
			$carrier_id = $topic_content_response->person_hasoccupation_occupation_occupationalcategory;
			$carrier = $businessTypesList[$carrier_id];
		}
		
		$array['person_givenname'] =$topic_content_response->person_givenname;
		$array['occupation'] =$carrier;
		$array['person_familyname'] =$topic_content_response->person_familyname;
		if(isset($topic_content_response->person_jobtitle) && !empty($topic_content_response->person_jobtitle))
		$job_title = $topic_content_response->person_jobtitle;
		if(isset($topic_content_response->person_email) && !empty($topic_content_response->person_email))
		$job_email = $topic_content_response->person_email;
		if(isset($topic_content_response->person_url) && !empty($topic_content_response->person_url))
		$job_url = $topic_content_response->person_url;
		if(isset($topic_content_response->person_telephone) && !empty($topic_content_response->person_telephone))
		{
		$telephone = $topic_content_response->person_telephone;
		}
		if(isset($topic_content_response->person_faxnumber) && !empty($topic_content_response->person_faxnumber))
		{
		$faxnumber = $topic_content_response->person_faxnumber;
		}
		if(isset($topic_content_response->person_knowsabout) && !empty($topic_content_response->person_knowsabout))
		{
		$knowsabout = $topic_content_response->person_knowsabout;
		}
		if(isset($topic_content_response->person_description) && !empty($topic_content_response->person_description))
		{
		$description = $topic_content_response->person_description;
		}
		$array['person_jobtitle'] =$job_title;
		$array['person_email'] =$job_email;
		$array['person_url'] =$job_url;
		$array['person_telephone'] =$telephone;
		$array['person_faxnumber'] =$faxnumber;
		$array['person_knowsabout'] =$knowsabout;
		$array['person_description'] =$description; */
	}
}


if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 