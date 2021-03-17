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
					$replaceStrings[$friendlyLogoName] = $topicLogoUrl;
					if ($hidden) {$showLogoAccordion = 'none';}
				break;
			}
		}
	}
}
$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;

if ($topicProfileHandle) {
	$topicImage = "<img id='pte_profile_pic_topic' src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; margin-left: 10px; border-radius: 50%;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img id='pte_profile_pic_topic' src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; margin-left: 10px; border-radius: 50%;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 10px; color: rgb(68, 68, 68); font-size: 24px;'></i>";
}

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
		$replaceStrings[$tkey] = $actualValue;
		
	}
}

$replaceStrings["{topicDomId}"] = $topicDomId;

$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
if (isset($replaceStrings['Occupation']) && intVal($replaceStrings['Occupation'])) {  //TODO test this
	$replaceStrings['Occupation'] = $businessTypesList[$replaceStrings['Occupation']];
} else {
	$replaceStrings['Occupation'] = "Not Specified";
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

if ($topicEmailRoute || $topicFaxRoute) {
	$topicFaxRouteFormatted = pte_format_pstn_number($topicFaxRoute);
	$dottedName = str_replace(array(', ', ',', "'", '"'), array('.', '.', "", ""), $topicName);
	$emailAddress = "{$dottedName} - ProTeam Edge Topic <{$topicEmailRoute}@files.{$domainName}>";
	$emailRouteHtml = $topicEmailRoute ? "<div title='Copy Email Route' class='pte_route_container_item pte_topic_link' onclick='pte_topic_link_copy_string(\"Email\", \"{$emailAddress}\");'><i class='far fa-copy'></i>&nbsp;&nbsp;Email</div>" : "";
	$faxHtml = $topicFaxRoute ? "<div title='Copy Fax Number Route' class='pte_route_container_item pte_topic_link' onclick='pte_topic_link_copy_string(\"Fax Number\", \"{$topicFaxRoute}\");'><i class='far fa-copy'></i>&nbsp;&nbsp;Fax: {$topicFaxRouteFormatted}</div>" : "";
	$routes = "
			<div class='pte_route_container'>
				<div class='pte_route_container_title'>Route Files to this Topic by</div>
				{$emailRouteHtml}
				{$faxHtml}
			</div>
	";
}
$friendlyLogoNameHtml = isset($friendlyLogoName) && $friendlyLogoName ? $friendlyLogoName : "Image/Logo";

$imageTitle = ($showLogoAccordion == 'block' || $showIconAccordian == 'block') ? "<div class='pte_accordion_section_title'>Topic Images</div>" : "";
$interActionImportanceTitle = ($showImportanceAccordions == 'block') ? "<div class='pte_accordion_section_title'>Interaction Priority</div>" : "";
$inboundRoutingTitle = ($showEmailAccordian == 'block' || $showFaxAccordian == 'block') ? "<div class='pte_accordion_section_title'>Inbound Routing</div>" : "";
$importContactsTitle = ($showAddressBookAccordion == 'block') ? "<div class='pte_accordion_section_title'>Contacts</div>" : "";

$settingsAccordion = "
	{$imageTitle}
	<button id='pte_topic_photo_accordion' class='pte_accordion'  style='display: {$showIconAccordian};' title='Change Personal Topic Icon'>{$profilePicTitle}</button>
	<div class='pte_panel' data-height='325px' style='display: {$showIconAccordian};' >
		<div id='pte_profile_image_selector' style='height: 100%; width: 100%;'></div>
		<div id='pte_profile_image_crop' style='height: 100%; width: 100%; display: none;'></div>
	</div>
	<button id='pte_topic_logo_accordion' class='pte_accordion' style='display: {$showLogoAccordion};' title='Change {$friendlyLogoNameHtml}'>{$friendlyLogoNameHtml}</button>
	<div class='pte_panel pte_extra_margin_after' data-height='325px' style='display: {$showLogoAccordion}; '>
		<div id='pte_profile_logo_selector' style='height: 100%; width: 100%;'></div>
		<div id='pte_profile_logo_crop' style='height: 100%; width: 100%; display: none;'></div>
	</div>
	{$interActionImportanceTitle}
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showImportanceAccordions};' title='Adjust Contact Importance'>VIP Contacts</button>
	<div class='pte_panel' style='display: {$showImportanceAccordions};' data-height='175px'>
		<div class='pte_important_topic_container'>
			<div class='pte_important_list_dropdown_container'><div class='pte_important_list_dropdown_inner'>{$networkOptions}</div></div>
			<ul id='pte_important_network' class='pte_important_topic_scrolling_list'>{$importantNetworkItems}</ul>
	</div>
	</div>
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showImportanceAccordions};' title='Adjust Topic Importance'>VIP Topics</button>
	<div class='pte_panel pte_extra_margin_after' style='display: {$showImportanceAccordions};' data-height='175px'>
		<div class='pte_important_topic_container'>
			<div class='pte_important_list_dropdown_container'><div class='pte_important_list_dropdown_inner'>{$topicOptions}</div></div>
			<ul id='pte_important_topic' class='pte_important_topic_scrolling_list'>{$importantTopicItems}</ul>
	</div>
	</div>
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: none;' title='Adjust Importance Values'>Type</button>
	<div class='pte_panel pte_extra_margin_after' style='display: none;' data-height='175px'>
		{$interactionTypeSliders}
	</div>
	{$inboundRoutingTitle}
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showEmailAccordian};' title='Manage Email Routes'>Email</button>
	<div class='pte_panel' style='display: {$showEmailAccordian};' data-height='203px'>
		{$emailUx}
	</div>
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showFaxAccordian};' title='Manage Fax Routes'>Fax</button>
	<div class='pte_panel s' style='display: {$showFaxAccordian};' data-height='175px'>
		{$faxUx}
	</div>
	{$importContactsTitle}
	<button id='pte_topic_address_book_accordion' class='pte_accordion' style='display: {$showAddressBookAccordion};' title='Important External Contacts'>Import</button>
	<div class='pte_panel'  data-height='500px' style='display: {$showAddressBookAccordion};'>
	  <div id='pte_profile_address_book' style='height: 100%; width: 100%; overflow: hidden !important;'>
			<div id='pte_address_book_ui' style='height: 100%;'></div>
		</div>
	</div>
	<script>
	jQuery('#pte_important_network_topic_list').select2({
		theme: 'bootstrap',
		width: '100%',
		closeOnSelect: false,
		placeholder: 'Add to Contacts...',
		allowClear: false
	});
	jQuery('#pte_important_network_topic_list').on('select2:select', function (e) {
		var data = e.params.data;
		pte_add_to_important_topics('pte_important_network', data);
	});
	jQuery('#pte_important_network_topic_list').on('select2:close', function (e) {
		jQuery('#pte_important_network_topic_list').val('').trigger('change');
	});
	jQuery('#pte_important_topic_list').select2({
		theme: 'bootstrap',
		width: '100%',
		closeOnSelect: false,
		placeholder: 'Add to Topics..',
		allowClear: false
	});
	jQuery('#pte_important_topic_list').on('select2:select', function (e) {
		var data = e.params.data;
		pte_add_to_important_topics('pte_important_topic', data);
	});
	jQuery('#pte_important_topic_list').on('select2:close', function (e) {
		jQuery('#pte_important_topic_list').val('').trigger('change');
	});
	</script>
";

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
	$proTeamSelector = "
		 <div id='alpn_proteam_selector_outer' class='alpn_proteam_selector_outer' style='float: right; display: {$proteamViewSelector};'>
			<select id='alpn_proteam_selector' class='alpn_selector'>
				<option></option>
				{$options}
			</select>
		</div>
	";
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
			$topicInfo = "Link to {$friendlyName}...";
			$coreTopicInfo = "Link to {$subjectStringFormatted}...";
			$tabButtons .= "<button id='tab_{$key}' data-tab-id='{$key}' data-tab-type='{$tabType}' data-stoken='{$subjectToken}' class='tablinks' onclick='pte_handle_tab_selected(this)'>{$value['name']}</button>";
			$tabSelector = "table_tab_{$key}";
			$uniqueFieldId = 0;
			$localFilterId = "alpn_local_selector_topic_filter_{$key}";
			$topicFiler = "<div class='pte_extra_filter_container pte_link_table_filter'><select id='{$localFilterId}' class='alpn_selector'><option></option></select></div>";
			$unlinkButton = $topicClass != 'list' ? "<i id='pte_extra_unlink_button' class='far fa-unlink pte_extra_button pte_extra_button_disabled' title='Unlink Topic' onclick='pte_unlink_selected_topic();'></i>" : '';
			$editButton = $newTypeKey ? "<i id='pte_extra_edit_topic_button' class='far fa-pencil-alt pte_extra_button pte_extra_button_disabled' title='Edit Topic' onclick='pte_edit_topic_link(\"{$newTypeKey}\");'></i>" : "";
			$addButton =  $newTypeKey ? "<i id='pte_extra_add_topic_button' class='far fa-plus-circle pte_extra_button' title='Create and Link to New {$friendlyName}' onclick='pte_new_topic_link(\"{$newTypeKey}\");'></i>" : "";
			$deleteButton =  $newTypeKey ? "<i id='pte_extra_delete_topic_button' class='far fa-trash-alt pte_extra_button pte_extra_button_disabled' title='Delete Topic {$friendlyName}' onclick='pte_delete_topic_link(\"{$newTypeKey}\");'></i>" : "";
			$makeDefaultButton =  $newTypeKey ? "<i id='pte_extra_default_topic_button' class='far fa-check-circle pte_extra_button pte_extra_button_disabled' title='Make this the Default Topic' onclick='pte_default_topic_link(\"{$newTypeKey}\");'></i>" : "";
			$editUnlink =  json_encode("<div class='pte_extra_crud_buttons'><div class='pte_extra_filter_container pte_topic_links_list'>{$topicList}</div>{$unlinkButton}{$deleteButton}{$editButton}{$addButton}{$makeDefaultButton}</div>");

			$initializeTable = "
				<script>
					var topicClass = '{$topicClass}';
					if (topicClass == 'record') {  //js init here if needed for record

					} else {
						var alpn_table_settings = JSON.parse(jQuery('#{$tabSelector}_desc').val());
						wdtRenderDataTable(jQuery('#table_tab_{$key}'), alpn_table_settings);
						alpn_prepare_search_field('#{$tabSelector}_filter');
						wpDataTables.{$tabSelector}.fnSettings().oLanguage.sZeroRecords = 'No Topic Links';
						wpDataTables.{$tabSelector}.fnSettings().oLanguage.sEmptyTable = 'No Topic Links';
						wpDataTables.{$tabSelector}.addOnDrawCallback( function(){
							alpn_handle_extra_table('{$key}');
						});
						jQuery({$editUnlink}).insertBefore('#{$tabSelector}_filter');
					}
					jQuery('#{$topicSelectId}').select2({
						theme: 'bootstrap',
						placeholder: '{$coreTopicInfo}',
						width: '175px',
						allowClear: true,
						closeOnSelect: false
					});
					jQuery('#{$topicSelectId}').on('select2:close', function (e) {
						jQuery('#{$topicSelectId}').val('').trigger('change');
					});
					jQuery('#{$topicSelectId}').on('select2:select', function (e) {
						var data = e.params.data;
						data.pte_type_key = '{$newTypeKey}';
						data.pte_topic_id = '{$topicId}';
						data.pte_subject_token = '{$subjectToken}';
						data.pte_owner_id = '{$userID}';
						data.pte_tab_id = '{$tabId}';
						pte_add_link_to_topic(data);
					});
				</script>
			";
			if (isset($topicClass) && $topicClass == 'record') {
				$editButton = "<div class='pte_record_button_bar'><i id='pte_extra_edit_topic_button' class='far fa-pencil-alt pte_extra_button' title='Edit Topic' onclick='pte_edit_topic_link(\"{$newTypeKey}\");'></i></div>";
				$tabTable =  $editButton . pte_get_create_linked_form ($ownerTopicId, $subjectToken, $newTypeKey);
			} else {
				$wpTable = do_shortcode("[wpdatatable id=8 var1='{$ownerTopicId}' var2='{$subjectToken}']");
				$tabTable = "<div class='pte_tab_table_wrapper'>" .  $wpTable . "</div>";
				$tabTable = str_replace("table_{$tableCounter}", "table_tab_{$key}", $tabTable);
				$tabTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $tabTable);
				$tableCounter += 2;
			}
			$tabHtml = "<div id='form_tab_{$key}'></div>";
		break;
	}
	$tabPanels .= "
		<div id='tabcontent_{$key}' data-tab-id='{$key}' class='pte_tabcontent'>
			{$topicSelector}
			{$tabTable}
			{$initializeTable}
		<div>
		{$tabHtml}
		</div>
		</div>";
}
$tabs = "<div id='pte_tab_wrapper' class='pte_tab_wrapper'><i id='pte_tab_bar_left_arrow' onmousedown='pte_scroll_tab(\"left\");' class='far fa-caret-left pte_tab_bar_left_arrow pte_ipanel_button_disabled'></i><div id='pte_tab' class='pte_tab' onscroll='pte_handle_tab_bar_scroll();'>{$tabButtons}</div><i id='pte_tab_bar_right_arrow' onmousedown='pte_scroll_tab(\"right\");' class='far fa-caret-right pte_tab_bar_right_arrow pte_ipanel_button_disabled'></i></div>{$tabPanels}";
//Buttons

$html .= "
			<div class='outer_button_line'>
				<div class='pte_vault_row_35'>
					<span class='fa-stack pte_icon_button_nav pte_icon_report_selected' title='Information' data-operation='to_info' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
						<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
						<i class='fas fa-info fa-stack-1x' style='font-size: 16px;'></i>
					</span>
					<span class='fa-stack pte_icon_button_nav' title='Report' data-operation='to_report' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
						<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
						<i class='fas fa-drafting-compass fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
					</span>
					<span class='fa-stack pte_icon_button_nav' title='Vault' data-operation='to_vault' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
						<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
						<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
					</span>
				</div>
				<div class='pte_vault_row_65 pte_vault_right'>
					  <i class='far fa-pencil-alt pte_icon_button {$pteEditDeleteClass}' title='Edit Topic' onclick='alpn_mission_control(\"edit_topic\", \"{$topicDomId}\")' ></i>
		       	<i class='far fa-trash-alt pte_icon_button {$pteEditDeleteClass}' title='Delete Topic' onclick='alpn_mission_control(\"delete_topic\", \"{$topicDomId}\")' ></i>
				</div>
				<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
			</div>
	  ";
//Title
$html .= "
					<div class='alpn_container_title_2'>
						<div id='pte_topic_form_title_view'>
							<span class='fa-stack pte_stacked_icon'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-info fa-stack-1x' style='font-size: 16px;'></i>
							</span>
							<span id='pte_topic_name'>{$topicName}</span>
						</div>
						<div id='pte_topic_form_title_view' class='pte_vault_right'>
							{$ownerFirstName}{$context} <div class='pte_title_topic_icon_container'>{$topicImage}</div>
						</div>
					</div>
			";
$html .= "
						<div id='pte_selected_topic_meta' class='pte_vault_row' data-tid='{$topicId}' data-tdid='{$topicDomId}' data-ttid='{$topicTypeId}' data-special='{$topicSpecial}'>
							<div id='pte_topic_form_edit_view_left' class='pte_vault_row_padding_right'>
								{$tabs}
							</div>
							<div id='pte_topic_form_edit_view_right' class=''>
								<script>pte_old_proteam_selected_id=''</script>
								{$proTeamTitle}
								<div id='alpn_inner_proteam_manager' class='alpn_inner_proteam_manager' data-for-topic='{$topicId}' data-for-topic-type='{$topicTypeId}' data-for-special='{$topicSpecial}' style='display: {$proteamContainer}'>
									<div id='alpn_proteam_title_line'>
										<div style='font-weight: bold; float: left; font-size: 14px; line-height: 32px;'>
										&nbsp;
										</div>
										{$proTeamSelector}
									</div>
									<div style='clear: both;'></div>
									<div id='alpn_proteam_selected_outer' class='alpn_proteam_selected_outer'>
										{$proTeamMembers}
									</div>
								</div>
								<div style='font-weight: bold;'>
								</div>
								{$networkContactTopics}
								{$settingsAccordion}
								{$routes}
							  </div>
							</div>
						 </div>
						";

 echo '<pre>';
print_r($replaceStrings); 
$i = 0;
$reconstructed_tabs = array();
foreach($topicTabs as $keys=>$vals) {
	if($vals['name']=='Info') {
		$topicTabs[$keys]['data'] = $replaceStrings;
	}
	else {
		$topicTabs[$keys] = $vals;
	}
	$i++;
}
print_r($topicTabs); 
if(!empty($topicTabs))
{
	$response = array('success' => 1, 'message'=>'Success data found.','data'=>$topicTabs);
}
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 