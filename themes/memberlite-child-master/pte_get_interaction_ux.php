<?php
require 'vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Bueltge\Marksimple\Marksimple;
use Abraham\TwitterOAuth\TwitterOAuth;


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$siteUrl = get_site_url();

$qVars = $_POST;
$processId = isset($qVars['process_id']) ? $qVars['process_id'] : '';

$html = "";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $userID, 'pte_user_network_id', true ); //Owners Topic ID

if ($processId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT ux_meta, priority, state FROM alpn_interactions WHERE process_id = %s AND owner_network_id = %s;", $processId, $ownerNetworkId)
	);

	if (isset($results[0])) {
		$uxMeta = json_decode($results[0]->ux_meta, true);
		$uxMeta['priority'] = $results[0]->priority;
		$uxMeta['state'] = $results[0]->state;
   	$html = pte_make_interaction_editor_ux($uxMeta);
	}
}
echo $html;

//end

function pte_make_interaction_editor_ux($uxMeta) {

	$InteractionTemplateNameSize = 10;
//	alpn_log('starting pte_make_interaction_editor_ux');

	$widgetTypeId  = isset($uxMeta['widget_type_id']) ? $uxMeta['widget_type_id'] : '';
	$interactionTypeName =	isset($uxMeta['interaction_type_name']) ? $uxMeta['interaction_type_name'] : "";
	$interactionTypeStatus =	isset($uxMeta['interaction_type_status']) ? $uxMeta['interaction_type_status'] : "";
	$interactionTemplateName =	isset($uxMeta['interaction_template_name']) && $uxMeta['interaction_template_name'] ? substr($uxMeta['interaction_template_name'], 0, $InteractionTemplateNameSize) . " - " : "";

	$interactionTypeNameStatus = "{$interactionTypeName} - {$interactionTemplateName}{$interactionTypeStatus}";

	$priority =	isset($uxMeta['priority']) ? $uxMeta['priority'] : 0;
	$processId = 	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";
	$processTypeId = 	isset($uxMeta['process_type_id']) ? $uxMeta['process_type_id'] : "";
	$interactionState = 	isset($uxMeta['state']) ? $uxMeta['state'] : "";
	$buttons = 	isset($uxMeta['buttons']) ? $uxMeta['buttons'] : "";

	$interactionComplete = 	isset($uxMeta['interaction_complete']) ? $uxMeta['interaction_complete'] : false;

	$fileAwayButtonState = $buttons['file'] && $uxMeta['state'] == 'active' ? "pte_ipanel_button_enabled" : "pte_ipanel_button_disabled";

	$html = "
			<div id='pte_interaction_information_title'>
				<div class='pte_interaction_type_name_status'>{$interactionTypeNameStatus}</div>
				<div id='pte_priority_container'>
					<i data-pid='{$processId}' onclick='event.stopPropagation(); pte_handle_file_away(this);' class='far fa-sparkles pte_interaction_editor_button {$fileAwayButtonState}' title='File Interaction Away'></i></div>
		";

	if ($interactionComplete) {
		$interactionPriorityClass = "pte_interaction_complete_editor_normal";
		$interactionPriorityBorderClass = "pte_importance_progress_bg_done_editor_normal";

		if ($priority > 2) {
			$interactionPriorityClass = "pte_interaction_complete_editor_important";
			$interactionPriorityBorderClass = "pte_importance_progress_bg_done_editor_important";
		}
		$html .= "<div class='pte_importance_progress_bg_done_editor {$interactionPriorityBorderClass}'><i class='far fa-check pte_interaction_complete_editor {$interactionPriorityClass}'></i></div>";
	}

	$html .="
			</div>
			</div>
			<div id='pte_interaction_information_panel' data-pid='{$processId}'>
			";

	switch ($widgetTypeId) {  //TODO generalize to make widgets and interactions more extensible

		case 'proteam_invitation_received':
			$html .= pte_make_invitation_received_panel($uxMeta);
		break;
		case 'deploy_contract':
			$html .= pte_make_smart_contract_panel($uxMeta);
		break;
    case 'twitter_actions':
      $html .= pte_make_twitter_actions_panel($uxMeta);
    break;
		case 'mint_nft':
			$html .= pte_make_mint_nft_panel($uxMeta);
		break;
		case 'fax_send':
		case 'topic_team_invite':
		case 'email_send':
		case 'sms_send':
			$html .= pte_make_send_panel($uxMeta);
		break;
		case 'waiting':
		case 'information':
			$html .= pte_make_info_panel($uxMeta);
		break;
	}

	$html .= "</div>
	<script>
		function pte_handle_send_interaction(theObj) {
				var jObj = jQuery(theObj);
				var buttonOperation = jObj.data('pteop');
				//validate here
				switch(buttonOperation) {
					case 'send_fax':
						var phoneNumber = jQuery('#pte_fax_send_input_field_fax_number').val().replace(/\D/g,'');
						phoneNumber = (phoneNumber.length == 11) ?  phoneNumber : '1' + phoneNumber;
						if (phoneNumber.length != 11) {
							console.log('ERROR', phoneNumber);
							jQuery('#pte_fax_send_error_line').html('Please enter a valid 10 or 11 digit fax number.');
							return;
						}
					break;
					case 'send_email':
					console.log('Trying to Send Email');
					var emailSelect = jQuery('#alpn_select2_small_fax_number_select');   //reused same for email as fax
					var emailSelectData = emailSelect.select2('data');
					if (emailSelectData) {
						var tId = emailSelectData[0].id;
						if (!tId) {
							console.log('ERROR');
							jQuery('#pte_fax_send_error_line').html('Please select a contact with an email address.');
							return;
						}
					}
					break;
					case 'send_sms':
					console.log('Trying to Send SMS');
					var emailSelect = jQuery('#alpn_select2_small_fax_number_select');   //reused same for email as fax
					var emailSelectData = emailSelect.select2('data');
					if (emailSelectData) {
						var tId = emailSelectData[0].id;
						if (!tId) {
							console.log('ERROR');
							jQuery('#pte_fax_send_error_line').html('Please select a contact with a mobile phone.');
							return;
						}
					}
					break;
				}
				var sendData = {
					'button_operation': buttonOperation,
					'process_id': '{$processId}',
					'process_type_id': '{$processTypeId}'
				};

				//Start interaction wait. updating interaction ux is where it gets turned off. TODO make sure this always works.
				var alpnSectionAlert = jQuery('#alpn_section_alert');
				var interactionWaitIndicator = jQuery('#interaction_wait_indicator');
				alpnSectionAlert.css('pointer-events', 'none');
				interactionWaitIndicator.show();

				pte_handle_widget_interaction(sendData);
		}
	</script>
	";
  return $html;
}

function pte_make_button_line($lineType, $uxMeta) {

	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : 0;
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : 0;
	$ownerId = 	isset($uxMeta['owner_id']) ? $uxMeta['owner_id'] : 0;
	$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true ); //Owners Topic ID
	$widgetTypeId  = isset($uxMeta['widget_type_id']) ? $uxMeta['widget_type_id'] : '';

	$html = "";
	switch ($lineType) {

		case 'deploy_contract_send':
			$html .= "
					<div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 10px 0 0 0;'>
						<span id='wsc_wait_indicator_send'></span><button id='wsc_ww_send_button' class='btn btn-danger btn-sm' onclick='wsc_handle_deploy_contract();' style='margin-right: 0; width: 120px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Deploy Contract</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
		case 'nft_mint':
			$html .= "
					<div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 10px 0 0 0;'>
						<span id='wsc_wait_indicator_send'></span><button id='wsc_ww_resend_button' class='btn btn-danger btn-sm' onclick='wsc_handle_resend_nft();' style='margin-right: 0; width: 120px; height: 24px; font-size: 12px; margin-bottom: 6px;'>Mint</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
		case 'nft_send':
			$mediaReady = (!isset($uxMeta['nft_ipfs_files']) || isset($uxMeta['nft_token_uri'])) ? "pte_ipanel_button_disabled" : "";
			$html .= "
					<div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 10px 0 0 0;'>
						<span id='wsc_wait_indicator_send'></span><button id='wsc_ww_send_button' class='btn btn-danger btn-sm {$mediaReady}' onclick='wsc_handle_send_nft();' style='margin-right: 0; width: 120px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Finalize and Mint</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
    case 'twitter_send':
      $html .= "
          <div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 10px 0 0 0;'>
            <span id='wsc_wait_indicator_send'></span><button id='wsc_ww_send_button' class='btn btn-danger btn-sm' onclick='wsc_ww_send_twitter();' style='margin-right: 0; width: 65px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Send</button>
          </div>
          <div style='clear: both;'></div>
      ";
    break;
		case 'link_settings':
			if ($widgetTypeId != "fax_send"){
				$expirationSelectHtml = pte_make_link_expiration_html();
				$linkOptions = pte_make_link_options_html();
				$html .= "
					<div id='pte_interaction_link_options'>
						<div class='pte_vault_bold'>xLink Security Settings</div>
						<div class='pte_vault_row'>
							<div class='pte_vault_row_40 pte_vault_bold'>
								<i class='far fa-tasks pte_plain_text' title='Permissions'></i> Permissions
							</div>
							<div class='pte_vault_row_60 pte_max_width_60'>
							{$linkOptions}
							</div>
						</div>
						<div class='pte_vault_row'>
							<div class='pte_vault_row_40 pte_vault_bold'>
								<i class='far fa-stopwatch pte_plain_text' title='Expiration'></i> Expiration
							</div>
							<div class='pte_vault_row_60'>
								{$expirationSelectHtml}
							</div>
						</div>
						<div class='pte_vault_row'>
							<div class='pte_vault_row_40 pte_vault_bold'>
								<i class='far fa-key pte_plain_text' title='Passcode'></i> Passcode
							</div>
							<div class='pte_vault_row_60'>
								<input id='link_interaction_password' class='pte_interaction_input' type='input' placeholder='No Passcode'>
							</div>
						</div>
					</div>
				";
			}
		break;
		case 'select_send':
			$templates  = isset($uxMeta['templates']) ? $uxMeta['templates'] : array();
			$sendKey = isset($uxMeta['fax_key']) ? 'send_fax' : 'send';
			$sendKey = isset($uxMeta['email_key']) ? 'send_email' : 'send';
			$sendKey = isset($uxMeta['sms_key']) ? 'send_sms' : 'send';
			$sendKey = isset($uxMeta['topic_invite_key']) ? 'send_invite' : 'send';
			if (count($templates)) {
				$selectPanel = "<select id='alpn_select2_small_template_select' class='' data-topic-id='{$topicId}'>";
				$selectPanel .= "<option value='0'>Select Template</option>";
				foreach ($templates as $key => $value){
					$id = $value['id'];
					$description = $value['short_description'];
					$defaultItem = $value['default_item'];
					$selected = $defaultItem == true ? " SELECTED " : "";
					$selectPanel .= "<option value='{$id}' {$selected}>{$description}</option>";
				}
				$selectPanel .= "</select>";
			} else {
				$selectPanel = "&nbsp";
			}


			$html .= "
					<div style='float: left; width: calc(100% - 60px); margin-top: 5px'>
						<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
					</div>
					<div style='float: right; width: 50px; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 0;'>
						<button data-pteop='{$sendKey}' id='pte_message_panel_send' class='btn btn-danger btn-sm pte_always_enabled' onclick='pte_handle_send_interaction(this);' style='margin-right: 0; width: 45px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Send</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
		case 'update':
			$html .= "
					<div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 0;'>
						<button data-pteop='recall' id='pte_message_panel_recall' class='btn btn-danger btn-sm' onclick='pte_handle_send_interaction(this);' style='margin-right: 0; width: 65px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Recall</button>
						<button data-pteop='update' id='pte_message_panel_update' class='btn btn-danger btn-sm' onclick='pte_handle_send_interaction(this);' style='margin-right: 0; width: 65px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Update</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
		case 'accept_decline':
			$html .= "
				<div id='pte_interaction_response_inner_buttons_container'>
						<button data-pteop='accept' id='pte_message_panel_accept' class='btn btn-danger btn-sm pte_interaction_response_inner_buttons' onclick='pte_handle_send_interaction(this);'>Accept</button>
						<button data-pteop='decline' id='pte_message_panel_decline' class='btn btn-danger btn-sm pte_interaction_response_inner_buttons' onclick='pte_handle_send_interaction(this);'>Decline</button>
				</div>
			";
		break;
		case 'select_topics_with_mobile_numbers':
			$mobileNumbers  = isset($uxMeta['mobile_numbers']) ? $uxMeta['mobile_numbers'] : array();
			$selectPanel = "<select id='alpn_select2_small_fax_number_select' data-topic-id='{$topicId}'>";
			$selectPanel .= "<option value='0'>Select Recipient</option>";
			foreach ($mobileNumbers as $key => $value){
				$tId = $value['id'];
				$tText = $value['text'];
				$selectPanel .= "<option value='{$tId}'>{$tText}</option>";
			}
			$selectPanel .= '</select>';
			$html .= "
			<div style='width: 100%; padding-top: 5px;'>
				<div style='float: left; width: 35px; margin: 0; font-weight: bold; height: 18px; line-height: 18px;'>
					To
				</div>
				<div style='float: right; width: calc(100% - 35px); height: 18px; line-height: 18px;'>
					<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
				</div>
				<div style='clear: both;'></div>
			</div>
			";
		break;

		case 'select_topics_with_email_addresses':
			$emailAddresses  = isset($uxMeta['email_addresses']) ? $uxMeta['email_addresses'] : array();
			$selectPanel = "<select id='alpn_select2_small_fax_number_select' class='' data-topic-id='{$topicId}'>";
			$selectPanel .= "<option value='0'>Select Recipient</option>";
			foreach ($emailAddresses as $key => $value){
				$tId = $value['id'];
				$tText = $value['text'];
				$selectPanel .= "<option value='{$tId}'>{$tText}</option>";
			}
			$selectPanel .= '</select>';
			$html .= "
			<div style='width: 100%; padding-top: 5px;'>
				<div style='float: left; width: 35px; margin: 0; font-weight: bold; height: 18px; line-height: 18px;'>
					To
				</div>
				<div style='float: right; width: calc(100% - 35px); height: 18px; line-height: 18px;'>
					<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
				</div>
				<div style='clear: both;'></div>
			</div>
			";
		break;

		case 'select_person_topics':
			$inviteList  = isset($uxMeta['invite_send_to_list']) ? $uxMeta['invite_send_to_list'] : array();
			$selectPanel = "<select id='alpn_select2_small_fax_number_select' class='' data-topic-id='{$topicId}'>";
			$selectPanel .= "<option value='0'>Select Recipient</option>";
			foreach ($inviteList as $key => $value){
				$tId = $value['id'];
				$tText = $value['text'];
				$selectPanel .= "<option value='{$tId}'>{$tText}</option>";
			}
			$selectPanel .= '</select>';
			$html .= "
			<div style='width: 100%; padding-top: 5px;'>
				<div style='float: left; width: 35px; margin: 0; font-weight: bold; height: 18px; line-height: 18px;'>
					To
				</div>
				<div style='float: right; width: calc(100% - 35px); height: 18px; line-height: 18px;'>
					<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
				</div>
				<div style='clear: both;'></div>
			</div>
			";
		break;

		case 'select_users_with_fax_numbers':
			$networkContacts = $topicContacts = '';
			$faxNumbers  = isset($uxMeta['fax_numbers']) ? $uxMeta['fax_numbers'] : array();
			$faxNumbersById = array();
			$selectPanel = "<select id='alpn_select2_small_fax_number_select' class='' data-topic-id='{$topicId}'>";
			$selectPanel .= "<option value='0'>Select Fax Recipient...</option>";
			foreach ($faxNumbers as $key => $value){
				$id = $value['id'];
				$name = $value['name'];
				$topicContent = $value['topic_content'];
				$firstName = isset($topicContent['person_givenname']) ? $topicContent['person_givenname'] : '';
				$lastName = isset($topicContent['person_familyname']) ? $topicContent['person_familyname'] : '';
				$companyName = isset($topicContent['organization_name']) ? $topicContent['organization_name'] : '';
				$faxNumber = isset($topicContent['person_faxnumber']) ? $topicContent['person_faxnumber'] : $topicContent['organization_faxnumber'];
				$selectPanel .= "<option value='{$id}'>{$name} - {$faxNumber}</option>";
				$faxNumbersById[$id]  = array(
					'first_name' => $firstName,
					'last_name' => $lastName,
					'company_name' => $companyName,
					'fax_number' => $faxNumber
				);
			}
			$selectPanel .= '</select>';
			$faxNumbersByIdEnc = json_encode($faxNumbersById);
			$html .= "
			<div style='width: 100%; padding-top: 5px; margin-bottom: 5px;'>
				<div style='float: left; width: 35px; margin: 0; font-weight: bold; height: 18px; line-height: 18px;'>
					To
				</div>
				<div style='float: right; width: calc(100% - 35px); height: 18px; line-height: 18px;'>
					<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
				</div>
				<script>
						var pteFaxNumbers = {$faxNumbersByIdEnc};
				</script>
				<div style='clear: both;'></div>
			</div>
			";
		break;
	}

	return $html;
}

function pte_make_interaction_link($linkType, $uxMeta) {

	alpn_log('pte_make_interaction_link');
	// alpn_log($linkType);
	// alpn_log($uxMeta);

	$currentDomain = PTE_HOST_DOMAIN_NAME;

	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$networkName = 	isset($uxMeta['network_name']) ? $uxMeta['network_name'] : "";
	$connectedContactStatus =	isset($uxMeta['connected_contact_status']) ? $uxMeta['connected_contact_status'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? preg_replace('/\D/', '', $uxMeta['topic_id']) : 0;   //TODO there were commas
	$topicTypeId = 	isset($uxMeta['topic_type_id']) ? $uxMeta['topic_type_id'] : "";
	$topicTypeSpecial = 	isset($uxMeta['topic_special']) ? $uxMeta['topic_special'] : "topic";
	$viewLinkFileType =	isset($uxMeta['view_link_file_type']) ? $uxMeta['view_link_file_type'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$vaultId = 	isset($uxMeta['vault_id']) ? $uxMeta['vault_id'] : 0;
	$vaultDomId = 	isset($uxMeta['vault_dom_id']) ? $uxMeta['vault_dom_id'] : 0;
	$vaultFileName = 	isset($uxMeta['vault_file_name']) && $uxMeta['vault_file_name'] ? stripslashes($uxMeta['vault_file_name']) : '';
	$vaultName = 	isset($uxMeta['vault_name']) ? $uxMeta['vault_name'] : "";
	$vaultTopicId = 	isset($uxMeta['vault_topic_id']) ? $uxMeta['vault_topic_id'] : "";
	$processId = 	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";
	$processTypeId = 	isset($uxMeta['process_type_id']) ? $uxMeta['process_type_id'] : "";
	$processFriendlyName = 	isset($uxMeta['process_friendlyname']) ? $uxMeta['process_friendlyname'] : "";
	$firstName = trim(substr($networkName, strrpos ($networkName, ",") + 1));   //TODO validate and solidify this or pass FN too...

	$topicDomId = 	isset($uxMeta['topic_dom_id']) ? $uxMeta['topic_dom_id'] : "";
	$networkDomId = 	isset($uxMeta['connected_network_dom_id']) ? $uxMeta['connected_network_dom_id'] : "";

	$linkInteractionPassword = 	isset($uxMeta['link_interaction_password']) && $uxMeta['link_interaction_password'] ? "Set" : "None";
	$linkInteractionExpiration = 	isset($uxMeta['link_interaction_expiration']) ? pte_get_link_expiration_string($uxMeta['link_interaction_expiration']) : 'Error';
	$linkInteractionOptions = 	isset($uxMeta['link_interaction_options']) ? pte_get_link_options_string($uxMeta['link_interaction_options']) : 'Error';
	$secureURL = 	isset($uxMeta['link_id']) ? "https://{$currentDomain}/viewer/?" . $uxMeta['link_id'] : 'Error';

	$viewString = $vaultFileName ? $vaultFileName : $viewLinkFileType;

	$html = "";
	switch ($linkType) {
		case 'url_panel':
			if ($vaultId) {
			$html .= "
					<div class='pte_outer_link_bar_container'>
						<div class='pte_link_bar_title pte_title_link' title='Copy Secure xLink to Clipboard.' onclick='pte_topic_link_copy_string(\"Secure xLink\", \"{$secureURL}\");'><i class='far fa-copy' style='margin-right: 5px;'></i>Secure xLink</div>
						<div class='pte_outer_link_bar_links'>
							<div class='pte_link_bar_link_all pte_link_bar_link_33'><div class='interaction_panel_row_link_no_link'><div class='pte_icon_interaction_link'><i class='far fa-tasks'></i></div>$linkInteractionOptions</div></div>
							<div class='pte_link_bar_link_all pte_link_bar_link_33'><div class='interaction_panel_row_link_no_link'><div class='pte_icon_interaction_link'><i class='far fa-stopwatch'></i></div>{$linkInteractionExpiration}</div></div>
							<div class='pte_link_bar_link_all pte_link_bar_link_33'><div class='interaction_panel_row_link_no_link'><div class='pte_icon_interaction_link'><i class='far fa-key'></i></div>{$linkInteractionPassword}</div></div>
						</div>
					</div>
					";
				}
		break;

		case 'topic_panel':
			$html .= "
					  <div class='pte_outer_link_bar_container'>
						<div class='pte_link_bar_title'>{$topicName}</div>
						<div class='pte_outer_link_bar_links'>
						<div class='pte_link_bar_link'><div data-topic-id='{$topicId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-topic-dom-id='{$topicDomId}' data-operation='to_topic_info_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>Info</div></div>
						<div class='pte_link_bar_link'><div data-topic-id='{$topicId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-topic-dom-id='{$topicDomId}' data-operation='to_topic_vault_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-lock-alt'></i></div>Vault</div></div>";
			if ($topicTypeSpecial != "user") {
				$html .= "
						<div class='pte_link_bar_link'><div data-topic-id='{$topicId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-topic-dom-id='{$topicDomId}' data-operation='to_topic_chat_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-comments'></i></div>Chat</div></div>
						<div class='pte_link_bar_link'></div>";
			} else {
				$html .= "<div class='pte_link_bar_link'></div><div class='pte_link_bar_link'></div>";
			}
			$html .= "
						</div>
					</div>
					";
		break;

		case 'personal_panel':
			$html .= "
					<div class='pte_outer_link_bar_container'>
						<div class='pte_link_bar_title'>Personal</div>
						<div class='pte_outer_link_bar_links'>
							<div class='pte_link_bar_link'><div data-topic-id='{$topicId}' data-topic-dom-id='{$topicDomId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-operation='personal_info' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>Info</div></div>
							<div class='pte_link_bar_link'><div data-topic-id='{$topicId}' data-topic-dom-id='{$topicDomId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-operation='personal_vault' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-lock-alt'></i></div>Vault</div></div>
							<div class='pte_link_bar_link'></div>
							<div class='pte_link_bar_link'></div>
						</div>
					</div>
					";
		break;
		case 'network_panel':
			if ($connectedContactStatus == 'not_connected_not_member') {
				$commFeaturesLink = "
					<div class='pte_link_bar_link'></div>
					<div class='pte_link_bar_link'></div>
				";
				$status = "<i class='far fa-user-slash' title='Not a Member'></i>";
			} else if ($connectedContactStatus == 'not_connected_member'){
				$commFeaturesLink = "
				<div class='pte_link_bar_link'></div>
				<div class='pte_link_bar_link'></div>
				";
				$status = "<i class='far fa-user' title='Member, Not Connected'></i>";
			} else {
				$commFeaturesLink = "
					<div class='pte_link_bar_link'><div data-topic-id='{$networkId}' data-topic-type-id='{$topicTypeId}' data-topic-special='contact' data-topic-dom-id='{$networkDomId}' data-operation='to_topic_chat_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-comments'></i></div>Chat</div></div>
					<div class='pte_link_bar_link'></div>
				";
				$status = "<i class='far fa-user-check' title='Member, Connected'></i>";
			}
			$html .= "
					<div class='pte_outer_link_bar_container'>
						<div class='pte_link_bar_title'>{$networkName} {$status}</div>
						<div class='pte_outer_link_bar_links'>
							<div class='pte_link_bar_link'><div data-topic-id='{$networkId}' data-topic-type-id='{$topicTypeId}' data-topic-special='contact' data-topic-dom-id='{$networkDomId}' data-operation='to_topic_info_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>Info</div></div>
							<div class='pte_link_bar_link'><div data-topic-id='{$networkId}' data-topic-type-id='{$topicTypeId}' data-topic-special='contact' data-topic-dom-id='{$networkDomId}' data-operation='to_topic_vault_by_id' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-lock-alt'></i></div>Vault</div></div>
							{$commFeaturesLink}

						</div>
					</div>
					";

		break;
		case 'vault_item':
			if ($vaultId) {
				$html .= "<div data-vault-id='{$vaultId}' data-vault-dom-id='{$vaultDomId}' data-topic-id='{$topicId}' data-topic-type-id='{$topicTypeId}' data-topic-special='{$topicTypeSpecial}' data-topic-dom-id='{$topicDomId}' data-operation='vault_item' class='interaction_panel_link' onclick='pte_handle_interaction_link_object(this);'><div class='pte_icon_interaction_link'><i class='far fa-file-pdf'></i></div>{$viewString}</div>";
			}

		break;
	}
	return $html;
}

function pte_make_data_line ($lineType, $uxMeta) {
	$html = '';
	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$networkName = 	isset($uxMeta['interaction_to_from_name']) ? $uxMeta['interaction_to_from_name'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$toFrom =	isset($uxMeta['interaction_to_from_string']) ? $uxMeta['interaction_to_from_string'] : "";
	$templateName =	isset($uxMeta['template_name']) ? $uxMeta['template_name'] : "";
	$fileName =	isset($uxMeta['file_name']) ? stripslashes($uxMeta['file_name']) : "";
	$staticName =	isset($uxMeta['interaction_to_from_name']) ? $uxMeta['interaction_to_from_name'] : "";

	$responseSelected = isset($uxMeta['button_operation']) ? ucfirst($uxMeta['button_operation']) : "";
	$responseMessage = isset($uxMeta['message_response']) && $uxMeta['message_response'] ? $uxMeta['message_response'] : "- -";

	$connectionLinkType = isset($uxMeta['connection_link_type']) ? $uxMeta['connection_link_type'] : 0;
	$connectionLinkTypeText = ($connectionLinkType > 0) ? "Linked to Topic" : "Joined Topic";

	$phoneNumber =	isset($uxMeta['fax_field_fax_number']) ? $uxMeta['fax_field_fax_number'] : "";

	switch ($lineType) {

		case 'nft_results':

			$walletError = isset($uxMeta['nft_wallet_error']) && $uxMeta['nft_wallet_error'] ? true : false;

			$mediaType = isset($uxMeta['nft_ipfs_files']) ? $uxMeta['nft_ipfs_files']['wsc_media_type'] : "";
			$nftImage = isset($uxMeta['nft_ipfs_files']['image']) ? $uxMeta['nft_ipfs_files']['image'] : "";
			$mediaUrl = isset($uxMeta['nft_ipfs_files']['image_url']) ? $uxMeta['nft_ipfs_files']['image_url'] : "";
			$mediaUrl = isset($uxMeta['nft_ipfs_files']['animation_url']) ? $uxMeta['nft_ipfs_files']['animation_url'] : $mediaUrl;
			$mediaUrl = isset($uxMeta['nft_ipfs_files']['document_url']) ? $uxMeta['nft_ipfs_files']['document_url'] : $mediaUrl;
			$mediaUrl = isset($uxMeta['nft_ipfs_files']['archive_url']) ? $uxMeta['nft_ipfs_files']['archive_url'] : $mediaUrl;
			$mediaUrl = isset($uxMeta['nft_ipfs_files']['music_url']) ? $uxMeta['nft_ipfs_files']['music_url'] : $mediaUrl;

			$nftName = isset($uxMeta['nft_name']) ? $uxMeta['nft_name'] : "";
			$nftDescription = isset($uxMeta['nft_description']) ? $uxMeta['nft_description'] : "";
			$nftRecipientAddress = isset($uxMeta['nft_recipient_id']) ? $uxMeta['nft_recipient_id'] : "";
			$nftContractAddress = isset($uxMeta['nft_contract_address']) ? $uxMeta['nft_contract_address'] : "";
			$nftTokenUri = isset($uxMeta['nft_token_uri']) ? $uxMeta['nft_token_uri'] : "";
			$nftQuantity = isset($uxMeta['nft_quantity']) ? $uxMeta['nft_quantity'] : "";
			$nftBlockChainId = isset($uxMeta['nft_blockchain']) ? $uxMeta['nft_blockchain'] : "";
			$nftTokenId = isset($uxMeta['nft_token_id']) ? $uxMeta['nft_token_id'] : "";
			$nftCertificateUrl = isset($uxMeta['nft_certificate_url']) ? $uxMeta['nft_certificate_url'] : "";

			if ($nftBlockChainId == "eth") {
				$openWalletInScan = "https://etherscan.io/address/{$nftRecipientAddress}";
				$openInScan = "https://etherscan.io/token/{$nftContractAddress}?a={$nftTokenId}";
				$openInScanName = "Etherscan";
				$openInOpenSea = "https://opensea.io/assets/{$nftContractAddress}/{$nftTokenId}";
			} else if ($nftBlockChainId == "polygon") {
				$openWalletInScan = "https://polygonscan.com/address/{$nftRecipientAddress}";
				$openInScan = "https://polygonscan.com/token/{$nftContractAddress}?a={$nftTokenId}";
				$openInScanName = "Polygonscan";
				$openInOpenSea = "https://opensea.io/assets/matic/{$nftContractAddress}/{$nftTokenId}";
			}

			$source = "https://ipfs.moralis.io:2053/ipfs/";
			$sourceLen = strlen($source);
			if (substr($mediaUrl, 0, $sourceLen) == $source) {
				$mediaUrl = "https://gateway.moralisipfs.com/ipfs/" . substr($mediaUrl, $sourceLen);
			}
			if (substr($nftImage, 0, $sourceLen) == $source) {
				$nftImage = "https://gateway.moralisipfs.com/ipfs/" . substr($nftImage, $sourceLen);
			}
			if (substr($nftTokenUri, 0, $sourceLen) == $source) {
				$nftTokenUri = "https://gateway.moralisipfs.com/ipfs/" . substr($nftTokenUri, $sourceLen);
			}

			if ($walletError) {
				$html .= pte_make_button_line("nft_mint", $uxMeta);
			}
			$html .="<div class='pte_data_line_title_wide'>Name</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$nftName}</div>";
			$html .= "<div class='pte_data_line_title_wide'>Description</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$nftDescription}</div>";
			$html .= "<div class='pte_data_line_title_wide'>Media Type</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$mediaType}</div>";
			$html .= "<div class='pte_data_line_title_wide'>Quantity</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$nftQuantity}</div>";
			$html .=  "<div class='wsc_vault_item_container'>" . pte_make_interaction_link('vault_item', $uxMeta) . "</div>";
			$html .= "<div class='pte_data_line_title_wide'>Info</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$openInScan}' target='_blank'>{$openInScanName}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Market</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$openInOpenSea}' target='_blank' title='View in OpenSea'>OpenSea</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Recipient</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$openWalletInScan}' target='_blank' title='View in {$openInScanName}'>{$nftRecipientAddress}</a></div>";
			$html .= "<div class='wsc_data_spacer_vertical'></div>";
			$html .= "<div class='pte_data_line_title_wide'>Image</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$nftImage}' target='_blank'>{$nftImage}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Media</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$mediaUrl}' target='_blank'>{$mediaUrl}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Metadata</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$nftTokenUri}' target='_blank'>{$nftTokenUri}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Certificate</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$nftCertificateUrl}' target='_blank' title='View Wiscle Certificate'>{$nftCertificateUrl}</a></div>";

		break;

		case 'twitter_results':

		$tweetId = isset($uxMeta['twitter_status_id']) ? $uxMeta['twitter_status_id'] : "";
		$twitterBase = "https://twitter.com/wisclenft/status/";

		$html = "<div class='pte_data_line_title_wide'>Tweet</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$twitterBase}{$tweetId}' target='_blank'>{$tweetId}</a></div>";
		if (isset($uxMeta['twitter_user_mentions']) && $uxMeta['twitter_user_mentions']) {
			foreach ($uxMeta['twitter_user_mentions'] as $userMention) {
				$userScreenName = $userMention['screen_name'];
				$html .= "<div class='pte_data_line_title_wide'>Mention</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='https://twitter.com/{$userScreenName}' target='_blank'>{$userScreenName}</a></div>";
			}
		}
		break;
		case 'smart_contract_address':

			$smartContractAddress = isset($uxMeta['smart_contract_address']) ? $uxMeta['smart_contract_address'] : "";
			$contractBlockChainId = isset($uxMeta['smart_contract_chain_id']) ? $uxMeta['smart_contract_chain_id'] : "";
			$contractTemplateId = isset($uxMeta['smart_contract_template_id']) ? $uxMeta['smart_contract_template_id'] : "";

			if ($contractBlockChainId == "eth") {
				$openInScan = "https://etherscan.io/token/{$smartContractAddress}";
				$openInScanName = "Etherscan";
				$openInOpenSea = "https://opensea.io/assets/{$smartContractAddress}";
			} else if ($contractBlockChainId == "polygon") {
				$openInScan = "https://polygonscan.com/token/{$smartContractAddress}";
				$openInScanName = "Polygonscan";
				$openInOpenSea = "https://opensea.io/assets/matic/{$smartContractAddress}";
			}

			$html .= "<div class='pte_data_line_title_wide'>OpenSea</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$openInOpenSea}' target='_blank'>{$openInOpenSea}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>{$openInScanName}</div><div id='pte_data_line_value' class='pte_data_line_value_wide'><a class='pte_topic_link' href='{$openInScan}' target='_blank'>{$openInScan}</a></div>";
			$html .= "<div class='pte_data_line_title_wide'>Blockchain</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$contractBlockChainId}</div>";
			$html .= "<div class='pte_data_line_title_wide'>Contract</div><div id='pte_data_line_value' class='pte_data_line_value_wide'>{$contractTemplateId}</div>";
		break;
		case 'to_from_line':
			$html = "<div class='pte_data_line_title'>{$toFrom}</div><div id='pte_to_line' class='pte_data_line_value' data-cid='{$networkId}'>{$networkName}</div>";
		break;
		case 'to_from_line_static':
			$html = "<div class='pte_data_line_title'>{$toFrom}</div><div class='pte_data_line_value'>{$staticName}</div>";
		break;
		case 'to_recipient_number_line':
			$html = "<div class='pte_data_line_title'>To</div><div class='pte_data_line_value'>{$phoneNumber}</div>";
		break;
		case 'from_sender_number_line':
			$html = "<div class='pte_data_line_title'>From</div><div class='pte_data_line_value'>{$phoneNumber}</div>";
		break;
		case 'regarding_line':
			$html = "<div class='pte_data_line_title'>Re</div><div class='pte_data_line_value'>{$topicName}</div>";
		break;
		case 'response_selected':
			$html = "<div class='pte_data_line_title'>Option</div><div class='pte_data_line_value'>{$responseSelected}</div>";
		break;
		case 'separator':
		$html = "<div class='pte_data_line_title pte_interaction_separator'>&nbsp;</div><div class='pte_data_line_value pte_interaction_separator'>&nbsp;</div>";
		break;
		case 'response_message':
			$html = "<div class='pte_data_line_title'>Note</div><div class='pte_data_line_value'>{$responseMessage}</div>";
		break;
		case 'connect_type':
			if ($uxMeta['button_operation'] != 'decline') {
				$html = "<div class='pte_data_line_title'>Type</div><div class='pte_data_line_value'>{$connectionLinkTypeText}</div>";
			}
		break;
		case 'file_name':
			$html = "<div class='pte_data_line_title'>Name</div><div class='pte_data_line_value'>{$fileName}</div>";
		break;
	}
	return $html;
}

function pte_make_message_line ($lineType, $uxMeta) {

	$html= "";
	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$networkName = 	isset($uxMeta['network_name']) ? $uxMeta['network_name'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$templateName =	isset($uxMeta['template_name']) ? $uxMeta['template_name'] : "";
	$processId =	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";

	$messageTitle =	isset($uxMeta['message_title']) && $uxMeta['message_title'] ? pte_encode_value(rawurldecode($uxMeta['message_title'])) : "-";
	$messageBody =	isset($uxMeta['message_body']) && $uxMeta['message_body'] ? $uxMeta['message_body'] : "-";

	if (isset($uxMeta['updated_date']) && $uxMeta['updated_date']) {
		$updatedString =	pte_date_to_js($uxMeta['updated_date'], "Updated: ");
		$messageVisibilty = "block";
	} else {
		$updatedString =	"";
		$messageVisibilty = "none";
	}

	$sendType = "<span class='pte_internal_link' data-topic-id='{$topicId}' data-operation='topic_info' onclick='pte_handle_interaction_link_object(this);'>{$topicName}</span>"; //TODO this needs to change based on type

	switch ($lineType) {
		case 'topic_team_invite_send_to':
		$html = "<div id='pte_email_send_outer'>";
		$html .= pte_make_button_line("select_person_topics", $uxMeta);
		$html .= "</div>";
		break;

		case 'message_view_only':
			$html = 	"<div id='pte_message_title_field_static' class='pte_interaction_message_title'>
									{$messageTitle}
								</div>
								<textarea id='pte_message_body_area' class='pte_interaction_message_body' readonly>{$messageBody}</textarea>
								<div class='pte_updated_message' style='display: {$messageVisibilty};'>{$updatedString}</div>
						";
		break;
		case 'message_editable_new':
		$buttonLineHtml = pte_make_button_line('select_send', $uxMeta);
			$html = 	"{$buttonLineHtml}
								 <input type='text' id='pte_message_title_field' placeholder='Message Title'></input>
								 <textarea id='pte_message_body_area' placeholder='Message Body'></textarea>
								 <div class='pte_updated_message' style='display: {$messageVisibilty};'>{$updatedString}</div>
						";
		break;

		case 'message_editable_update':
		$buttonLineHtml = pte_make_button_line('update', $uxMeta);
		$html = 	"{$buttonLineHtml}
							 <input type='text' id='pte_message_title_field' value='{$messageTitle}' placeholder='Message Title'></input>
							 <textarea id='pte_message_body_area' placeholder='Message Body'>{$messageBody}</textarea>
							 <div class='pte_updated_message' style='display: {$messageVisibilty};'>{$updatedString}</div>
					";
	break;

		case 'accept_decline_response':
		$html = "<div id='pte_interaction_response_outer'>";
		$html .= pte_make_button_line("accept_decline", $uxMeta);
		$html .= "
						<div id='pte_interaction_response_inner_textarea_container'>
								<textarea id='pte_message_body_area_response' placeholder='Optional Note'></textarea>
						</div>
					</div>
		";
		break;
		case 'fax_send_to':
		$html = "<div id='pte_fax_send_outer'>";
		$html .= pte_make_button_line("select_users_with_fax_numbers", $uxMeta);
		$html .= "
							<div id='pte_fax_send_error_line'></div>
					</div>
		";
		break;
		case 'email_send_to':
		$html = "<div id='pte_email_send_outer'>";
		$html .= pte_make_button_line("select_topics_with_email_addresses", $uxMeta);
		$html .= "</div>";
		break;
		case 'sms_send_to':
		$html = "<div id='pte_email_send_outer'>";
		$html .= pte_make_button_line("select_topics_with_mobile_numbers", $uxMeta);
		$html .= "</div>";
		break;
	}
	return $html;
}


function pte_make_info_panel($uxMeta){

	$html = "";
	$informationMessage =	isset($uxMeta['information_message']) ? $uxMeta['information_message'] : "";
	$informationLines =	isset($uxMeta['content_lines']) ? $uxMeta['content_lines'] : array();
	$dataLines =	isset($uxMeta['data_lines']) ? $uxMeta['data_lines'] : array();
	$messageLines =	isset($uxMeta['message_lines']) ? $uxMeta['message_lines'] : array();
	$panelLines =	isset($uxMeta['panel_lines']) ? $uxMeta['panel_lines'] : array();

	//Data Lines
		$html .= "<div style='margin-top: 0px; padding: 0 5px;'>";
		foreach ($dataLines as $key) {
			$html .= pte_make_data_line($key, $uxMeta);
		}
		$html .= "</div>";
	//Message Lines
	$html .= "<div id='pte_interaction_message_container'>";
		foreach ($messageLines as $key) {
			$html .= pte_make_message_line($key, $uxMeta);
	}
	$html .= "</div>";
	//Link Lines
	$html .= "<div id='pte_interaction_information_links'>";
		foreach ($informationLines as $key) {
			$html .= pte_make_interaction_link($key, $uxMeta);
		}
	$html .= "</div>";

	$html .= "</div>"; //end info panel

return $html;
}

function pte_make_smart_contract_panel ($uxMeta) {

	global $wpdb;

	$html = "";

	$supportedChains = array("eth" => "Ethereum", "polygon" => "Polygon");
	$selectedBlockChain = "polygon";

	$sendLine = pte_make_button_line('deploy_contract_send', $uxMeta);
	$processId = $uxMeta['process_id'];

	$filesProcessing =	isset($uxMeta['nft_ipfs_files']) ?  "Ready" : "Processing...";

	$blockChainSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_nft_blockchains'>";
	foreach ($supportedChains as  $key => $value) {
		$selectedItem = ($key == $selectedBlockChain) ? " SELECTED " : "";
		$blockChainSelect .= "<option value='{$key}' $selectedItem>{$value}</option>";
	}
	$blockChainSelect .= "</select></div>";

	$contractTemplateSelect = "";
	$status = "active";

	$contractTemplates = $wpdb->get_results(
		$wpdb->prepare("SELECT id, contract_name, contract_description FROM alpn_smart_contract_templates WHERE status = %s", $status)
	 );

	if (isset($contractTemplates[0])) {
				$contractTemplateSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_smart_contracts'>";
				foreach ($contractTemplates as $key => $value) {
					$contractTemplateSelect .= "<option value='{$value->id}' title='{$value->contract_description}'>{$value->contract_name}</option>";
				}
				$contractTemplateSelect .= "</select></div>";

	}

	$accountSelectData = wsc_get_owned_web3_accounts_list();
	$accountSelect = $accountSelectData['html'];

	$infoPanel = pte_make_info_panel($uxMeta);
	$html .= "<div id='wsc_twitter_panel'>
							<div class='wsc_section_title_ww'>Web3 Account</div>
							{$accountSelect}
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Chain</div>
							{$blockChainSelect}
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Type</div>
							{$contractTemplateSelect}
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Name</div>
							<input type='text' id='wsc_nft_contract_name_field' class='wsc_ww_field' placeholder='Collection Name in Marketplace'></input>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Symbol</div>
							<input type='text' id='wsc_nft_contract_symbol_field' class='wsc_ww_field' placeholder='Collection Symbol in Marketplace'></input>
							{$sendLine}
							{$infoPanel}
						</div>
						<script>
								jQuery('select#alpn_select2_small_owned_accounts').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								jQuery('select#alpn_select2_small_nft_blockchains').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								jQuery('select#alpn_select2_small_smart_contracts').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								function wsc_get_smart_contract_values(){
									var contractName = jQuery('input#wsc_nft_contract_name_field').val();
									var contractSymbol = jQuery('input#wsc_nft_contract_symbol_field').val();
									const chainList = jQuery('#alpn_select2_small_nft_blockchains');
									const chainData = chainList.select2('data');
									if (typeof chainData != 'undefined' && typeof chainData[0] != 'undefined') {
										 var contractBlockChain = chainData[0].id;
									}
									const contractsList = jQuery('#alpn_select2_small_smart_contracts');
									const contractsData = contractsList.select2('data');
									if (typeof contractsData != 'undefined' && typeof contractsData[0] != 'undefined') {
										 var contractTemplateId = contractsData[0].id;
									}
									const nftAccountList = jQuery('select#alpn_select2_small_owned_accounts');
									const nftAccountData = nftAccountList.select2('data');
									if (typeof nftAccountData != 'undefined' && typeof nftAccountData[0] != 'undefined') {
										 var nftAccountType = jQuery(nftAccountData[0].element).data('cust');
										 var nftAccount = nftAccountData[0].id;
									} else {
										var nftAccountType = '';
										var nftAccount = '';
									}
									return {
										'contract_name': contractName,
										'contract_account': nftAccount,
										'contract_account_type': nftAccountType,
										'contract_symbol': contractSymbol,
										'contract_blockchain': contractBlockChain,
										'contract_template_id': contractTemplateId,
										'process_id': '{$processId}'
									};
								}
							</script>";
return $html;
}

function pte_make_mint_nft_panel ($uxMeta) {

	global $wpdb;

	$supportedChains = array("eth" => "Ethereum", "polygon" => "Polygon");
	$selectedBlockChain = "polygon";

	$sendLine = pte_make_button_line('nft_send', $uxMeta);
	$processId = $uxMeta['process_id'];
	$ownerId = $uxMeta['owner_id'];

	$filesProcessing =	isset($uxMeta['nft_ipfs_files']) ?  "Ready" : "Processing...";

	$accountSelectData = wsc_get_owned_web3_accounts_list();
	$accountSelect = $accountSelectData['html'];

	$contractData = array(
		"account_address" => $accountSelectData['selected_account_address'],
		"chain_id" => $selectedBlockChain
	);

	$contractSelect = wsc_get_available_contracts_list($contractData);

	$blockChainSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_nft_blockchains'>";
	foreach ($supportedChains as  $key => $value) {
		$selectedItem = ($key == $selectedBlockChain) ? " SELECTED " : "";
		$blockChainSelect .= "<option value='{$key}' $selectedItem>{$value}</option>";
	}
	$blockChainSelect .= "</select></div>";

	$mediaUrlHtml = $mediaUrl ? "<div id='wsc_nft_ww_media_url' class='wsc_nft_url_container'>Media: {$mediaUrl}</div>" : "";
	$imageUrlHtml = $mediaUrl ? "<div id='wsc_nft_ww_image_url'>Image: {$imageUrl}</div>" : "";
	$wiscleLinkHtml = $mediaUrl ? "<div id='wsc_nft_ww_image_url'>Vault: {$wiscleLink}</div>" : "";

	$html .= "<div id='wsc_twitter_panel'>
							<div class='wsc_section_title_ww'>Web3 Account</div>
							{$accountSelect}
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Chain</div>
							{$blockChainSelect}
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Smart Contract/Collection</div>
							<div id='wsc_contract_list_container_ww'>{$contractSelect}</div>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Name</div>
							<input type='text' id='wsc_nft_name_field' class='wsc_ww_field' placeholder='NFT Name'></input>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Description</div>
							<textarea id='wsc_nft_description_field' class='wsc_textarea_standard' placeholder='NFT Description'>{$textContent}</textarea>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Attributes</div>
							- under construction --
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Quantity</div>
							<input type='text' id='wsc_nft_quantity_field' class='wsc_ww_field' placeholder='Quantity Available'></input>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Web3 Account to Receive NFT</div>
							<input type='text' class='wsc_ww_field' id='wsc_nft_account_field' placeholder='0x... Leave empty to receive in your own account'></input>
							<div class='wsc_section_title_ww wsc_ww_line_separator'>Media: <span id='wsc_nft_files_status' style='font-weight: normal;'>{$filesProcessing}</span></div>
							{$sendLine}
							{$mediaUrlHtml}
							{$imageUrlHtml}
							{$wiscleLinkHtml}
						</div>
						<script>
								jQuery('select#alpn_select2_small_owned_accounts').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								jQuery('select#alpn_select2_small_smart_contracts').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								jQuery('select#alpn_select2_small_owned_accounts').on('select2:select', function (e) {
									wsc_update_available_contracts();
								});
								jQuery('select#alpn_select2_small_nft_blockchains').select2( {
									theme: 'bootstrap',
									width: '100%',
									allowClear: false,
									minimumResultsForSearch: -1
								});
								jQuery('select#alpn_select2_small_nft_blockchains').on('select2:select', function (e) {
									wsc_update_available_contracts();
								});

								function wsc_get_mint_nft_values(){
									const nftName = jQuery('input#wsc_nft_name_field').val();
									const nftDescription = jQuery('textarea#wsc_nft_description_field').val();
									const nftRecipientId = jQuery('input#wsc_nft_account_field').val();
									const nftQuantity = jQuery('input#wsc_nft_quantity_field').val();
									const nftAccountList = jQuery('select#alpn_select2_small_owned_accounts');
									const nftAccountData = nftAccountList.select2('data');
									if (typeof nftAccountData != 'undefined' && typeof nftAccountData[0] != 'undefined') {
										 var nftAccountType = jQuery(nftAccountData[0].element).data('cust');
										 var nftAccount = nftAccountData[0].id;
									} else {
										var nftAccount = '';
									}
									const nftChainList = jQuery('select#alpn_select2_small_nft_blockchains');
									const nftChainData = nftChainList.select2('data');
									if (typeof nftChainData != 'undefined' && typeof nftChainData[0] != 'undefined') {
										var nftBlockchain = nftChainData[0].id;
									} else {
										var nftBlockchain = '';
									}
									const nftContractsList = jQuery('select#alpn_select2_small_smart_contracts');
									const nftContractsData = nftContractsList.select2('data');
									if (typeof nftContractsData != 'undefined' && typeof nftContractsData[0] != 'undefined') {
										 var nftContractAddress = nftContractsData[0].id;
									} else {
										var nftContractAddress = '';
									}
									return {
										'nft_name': nftName,
										'nft_account': nftAccount,
										'nft_account_type': nftAccountType,
										'nft_description': nftDescription,
										'nft_recipient_id': nftRecipientId,
										'nft_blockchain': nftBlockchain,
										'nft_quantity': nftQuantity,
										'nft_contract_address': nftContractAddress,
										'process_id': '{$processId}',
										'nft_attributes': {}
									};
								}
							</script>";
return $html;
}

function pte_make_twitter_actions_panel ($uxMeta) {

	global $wpdb;

  $connectToTwitter = "";

  $initialAction = isset($uxMeta['twitter_action']) ? $uxMeta['twitter_action'] : "tweet";
  $textContent = isset($uxMeta['twitter_text_data']) ? $uxMeta['twitter_text_data'] : "";


	$userInfo = wp_get_current_user();
	$ownerId = $userInfo->data->ID;

	$processId = 	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";

  $actionsArray = array(
    "tweet" => "Tweet up to 4 NFTs",
    "pfp" => "Set Profile Picture to an NFT",
    "3x1" => "Set Profile Banner to 3 NFTs",
    "6x2" => "Set Profile Banner to 12 NFTs",
    "9x3" => "Set Profile Banner to 27 NFTs",
    "12x4" => "Set Profile Banner to 48 NFTs",
    "15x5" => "Set Profile Banner to 75 NFTs",
    "2xwords" => "Set Profile Banner to 2 NFTs and Caption",
    "8xwords" => "Set Profile Banner to 8 NFTs and Caption",
    "18xwords" => "Set Profile Banner to 18 NFTs and Caption",
    "32xwords" => "Set Profile Banner to 32 NFTs and Caption"
  );
  $hasWordsArray = array("tweet", "2xwords", "8xwords", "18xwords", "32xwords");
  $hasWordsClass= in_array($initialAction, $hasWordsArray) ? "" : "wsc_owner_tools_off";

	$html = "";

  if ($processId && $ownerId) {

  	$setData = $wpdb->get_results(
  		$wpdb->prepare("SELECT DISTINCT set_name FROM alpn_nft_sets WHERE owner_id = %d ORDER BY set_name", $ownerId)
  	);

    if (isset($setData[0])) {

          $initialSet = isset($uxMeta['twitter_set_name']) ? $uxMeta['twitter_set_name'] : $setData[0]->set_name;

          $twitterActionsSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_twitter_actions'>";
          foreach ($actionsArray as  $key => $value) {
            $selectedItem = ($key == $initialAction) ? " SELECTED " : "";
        		$twitterActionsSelect .= "<option value='{$key}' $selectedItem>{$value}</option>";
        	}
          $twitterActionsSelect .= "</select></div>";

        	$setSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_nft_sets'>";
        	foreach ($setData as  $setItem) {
            $selectedItem = ($setItem->set_name == $initialSet) ? " SELECTED " : "";
        		$setSelect .= "<option value='{$setItem->set_name}' $selectedItem>{$setItem->set_name}</option>";
        	}
        	$setSelect .= "</select></div>";

          $previewHtml = wsc_get_twitter_preview_art($initialSet, $initialAction, $processId, $textContent);

          $userMeta = $wpdb->get_results(
            $wpdb->prepare("SELECT twitter from alpn_user_metadata WHERE id = %d ", $ownerId)
        	);
          $accessTokenData = json_decode($userMeta[0]->twitter, true);
          $accessToken = isset($accessTokenData['oauth_token']) ? $accessTokenData['oauth_token'] : false;

          if ($accessToken && $accessTokenData['user_id']) {
            $twitterName = $accessTokenData['screen_name'];
            $connectToTwitter = "<div id='wsc_twitter_connection' class='wsc_twitter_connection' data-wsc-state='connected'>
                                    Status: Connected as @<a class='wsc_external_links' href='https://twitter.com/{$twitterName}' target='_blank'>{$twitterName}</a> -- <a class='wsc_external_links' onclick='wsc_twitter_disconnect();'>Disconnect</a>
                                </div>";
          } else {
            $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_CALLBACK));

            if (isset($request_token['oauth_token'])) {
              $userMeta = array('twitter' => json_encode($request_token));
              $whereClause = array('id' => $ownerId);
              $wpdb->update( 'alpn_user_metadata', $userMeta, $whereClause );

              $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
              $connectToTwitter = "<div id='wsc_twitter_connection' class='wsc_twitter_connection' data-wsc-state='disconnected'>
                                    Status: Disconnected -- <a class='wsc_external_links' onclick='window.open(`{$url}`, `_blank`, `top=100,left=500,width=640,height=480`)'>Connect to Twitter</a>
                                  </div>";
            }
          }

          $sendLine = pte_make_button_line('twitter_send', $uxMeta);

        	$html .= "<div id='wsc_twitter_panel'>
          <div class='wsc_section_title_ww'>Twitter</div>
                      {$connectToTwitter}
                      <div class='wsc_section_title_ww wsc_ww_line_separator'>Workflow</div>
                      {$twitterActionsSelect}
                      <div class='wsc_section_title_ww wsc_ww_line_separator'>NFT Set</div>
                      {$setSelect}
                      <div id='wsc_twitter_preview_area' class='wsc_twitter_preview_area {$hasWordsClass}'>
                        <div class='wsc_section_title_ww wsc_ww_line_separator'>Words</div>
                        <textarea id='wsc_twitter_note' class='wsc_textarea_standard'>{$textContent}</textarea>
                      </div>
                      {$sendLine}
                      <div class='wsc_section_title_ww'>Art Preview</div>
                      <div id='wsc_twitter_preview_container'>{$previewHtml}</div>
        						</div>
                		<script>
                        function wsc_handle_twitter_action_changes(){
                          var context = wsc_twitter_ww_get_changes();
                          wsc_update_preview(context.twitter_action_slug, context.twitter_set_slug, context.twitter_text_data);
                        }
                				jQuery('select#alpn_select2_small_twitter_actions').select2( {
                					theme: 'bootstrap',
                					width: '100%',
                					allowClear: false,
                					minimumResultsForSearch: -1
                				});
                				jQuery('#alpn_select2_small_twitter_actions').on('select2:select', function (e) {
                          wsc_handle_twitter_action_changes();
                				});
                        jQuery('select#alpn_select2_small_nft_sets').select2( {
                          theme: 'bootstrap',
                          width: '100%',
                          allowClear: false
                        });
                        jQuery('#alpn_select2_small_nft_sets').on('select2:select', function (e) {
                          wsc_handle_twitter_action_changes();
                        });
                        jQuery('textarea#wsc_twitter_note').donetyping(function(){
                            wsc_handle_twitter_action_changes();
                        });
                      </script>
        		";


    }




  } else {
    alpn_log("Error Twitter");
  }

return $html;
}


function pte_make_invitation_received_panel ($uxMeta) {

	global $wpdb;

	$userInfo = wp_get_current_user();
	$ownerId = $userInfo->data->ID;

	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$html = "";

	$topicTypeId = 	isset($uxMeta['topic_type_id']) ? $uxMeta['topic_type_id'] : 0;
	$topicTypeSpecial = 	isset($uxMeta['topic_special']) ? $uxMeta['topic_special'] : 'topic';

	$networkContactPanel = pte_make_interaction_link('network_panel', $uxMeta);
	$topicPanel = pte_make_interaction_link('topic_panel', $uxMeta);

	$messageViewOnly = pte_make_message_line('message_view_only', $uxMeta);
	$acceptDeclineResponse = pte_make_message_line('accept_decline_response', $uxMeta);

	$toFromLineHtml = pte_make_data_line('to_from_line', $uxMeta);
	$regardingLine = pte_make_data_line('regarding_line', $uxMeta);
	$typeLine = pte_make_data_line('type_line', $uxMeta);

	$linkTopics = $wpdb->get_results(
		$wpdb->prepare("SELECT id, name FROM alpn_topics WHERE special = 'topic' AND owner_id = %s ORDER BY NAME ASC", $ownerId)
	);
	$linkTopicSelect = "<select id='alpn_select2_small_link_topic_select'>";
	foreach ($linkTopics as $key => $value) {
		$linkTopicSelect .= "<option value='{$value->id}'>{$value->name}</option>";
	}
	$linkTopicSelect .= "</select>";

	$connectionTypeSelect = "<select id='alpn_select2_small_connection_type_select'>";
	$connectionTypeSelect .= "<option value='0'>Join Topic</option>";
	$connectionTypeSelect .= "<option value='1'>Link to My Topic</option>";
	//$connectionTypeSelect .= "<option value='2'>Create New Linked Topic</option>";   TODO implement this
	$connectionTypeSelect .= "</select>";

	$html .= "<div id='pte_invitation_received_panel_outer'>
						{$toFromLineHtml}
						{$regardingLine}
						{$typeLine}
						<div id='pte_invitation_received_panel'>
						{$messageViewOnly}
						<div id='pte_topic_connection_type'>
						{$connectionTypeSelect}
						</div>
						<div id='pte_topic_existing' style='display: none;'>
						{$linkTopicSelect}
						</div>
						<div id='pte_topic_data_transfer'>
						Empty
						</div>
						{$acceptDeclineResponse}
						</div>
						{$networkContactPanel}
						</div>
		<script>
				function pte_handle_connection_type_changed(data) {
					console.log('pte_handle_connection_type_changed...');
					if (typeof data != 'undefined') {
						switch(data.id) {
							case '0':
								jQuery('#pte_topic_existing').hide();
								// jQuery('#pte_topic_data_transfer').hide();
							break;
							case '1':
								jQuery('#pte_topic_existing').show();
								// jQuery('#pte_topic_data_transfer').hide();
							break;
							case '2':
								jQuery('#pte_topic_existing').hide();
								// jQuery('#pte_topic_data_transfer').show();
							break;
						}
					}
				}
				jQuery('#alpn_select2_small_connection_type_select').select2( {
					theme: 'bootstrap',
					width: '100%',
					allowClear: false,
					minimumResultsForSearch: -1
				});
				jQuery('#alpn_select2_small_connection_type_select').on('select2:select', function (e) {
					pte_handle_connection_type_changed(e.params.data);
				});
				pte_handle_connection_type_changed();
				jQuery('#alpn_select2_small_link_topic_select').select2( {
					theme: 'bootstrap',
					width: '100%',
					allowClear: false,
				});
		</script>
		";
return $html;
}


function pte_make_send_panel($uxMeta) {

	global $wpdb;

	$userInfo = wp_get_current_user();
	$ownerId = $userInfo->data->ID;
	$messageTypeId = $uxMeta['template_type_id'];

	$widgetTypeId  = isset($uxMeta['widget_type_id']) ? $uxMeta['widget_type_id'] : '';
	$vaultId  = isset($uxMeta['vault_id']) ? $uxMeta['vault_id'] : false;
	//Lookup templates for this user for this template type

	$templateData = $wpdb->get_results(
		$wpdb->prepare("SELECT tt.type_key, tm.id, tm.name FROM alpn_topic_types tt JOIN alpn_templates tm ON tm.type_key = tt.type_key AND tm.template_type = 'message' WHERE tt.owner_id = %d AND tt.id = %d;",$ownerId, $uxMeta['topic_type_id'])
	);

	$templates = array();
	foreach ($templateData as $key => $value) {
		$templates[] = array("id" => $value->id, "short_description" => $value->name, "default_item" => 0);
	}
	$uxMeta['templates'] = $templates;


	if ($widgetTypeId == "fax_send") {

		$faxData = $wpdb->get_results( //all my topics that have fax numbers but not me
			$wpdb->prepare("SELECT id, name, topic_content, topic_type_id FROM alpn_topics WHERE special != 'user' AND owner_id = %s AND (JSON_EXTRACT(topic_content, '$.organization_faxnumber') != '' OR JSON_EXTRACT(topic_content, '$.person_faxnumber') != '') ORDER BY NAME ASC", $ownerId)
		);
		$faxNumbers = array();
		foreach ($faxData as $key => $value) {
			$faxNumbers[] = array("id" => $value->id, "name" => $value->name, "topic_type_id" => $value->topic_type_id, "topic_content" => json_decode($value->topic_content, true));
		}
		$uxMeta['fax_numbers'] = $faxNumbers;
		$uxMeta['fax_key'] = true;
		$sendToHtml = pte_make_message_line('fax_send_to', $uxMeta);
	}

	if ($widgetTypeId == "email_send") {
		//get topics with ids
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT t.id, t.name, t.topic_content, t.topic_type_id, t.connected_id, t.special, ct.topic_content AS connected_topic_content FROM alpn_topics t LEFT JOIN alpn_topic_types tt on tt.id = t.topic_type_id LEFT JOIN alpn_topics ct ON ct.id = t.connected_id WHERE JSON_EXTRACT(t.topic_content, '$.person_email') <> '' AND t.owner_id = %s AND tt.schema_key = 'Person' AND t.special <> 'user' ORDER BY NAME ASC", $ownerId)
		 );

		$emailAddresses = array();
		foreach ($results as $key => $value) {   //If connected with someone, use their data.
			$tId =  $value->id;
			$tTypeSpecial =  $value->special;
			$tConnectedId = $value->connected_id;
			$tContent = json_decode($value->topic_content, true);
			if ($tTypeSpecial == 'contact' && $tConnectedId) {
				$tConnectedContent = json_decode($value->connected_topic_content);
			}
			if (isset($tConnectedContent['person_email'])) {
				$emailAddress = $tConnectedContent['person_email'];
			} else {
				$emailAddress = $tContent['person_email'];
			}
			$emailAddresses[] = array("id" => $tId, "text" => "{$value->name}&nbsp;&nbsp;({$emailAddress})");
		}
		$uxMeta['email_addresses'] = $emailAddresses;
		$uxMeta['email_key'] = true;
		$sendToHtml = pte_make_message_line('email_send_to', $uxMeta);
	}

	if ($widgetTypeId == "sms_send") {

//only difference in queries is which field we're checking for not empty in json. And have to check network contact
		// $results = $wpdb->get_results(
		// 	$wpdb->prepare("SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle, t2.topic_content AS connected_topic_content FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE JSON_EXTRACT(t.topic_content, '$.person_telephone') != '' AND t.special != 'user' AND t.owner_id = %s ORDER BY NAME ASC", $ownerId)
		//  );

		 $results = $wpdb->get_results(
			 $wpdb->prepare("SELECT t.id, t.name, t.topic_content, t.topic_type_id, t.connected_id, t.special, ct.topic_content AS connected_topic_content FROM alpn_topics t LEFT JOIN alpn_topic_types tt on tt.id = t.topic_type_id LEFT JOIN alpn_topics ct ON ct.id = t.connected_id WHERE (JSON_EXTRACT(t.topic_content, '$.person_telephone') <> '' OR (JSON_EXTRACT(ct.topic_content, '$.person_telephone') <> '')) AND t.owner_id = %s AND tt.schema_key = 'Person' AND t.special <> 'user' ORDER BY NAME ASC", $ownerId)
		);

		$mobileNumbers = array();
 		foreach ($results as $key => $value) {   //If connected with someone, use their data.
 			$tId =  $value->id;
 			$tTypeId =  $value->topic_type_id;
			$tTypeSpecial =  $value->special;
			$tConnectedId = $value->connected_id;
 			$tContent = $value->topic_content;
 			if ($tTypeSpecial == 'contact' && $tConnectedId) {
 				$tContent = $value->connected_topic_content;
 			}
 			$tContent = json_decode($tContent, true);
 			$mobileNumber = $tContent['person_telephone'];
 			$mobileNumbers[] = array("id" => $tId, "text" => "{$value->name} - {$mobileNumber}");
 		}
 		$uxMeta['mobile_numbers'] = $mobileNumbers;
		$uxMeta['sms_key'] = true;
		$sendToHtml = pte_make_message_line('sms_send_to', $uxMeta);
	}

	if ($widgetTypeId == "topic_team_invite") {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT t.id, t.name, t.topic_content, t.topic_type_id, t.connected_id, t.special, ct.topic_content AS connected_topic_content FROM alpn_topics t LEFT JOIN alpn_topic_types tt on tt.id = t.topic_type_id LEFT JOIN alpn_topics ct ON ct.id = t.connected_id WHERE t.owner_id = %d AND tt.schema_key = 'Person' AND t.special = 'contact' ORDER BY t.name ASC", $ownerId)
	 );

	 //t.special <> 'user' for persons in topic too

	 $inviteList = array();
	 foreach ($results as $key => $value) {   //If connected with someone, use their data.
		 $tId =  $value->id;
		 $tTypeId =  $value->topic_type_id;
		 $tTypeSpecial =  $value->special;
		 $tConnectedId = $value->connected_id;
		 $tContent = $value->topic_content;
		 if ($tTypeSpecial == 'contact' && $tConnectedId) {
			 $tContent = $value->connected_topic_content;
		 }
		 $tContent = json_decode($tContent, true);
		 $inviteList[] = array("id" => $tId, "text" => "{$value->name}");
	 }
	 $uxMeta['invite_send_to_list'] = $inviteList;
	 $uxMeta['topic_invite_key'] = true;
	 $sendToHtml = pte_make_message_line('topic_team_invite_send_to', $uxMeta);
	}

	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : 0;
	$topicTypeId = 	isset($uxMeta['topic_type_id']) ? $uxMeta['topic_type_id'] : 0;
	$topicTypeSpecial =	isset($uxMeta['topic_special']) ? $uxMeta['topic_special'] : 'topic';
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";

	$html = "";
	$messageLineHtml = pte_make_message_line('message_editable_new', $uxMeta);
	if ($vaultId) {
		$vaultItemHTML = pte_make_interaction_link('vault_item', $uxMeta);
		$linkSettings = pte_make_button_line('link_settings', $uxMeta);
	}
	$linkPanel = pte_make_interaction_link('topic_panel', $uxMeta);
	$regardingLineHtml = pte_make_data_line('regarding_line', $uxMeta);
	$contractAddressHtml = pte_make_data_line('regarding_line', $uxMeta);

	$html .= "
	<div class='pte_interaction_information_panel'>
		<div class='proteam_message_panel'>
			<div style='margin-top: 32px; padding: 0 5px; font-size: 13px; font-weight: normal; line-height: 20px; margin-bottom: 0px;'>
				{$sendToHtml}
				{$regardingLineHtml}
				{$messageLineHtml}
				{$linkSettings}
				{$vaultItemHTML}
				{$linkPanel}
			</div>
		</div>
		</div>
    <script>
				jQuery('#alpn_select2_small_template_select').select2( {
					theme: 'bootstrap',
					width: '100%',
					allowClear: false,
					minimumResultsForSearch: -1
				});
				jQuery('#alpn_select2_small_template_select').on('select2:select', function (e) {
					pte_handle_message_merge('message');
				});
    		jQuery('#alpn_select2_small_fax_number_select').select2( {
    			theme: 'bootstrap',
    			width: '100%',
					allowClear: false
    		});
				jQuery('#alpn_select2_small_fax_number_select').on('select2:select', function (e) {
					var data = e.params.data;
					pte_handle_fax_number_selected(data);
					pte_handle_message_merge('message');
				});
				pte_handle_message_merge('message');
    </script>
		";
	return $html;
}

function pte_make_link_options_html($id = ''){

	$id = $id ? "_{$id}" : "";

	$selectPanel = "<select id='alpn_select2_small_link_options_select{$id}'>";
		$selectPanel .= "<option value='0'>View</option>";
		$selectPanel .= "<option value='1' SELECTED>View, Print</option>";
		$selectPanel .= "<option value='2'>View, Print, Copy & Download</option>";
		$selectPanel .= "</select>
	<script>
	jQuery('#alpn_select2_small_link_options_select{$id}').select2( {
		theme: 'bootstrap',
		width: '100%',
		allowClear: false,
		minimumResultsForSearch: -1
	});
	</script>
	";
	return $selectPanel;
}

function pte_get_link_options_string($value){
	$linkOptionsMap = array(
		"0" => "View",
		"1" => "Print",
		"2" => "Copy/Download",
	);
	return $linkOptionsMap[$value];
}

function pte_make_link_expiration_html($id = ''){

	$id = $id ? "_{$id}" : "";

	$selectPanel = "<select id='alpn_select2_small_link_expiration_select{$id}'>";
		$selectPanel .= "<option value='30'>30 Minutes</option>";
		$selectPanel .= "<option value='60'>1 Hour</option>";
		$selectPanel .= "<option value='480'>8 Hours</option>";
		$selectPanel .= "<option value='1440' SELECTED >1 Day</option>";
		$selectPanel .= "<option value='2880'>2 Days</option>";
		$selectPanel .= "<option value='10080'>1 Week</option>";
		$selectPanel .= "<option value='0'>Manual Expiration</option>";
		$selectPanel .= "</select>
	<script>
	jQuery('#alpn_select2_small_link_expiration_select{$id}').select2( {
		theme: 'bootstrap',
		width: '100%',
		allowClear: false,
		minimumResultsForSearch: -1
	});
	</script>
	";
	return $selectPanel;
}

function pte_make_expiration_html(){
	$selectPanel = "  <div style='margin-top: 4px; width: 100%;'>
											<div style='width: 80px; display: inline-block; font-weight: bold;  vertical-align: text-bottom;'  title='Only if Incomplete'>
												Revisit in:
											</div>
										<div style='display: inline-block; width: calc(100% - 90px);'>
										<select id='alpn_select2_small_expiration_select' class=''>";
		$selectPanel .= "<option value='5'>5 Minutes</option>";
		$selectPanel .= "<option value='10'>10 Minutes</option>";
		$selectPanel .= "<option value='15'>15 Minutes</option>";
		$selectPanel .= "<option value='30'>30 Minutes</option>";
		$selectPanel .= "<option value='45'>45 Minutes</option>";
		$selectPanel .= "<option value='60'>1 Hour</option>";
		$selectPanel .= "<option value='120'>2 Hours</option>";
		$selectPanel .= "<option value='240'>4 Hours</option>";
		$selectPanel .= "<option value='480'>8 Hours</option>";
		$selectPanel .= "<option value='1440' SELECTED >1 Day</option>";
		$selectPanel .= "<option value='2880'>2 Days</option>";
		$selectPanel .= "<option value='4320'>3 Days</option>";
		$selectPanel .= "<option value='5760'>4 Days</option>";
		$selectPanel .= "<option value='7200'>5 Days</option>";
		$selectPanel .= "<option value='8640'>6 Days</option>";
		$selectPanel .= "<option value='10080'>1 Week</option>";
		$selectPanel .= "</select>
										</div>
									</div>
	<script>
	jQuery('#alpn_select2_small_expiration_select').select2( {
		theme: 'bootstrap',
		width: '100%',
		allowClear: false,
		minimumResultsForSearch: -1
	});
	</script>
	";
	return $selectPanel;
}


?>
