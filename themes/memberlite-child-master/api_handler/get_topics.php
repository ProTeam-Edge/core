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
$userID = $data->userID;
$type =  $data->type;


$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
$sql = "SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.special, tt.type_key, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.topic_content AS connected_topic_content, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.id = ".$id."";
$results = $wpdb->get_results($sql);

 if (!isset($results[0])) {
	 $html = "
	 <div class='pte_topic_error_message'>
	 		The selected topic has been deleted. Please select another topic or link.
	 </div>";
	 echo $html;
	 exit;
 }

$topicData = $results[0];
$topicTypeId = $topicData->topic_type_id;
$topicSpecial = $topicData->special;
$topicTypeName = $topicData->topic_name;
$topicIcon = $topicData->icon;
$topicId = $topicData->id;
$topicOwnerId = $topicData->owner_id;
$topicImageHandle = $topicData->image_handle;
$topicLogoHandle = $topicData->logo_handle;
$topicProfileHandle = $topicData->profile_handle;
$topicName = $topicData->name;
$topicChannelId = $topicData->channel_id;
$topicDomId = $topicData->dom_id;
$topicMeta = json_decode($topicData->topic_type_meta, true);
$topicContent = json_decode($topicData->topic_content, true);
$topicHtml = stripcslashes($topicData->html_template);
$typeKey = $topicData->type_key;
$nameMap = pte_name_extract($topicMeta['field_map']);
$fieldMap = array_flip($nameMap);

$topicEmailRoute = $topicData->email_route_id;
$topicFaxRoute = $topicData->pstn_number;

$ownerFirstName = '';
$context = $topicTypeName;
$proteamViewSelector = "block";
$proteamContainer = 'block';
$proTeamTitle = "Team Members";
$profilePicTitle = "Icon";
$showMessageAccordion = "none";
$showLogoAccordion = "block";
$showAddressBookAccordion = "none";
$showImportanceAccordions = "none";
$showFaxAccordian = "none";
$showEmailAccordian = "none";
$showIconAccordian = "block";
$pteEditDeleteClass = 'pte_ipanel_button_enabled';
$subjectToken = '';

$fullMap = $topicMeta['field_map'];
$topicTabs = array();
$infoColor = $topicData->connected_id ? '#700000' : '#444';
$infoTitle = $topicData->connected_id ? 'Info Comes from Contact Topic' : 'Info Comes from Your Topic';

$linkId = 0;
$topicTabs[] = array(   //Info Page. All Topics Have Them
	'type' => 'page',
	'key' => $typeKey,
	'id' => $linkId,
	'name' => "Info",
	'subject_token' => $subjectToken,
	'owner_topic_id' => $topicId,
	'topic_title' => ''
);

$topicLinkKeys = array();

$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;


if (!$topicBelongsToUser) {
	$ownerTopicContent = json_decode($topicData->owner_topic_content, true);
	$ownerFirst = isset($ownerTopicContent['person_givenname']) ? $ownerTopicContent['person_givenname'] : "Not Specified";
	$ownerFirstName = "<div id='pte_interaction_owner_outer'><div id='pte_interaction_owner_inner_message'>Topic Owner</div><div id='pte_interaction_owner_inner_name'>{$ownerFirst}</div></div>";
	$showIconAccordian = "none";
	$pteEditDeleteClass = 'pte_ipanel_button_disabled';
	foreach ($topicTabs as $key => $value) {
		if ($value['type'] == 'linked') {
			unset($topicTabs[$key]);
		}
	}
} else {
	if ($topicSpecial == 'contact') {
		$networkContactTopics = get_network_contact_topics($topicId);
	}
	$connectedId = $topicData->connected_id;
	if ($connectedId) {
		$topicContent = json_decode($topicData->connected_topic_content, true);
	}
}

//map and replace
foreach($topicContent as $key => $value){	   //deals with date/time being arrays
	if (is_array($value)) {
		foreach ($value as $key2 => $value2) {
			$actualValue = $value2;
		}
	} else {
		$actualValue = str_replace("*r*n*", "\r\n", $value);
	}
	$isSystemType = substr($key, 0, 4) == 'pte_' ? true : false;
	if (!$isSystemType) {
		if(isset($nameMap[$key]))
		$tkey = $nameMap[$key];
		else 
		$tkey = '';
		if(!empty($tkey))
		$replaceStrings[$tkey] = $actualValue;
	}
}
foreach ($fullMap as $key => $value) {

	$fieldType = isset($value['type']) ? $value['type'] : "";
	$hidden = isset($value['hidden']) && ($value['hidden'] == "true") ? true : false;

	if (substr($fieldType, 0, 5) == "core_" && !$hidden) {
		$fieldTypeArray = explode("_", $fieldType);
		if (count($fieldTypeArray) == 2) {  //Handle Core Type Mapping
			$mainCoreTopic = true;
		} else {  //Handle User Topic Type
			$mainCoreTopic = false;
		}

		$topicLinkKeys[] = $fieldType;
		$linkId++;
		$topicTabs[] = array(
			'main_core_topic' => $mainCoreTopic,
			'type' => 'linked',
			'id' => $linkId,
			'name' => $value['friendly'] ? $value['friendly'] : "Not Specified",
			'key' => $fieldType,  //object type
			'subject_token' => $key,   //field_unique id
			'owner_topic_id' => $topicId
		);
	} else {  //Handle System Types
		$isSystemType = isset($value['schema_key']) && substr($value['schema_key'], 0, 4) == 'pte_' ? true : false;
		if ($isSystemType) {
			switch ($value['schema_key']) {
				case 'pte_added_Date':
					$replaceStrings[$value['friendly']] =$topicData->created_date;
				break;
				case 'pte_modified_Date':
					$replaceStrings[$value['friendly']] = $topicData->modified_date;
				break;
				case 'pte_image_URL':
					if ($topicLogoHandle) {
						$topicLogoUrl = "{$ppCdnBase}{$topicLogoHandle}";
					}
					$friendlyLogoName = $value['friendly'];
					echo $topicLogoHandle;
					die;
					$replaceStrings[$friendlyLogoName] = $topicLogoUrl;
					if ($hidden) {$showLogoAccordion = 'none';}
				break;
			}
		}
	}
}
//$replaceStrings["{topicDomId}"] = $topicDomId;

$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
if (isset($replaceStrings['Occupation']) && intVal($replaceStrings['Occupation'])) {  //TODO test this
	$replaceStrings['Occupation'] = $businessTypesList[$replaceStrings['Occupation']];
} else {
	//$replaceStrings['Occupation'] = "Not Specified";
}

if ($topicSpecial == 'contact' || $topicSpecial == 'user' ) {   //user or network

	if ($topicSpecial == 'contact') {
		$context = "Contact";
		$proteamViewSelector = "none";
		$proteamContainer = 'none';
		$proTeamTitle = "";
		$profilePicTitle = "Icon";
		$showMessageAccordion = "none";
		$showImportanceAccordions = "none";
		$showFaxAccordian = "none";
		$showEmailAccordian = "none";

	}
	if ($topicSpecial == 'user') {
		$context = "Personal";
		$proteamViewSelector = "none";
		$proteamContainer = 'none';
		$proTeamTitle = "";
		$profilePicTitle = "Icon";
		$showMessageAccordion = "block";
		$showAddressBookAccordion = "none";   //TODO Turn this back to block to turn it on.
		$showImportanceAccordions = "block";
		$showFaxAccordian = "block";
		$showEmailAccordian = "block";

		$networkOptions = pte_get_topic_list('network_contacts') ;
		$topicOptions = pte_get_topic_list('topics') ;

		$importantNetworkItems = pte_get_important_items('pte_important_network');
		$importantTopicItems = pte_get_important_items('pte_important_topic');

		$interactionTypeSliders = pte_get_interaction_settings_sliders(array());

		$faxUx = pte_get_fax_ux();
		$emailUx = pte_get_email_ux();
	}
}






$proTeamSelector = '';  //TODO extend selector to include all Persons (minus self) Test. Cool do this.
if ($topicBelongsToUser) {
	$network = array();
	$options = "";
	$network = $wpdb->get_results( //for select box
		$wpdb->prepare("SELECT t.id, t.name, t.connected_id, t.dom_id FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = %d AND tt.schema_key = 'Person' AND t.special != 'user' ORDER BY name ASC", $userID)
	 );
	foreach ($network as $key => $value){
		$options .= "<option data-dom-id='{$value->dom_id}' data-wp-id='{$value->connected_id}' value='{$value->id}'>{$value->name}</option>";
	}
	
}

$proteam = $wpdb->get_results(  //get proteam
	$wpdb->prepare("SELECT p.*, t.name, t.image_handle, t.profile_handle, t.dom_id, t.alt_id, t.connected_id FROM alpn_proteams p LEFT JOIN alpn_topics_network_profile t ON p.proteam_member_id = t.id WHERE p.topic_id = '%s' ORDER BY name ASC", $topicId)
 );

$proTeamMembers = "";
$topicHasTeamMembers = count($proteam) ? true : false;
$proTeamTitle = ($proteamContainer == 'block') ? "<div class='pte_proteam_title'>Topic Team</div>" : "";

foreach ($proteam as $key => $value) {
	if ($topicBelongsToUser) {
		$topicNetworkId = $value->id;
		$topicDomIdProTeam = $value->dom_id;
		$topicNetworkName = $value->name;
		$topicAccessLevel= $value->access_level;
		$connectedContactStatus = 'not_connected_not_member';
		if ($value->connected_id) {
			$connectedContactStatus = 'connected_member';
		} else if ($value->alt_id) {
			 $userData = get_user_by('email', $value->alt_id);
			 if (isset($userData->data->ID) && $userData->data->ID) {
				 $connectedContactStatus = 'not_connected_member';
			 }
		}
		$topicNetworkRights = json_decode($value->member_rights, true);
		$checked = array();
		foreach ($topicNetworkRights as $key2 => $value2) {
			$checked[$key2] = $value2;
		}
		$topicPanelData = array(
			'proTeamRowId' => $value->id,
			'topicNetworkId' => $value->proteam_member_id,
			'topicDomId' => $topicDomIdProTeam,
			'topicNetworkName' => $value->name,
			'topicAccessLevel' => $topicAccessLevel,
			'state' => $value->state,
			'checked' => $checked,
			'connected_contact_status' => $connectedContactStatus
		);
		$proTeamMembers .= pte_make_rights_panel_view($topicPanelData);
	} else {
		$proTeamMembers .= "
			<div>{$value->name}</div>
		";
	}
}

//TODO Prefill with correct token data
//TODO use this in interactions for templating tied to IAs
$messageTypeId = '1';
$templates = $wpdb->get_results(
	$wpdb->prepare("SELECT id, short_description FROM alpn_message_templates WHERE owner_id = '%s' AND message_type_id = '%s' ORDER BY short_description ASC", $userID, $messageTypeId)
 );

$tabButtons = $tabPanels = $initializeTable = $tabTable = $topicSelector = '';

if ($topicBelongsToUser) {
	//Team Links
	//Being user by. Linked to me.
	$linkId++;
	$topicTabs[] = array(
		'type' => 'linked',
		'id' => $linkId,
		'name' => "Linked by",
		'key' => '',
		'subject_token' => 'pte_inbound',
		'owner_topic_id' => $topicId,
		'topic_title' => 'Links to this Topic'
	);

	if ($topicHasTeamMembers) {
		$linkId++;
		$topicTabs[] = array(
			'type' => 'linked',
			'id' => $linkId,
			'name' => "Team",
			'key' => '',
			'subject_token' => 'pte_external',
			'owner_topic_id' => $topicId,
			'topic_title' => 'Links to Team Member Topics'
		);
	}
}

$usedTopicTypes = array(); //Needed to handle topic_class and source type key mapping correctly
$typeKeyMap = array();
if (count($topicLinkKeys)) {
	$topicLinkKeysString = implode(',', array_map('pte_add_quotes', $topicLinkKeys)); //so we can pull all used topic types.
	$usedTopicTypesData = $wpdb->get_results(
		$wpdb->prepare("SELECT id, topic_class, type_key, source_type_key FROM alpn_topic_types WHERE (type_key IN ({$topicLinkKeysString}) OR source_type_key IN ({$topicLinkKeysString})) AND owner_id = %d", $userID)
	 );

	 foreach ($usedTopicTypesData as $key => $value) {
		 $typeKey = $value->type_key;
		 $sourceTypeKey = $value->source_type_key;
		 $typeKeyMap[$sourceTypeKey] = $typeKey;
		 $usedTopicTypes[$typeKey] = $value->topic_class;
	 }
}




if($type=='multiple') {
foreach($topicTabs as $keys=>$vals) {
	if($vals['name']=='Info') {
		$topicTabs[$keys]['data']['type'] = 'single';
		$topicTabs[$keys]['data']['data'] = $replaceStrings;
	}
	else {
		$linked_sql = "select owner_topic_id, name, about, owner_id, dom_id, draw_id, owner_name, type_key, subject_token, connected_topic_id, connected_id, connected_topic_type_id, link_id, connected_topic_special, topic_class, list_default FROM alpn_topics_linked_view
		WHERE owner_topic_id = '".$vals['owner_topic_id']."' AND subject_token = '".$vals['subject_token']."' AND owner_id = ".$userID." AND name !='' ORDER BY name ASC";
		$linked_data = $wpdb->get_results($linked_sql,ARRAY_A);
		
		$topicTabs[$keys] = $vals;
		$topicTabs[$keys]['data']['type'] = 'multiple';
		$topicTabs[$keys]['data']['data'] = $linked_data;

	}
	$i++;
}
}
else {
	$topicTabs = $replaceStrings;
}
if(!empty($topicTabs))
{
	$response = array('success' => 1, 'message'=>'Success data found.','data'=>$topicTabs);
}
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 