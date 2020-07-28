<?php

function usernetwork_shortcode($attr) {

	global $wpdb, $wpdb_readonly; //Wordpress DB Access

    extract(shortcode_atts(array(
        'block_type' => 'block_type'
    ), $attr));

    $html = "";

	$templateDirectory = get_template_directory_uri();

	if (is_user_logged_in()) {  //only adds it one time
		$userInfo = wp_get_current_user();
		$userID = $userInfo->data->ID;
		$userDisplayName = $userInfo->data->display_name;
		$ownerNetworkId = get_user_meta( $userID, 'pte_user_network_id', true ); //Owners Topic ID

		$topicTypeId = '5';
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT dom_id, id, image_handle, name, sync_id FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '%s';", $userID, $topicTypeId)
		);

		if (isset($results[0])) {
			$userTopicId = $results[0]->id;
			$userImageHandle = $results[0]->image_handle;
			$domId = $results[0]->dom_id;
			$userName = $results[0]->name;
			$syncId = $results[0]->sync_id;
		}

	} else {
		$userTopicId = 0;
		$userID = 0;
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

    switch ($block_type) {

			case 'chrome':

			$results = $wpdb_readonly->get_results(
				$wpdb_readonly->prepare(
					"SELECT id, name, '0' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '5' UNION
					 SELECT id, name, '1' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '4' UNION
					 SELECT id, name, '2' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id NOT IN ('4', '5')
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
							<img src='https://proteamedge.com/wp-content/themes/memberlite-child-master/pte_logo_extension.png'>
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
				alpn_user_displayname = '{$userName}';
				alpn_avatar_baseurl = '{$avatarUrl}';
				alpn_avatar_handle = '{$userImageHandle}';
				alpn_avatar_url = '{$fullAvatarUrl}';
				alpn_templatedir = '{$templateDirectory}-child-master/';
				</script>
			";

			break;
      case 'network':

			$businessTypes = get_custom_post_items('pte_profession', 'ASC');
			$optionsStr = $iconStr = '';
			foreach ($businessTypes as $key => $value){
				//$iconStr =
				$optionsStr .= "<option value='{$key}'>{$value}</option>";
			}

			$html = "<div id='alpn_section_network'><div class='alpn_title_bar'><div class='alpn_section_head_left'>Network Contacts</div><div class='alpn_section_head_right'><i class='far fa-plus-circle alpn_icons' title='Add Network Contact' onclick='alpn_mission_control(\"add_topic\", \"\", \"4\");'></i></div></div>
			<div id='alpn_selector_container_left' class='alpn_selector_container_left'><select id='alpn_selector_network' class='alpn_selector'><option></option>{$optionsStr}</select></div>
			";
			$html .= do_shortcode("[wpdatatable id=2]");

			//Hack the HTML -- TODO Find better way. Move to passing in array rather than one per line.
			//$html = str_replace('table_1', 'table_vault', $html);

			$html = str_replace('table_1', 'table_network', $html);
			$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
			$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":5,', $html);

			$html .= "</div>";

			if ($userID) {  // one time

						$html .= "<script>
					   pte_chrome_extension = false;
					   alpn_user_id = {$userID};
						 alpn_sync_id = '{$syncId}';
						 alpn_user_topic_id = {$userTopicId};
						 alpn_user_displayname = '{$userName}';
						 alpn_avatar_baseurl = '{$avatarUrl}';
						 alpn_avatar_handle = '{$userImageHandle}';
						 alpn_avatar_url = '{$fullAvatarUrl}';
						 alpn_templatedir = '{$templateDirectory}-child-master/';
						 </script>";
			} else {
				$html .= "<script>alpn_user_id = 0;</script>";
			}

        break;
		case 'topic':

			$selectHtml = "";
			$status = 'active';

			if ($userID) {
				try {
					$results = $wpdb->get_results(
						$wpdb->prepare("SELECT id, name, icon FROM alpn_topic_types WHERE topic_state = %s ORDER BY name ASC;", $status)
					);
					if (count($results)) {
						foreach ($results as $key => $value) {
							$selectHtml .= "<option value='{$value->id}' data-icon='{$value->icon}'>{$value->name}</option>";
					}
					}
				}
				catch(Exception $e) {
					pp($e);
				}
			}

			$html = "
			<div id='alpn_section_topic'>
			<div class='alpn_title_bar'>
			<div class='alpn_section_head_left'>
				Topics
			</div>
			<div class='alpn_section_head_right'>
				<span style='display: inline-block;'><select id='alpn_selector_topic_type' class='alpn_selector_topic_type'>{$selectHtml}</select></span>
				<span style='display: inline-block;'><i class='far fa-plus-circle alpn_icons' title='Add Topic' onclick='alpn_mission_control(\"add_topic\");'></i></span>
			</div>
			</div>
			<div id='alpn_topic_container_left' class='alpn_topic_container_left'><select id='alpn_selector_topic_filter' class='alpn_selector'><option></option></select></div>
			";
			$html .= do_shortcode("[wpdatatable id=3]");

		  //Hack the HTML -- TODO Find better way. Move to passing in array

			$html = str_replace('table_3', 'table_topic', $html);
			$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
			$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":5,', $html);

			$html .= "</div>";

		break;

		case 'task':

			$html = "<div id='alpn_section_alert'>
								<div class='alpn_title_bar' style='background-color: transparent; margin-bottom: 5px;'>
									<div class='alpn_section_head_left'>Interactions</div>
									<div class='alpn_section_head_right'>
										<i class='far fa-repeat-alt' style='color: #3172B6; margin-right: 5px;'></i>18
										<i class='far fa-pause-circle' style='color: #3172B6; margin-left: 15px;'></i>
									</div>
							 </div>
							 <div id='pte_interaction_outer_container'>
							 <div id='pte_interaction_card_importance_editor'></div>
							 <div id='pte_interaction_current'>

		 					 </div>
							 </div>
							 <div id='pte_interactions_table_container'>
					";
			$html .= do_shortcode("[wpdatatable id=4 var1='{$ownerNetworkId}' var2='active']");


		//Hack the HTML -- TODO Find better way. Move to passing in array

			$html = str_replace('table_5', 'table_interactions', $html);
			$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
			$html .= "</div></div>";

		break;

		case 'chat':
			$html =
				"<div id='alpn_chat_panel' class='alpn_chat_panel'>
				 	<div id='alpn_chat_title' class='alpn_chat_title'>
						<div id='alpn_chat_title_left' class='alpn_chat_title_left'>
						</div>
						<div id='alpn_chat_title_right' class='alpn_chat_title_right'>
						</div>
				 	</div>
				 	<iframe id='alpn_chat_body' class='alpn_chat_body'></iframe>
				</div>";
		break;

		case 'self':

		/* Messaging Test
			$message = array(
					"title" => "Hey There Dude!",
					"body" => "Hello, World! Welcome to PTE Messaging.",
					"send_level" => "blue"
				);

			pte_send_message("12", $message);

*/
			if ($fullAvatarUrl) {
				$profileImage = "<img id='user_profile_image' src='{$fullAvatarUrl}' style='height: 34px; width: 34px; border-radius: 50%;'>";
			} else {
				$profileImage = "<i class='far fa-address-card alpn_icon_left' style='font-size: 1.3em;  vertical-align: middle;' title='About Me'></i>";
			}

			$html .= "
			<div class='alpn_user_outer' onclick='alpn_mission_control(\"select_by_mode\", \"{$domId}\");'>
				<div id='alpn_me_field'>
					<div id='alpn_field_{$domId}' class='alpn_user_container' data-uid='{$domId}' data-topic-id='{$userTopicId}' style='float: left; vertical-align: middle; line-height: 34px;'>
						Personal
					</div>
					<div id='alpn_me_icon' style='float: right; max-height: 34px;'>
							{$profileImage}
					</div>
					<div style='clear: both;'></div>
				</div>
			</div>";
			break;


		default:
			$html = "<div class='alpn_section_head'>Unknown Shortcode</div>";
    }


return $html;

}

add_shortcode('alpn_network', 'usernetwork_shortcode');


?>
