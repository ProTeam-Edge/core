<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc

// Next is to save report formats by by type_key.
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$siteUrl = get_site_url();
$rootUrl = PTE_ROOT_URL;
$ppCdnBase = PTE_IMAGES_ROOT_URL;

$qVars = $_POST;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//update_user_meta( $userID, "pte_user_network_id",  11);
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

// pp($userID);
// pp($userMeta);

$rightsCheckData = array(
  "topic_dom_id" => $recordId
);
if (!pte_user_rights_check("topic_dom_edit", $rightsCheckData)) {
  $html = "
  <div class='pte_topic_error_message'>
     You do not have permission to design reports on this Topic.
  </div>";
  echo $html;
  exit;
}

$html = '';
$script = "<script>
		function iformat_color(icon) {
			if (icon.id) {
				return \"<span class='pte_vault_bold pte_standard_color_\" + icon.id + \"'><i class='fas fa-circle'></i>&nbsp;&nbsp;\" + icon.text + \"</span>\";
			}
		}
";

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.type_key, tt.special, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.dom_id = %s", $recordId)
 );


if (!isset($results[0])) {
 	 $html = "
 	 <div class='pte_topic_error_message'>
 	 		The selected topic has been deleted. Please select another topic or link.
 	 </div>";
 	 echo $html;
 	 exit;
}

	$record = $results[0];
	$topicTypeId = $record->topic_type_id;
	$topicTypeSpecial = $record->special;
	$topicIcon = $record->icon;
	$topicName = $record->topic_name;
	$topicId = $record->id;
	$topicDomId = $record->dom_id;
	$topicImageHandle = $record->image_handle;
	$topicProfileHandle = $record->profile_handle;
	$topicOwnerId = $record->owner_id;
	$context = $topicName;

	$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;
	$permissionLevel = 0;
	$ownerName = "";
	$ownerFirstName = "";

	$topicMeta = json_decode($record->topic_type_meta, true);
	$typeKey = $record->type_key;
	$subjectToken = '';

	$fullMap = $topicMeta['field_map'];
	$topicTabs = array();

	alpn_log('Topic PDF');

	$user = $wpdb->get_results(
		$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.special, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id WHERE t.owner_id = %d AND t.special = 'user'", $userID)
	 );

	 $orgKey = '';
	 if (isset($user[0])) {
		 $userTopicMeta = json_decode($user[0]->topic_type_meta, true);
		 $userFullMap = $userTopicMeta['field_map'];

		 foreach ($userFullMap as $key2 => $value2) {
			 if (isset($value2['schema_key']) && $value2['schema_key'] == "pte_Organization") {
				 //alpn_log($value2);
				 $userOrganizationSubjectKey = $key2;
		 }
			 if (isset($value2['schema_key']) && $value2['schema_key'] == "pte_Place") {
				 //alpn_log($value2);
				 $userPlaceSubjectKey = $key2;
			 }
		 }
	 }

	//TODO Create a function for getting topicTabs - repoeated everywhere.

	$linkId = 0;
	$topicTabs[] = array(   //Info Page. All Topics Have Them
		'type' => 'page',
		'key' => $typeKey,
		'id' => $linkId,
		'name' => $topicName,
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
		}
	}

	$proteam = $wpdb->get_results(  //get proteam
		$wpdb->prepare("SELECT p.*, t.name, t.image_handle, t.profile_handle, t.dom_id FROM alpn_proteams p LEFT JOIN alpn_topics_network_profile t ON p.proteam_member_id = t.id WHERE p.topic_id = '%s' ORDER BY name ASC", $topicId)
	 );
	$topicHasTeamMembers = count($proteam) ? true : false;

	if ($topicBelongsToUser) {
		//Team Links
		//Being user by. Linked to me.
		$linkId++;
		$topicTabs[] = array(
			'type' => 'linked',
			'id' => $linkId,
			'name' => "Linked by",
			'key' => 'pte_inbound',
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
				'key' => 'pte_external',
				'subject_token' => 'pte_external',
				'owner_topic_id' => $topicId,
				'topic_title' => 'Links to Team Member Topics'
			);
		}
	}

	//Linked Topics by Section (TT)
	$topicTypesUsed = array();
	foreach ($topicTabs as $key => $value) {
		$topicKey = $value['key'];
		$subjectToken = $value['subject_token'];
		$ownerTopicId = $value['owner_topic_id'];  //Same for all. Todo confirm
		if ($subjectToken) {
			$topicTypesUsed[] = $subjectToken;
		}
	}

	$topicsUsedList = "(" .  implode(',', array_map('pte_add_quotes', $topicTypesUsed)) . ")"; //so we can pull all used topic types.
	$topicLinks = $wpdb->get_results(
	$wpdb->prepare("select connected_topic_id, name, subject_token FROM alpn_topics_linked_view WHERE owner_topic_id = %d AND subject_token in {$topicsUsedList} AND owner_id = %d ORDER BY subject_token ASC, name ASC", $ownerTopicId, $userID)
 	);

	$topicSections = "<div class='pte_quick_report_title_text'>Sections</div>";

	foreach ($topicTabs as $key1 => $value1) {
		$topicKey = $value1['key'];
		$topicName = $value1['name'];
		$subjectToken = $value1['subject_token'];
		$topicSelector = "<select id='alpn_select2_small_report_section_{$key1}' data-topic-key='{$topicKey}'>";
		$topicSelector .= "<option value='all'>All Linked Topics</option>";
		$topicSelector .= "<option value='exclude'>Exclude</option>";
		$pteCounter = 0;
		foreach ($topicLinks as $key5 => $value5) {
			if ($value5->subject_token == $subjectToken) {
				$topicSelector .= "<option value='{$pteCounter}'>{$value5->name}</option>";
				$pteCounter++;
			}
		}
		$topicSelector .= "</select>";
		$topicSections .= "

		<div class='pte_quick_report_widget_row'>
			<div id='pte_section_name_{$key1}' class='pte_vault_row_50'>{$topicName}</div>
			<div class='pte_vault_row_50 pte_max_width_50'>{$topicSelector}</div>
		</div>
		";

		$script .= "
				jQuery('#alpn_select2_small_report_section_{$key1}').select2({
					theme: 'bootstrap',
					width: '100%'
				});
		";
	}

	$showBannerSelector = "<select id='alpn_select2_small_report_show_selector'>";
	$showBannerSelector .= "<option value='1'>Yes</option>";
	$showBannerSelector .= "<option value='2'>No</option>";
	$showBannerSelector .= "</select>";

	$bannerSelector = "<select id='alpn_select2_small_report_banner_selector'>";
	$bannerSelector .= "<option value='1'>Banner 1</option>";
	$bannerSelector .= "</select>";

	$placeSelectorName = "alpn_select2_small_report_place";
	$placeSelector = pte_get_topic_list("linked_topics", $userMeta, $placeSelectorName, $userPlaceSubjectKey, true, "To fill in address fields, add a Place to your Personal Topic");

	$organizationSelectorName = "alpn_select2_small_report_organization";
	$organizationSelector = pte_get_topic_list("linked_topics", $userMeta, $organizationSelectorName, $userOrganizationSubjectKey, true, "To fill in organization fields, add an Organization to your Personal Topic");

	$dataSections = "<div class='pte_quick_report_title_text'>Banner</div>";
	$dataSections .= "
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Show</div>
											<div class='pte_vault_row_50 pte_max_width_50'>{$showBannerSelector}</div>
										</div>
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Organization</div>
											<div class='pte_vault_row_50 pte_max_width_50'>{$organizationSelector}</div>
									 	</div>
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Place</div>
											<div class='pte_vault_row_50 pte_max_width_50'>$placeSelector</div>
									 	</div>
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Layout</div>
											<div class='pte_vault_row_50 pte_max_width_50'>{$bannerSelector}</div>
										</div>
										";

	$styleSelector = "<select id='alpn_select2_small_report_style_selector'>";
	$styleSelector .= "<option value='1'>Style 1</option>";
	$styleSelector .= "<option value='2'>Style 2</option>";
	$styleSelector .= "<option value='3'>Style 3</option>";
	$styleSelector .= "<option value='4'>Style 4</option>";
	$styleSelector .= "</select>";

	$standardColorCount = PTE_STANDARD_COLOR_COUNT;
	$accentColorSelector = "<select id='alpn_select2_small_report_accent_color_selector'>";
	for ($i = 0; $i < $standardColorCount; $i++ ) {
		$colorNumber = $i + 1;
		$accentColorSelector .= "<option value='{$i}'>Color {$colorNumber}</option>";
	}
	$accentColorSelector .= "</select>";

	$otherSettings = "<div class='pte_quick_report_title_text'>Settings</div>";
	$otherSettings .= "
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Accent Style</div>
											<div class='pte_vault_row_50 pte_max_width_50'>{$styleSelector}</div>
									 	</div>
										<div class='pte_quick_report_widget_row'>
											<div class='pte_vault_row_50'>Accent Color</div>
											<div class='pte_vault_row_50 pte_max_width_50'>{$accentColorSelector}</div>
									 	</div>
										";

	$script .= "
			jQuery('#alpn_select2_small_report_show_selector').select2({
				minimumResultsForSearch: -1,
			  theme: 'bootstrap',
				width: '100%'
			});
			jQuery('#alpn_select2_small_report_banner_selector').select2({
				minimumResultsForSearch: -1,
			  theme: 'bootstrap',
				width: '100%'
			});

			jQuery('#alpn_select2_small_report_style_selector').select2({
				minimumResultsForSearch: -1,
			  theme: 'bootstrap',
				width: '100%'
			});
			jQuery('#alpn_select2_small_report_accent_color_selector').select2({
				minimumResultsForSearch: -1,
				theme: 'bootstrap',
				width: '100%',
				allowClear: false,
				templateSelection: iformat_color,
				templateResult: iformat_color,
				escapeMarkup: function(text) {
					return text;
				}
			});
			jQuery('#{$placeSelectorName}').select2({
				minimumResultsForSearch: -1,
			  theme: 'bootstrap',
				width: '100%',
				allowClear: false
			});
			jQuery('#{$organizationSelectorName}').select2({
				minimumResultsForSearch: -1,
			  theme: 'bootstrap',
				width: '100%',
				allowClear: false
			});
	";

	if (!$topicBelongsToUser) {
		$topicOwnerContent = json_decode($record->owner_topic_content, true);
		$topicOwnerName = isset($topicOwnerContent['person_givenname']) ? $topicOwnerContent['person_givenname']: "Not Specified";
		$ownerFirstName = "<div id='pte_interaction_owner_outer'><div id='pte_interaction_owner_inner_message'>Topic Owner</div><div id='pte_interaction_owner_inner_name'>{$topicOwnerName}</div></div>";
		$permissionLevel = $record->access_level;
		//TODO Handle if no permissionlevel. Means removed from Proteam or something.

	} else {
			$permissionLevel = 40;
			$connectedId = $record->connected_topic_id;
			if ($connectedId) {
			}
	}
	if ($topicTypeSpecial == 'user') {
		$context = "Personal";
	}
	if ($topicTypeSpecial == 'contact') {
		$context = "Contact";
	}
	$contextAll = "{$context}";

$pdfViewer = pte_get_viewer_template();
$viewerUrl = $siteUrl;

if ($topicProfileHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 10px; color: rgb(68, 68, 68); font-size: 24px;'></i>";
}

$savedReportTable = do_shortcode("[wpdatatable id='10' var1='report' var2='{$typeKey}']");

$savedReportTable = str_replace('table_1', 'table_reports', $savedReportTable);
$savedReportTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $savedReportTable);
$savedReportTable = str_replace('"iDisplayLength":5,', '"iDisplayLength":10,', $savedReportTable);

$buttons = "
			<div id='pte_report_saved_list_container'>
				<div id='pte_report_saved_list_container_inner'>
					<div class='pte_quick_report_widget_row'>
						<div id='pte_section_name' class='pte_vault_row_35 pte_vault_bold'>Saved Reports</div>
						<div class='pte_vault_row_65 pte_max_width_65 pte_vault_right'>
						<img id='pte_refresh_report_loading' class='pte_refresh_report_loading' src='{$rootUrl}pdf/web/images/loading-icon.gif'>
							<i class='far fa-sync-alt quick_report_button' title='Refresh Report Using Changed Settings' onclick='pte_handle_report_settings(\"refresh\");'></i>
							<i id='pte_report_button_save' class='far fa-save quick_report_button' title='Save Report Settings Template' onclick='pte_handle_report_settings(\"save\");' style='font-size: 20px;'></i>
							<i id='pte_report_button_clone' class='far fa-clone quick_report_button pte_extra_button_disabled' title='Duplicate Report Settings Template' onclick='pte_handle_report_settings(\"clone\");' ></i>
							<i id='pte_report_button_delete' class='far fa-trash-alt quick_report_button pte_indent_right_margin pte_extra_button_disabled' title='Delete Report Settings Template' onclick='pte_handle_report_settings(\"delete\");'></i>
						</div>
					</div>
					<div id='pte_saved_reports' class='pte_report_saved_list'>{$savedReportTable}</div>
				</div>
			</div>
";

$script .= "
			var pte_report_section_count = {$key1};

			</script>";
$html .= "
					<div class='outer_button_line'>
						<div class='pte_vault_row_35'>
							<span class='fa-stack pte_icon_button_nav ' title='Information' data-operation='to_info' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-info fa-stack-1x' style='font-size: 16px;'></i>
							</span>
							<span class='fa-stack pte_icon_button_nav pte_icon_report_selected' title='Report' data-operation='to_report' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-drafting-compass fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
							</span>
							<span class='fa-stack pte_icon_button_nav' title='Vault' data-operation='to_vault' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
							</span>
						</div>
						<div class='pte_vault_row_65 pte_vault_right'>
						<i id='alpn_vault_copy' class='far fa-file-pdf pte_icon_button pte_extra_button_disabled' title='Put this PDF Report in Vault and Stay' onclick='pte_copy_report_to_vault(0);'></i>
						<span id='alpn_vault_copy_go'class='fa-stack pte_icon_button_nav pte_extra_button_disabled' title='Put this PDF Report in Vault and Go' onclick='pte_copy_report_to_vault(1);'>
							<i class='far fa-file fa-stack-1x' style='font-size: 24px;'></i>
							<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 11px; top: 2px;'></i>
						</span>
						</div>
						<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
	  			</div>

					<div id='pte_selected_topic_meta' class='alpn_container_title_2' data-topic-id='{$topicId}' data-tid='{$topicId}' data-ttid='{$topicTypeId}' data-special='{$topicTypeSpecial}' data-tdid='{$topicDomId}' data-tkey='{$typeKey}' data-oid='{$topicOwnerId}'>
						<div id='pte_topic_form_title_view'>
							<span class='fa-stack pte_stacked_icon'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-drafting-compass fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
							</span>
							<span id='pte_topic_name'>{$record->name}</span>
						</div>
						<div id='pte_topic_form_title_view' class='pte_vault_right'>
							{$ownerFirstName}{$contextAll} <div class='pte_title_topic_icon_container'>{$topicImage}</div>
						</div>
					</div>
";

$html .= "
					<div id='alpn_vault_main_container'>
						<div id='alpn_outer_vault' class='pte_outer_vault_small'>
							{$dataSections}
							{$topicSections}
							{$otherSettings}
							{$buttons}
				 ";

$html .= "	</div>
						<div id='pte_vault_container' class='pte_outer_vault_small'>
							<div id='alpn_add_edit_outer_container' class='alpn_add_edit_outer_container'></div>
							<div id='alpn_vault_preview_embedded'>
								<div id='pte_pdf_ui'></div>
								{$pdfViewer}
							</div>
						</div>
						<div style='clear: both;'></div>
					</div>
					{$script}
				";
echo $html;

?>
