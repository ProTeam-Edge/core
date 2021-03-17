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
$topicId = $topicData->id;
$topicOwnerId = $topicData->owner_id;
$topicMeta = json_decode($topicData->topic_type_meta, true);
$typeKey = $topicData->type_key;
$nameMap = pte_name_extract($topicMeta['field_map']);
$fieldMap = array_flip($nameMap);
$ownerFirstName = '';
$context ="";
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
$linkId = 0;
$topicTabs[] = array(   //Info Page. All Topics Have Them
	'type' => 'page',
	'key' => $typeKey,
	'id' => $linkId,
	'name' => "Info",
	'subject_token' => $subjectToken,
	'owner_topic_id' => $topicId,
	
);
$topicLinkKeys = array();
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
			'type' => 'linked',
			'id' => $linkId,
			'name' => $value['friendly'] ? $value['friendly'] : "Not Specified",
			'key' => $fieldType,  //object type
			'subject_token' => $key,   //field_unique id
			'owner_topic_id' => $topicId
		);
	}
	else {  //Handle System Types
		$isSystemType = isset($value['schema_key']) && substr($value['schema_key'], 0, 4) == 'pte_' ? true : false;
		if ($isSystemType) {
			switch ($value['schema_key']) {
				case 'pte_added_Date':
					$replaceStrings['-{' . 'pte_added_date' . '}-'] = pte_date_to_js($topicData->created_date);
					$replaceStrings['-{' . 'pte_added_date_title' . '}-'] = $value['friendly'];
				break;
				case 'pte_modified_Date':
					$replaceStrings['-{' . 'pte_modified_date' . '}-'] = pte_date_to_js($topicData->modified_date);
					$replaceStrings['-{' . 'pte_modified_date_title' . '}-'] = $value['friendly'];
				break;
				case 'pte_image_URL':
					if ($topicLogoHandle) {
						$topicLogoUrl = "<div onclick='jQuery(\"#pte_topic_logo_accordion\").click();' style='display: inline-block; width 40%; cursor: pointer;'><img class='pte_logo_image_screen' style='' src='{$ppCdnBase}{$topicLogoHandle}'></div>";
					}
					$friendlyLogoName = $value['friendly'];
					$replaceStrings['-{' . 'pte_image_logo' . '}-'] = $topicLogoUrl;
					$replaceStrings['-{' . 'pte_image_logo_title' . '}-'] = $friendlyLogoName;
					if ($hidden) {$showLogoAccordion = 'none';}
				break;
			}
		}
	}
}
$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;
if (!$topicBelongsToUser) {
	
	foreach ($topicTabs as $key => $value) {
		if ($value['type'] == 'linked') {
			unset($topicTabs[$key]);
		}
	}
}
$proteam = $wpdb->get_results(  //get proteam
	$wpdb->prepare("SELECT p.*, t.name, t.image_handle, t.profile_handle, t.dom_id, t.alt_id, t.connected_id FROM alpn_proteams p LEFT JOIN alpn_topics_network_profile t ON p.proteam_member_id = t.id WHERE p.topic_id = '%s' ORDER BY name ASC", $topicId)
 );
 $topicHasTeamMembers = count($proteam) ? true : false;
//TODO Prefill with correct token data
//TODO use this in interactions for templating tied to IAs
$messageTypeId = '1';
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
$tableCounter = 1; //wpforms numbers tables odd numbers
foreach ($topicTabs as $key => $value) {

	$topicClass = isset($usedTopicTypes[$typeKey]) && $usedTopicTypes[$typeKey] ? $usedTopicTypes[$typeKey] : "special";
	$newTypeKey = isset($typeKeyMap[$value['key']]) && $typeKeyMap[$value['key']] ? $typeKeyMap[$value['key']] : $value['key'];
	$newTopicClass = isset($usedTopicTypes[$newTypeKey]) && $usedTopicTypes[$newTypeKey] ? $usedTopicTypes[$newTypeKey] : "special";

	$tabType = $value['type'];
	$tabId = $value['id'];
	$friendlyName = $value['name'];
	$subjectToken = $value['subject_token'];
	$ownerTopicId = $value['owner_topic_id'];

	$topicTitle = isset($value['topic_title'])  ? $value['topic_title'] : $newTopicClass;

	$subjectTokenParts = explode("_", $newTypeKey);
	if (count($subjectTokenParts) &&  isset($subjectTokenParts[1])){
		$subjectString = $subjectTokenParts[1];
	}

	$subjectStringFormatted = ucfirst($subjectString);
	switch ($tabType) {
		case 'page':
			$tabButtons .= "<button id='tab_{$key}' data-tab-id='{$key}' data-tab-type='{$tabType}' data-stoken='{$subjectToken}' class='tablinks' onclick='pte_handle_tab_selected(this)'>{$value['name']}</button>";
			$tabHtml = str_replace(array_keys($replaceStrings), $replaceStrings, $value['html']);
		break;
		case 'linked':
			$topicTitle = "<div class='pte_topic_title'>{$topicTitle}</div>";
   		$topicSelectId = "pte_single_topic_type_list_{$tabId}";
			$topicClass = isset($usedTopicTypes[$newTypeKey]) && $usedTopicTypes[$newTypeKey] ? $usedTopicTypes[$newTypeKey] : "special";
			$topicList = '';
			switch ($topicClass) {
				case 'topic':
					$topicList = $newTypeKey ? pte_get_topic_list('single_schema_type', $subjectString, $topicSelectId) : "";
				break;
				case 'link':
					$topicList = $newTypeKey ? pte_get_topic_list('type_key', $subjectString, $topicSelectId, $newTypeKey) : "";
					
				break;
			}
	}	
}


if(!empty($topicTabs))
{
	$response = array('success' => 1, 'message'=>'Success data found.','data'=>$topicTabs);
}
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 