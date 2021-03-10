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
$type = $data->type;
$subject_token = $data->subject_token;

$sql = "SELECT * from alpn_topics where id = ".$id."";
$results = $wpdb->get_row($sql);
$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		if($type=='pte_place') {
			$place_name = $place_streetaddress = $place_addresslocality = $place_addressregion = $place_postalcode = $place_telephone = $place_faxnumber =  $place_url = $place_description = 'None' ;
			
			if(isset($topic_content_response->place_name) && !empty($topic_content_response->place_name))
				$place_name = $topic_content_response->place_name;
				
			if(isset($topic_content_response->place_address_postaladdress_streetaddress) && !empty($topic_content_response->place_address_postaladdress_streetaddress)) {
				$place_streetaddress = $topic_content_response->place_address_postaladdress_streetaddress;
			}
			if(isset($topic_content_response->place_address_postaladdress_addresslocality) && !empty($topic_content_response->place_address_postaladdress_addresslocality)) {
				$place_addresslocality = $topic_content_response->place_address_postaladdress_addresslocality;
			}
			if(isset($topic_content_response->place_address_postaladdress_addressregion) && !empty($topic_content_response->place_address_postaladdress_addressregion)) {
				$place_addressregion = $topic_content_response->place_address_postaladdress_addressregion;
			}
			if(isset($topic_content_response->place_address_postaladdress_postalcode) && !empty($topic_content_response->place_address_postaladdress_postalcode)) {
				$place_postalcode = $topic_content_response->place_address_postaladdress_postalcode;
			}
			if(isset($topic_content_response->place_telephone) && !empty($topic_content_response->place_telephone)) 
				$place_telephone = $topic_content_response->place_telephone;
			if(isset($topic_content_response->place_faxnumber) && !empty($topic_content_response->place_faxnumber)) 
				$place_faxnumber = $topic_content_response->place_faxnumber;
			if(isset($topic_content_response->place_url) && !empty($topic_content_response->place_url)) 
				$place_url = $topic_content_response->place_url;
			if(isset($topic_content_response->place_description) && !empty($topic_content_response->place_description)) 
			$place_description = $topic_content_response->place_description;
			$array['place_name'] =$place_name;
			$array['place_streetaddress'] =$place_streetaddress;
			$array['place_addresslocality'] =$place_addresslocality;
			$array['place_addressregion'] =$place_addressregion;
			$array['place_postalcode'] =$place_postalcode;
			$array['place_telephone'] =$place_telephone;
			$array['place_faxnumber'] =$place_faxnumber;
			$array['place_url'] =$place_url;
			$array['place_description'] =$place_description; 
		}
		else if($type=='pte_organization') { 
			$organization_name = $organization_telephone = $organization_faxnumber = $organization_email = $organization_url = $organization_description =  'None' ;
			if(isset($topic_content_response->organization_name) && !empty($topic_content_response->organization_name)) {
				$organization_name = $topic_content_response->organization_name;
			}
			if(isset($topic_content_response->organization_telephone) && !empty($topic_content_response->organization_telephone)) {
				$organization_telephone = $topic_content_response->organization_telephone;
			}
			if(isset($topic_content_response->organization_faxnumber) && !empty($topic_content_response->organization_faxnumber)) {
				$organization_faxnumber = $topic_content_response->organization_faxnumber;
			}
			if(isset($topic_content_response->organization_email) && !empty($topic_content_response->organization_email)) {
				$organization_email = $topic_content_response->organization_email;
			}
			if(isset($topic_content_response->organization_url) && !empty($topic_content_response->organization_url)) {
				$organization_url = $topic_content_response->organization_url;
			}
			if(isset($topic_content_response->organization_description) && !empty($topic_content_response->organization_description)) {
				$organization_description = $topic_content_response->organization_description;
			}
			$array['organization_name'] =$organization_name;
			$array['organization_telephone'] =$organization_telephone;
			$array['organization_faxnumber'] =$organization_faxnumber;
			$array['organization_email'] =$organization_email;
			$array['organization_url'] =$organization_url;
			$array['organization_description'] =$organization_description;
		}
		else if($type=='pte_notedigitaldocument') {
			echo '<pre>';
			print_r($topic_content_response);
		}
	}
}


if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 