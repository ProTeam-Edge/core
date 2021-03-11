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
$sql = "SELECT * from alpn_topics where id = ".$id."";
$results = $wpdb->get_row($sql);
$topic_content = '';
$businessTypesList = get_custom_post_items('pte_profession', 'ASC');

$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		$job_title = $job_email = $job_url = $telephone = $faxnumber = $knowsabout = $carrier =  $description = 'None' ;
		if(isset($topic_content_response->person_hasoccupation_occupation_occupationalcategory) && !empty($topic_content_response->person_hasoccupation_occupation_occupationalcategory)){
			$carrier_id = $topic_content_response->person_hasoccupation_occupation_occupationalcategory;
			$carrier = $businessTypesList[$carrier_id];
		}
		
		
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
		$array[0]['label'] ='Firstname';
		$array[0]['value'] =$topic_content_response->person_givenname;
		$array[1]['label'] ='Lastname';
		$array[1]['value'] =$topic_content_response->person_familyname;
		
		$array[2]['label'] ='Title';
		$array[2]['value'] =$job_title;
		
		$array[3]['label'] ='Occupation';
		$array[3]['value'] =$carrier;
	
		$array[4]['label'] ='Email';
		$array[4]['value'] =$job_email;
		
		$array[5]['label'] ='Linked-In';
		$array[5]['value'] =$job_url;
		
		$array[6]['label'] ='Telephone';
		$array[6]['value'] =$telephone;
		
		$array[7]['label'] ='Fax Number';
		$array[7]['value'] =$faxnumber;
		
		$array[8]['label'] ='#interests';
		$array[8]['value'] =$knowsabout;
	
		$array[9]['label'] ='About';
		$array[9]['value'] =$description;
		

	}
}
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success topics found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No contacts found.','data'=>"");

echo json_encode($response); 