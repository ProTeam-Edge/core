<?php

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;

global $memberFeatures, $senderName;
$senderName = "Wiscle";

$memberFeatures= array(
	'fax_1' => true,
	'fax_2' => true,
	'template_editor_main' => false,
	'topic_type_editor_main' => false
);

function db_shortcode($attr) {

	global $wpdb, $memberFeatures, $wpdb_readonly; //Wordpress DB Access

    extract(shortcode_atts(array(
        'block_type' => 'block_type'
    ), $attr));

  $html = $domID = $parseUserName = $parsePassword = $userTopicId = $userImageHandle = $syncId = $userTopicId = $userTopicTypeId = $contactTopicTypeId = $userDisplayName = $standardColorCount = "";

	$templateDirectory = get_template_directory_uri();

	if (is_user_logged_in()) {  //TODO only hit database when needed
		$userInfo = wp_get_current_user();
		$userID = $userInfo->data->ID;
		$userEmail = $userInfo->user_email;
		$ownerNetworkId = get_user_meta( $userID, 'pte_user_network_id', true ); //Owners Topic ID
		$isNewMember = get_user_meta( $userID, 'wsc_new_member', true );
		if ($isNewMember) {
			update_user_meta( $userID, "wsc_new_member", false);
		}
		$specialType = 'user';
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT JSON_UNQUOTE(JSON_EXTRACT(t.topic_content, '$.person_givenname')) AS owner_givenname, JSON_UNQUOTE(JSON_EXTRACT(t.topic_content, '$.person_familyname')) AS owner_familyname, t.dom_id, t.id, t.image_handle, t.name, t.sync_id, t.topic_type_id AS user_topic_type_id, tt.id AS contact_topic_type_id FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.owner_id = %d AND tt.special = 'contact' WHERE t.owner_id = %d AND t.special = 'user';", $userID, $userID)
		);
		if (isset($results[0])) {
			$userTopicId = $results[0]->id;
			$userTopicTypeId = $results[0]->user_topic_type_id;
			$contactTopicTypeId = $results[0]->contact_topic_type_id;
			$userImageHandle = $results[0]->image_handle;
			$domId = $results[0]->dom_id;
			$userName = $results[0]->name;
			$userDisplayName = addslashes(trim($results[0]->owner_givenname . ' ' . $results[0]->owner_familyname));
			$firstName = addslashes(trim($results[0]->owner_givenname ));
			$syncId = $results[0]->sync_id;
			$standardColorCount = PTE_STANDARD_COLOR_COUNT;
		} else {

		}

	} else {
		$userTopicId = 0;
		$userID = 0;
		$userTopicTypeId = 0;
		$contactTopicTypeId = 0;
		$userImageHandle ="";
		$userDisplayName = "Guest";
		$fullAvatarUrl = "";
		$userName = '';
		$syncId='';
	}

	$iconArray = array(
		"1" => ""
	);
	$avatarUrl = "https://storage.googleapis.com/pte_media_store_1/";
	$fullAvatarUrl = $userImageHandle ? "{$avatarUrl}{$userImageHandle}" : "";

	$rootUrl = PTE_ROOT_URL;

    switch ($block_type) {

			case 'load_user_data':
				if ($userID) {
					$html .= "
					<script>
						alpn_user_id = {$userID};
						alpn_sync_id = '{$syncId}';
						alpn_user_topic_id = {$userTopicId};
						alpn_user_topic_type_id = {$userTopicTypeId};
						alpn_contact_topic_type_id = {$contactTopicTypeId};
						alpn_user_displayname = '{$userDisplayName}';
						alpn_user_firstName = '{$firstName}';
						alpn_user_email = '{$userEmail}';
						alpn_avatar_baseurl = '{$avatarUrl}';
						alpn_avatar_handle = '{$userImageHandle}';
						alpn_avatar_url = '{$fullAvatarUrl}';
						alpn_templatedir = '{$templateDirectory}-child-master/';
						pte_standard_color_count = $standardColorCount;
					</script>
					";
				} else {
					$html .= "
					<script>
						alpn_user_id = 0;
					</script>
					";
				}
			break;
			case 'topic_manager':
			 $topicManagerSettings = array(
				 'setting_1' => 'open'
			 );
				$html .= pte_get_topic_manager($topicManagerSettings);

				if ($userID) {
					$html .= "
					<script>
						alpn_user_id = {$userID};
						alpn_sync_id = '{$syncId}';
						alpn_user_topic_id = {$userTopicId};
						alpn_user_topic_type_id = {$userTopicTypeId};
						alpn_contact_topic_type_id = {$contactTopicTypeId};
						alpn_user_displayname = '{$userDisplayName}';
						alpn_user_firstName = '{$firstName}';
						alpn_user_email = '{$userEmail}';
						alpn_avatar_baseurl = '{$avatarUrl}';
						alpn_avatar_handle = '{$userImageHandle}';
						alpn_avatar_url = '{$fullAvatarUrl}';
						alpn_templatedir = '{$templateDirectory}-child-master/';
						pte_standard_color_count = $standardColorCount;
					</script>
					";
				} else {
					$html .= "
					<script>
						alpn_user_id = 0;
					</script>
					";
				}

			break;

			case 'gallery':
			 if (!$userID) {
				 $html = "<style>#site-navigation{display: none;}</style>";
			 }

			 $gallerySettings = array(
			 );
			 $html .= "
				 <script>
				 alpn_templatedir = '{$templateDirectory}-child-master/';
				 </script>
			 ";
				$html .= wsc_get_gallery($gallerySettings);


			break;

			case 'viewer':
			 if (!$userID) {  //External visotor does not need to see navigations
				 $html = "<style>#site-navigation{display: none;}</style>";
			 }

			 $viewerSettings = array(
				 'sidebar_state' => 'closed'
			 );

				$html .= pte_get_viewer($viewerSettings);
			break;

			case 'template_editor':
			 $topicManagerSettings = array(
				 'setting_1' => 'open'
			 );
				$html .= pte_get_template_editor($topicManagerSettings);

				if ($userID) {
					$html .= "
					<script>
						alpn_user_id = {$userID};
						alpn_sync_id = '{$syncId}';
						alpn_user_topic_id = {$userTopicId};
						alpn_user_topic_type_id = {$userTopicTypeId};
						alpn_contact_topic_type_id = {$contactTopicTypeId};
						alpn_user_displayname = '{$userDisplayName}';
						alpn_user_firstName = '{$firstName}';
						alpn_user_email = '{$userEmail}';
						alpn_avatar_baseurl = '{$avatarUrl}';
						alpn_avatar_handle = '{$userImageHandle}';
						alpn_avatar_url = '{$fullAvatarUrl}';
						alpn_templatedir = '{$templateDirectory}-child-master/';
						pte_standard_color_count = $standardColorCount;
					</script>
					";
				} else {
					$html .= "
					<script>
						alpn_user_id = 0;
					</script>
					";
				}

			break;


			case 'chrome':

			$results = $wpdb_readonly->get_results(
				$wpdb_readonly->prepare(
					"SELECT id, name, '0' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'user' UNION
					 SELECT id, name, '1' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'contact' UNION
					 SELECT id, name, '2' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'topic'
					 ORDER BY row_type ASC, name ASC;",
					 $userID, $userID, $userID)
			);

			$topicOptions = "";

			if (isset($results[0])) {

				$topicOptions .= "
					<optgroup label='Personal'>
					<option value='{$results[0]->id}'>{$results[0]->name}</option>
					</optgroup>
					<optgroup label='Network'>
				";

				foreach ($results as $key => $value) {
						if ($value->row_type == 1) {
							$topicOptions .= "
								<option value='{$value->id}'>{$value->name}</option>
								";
						}
				}

				$topicOptions .= "
					</optgroup>
					<optgroup label='Topics'>
				";

				foreach ($results as $key => $value) {
					if ($value->row_type == 2) {
						$topicOptions .= "
							<option value='{$value->id}'>{$value->name}</option>
							";
					}
				}

				$topicOptions .= "
					</optgroup>
				";
			}

			$html = "";
			$html .= "
				<div class='pte_extension_outer'>
					<div class='pte_extension_inner'>
						<div class='pte_extension_title'>
								Send File to Topic
						</div>
						<div class='pte_extension_fields'>
							<div class='pte_extension_fields_left'>
								<div id='pte_uppy_uploader'>
								</div>
							</div>
							<div class='pte_extension_fields_right'>
							<select id='pte_extension_topic_select'>
							{$topicOptions}
							</select>
							<div style='margin-top: 5px;'>Description</div>
							<textarea id='alpn_about_field' style='height: 80px;' placeholder='Describe your vault entry so it can be easily found...'></textarea>
							<div style='margin-top: 5px;'>Access</div>
							<select id='alpn_selector_sharing' class='alpn_selector_sharing'>
								<option value='40'>Private</option>
								<option value='10'>General</option>
								<option value='20'>Restricted</option>
							</select>
							</div>
							<div style='clear: both;'></div>
						</div>
						<div style='text-align: right; width: 100%;'>
							<img src='https://wiscle.com/wp-content/themes/memberlite-child-master/pte_logo_extension.png'>
						</div>
					</div>
				</div>
				<style>
				.uppy-DashboardItem-action--remove {display: none !important;}
				.select2-results__group {padding-left: 10px !important;}
				li[role='option'] {padding-left: 20px !important;}
				</style>
				<script>
				pte_chrome_extension = true;
				alpn_user_id = {$userID};
				alpn_sync_id = '{$syncId}';
				alpn_user_topic_id = {$userTopicId};
				alpn_user_topic_type_id = {$userTopicTypeId};
				alpn_contact_topic_type_id = {$contactTopicTypeId};
				alpn_user_displayname = '{$userDisplayName}';
				alpn_user_firstName = '{$firstName}';
				alpn_user_email = '{$userEmail}';
				alpn_avatar_baseurl = '{$avatarUrl}';
				alpn_avatar_handle = '{$userImageHandle}';
				alpn_avatar_url = '{$fullAvatarUrl}';
				alpn_templatedir = '{$templateDirectory}-child-master/';
				pte_standard_color_count = $standardColorCount;
				</script>
			";

			break;
      case 'network':
				$parseSessionToken = get_user_meta( $userID, 'wsc_parse_session_token', true );

				$urlTopicOperation = isset($_GET['topic_operation']) && $_GET['topic_operation'] ? $_GET['topic_operation'] : "";
				$urlDestinationTopicId = isset($_GET['destination_topic_id']) && $_GET['destination_topic_id'] ? $_GET['destination_topic_id'] : "";

				$connectedStatus = "wants_to_connect";
				$connections = $wpdb->get_results(
					$wpdb->prepare("SELECT id FROM alpn_member_connections WHERE owner_id = %d AND connected_status COLLATE utf8mb4_general_ci = %s", $userID, $connectedStatus)
				);
				$hasConnectionRequests = isset($connections[0]) ? true : false;
				$manageConnectionsButtonClass = $hasConnectionRequests ? "wcl_connections_button_highlighted" : "";
				$manageConnectionsButtonTitle = $hasConnectionRequests ? "You have connection requests waiting" : "Manage Connections";

				$businessTypes = get_custom_post_items('pte_profession', 'ASC');
				$optionsStr = $iconStr = '';

				foreach ($businessTypes as $key => $value){
					//$iconStr =
					$optionsStr .= "<option value='{$key}'>{$value}</option>";
				}
				$html = "
						<div id='alpn_section_network'>
							<div class='alpn_title_bar'>
								<div class='alpn_section_head_left'>Contacts</div>
								<div class='alpn_section_head_right'>
									<i id='wcl_manage_connections_icon' class='far fa-user-circle alpn_icons {$manageConnectionsButtonClass}' title='{$manageConnectionsButtonTitle}' onclick='alpn_mission_control(\"manage_connections\", \"\", alpn_contact_topic_type_id);'></i>
									<i class='far fa-plus-circle alpn_icons' title='Add/Import Contacts' onclick='alpn_mission_control(\"add_topic\", \"\", alpn_contact_topic_type_id);'></i>
								</div>
							</div>
							<div id='alpn_selector_container_left' class='alpn_selector_container_left pte_hidden_for_now'>
								<select id='alpn_selector_network' class='alpn_selector'><option></option>{$optionsStr}</select>
							</div>
				";
				$html .= do_shortcode("[wpdatatable id=2]");

				$html = str_replace('table_1', 'table_network', $html);
				$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
				$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":5,', $html);

				$html .= "</div>";

				if ($userID) {  // one time
							$html .= "<script>
						   pte_chrome_extension = false;
						   alpn_user_id = {$userID};
							 alpn_sync_id = '{$syncId}';
							 wsc_topic_operation = '{$urlTopicOperation}';
							 wsc_destination_topic_id = '{$urlDestinationTopicId}';
							 alpn_sync_id = '{$syncId}';
							 alpn_user_topic_id = {$userTopicId};
							 alpn_user_topic_type_id = {$userTopicTypeId};
	 						 alpn_contact_topic_type_id = {$contactTopicTypeId};
							 alpn_user_displayname = '{$userDisplayName}';
							 alpn_user_firstName = '{$firstName}';
							 alpn_user_email = '{$userEmail}';
							 alpn_avatar_baseurl = '{$avatarUrl}';
							 alpn_avatar_handle = '{$userImageHandle}';
							 alpn_avatar_url = '{$fullAvatarUrl}';
							 alpn_templatedir = '{$templateDirectory}-child-master/';
							 pte_standard_color_count = {$standardColorCount};
							 const wsc_parse_session_token = '{$parseSessionToken}';
							 </script>";
				} else {
					$html .= "<script>alpn_user_id = 0;</script>";
				}

    break;
		case 'topic':

			$selectHtml = "";
			$status = 'user';
			$topicClass = 'topic';

			if ($userID) {
				try {
					$results = $wpdb->get_results(
						$wpdb->prepare("SELECT id, name, icon FROM alpn_topic_types WHERE topic_class = %s AND topic_state = %s AND owner_id = %d AND special = 'topic' ORDER BY name ASC;", $topicClass, $status, $userID)
					);
					if (count($results)) {
						foreach ($results as $key => $value) {
							$selectHtml .= "<option value='{$value->id}' data-icon='{$value->icon}'>{$value->name}</option>";
					}
					}
				}
				catch(Exception $e) {
					alpn_log('DRAWING TOPIC PANEL');
					alpn_log($e);
				}
			}

			$html = "
			<div id='alpn_section_topic'>
			<div class='alpn_title_bar'>
			<div class='alpn_section_head_left'>
				Topics
			</div>
			<div class='alpn_section_head_right'>
				<span style='display: inline-block;'><select id='alpn_selector_topic_type' class='alpn_selector'>{$selectHtml}</select></span>
				<span style='display: inline-block;'><i class='far fa-plus-circle alpn_icons' title='Add Topic' onclick='alpn_mission_control(\"add_topic\");'></i></span>
			</div>
			</div>
			<div id='alpn_topic_container_left' class='alpn_topic_container_left'><select id='alpn_selector_topic_filter' class='alpn_selector'><option></option></select></div>
			";
			$html .= do_shortcode("[wpdatatable id=3]");

			$html = str_replace('table_3', 'table_topic', $html);
			$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
			$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":5,', $html);

			$html .= "</div>";

		break;

		case 'task':  //Wiscle Workflows

	 $interactionChooser = "<select id='alpn_selector_interaction_selector' class='alpn_selector_interaction_selector alpn_selector'>";

	 // if($memberFeatures['fax_1']) {   //Fax Available to all levels other than Community  //TODO make this dynamic
	 // 	$interactionChooser .= "<option value='fax' data-icon='far fa-fax'>Send as Fax</option>";
	 // }

	 $interactionChooser .= "</select>";

			$html = "";
			$html .= "<div id='alpn_section_alert'>
					 <div id='pte_interaction_outer_container'>
					 	<div id='pte_interaction_ux_message' class='pte_interaction_ux_message'>Wokflow Recalled</div>
					 	<div id='pte_interaction_current'>
 					 	</div>
					 </div>

					 <div id='pte_interaction_bar' class='pte_vault_row'>

						<div class='pte_vault_row_65'>
							<span id='wsc_flowbox_title'>Workflows <img id='interaction_wait_indicator' class='pte_refresh_report_loading' src='{$rootUrl}pdf/web/images/loading-icon.gif'></span>
						</div>
						<div class='pte_vault_row_35'>
						<div id='pte_on_off_outer'>
							 <div class='onoffswitch' title='View Active or Filed Workflows'>
								 <input onchange='pte_handle_active_filed_change(this);' type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' id='myonoffswitch' tabindex='0' checked>
								 <label class='onoffswitch-label' for='myonoffswitch'>
										 <span class='onoffswitch-inner'></span>
										 <span class='onoffswitch-switch'></span>
								 </label>
							 </div>
						 </div>
						</div>
					</div>


					<div class='wsc_workflow_selector'>
						 <div id='wsc_ww_container'>{$interactionChooser}</div><div id='wsc_ww_container_icon'><i id='alpn_vault_interaction_start' class='far fa-arrow-circle-right wsc_ww_start_icon' title='Start Workflow' onclick='pte_handle_interaction_start(this);'></i></div>
					</div>

					";
			$html .= "<div id='pte_interactions_table_container'>";

			$interactionFilterTypes = array(
				"10" => "All",
			  "20" => "For Selected Topic",
			  "30" => "Faxes Sent",
			  "40" => "Faxes Received",
			  "50" => "ProTeam Invite",
			  "60" => "Form Fill Requests"
			);

			$optionsStr = '';
			foreach ($interactionFilterTypes as $key => $value){
				//$iconStr =
				$optionsStr .= "<option value='{$key}'>{$value}</option>";
			}
			// $html .= "<div id='pte_interaction_table_filter_container' class='alpn_selector_container_left pte_hidden_for_now'><select id='pte_interaction_table_filter' class='alpn_selector'>{$optionsStr}</select></div>";
			$html .= do_shortcode("[wpdatatable id=4 var1='{$ownerNetworkId}' var2='active']");
			$html = str_replace('table_5', 'table_interactions', $html);
			$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
			$html .= "</div>";
			$html .= "</div>";

		break;

		case 'vit-chat':

			$audioOnOffHtml = "<div id='alpn_chat_audio_on_off' title='Your audio is off. Press to turn on audio.' onclick='event.stopPropagation(); var data = {\"name\": \"pte_join_audio_current_channel\"}; pte_message_chat_window(data);'><img id='pte_chat_on_off_button' class='pte_chat_title_button' src='{$rootUrl}dist/assets/button_off.png' ><div id='alpn_chat_audio_on_off_text'>OFF</div></div>";

			$audioMuteUnmuteHtml = "<div id='alpn_chat_audio_mute' title='You are muted. Press to un-mute.' onclick='event.stopPropagation(); pte_handle_mute_audio();'><img id='pte_chat_mute_button' class='pte_chat_title_button' src='{$rootUrl}dist/assets/button_off.png'><div id='alpn_chat_audio_mute_text'><i id='pte_chat_audio_icon' class='fas fa-microphone-slash'></i></div></div>";

			$html =
				"<div id='alpn_chat_panel' class='alpn_chat_panel'>
				<div id='pte_chat_dropzone'>Drop a file here to add it to this Topics File Vault and to send a link in Chat [Coming Soon!]</div>
				 	<div id='alpn_chat_title' class='alpn_chat_title'>
						<div id='alpn_chat_title_title'><div id='pte_chat_topic_label'>Discussion&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</div><div id='pte_chat_topic_name'>--</div></div>
						<div id='alpn_chat_stats'>
							<div id='alpn_chat_chat'>
								<div id='wsc_chat_important'></div>Chat  &nbsp;&nbsp;<span id='pte_chat_total_unreads'>--</span>
							</div>
							<div id='alpn_chat_audio'>
								<div id='alpn_chat_audio_status_area'><div id='wsc_audio_important'></div>Audio  &nbsp;&nbsp;<span id='pte_active_audio_channels'>--</span></div>{$audioOnOffHtml}{$audioMuteUnmuteHtml}
							</div>
						</div>
					</div>
				 	<iframe id='alpn_chat_body' class='alpn_chat_body'></iframe>
				</div>";


				// alpn_log('SETTING UP CHAT');
				// alpn_log($html);

		break;

		case 'self':

		if(!is_user_logged_in()) {
			 echo '<script>window.location.href = "./my-account";</script>';
			 exit;
		}

			$hideMissionControl = "";
			$userRoles =$userInfo->roles;
			$isAdmin = in_array('administrator', $userRoles);
			$isContributor = in_array('contributor', $userRoles);
			$isSubscriber = in_array('subscriber', $userRoles);
			if ($isSubscriber) {
					$hideMissionControl = "
					<script>
						jQuery('article#post-859 div.entry-content').html(\"<div class='pte_membership_message'>Please contact Angela or Patrick to access Mission Control.</div>\");
					</script>
					";			}

			if ($fullAvatarUrl) {
				$profileImage = "<img id='user_profile_image' src='{$fullAvatarUrl}' style='height: 32px; width: 32px; border-radius: 50%;'>";
			} else {
				$profileImage = "<i class='far fa-address-card alpn_icon_left' style='font-size: 20px;  line-height: 34px;' title='About Me'></i>";
			}

			$html .= "
			<div class='alpn_user_outer' onclick='alpn_mission_control(\"select_by_mode\", \"{$domId}\");'>
				<div id='alpn_me_field'>
					<div id='alpn_field_{$domId}' class='alpn_user_container' data-uid='{$domId}' data-topic-id='{$userTopicId}' data-nm='{$isNewMember}'>
						Personal
					</div>
					<div id='alpn_me_icon' style='float: right; max-height: 34px;'>
							{$profileImage}
					</div>
					<div style='clear: both;'></div>
				</div>
			</div>
			{$hideMissionControl}
			";
			break;


		default:
			$html = "<div class='alpn_section_head'>Unknown Shortcode</div>";
    }


return $html;

}

add_shortcode('alpn_network', 'db_shortcode');


function simple_shortcode($data) {

	global $wpdb;
	$html = "";

		extract(shortcode_atts(array(
			'operation' => 'operation',
			'option_1' => 'option_1',
			'option_2' => 'option_2'
		), $data));

		switch ($operation) {

			case 'one_click_register':

				$email = $_GET['id'];

				$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

				if (preg_match($pattern, $email) === 1) {
					$userInfo = get_user_by( 'email', $email );

					if (!isset($userInfo->data->ID)) {

						$regData = $wpdb->get_results(
							$wpdb->prepare("SELECT id, last_name, first_name FROM alpn_approved_registrations WHERE email = %s", $email)
						 );
						if (isset($regData[0])) {

							 $registrationFirstName = $regData[0]->first_name;
							 $registrationLastName = $regData[0]->last_name;
							 $userName = pte_get_short_id();
							 $args = array(
								 "first_name" => $registrationFirstName,
								 "last_name" => $registrationLastName,
								 "role" => "contributor"
							 );
							 $result = wc_create_new_customer( $email, $userName, "", $args );

							 $user = get_user_by( 'email', $email );
							 $user->remove_role( 'subscriber' );
							 $user->add_role( 'contributor' );

							 $displayMessage = "Please check your email inbox/spam for your password:&nbsp; {$email}";
						 } else {
							 $displayMessage = "This email address was not found in our pregistrations. Please contact Angela or Patrick.";
						 }
						} else {
							$displayMessage = "Member Already Exists.";
						}
					} else {
						$displayMessage = "Invalid Email Format.";
					}

				$html = "<div class='pte_membership_message'>{$displayMessage}</div>";
			break;

			case 'token_stats':
				$html = "
					<div id='kt-info-box_27f7c6-d3' class='wp-block-kadence-infobox wsc_community_job'>
					<div class='kt-blocks-info-box-link-wrap kt-blocks-info-box-media-align-top kt-info-halign-left wsc_wcl_stats'>
					<div class='kt-infobox-textcontent'>
						<div class='wsc_stats_box'>
							<div class='pte_vault_row_100 wsc_centered wsc_opportunity_title'>
							💰WCL Token Stats
							</div>
						</div>

						<div class='wsc_stats_box'>
							<div class='pte_vault_row_20'>Supply</div>
							<div class='pte_vault_row_30 pte_vault_centered'>10,000,000</div>
							<div class='pte_vault_row_20 no_padding'>Circulation</div>
							<div class='pte_vault_row_30 pte_vault_centered'>3,000</div>
						</div>
						<div class='wsc_stats_box'>
							<div class='pte_vault_row_20'>Locked</div>
							<div class='pte_vault_row_30 pte_vault_centered'>2,400,000</div>
							<div class='pte_vault_row_20'>Value</div>
							<div class='pte_vault_row_30 pte_vault_centered'>Check Back Soon</div>
						</div>
						<div class='wsc_stats_box'>
							<div class='pte_vault_row_20'></div>
							<div class='pte_vault_row_30 pte_vault_centered'></div>
							<div class='pte_vault_row_20'>Liquidity</div>
							<div class='pte_vault_row_30 pte_vault_centered'>Check Back Soon</div>
						</div>

					</div>
					</div>
					</div>
				";
			break;

			case 'individual_job':
				$status = 'active';
				$rewardDetails = array();
				$jobId = (isset($_GET['id']) && $_GET['id']) ? $_GET['id'] : false;

				if ($jobId) {
					$job = $wpdb->get_results(
						$wpdb->prepare("SELECT * FROM alpn_postings WHERE id = %d;", $jobId)
					);

					if (isset($job[0])) {
						$jobData = $job[0];
						$jobTitle = $jobData->title;
						$jobDescription = $jobData->description;
						$jobIcon_id = $jobData->icon_id;
						$jobRewards = json_decode($jobData->rewards);
						$jobAvailable = $jobData->available;
						$jobStatus = $jobData->status;

						$rewardString = "(" . implode(",", $jobRewards) . ")";
						$rewardResults = $wpdb->get_results("SELECT * FROM alpn_rewards WHERE id IN {$rewardString};");
						foreach ($rewardResults as $key => $value) {
							$rewardDetails[$value->id] = $value;
						}

						$rewardHtml = '';

						if (is_user_logged_in()) {
							$formHtml = "As an existing member, please contact Angela in chat to discuss this opportunity.";
						} else {
							$formHtml = do_shortcode('[wpforms id="8192"]');
						}

						foreach ($jobRewards as $key) {
							if (isset($rewardDetails[$key])) {
								$uType = ucfirst($rewardDetails[$key]->type);
								$rewardHtml .= "
								<table class='wsc_reward_table'>
									<tr class='wsc_reward_row_first'>
										<td class='wsc_reward_col_left'>$uType</td>
										<td class='wsc_reward_col_center'>{$rewardDetails[$key]->title}</td>
										<td class='wsc_reward_col_right'>💰WCL&nbsp; {$rewardDetails[$key]->reward_token}</td>
									</tr>
									<tr class='wsc_reward_row'>
										<td class='wsc_reward_col' colspan='3'>
											<ol class='wsc_rewards_list'>
												{$rewardDetails[$key]->description}
											</ol>
										</td>
									</tr>
								</table>
								";
							}
						}
						$html = "
							<div id='kt-info-box_27f7c6-d3' class='wp-block-kadence-infobox wsc_community_job'>
							<div class='kt-blocks-info-box-link-wrap kt-blocks-info-box-media-align-top kt-info-halign-left'>
							<div class='kt-infobox-textcontent'>
							<h3 class='kt-blocks-info-box-title'>{$jobTitle} ({$jobAvailable})</h3>
							<p class='kt-blocks-info-box-text'>{$jobDescription}</p>
							<div class='kt-blocks-info-box-learnmore-wrap'>
								<div class='wsc_job_signup'>
									{$formHtml}
								</div>
							</div>
							<div class='wsc_opportunity_title'>Available:</div>
							<div class='wcl_reward_container'>{$rewardHtml}</div>
							</div>
							</div>
							</div>
						";
					}

				}
			break;

			case 'job_board':

			$status = 'active';
			$rewards = array();
			$rewardDetails = array();

			$jobs = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM alpn_postings WHERE status = %s ORDER BY display_order;", $status)
			);
			if (isset($jobs[0])) {
				foreach($jobs as $key => $value) {
					$rewards = array_merge($rewards, json_decode($value->rewards));
				}
				$rewards = array_unique($rewards);
				$rewardString = "(" . implode(",",$rewards) . ")";
				$rewardResults = $wpdb->get_results("SELECT * FROM alpn_rewards WHERE id IN {$rewardString};");

				foreach ($rewardResults as $key => $value) {
					$rewardDetails[$value->id] = $value;
				}
				foreach ($jobs as $key => $value) {
					$rewardHtml = '';
					foreach (json_decode($value->rewards) as $key2) {
						if (isset($rewardDetails[$key2])) {
							$uType = ucfirst($rewardDetails[$key2]->type);
							$rewardHtml .= "
							<table class='wsc_reward_table'>
							  <tr class='wsc_reward_row'>
								<td class='wsc_reward_col_left'>$uType</td>
							    <td class='wsc_reward_col_center'>{$rewardDetails[$key2]->title}</td>
							    <td class='wsc_reward_col_right'>💰{$rewardDetails[$key2]->reward_token}</td>
							  </tr>
							</table>
							";
						}
					}

					$html .= "
					<div id='kt-info-box_27f7c6-d3' class='wp-block-kadence-infobox wsc_community_job'>
					<div class='kt-blocks-info-box-link-wrap kt-blocks-info-box-media-align-top kt-info-halign-left'>
					<div class='kt-infobox-textcontent'>
					<h3 class='kt-blocks-info-box-title'>{$value->title} ({$value->available})</h3>
					<p class='kt-blocks-info-box-text'>{$value->description}</p>
					<div class='wcl_reward_container'>{$rewardHtml}</div>
					<div class='kt-blocks-info-box-learnmore-wrap'>
						<a class='kt-blocks-info-box-learnmore info-box-link' href='https://wiscle.com/job?id={$value->id}'>I'm Interested</a>
					</div>
					</div>
					</div>
					</div>
					";
				}

			}
			break;

			case 'logged_status':
				if (is_user_logged_in()) {
					$html = $option_1;
				} else {
					$html = $option_2;
				}
			break;

			case 'admin_check':

				if (is_user_logged_in()) {
					$userInfo = wp_get_current_user();
					$userRoles =$userInfo->roles;
					$isAdmin = in_array('administrator', $userRoles);
					$isContributor = in_array('contributor', $userRoles);
					$isSubscriber = in_array('subscriber', $userRoles);

					$optionOneIsShortcode = (substr($option_1, 0, 1) == "*") && (substr($option_1, -1) == "*") ? true : false;
					$optionTwoIsShortcode = (substr($option_2, 0, 1) == "*") && (substr($option_2, -1) == "*") ? true : false;

					if ($isAdmin) {
						if ($optionOneIsShortcode) {
							$html = do_shortcode("[" . substr($option_1, 1, strlen($option_1) - 2 )  . "]");
						} else {
							$html = $option_1;
						}
					} else {
						if ($optionTwoIsShortcode) {
							$html = do_shortcode("[" . substr($option_2, 1, strlen($option_2) - 2 )  . "]");
						} else {
							$html = $option_2;
						}
					}
				}

			break;

		}

	return $html;

}
add_shortcode('vitriva', 'simple_shortcode');



?>
