<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()


$html="";
$proTeamHtml ="";
$qVars = $_POST;

//pp($qVars);


$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$pteUserTimezoneOffset = isset($qVars['pte_user_timezone_offset']) ? $qVars['pte_user_timezone_offset'] : '';
$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

//Get topic information
$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.topic_type_id=5 WHERE t.dom_id = %s", $recordId)
 );
$topicData = $results[0];

$topicTypeId = $topicData->topic_type_id;
$topicTypeName = $topicData->topic_name;
$topicIcon = $topicData->icon;
$topicId = $topicData->id;
$topicImageHandle = $topicData->image_handle;
$topicLogoHandle = $topicData->logo_handle;
$topicProfileHandle = $topicData->profile_handle;
$topicName = $topicData->name;
$topicChannelId = $topicData->channel_id;
$topicTabs = json_decode($topicData->html_template, true);
$topicTabs = isset($topicTabs['tabs']) ? $topicTabs['tabs'] : $topicTabs;
$topicDomId = $topicData->dom_id;
$topicMeta = json_decode($topicData->topic_type_meta, true);
$topicContent = json_decode($topicData->topic_content, true);
$fieldMap = array_flip($topicMeta['field_map']);

//map and replace
$replaceStrings = array();
foreach($topicContent as $key => $value){	   //deals with date/time being arrays
	if (is_array($value)) {
		foreach ($value as $key2 => $value2) {
			$actualValue = $value2;
		}
	} else {
		$actualValue = $value;
	}
	$replaceStrings['{' . $key . '}'] = $actualValue;
}

$replaceStrings["{topicDomId}"] = $topicDomId;

$context = $topicTypeName;
$proteamViewSelector = "block";
$proteamContainer = 'block';
$proTeamTitle = "ProTeam";
$profilePicTitle = "Topic Icon - {$topicTypeName}";
$showMessageAccordion = "none";
$showLogoAccordion = "none";
$showAddressBookAccordion = "none";
$showImportanceAccordions = "none";
$showFaxAccordian = "none";

$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
if (isset($replaceStrings['{organization.type}'])) {
	$replaceStrings['{businessTypeFriendly}'] = $businessTypesList[$replaceStrings['{organization.type}']];
} else {
	$replaceStrings['{businessTypeFriendly}'] = "Not Specified";
}

if ($topicTypeId == '4' || $topicTypeId == '5') {   //user or network

	if ($topicTypeId == '4') {
		$context = "Network";
		$proteamViewSelector = "none";
		$proteamContainer = 'none';
		$proTeamTitle = "";
		$profilePicTitle = "Network Contact Icon";
		$showMessageAccordion = "none";
		$showLogoAccordion = "none";
		$showImportanceAccordions = "none";
		$showFaxAccordian = "none";
	}
	if ($topicTypeId == '5') {
		$context = "Personal";
		$proteamViewSelector = "none";
		$proteamContainer = 'none';
		$proTeamTitle = "";
		$profilePicTitle = "Personal Icon";
		$showMessageAccordion = "block";
		$showLogoAccordion = "block";
		$showAddressBookAccordion = "block";
		$showImportanceAccordions = "block";
		$showFaxAccordian = "block";
	}
}

$networkOptions = pte_get_topic_list('network_contacts') ;
$topicOptions = pte_get_topic_list('topics') ;

$importantNetworkItems = pte_get_important_items('pte_important_network');
$importantTopicItems = pte_get_important_items('pte_important_topic');

$interactionTypeSliders = pte_get_interaction_settings_sliders(array());

//pte_search_for_fax_number();

$settingsAccordion = "
	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showImportanceAccordions};' title='Adjust Network Contact Importance'>VIP Network Contacts</button>
	<div class='pte_panel' style='display: {$showImportanceAccordions};' data-height='175px'>
		<div class='pte_important_topic_container'>
			<div class='pte_important_list_dropdown_container'><div class='pte_important_list_dropdown_inner'>{$networkOptions}</div></div>
			<ul id='pte_important_network' class='pte_important_topic_scrolling_list'>{$importantNetworkItems}</ul>
	</div>
	</div>

	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showImportanceAccordions};' title='Adjust Topic Importance'>VIP Topics</button>
	<div class='pte_panel' style='display: {$showImportanceAccordions};' data-height='175px'>
		<div class='pte_important_topic_container'>
			<div class='pte_important_list_dropdown_container'><div class='pte_important_list_dropdown_inner'>{$topicOptions}</div></div>
			<ul id='pte_important_topic' class='pte_important_topic_scrolling_list'>{$importantTopicItems}</ul>
	</div>
	</div>

	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showImportanceAccordions};' title='Adjust Importance Values'>Interaction Importance Settings</button>
	<div class='pte_panel' style='display: {$showImportanceAccordions};' data-height='175px'>
		{$interactionTypeSliders}
	</div>

	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showMessageAccordion};' title='Manage Notifications'>Notification Settings</button>
	<div class='pte_panel' style='display: {$showMessageAccordion};' data-height='275px'>
		<p>Red, Green by notification type: </p>
	</div>

	<button id='pte_topic_message_accordion' class='pte_accordion' style='display: {$showFaxAccordian};' title='Manage Fax Settings'>Fax Routing</button>
	<div class='pte_panel' style='display: {$showFaxAccordian};' data-height='200px'>
		<p>Fax and Routing</p>
	</div>

	<button id='pte_topic_photo_accordion' class='pte_accordion' title='Change Personal Topic Icon'>{$profilePicTitle}</button>
	<div class='pte_panel' data-height='325px'>
	  <div id='pte_profile_image_selector' style='height: 100%; width: 100%;'></div>
		<div id='pte_profile_image_crop' style='height: 100%; width: 100%; display: none;'></div>
	</div>

	<button id='pte_topic_logo_accordion' class='pte_accordion' style='display: {$showLogoAccordion};' title='Change Organization Logo'>Organization Logo</button>
	<div class='pte_panel'  data-height='325px' style='display: {$showLogoAccordion}; '>
		<div id='pte_profile_logo_selector' style='height: 100%; width: 100%;'></div>
		<div id='pte_profile_logo_crop' style='height: 100%; width: 100%; display: none;'></div>
	</div>

	<button id='pte_topic_logo_accordion' class='pte_accordion' style='display: {$showAddressBookAccordion};' title='Important External Contacts'>Import Network Contacts</button>
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

$network = $wpdb->get_results( //for select box
	$wpdb->prepare("SELECT id, name, connected_id FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '4' ORDER BY name ASC", $userID)
 );

$options = "";
foreach ($network as $key => $value){
	$options .= "<option data-wp-id='{$value->connected_id}' value='{$value->id}'>{$value->name}</option>";
}

$proteam = $wpdb->get_results(  //get proteam
	$wpdb->prepare("SELECT p.*, t.name, t.image_handle, t.profile_handle FROM alpn_proteams p LEFT JOIN alpn_topics_network_profile t ON p.proteam_member_id = t.id WHERE p.owner_id = '%s' AND p.topic_id = '%s' ORDER BY name ASC", $userID, $topicId)
 );

$proTeamMembers = "";
foreach ($proteam as $key => $value) {

	$topicNetworkId = $value->id;
	$topicNetworkName = $value->name;
	$topicAccessLevel= $value->access_level;
	$topicNetworkRights = json_decode($value->member_rights, true);

	$checked = array();
	foreach ($topicNetworkRights as $key2 => $value2) {
		$checked[$key2] = $value2;
	}

	$topicPanelData = array(
		'proTeamRowId' => $value->id,
		'topicNetworkId' => $value->proteam_member_id,
		'topicNetworkName' => $value->name,
		'topicAccessLevel' => $topicAccessLevel,
		'state' => $value->state,
		'checked' => $checked
	);

	$proTeamMembers .= pte_make_rights_panel_view($topicPanelData);
}

$topicLogoUrl = '';
if ($topicLogoHandle) {
	$topicLogoUrl = "<div onclick='jQuery(\"#pte_topic_logo_accordion\").click();' style='display: inline-block; width 40%; cursor: pointer;'><img id='pte_profile_logo_image' style='' src='{$ppCdnBase}{$topicLogoHandle}'></div>";
}
$replaceStrings['{alpn_profile_business_logo}'] = $topicLogoUrl;

//TODO Prefill with correct token data

//TODO use this in interactions for templating tied to IAs
$messageTypeId = '1';
$templates = $wpdb->get_results(
	$wpdb->prepare("SELECT id, short_description FROM alpn_message_templates WHERE owner_id = '%s' AND message_type_id = '%s' ORDER BY short_description ASC", $userID, $messageTypeId)
 );

$profileImageSelector = "";

if ($topicProfileHandle) {
	$topicImage = "<img id='pte_profile_pic_topic' src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; margin-left: 10px; border-radius: 50%;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img id='pte_profile_pic_topic' src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; margin-left: 10px; border-radius: 50%;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 15px;  margin-top: 2px; color: #4499d7; font-size: 1.2em;'></i>";
}


//Load needed tab definitions.
$requiredTabs = '';
foreach ($topicTabs as $key => $value) {
	if ($value['type'] == 'extra')
	$requiredTabs .= $value['id'] . ", ";
}
$requiredTabs = "(" . rtrim($requiredTabs, ", ") . ")";
$tabDefinitionObject = $wpdb->get_results("SELECT * from alpn_topic_tabs WHERE id IN {$requiredTabs}");

$tabDefinitions = array();
foreach ($tabDefinitionObject as $key => $value) {
	$tabDefinitions[$value->id] = $value;
}

$tabButtons = $tabPanels = $initializeTable = $tabTable = '';

$tableCounter = 1; //wpforms numbers tables odd numbers
foreach ($topicTabs as $key => $value) {

	$tabType = $value['type'];

	if (isset($value['id'])) {
		$tabId = $value['id'];
	}

	switch ($tabType) {
		case 'page':
			$tabButtons .= "<button id='tab_{$key}' data-tab-id='{$key}' data-tab-type='{$tabType}' class='tablinks' onclick='pte_handle_tab_selected(this)'>{$value['name']}</button>";
			$tabHtml = str_replace(array_keys($replaceStrings), $replaceStrings, $value['html']);
		break;
		case 'extra':
			$tabName = $tabDefinitions[$tabId]->name;
			$formId = $tabDefinitions[$tabId]->form_id;
			$tabSelector = "table_tab_{$key}";
			$tabMeta = json_decode($tabDefinitions[$tabId]->meta, true);
			$tabFields = $tabMeta['field_map'];
			$uniqueFieldId = $tabMeta['pte.meta'];
			$tabButtons .= "<button id='tab_{$key}' data-tab-id='{$key}' data-tab-type='{$tabType}' class='tablinks' onclick='pte_handle_tab_selected(this)'>{$tabName}</button>";
			$crudButtons = json_encode("<div class='pte_extra_crud_buttons'><i class='far fa-plus-circle pte_extra_icon_button' title='Add Item' onclick='pte_extra_control(\"add\", \"{$key}\", \"{$tabId}\", \"{$topicId}\", \"{$formId}\", \"{$uniqueFieldId}\")'></i><i id='pte_extre_delete_item' class='far fa-trash-alt pte_extra_icon_button pte_extra_button_disabled' title='Delete Item' onclick='pte_extra_control(\"delete\", \"{$key}\", \"{$tabId}\", \"{$topicId}\", \"{$formId}\", \"{$uniqueFieldId}\")'></i></div>");
			$initializeTable = "
				<script>
					var alpn_table_settings = JSON.parse(jQuery('#{$tabSelector}_desc').val());
					wdtRenderDataTable(jQuery('#table_tab_{$key}'), alpn_table_settings);
					alpn_prepare_search_field('#{$tabSelector}_filter');
					jQuery({$crudButtons}).insertBefore('#{$tabSelector}_filter');
					wpDataTables.{$tabSelector}.fnSettings().oLanguage.sZeroRecords = 'No Entries...';
					wpDataTables.{$tabSelector}.fnSettings().oLanguage.sEmptyTable = 'No Entries...';
					wpDataTables.{$tabSelector}.addOnDrawCallback( function(){
						alpn_handle_extra_table('{$key}');
					})
					pte_get_formlet('{$key}', '', '{$tabId}', '{$topicId}', '{$formId}', '{$uniqueFieldId}');
				</script>
			";

			$sortOrder = $tabMeta['alpn_sort_order'];
			$wpTable = do_shortcode("[wpdatatable id=7 var1='{$topicId}' var2='{$sortOrder}', var3='{$tabId}']"); //TODO vars and owner id
			$tabTable = "<div class='pte_tab_table_wrapper' style='margin-bottom: 10px;'>" .  $wpTable . "</div>";
			$tabTable = str_replace("table_{$tableCounter}", "table_tab_{$key}", $tabTable);
			$tabTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $tabTable);
			$row_id = '';
			$extraMeta = array(
				"pte_user_timezone_offset" => $pteUserTimezoneOffset,
				"topic_id" => $topicId,
				"row_id" => $row_id,
				"tab_type_id" => $tabId
			);
			$tabHtml = "<div id='form_tab_{$key}'></div>";
			$tableCounter += 2;
		break;
	}

	$tabPanels .= "<div id='tabcontent_{$key}' data-tab-id='{$key}' class='pte_tabcontent'>{$tabTable}{$initializeTable}<div>{$tabHtml}</div></div>";

}
$tabs = "<div class='pte_tab'>{$tabButtons}</div>{$tabPanels}";

//Buttons
$html .= "<div class='outer_button_line'>
			  <i class='far fa-lock-alt pte_icon_button' title='Vault' onclick='alpn_mission_control(\"vault\", \"{$topicDomId}\")' style='font-size: 28px; width: 40px; float: left; margin-left: 10px;'></i>
			  <div id='alpn_message_area' class='alpn_message_area'>
			  </div>
			  <div id='alpn_vault_button_bar' class='alpn_vault_button_bar'>
			   <i class='far fa-pencil-alt pte_icon_button' title='Edit Topic' onclick='alpn_mission_control(\"edit_topic\", \"{$topicDomId}\")' ></i>
		       <i class='far fa-trash-alt pte_icon_button' title='Delete Topic' onclick='alpn_mission_control(\"delete_topic\", \"{$topicDomId}\")' ></i>
			   <div style='display: inline-block; width: 10px;'></div>
			  </div>
			  <div style='clear: both;'></div>
		  </div>
		  ";
//Title
$html .= "<div class='alpn_container_title_2'>
			<div class='alpn_container_2_left'><i class='far fa-info-circle' style='vertical-align: middle; width: 30px; margin-bottom: 5px; font-size: 1.2em; color: rgb(68, 68, 68);'></i>&nbsp;&nbsp;{$topicName}</div>
			<div class='alpn_container_2_right'><div style='display: inline-block; vertical-align: middle;'>{$context}</div><div style='display: inline-block;  vertical-align: middle; height: 35px;'>{$topicImage}</div></div>
		  </div>
		  <div style='clear: both;'></div>";
$html .= "<div class='wp-block-kadence-rowlayout alignnone>
		  	<div class='kt-row-layout-inner'>
				<div class='alpn_field_row kt-row-column-wrap kt-has-2-columns kt-gutter-default kt-v-gutter-default kt-row-valign-top kt-row-layout-right-golden kt-tab-layout-inherit kt-m-colapse-left-to-right kt-mobile-layout-row kt-custom-first-width-60 kt-custom-second-width-40'>
					<div class='wp-block-kadence-column inner-column-1'>
						<div class='kt-inside-inner-col'>
							{$tabs}
						</div>
					</div>
					<div class='wp-block-kadence-column inner-column-2'>
						<div class='kt-inside-inner-col'>
							<script>pte_old_proteam_selected_id=''</script>
							<div id='alpn_inner_proteam_manager' class='alpn_inner_proteam_manager' data-for-topic='{$topicId}' data-for-topic-type='{$topicTypeId}' style='display: {$proteamContainer}'>
								<div id='alpn_proteam_title_line'>
									<div style='font-weight: bold; float: left;'>
									{$proTeamTitle}
									</div>
									<div id='alpn_proteam_selector_outer' class='alpn_proteam_selector_outer' style='float: right; display: {$proteamViewSelector};'>
										<select id='alpn_proteam_selector' class='alpn_selector'>
											<option></option>
											{$options}
										</select>
									</div>
								</div>
								<div style='clear: both;'></div>
								<div id='alpn_proteam_selected_outer' class='alpn_proteam_selected_outer'>
									{$proTeamMembers}
								</div>
							</div>
							<div style='font-weight: bold;'>
							</div>
							{$settingsAccordion}
						</div>
					</div>
				</div>
			</div>
		</div>";

//pp($wpdb);

echo $html;

?>
