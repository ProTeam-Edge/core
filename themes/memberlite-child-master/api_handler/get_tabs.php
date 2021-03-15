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


$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
$sql = "SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.special, tt.type_key, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.topic_content AS connected_topic_content, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.id = ".$id."";
$topicData = $wpdb->get_row($sql);
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
	'name' => "<span style='color: {$infoColor};' title='{$infoTitle}'>Info</span>",
	'html' => $topicHtml,
	'subject_token' => $subjectToken,
	'owner_topic_id' => $topicId,
	'topic_title' => ''
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
echo '<pre>';
print_r($topicTabs);
die;
if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 