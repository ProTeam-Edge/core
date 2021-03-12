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

$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
$sql = "SELECT a.topic_content,b.connected_topic_type_meta from alpn_topics as a inner join alpn_topics_linked_view as b on a.id=b.owner_topic_id where a.id = ".$id." ";
$results = $wpdb->get_row($sql);
$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		if($type=='personal_info') {
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
			$notedigitaldocument_headline = $notedigitaldocument_text =  'None' ;
			if(isset($topic_content_response->notedigitaldocument_headline) && !empty($topic_content_response->notedigitaldocument_headline)) {
				$notedigitaldocument_headline = $topic_content_response->notedigitaldocument_headline;
			}
			if(isset($topic_content_response->notedigitaldocument_text) && !empty($topic_content_response->notedigitaldocument_text)) {
				$notedigitaldocument_text = $topic_content_response->notedigitaldocument_text;
			}
			$array['notedigitaldocument_text'] =$notedigitaldocument_text;
			$array['notedigitaldocument_headline'] =$notedigitaldocument_headline;
		}
		else if($type=='core_general') {
			$name = $about =  'None' ;
			if(isset($topic_content_response->thing_name) && !empty($topic_content_response->thing_name)) {
				$name = $topic_content_response->thing_name;
			}
			if(isset($topic_content_response->thing_description) && !empty($topic_content_response->thing_description)) {
				$about = $topic_content_response->thing_description;
			}
			$array[0]['label'] ='Name';
			$array[0]['value'] =$name;
			$array[1]['label'] ='About';
			$array[1]['value'] =$about;
		}
		else {
			$i = 0;
			$decode = json_decode($topic_content_response->alpn_topics_linked_view);
			echo '<pre>';
			print_r($decode);
			foreach($topic_content_response as $keys=>$vals) {
				
				if($keys!="pte_meta")
				{
					
				$array[$i]['label']=$keys;
				if(empty($vals)) {
					$fval = 'None';
				}
				else {
					if($keys=='person_hasoccupation_occupation_occupationalcategory') {
						$fval = $businessTypesList[$vals];
					}
					else
					$fval = $vals;
				}
				$array[$i]['value']=$fval;
				$i++;
				}
			}
		}
	}
}


if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 