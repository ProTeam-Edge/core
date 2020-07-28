<?php
require 'vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Bueltge\Marksimple\Marksimple;

$siteUrl = get_site_url();

$qVars = $_POST;
$processId = isset($qVars['process_id']) ? $qVars['process_id'] : '';

$html = "";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $userID, 'pte_user_network_id', true ); //Owners Topic ID

if ($processId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT ux_meta, priority FROM alpn_interactions WHERE process_id = %s AND owner_network_id = %s;", $processId, $ownerNetworkId)
	);

	if (isset($results[0])) {
		$uxMeta = json_decode($results[0]->ux_meta, true);
		$uxMeta['priority'] = $results[0]->priority;
		$html = pte_make_interaction_editor_ux($uxMeta);
	}
}
echo $html;

//end

function pte_make_interaction_editor_ux($uxMeta) {

	$widgetTypeId  = isset($uxMeta['widget_type_id']) ? $uxMeta['widget_type_id'] : '';
	$informationTitle =	isset($uxMeta['information_title']) ? $uxMeta['information_title'] : "";

//Three different styles for infoTitle styling.
	$informationTitle = str_replace("|style_1b|", "<span class='pte_line_style_1'>", $informationTitle);  //GREEN COMPLETE
	$informationTitle = str_replace("|style_1e|", "</span>", $informationTitle);
	$informationTitle = str_replace("|style_2b|", "<span class='pte_line_style_2'>", $informationTitle);  //TBD
	$informationTitle = str_replace("|style_2e|", "</span>", $informationTitle);
	$informationTitle = str_replace("|style_3b|", "<span class='pte_line_style_3'>", $informationTitle);  //TBD
	$informationTitle = str_replace("|style_3e|", "</span>", $informationTitle);

	$priority =	isset($uxMeta['priority']) ? $uxMeta['priority'] * 100 : 0;
	$processId = 	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";
	$processTypeId = 	isset($uxMeta['process_type_id']) ? $uxMeta['process_type_id'] : "";
	$html = "
			<div id='pte_interaction_information_title'>
				{$informationTitle}
				<div id='pte_priority_container'>
					<i class='far fa-chevron-circle-up pte_interaction_editor_button pte_ipanel_button_enabled' title='Recall Interaction'></i>
					<i class='far fa-archive pte_interaction_editor_button pte_ipanel_button_enabled' title='File Interaction Away'></i></div>
					</div>
			</div>
			<div id='pte_interaction_information_panel'>
			";

	switch ($widgetTypeId) {  //TODO generalize to make widgets and interactions more extensible

		case 'message_send':
			$html .= pte_make_message_panel($uxMeta);
		break;
		case 'waiting':
		case 'information':
			$html .= pte_make_info_panel($uxMeta);
		break;
	}

	//$html .= pte_make_button_bar($uxMeta);

	$html .= "
	<script>
		jQuery('#pte_interaction_card_importance_editor').css('width', '{$priority}%');
		function pte_handle_send_interaction(theObj) {
				var templateControl = jQuery('#alpn_select2_small_template_select');
				var templateData = templateControl.select2('data');
				var messageTitle = jQuery('#pte_message_title_field').val();
				var messageBody = jQuery('#pte_message_body_area').val();
				var messageResponse = jQuery('#pte_message_body_area_response').val();
				var expirationControl = jQuery('#alpn_select2_small_expiration_select');
				var expirationData = expirationControl.select2('data');
				if (typeof templateData != 'undefined' && typeof templateData[0] != 'undefined') {
					var templateId = templateData[0].id;
					var templateName = templateData[0].text;
				} else {
					var templateId = 0;
					var templateName = '';
				}
				if (typeof expirationData != 'undefined' && typeof expirationData[0] != 'undefined') {
					var expirationMinutes = expirationData[0].id;
				} else {
					var expirationMinutes = 0;
				}
				var jObj = jQuery(theObj);
				var buttonOperation = jObj.data('pteop');
				console.log(jObj);
				var sendData = {
					'message_response': messageResponse,
					'button_operation': buttonOperation,
					'template_id': templateId,
					'template_name': templateName,
					'message_title': messageTitle,
					'message_body': messageBody,
					'expiration_minutes': expirationMinutes,
					'process_id': '{$processId}',
					'process_type_id': '{$processTypeId}'
				};
				pte_handle_widget_interaction(sendData);
		}
	</script>
	";
  return $html;
}

function pte_make_button_bar($uxMeta){
	$html = "";

	$buttons = 	isset($uxMeta['buttons']) ? $uxMeta['buttons'] : array();

	$html .= "<div id='pte_interaction_panel_buttons'>
						<div id='pte_interaction_panel_buttons_container'>
						";

	$html .= "
		<i class='far fa-chevron-circle-up pte_interaction_panel_button pte_ipanel_button_enabled' title='Recall Interaction'></i>
		<i class='far fa-archive pte_interaction_panel_button pte_ipanel_button_enabled' title='File Interaction Away'></i></div>
	";

	$html .= "</div>
						</div>";

	return $html;
}


function pte_make_button_line($lineType, $uxMeta) {

	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";

	$html = "";
	switch ($lineType) {
		case 'select_send':
			$templates  = isset($uxMeta['templates']) ? $uxMeta['templates'] : array();
			$selectPanel = "<select id='alpn_select2_small_template_select' class='' data-topic-id='{$topicId}' data-network-id='{$networkId}'>";
			foreach ($templates as $key => $value){
				$id = $value['id'];
				$description = $value['short_description'];
				$defaultItem = $value['default_item'];
				$selected = $defaultItem == true ? " SELECTED " : "";
				$selectPanel .= "<option value='{$id}' {$selected}>{$description}</option>";
			}
			$selectPanel .= "</select>";
			$html .= "
					<div style='float: left; width: calc(100% - 60px); margin-top: 5px;'>
						<div style='width: 100%; margin-bottom: 0px;'>{$selectPanel}</div>
					</div>
					<div style='float: right; width: 50px; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 0;'>
						<button data-pteop='send' id='pte_message_panel_send' class='btn btn-danger btn-sm' onclick='pte_handle_send_interaction(this);' style='margin-right: 0; width: 45px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Send</button>
					</div>
					<div style='clear: both;'></div>
			";
		break;
		case 'update':
			$html .= "
					<div style='float: right; width: 100%; text-align: right; font-size: 20px; color: rgb(0, 116, 187); margin: 0;'>
						<button data-pteop='update' id='pte_message_panel_send' class='btn btn-danger btn-sm' onclick='pte_handle_send_interaction(this);' style='margin-right: 0; width: 65px; height: 20px; font-size: 12px; margin-bottom: 6px;'>Update</button>
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
	}
	return $html;
}

function pte_make_interaction_link($linkType, $uxMeta) {
	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$networkName = 	isset($uxMeta['network_name']) ? $uxMeta['network_name'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$vaultId = 	isset($uxMeta['vault_id']) ? $uxMeta['vault_id'] : "";
	$vaultName = 	isset($uxMeta['vault_name']) ? $uxMeta['vault_name'] : "";
	$processId = 	isset($uxMeta['process_id']) ? $uxMeta['process_id'] : "";
	$processTypeId = 	isset($uxMeta['process_type_id']) ? $uxMeta['process_type_id'] : "";
	$processFriendlyName = 	isset($uxMeta['process_friendlyname']) ? $uxMeta['process_friendlyname'] : "";
	$firstName = trim(substr($networkName, strrpos ($networkName, ",") + 1));   //TODO validate and solidify this or pass FN too...
	$html = "";
	switch ($linkType) {
		case 'topic_info':
			$html .= "<div data-topic-id='{$topicId}' data-operation='topic_info' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>Info - {$topicName}</div>";
		break;
		case 'topic_vault':
			$html .= "<div data-topic-id='{$topicId}' data-operation='topic_vault' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-lock-alt'></i></div>Vault - {$topicName}</div>";
		break;
		case 'network_info':
			$html .= "<div data-network-id='{$networkId}' data-operation='network_info' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>Info - {$networkName}</div>";
		break;
		case 'network_vault':
			$html .= "<div data-network-id='{$networkId}' data-operation='network_vault' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-lock-alt'></i></div>Vault - {$networkName}</div>";
		break;
		case 'network_chat':
			$html .= "<div data-network-id='{$networkId}' data-operation='network_chat' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-comments'></i></div>Chat with {$firstName}</div>";
		break;
		case 'network_audio':
			$html .= "<div data-network-id='{$networkId}' data-operation='network_audio' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-microphone'></i></div>Audio Conference with {$firstName}</div>";
		break;
		case 'vault_item':
			$html .= "<div data-vault-id='{$vaultId}' data-topic-id='{$topicId}' data-operation='vault_item' class='interaction_panel_link' onclick='pte_handle_interaction_link(this);'><div class='pte_icon_interaction_link'><i class='far fa-sticky-note'></i></div>VAULT FILE</div>";
		break;
	}
	return $html;
}

function pte_make_data_line ($lineType, $uxMeta) {

	$html = '';
	$networkId = 	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : "";
	$networkName = 	isset($uxMeta['network_name']) ? $uxMeta['network_name'] : "";
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$toFrom =	isset($uxMeta['to_from']) ? $uxMeta['to_from'] : "NA";
	$templateName =	isset($uxMeta['template_name']) ? $uxMeta['template_name'] : "";
	$sendType = "<span class='pte_internal_link' data-topic-id='{$topicId}' data-operation='topic_info' onclick='pte_handle_interaction_link(this);'>{$topicName}</span>"; //TODO this needs to change based on type

	switch ($lineType) {
		case 'to_from_line':
			$html = "<div style='width: 75px; display: inline-block; font-weight: bold;'>{$toFrom}:</div><div style='display: inline-block;'><span class='pte_internal_link' data-network-id='{$networkId}' data-operation='network_info' onclick='pte_handle_interaction_link(this);'>{$networkName}</span></div><br>";
		break;
		case 'regarding_line':
			$html = "<div style='width: 75px; display: inline-block; font-weight: bold;'>Regarding:</div><div style='display: inline-block;'>{$sendType}</div><br>";
		break;
		case 'type_line':
			$html = "<div style='width: 75px; display: inline-block; font-weight: bold;'>Type:</div><div style='display: inline-block;'>{$templateName}</div><br>";
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

	$messageTitle =	isset($uxMeta['message_title']) ? $uxMeta['message_title'] : "";
	$messageBody =	isset($uxMeta['message_body']) ? $uxMeta['message_body'] : "";

	$sendType = "<span class='pte_internal_link' data-topic-id='{$topicId}' data-operation='topic_info' onclick='pte_handle_interaction_link(this);'>{$topicName}</span>"; //TODO this needs to change based on type

	switch ($lineType) {
		case 'message_view_only':
			$html = 	"<div class='pte_interaction_message_title'>
									{$messageTitle}
								</div>
								<textarea class='pte_interaction_message_body' readonly>{$messageBody}</textarea>
						";
		break;
		case 'message_editable_new':
		$buttonLineHtml = pte_make_button_line('select_send', $uxMeta);
			$html = 	"{$buttonLineHtml}
								 <input type='text' id='pte_message_title_field'></input>
								 <textarea id='pte_message_body_area'></textarea>
						";
		break;

		case 'message_editable_update':
		$buttonLineHtml = pte_make_button_line('update', $uxMeta);
		$html = 	"{$buttonLineHtml}
							 <input type='text' id='pte_message_title_field' value='{$messageTitle}'></input>
							 <textarea id='pte_message_body_area'>{$messageBody}</textarea>
					";
	break;

		case 'accept_decline_response':
		$html = "<div id='pte_interaction_response_outer'>";
		$html .= pte_make_button_line("accept_decline", $uxMeta);
		$html .= "
						<div id='pte_interaction_response_inner_textarea_container'>
								<textarea id='pte_message_body_area_response'></textarea>
						</div>
					</div>
		";
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
	//Data Lines
		$html .= "<div style='margin-top: 0px; padding: 0 10px; font-size: 13px; font-weight: normal; line-height: 20px; margin-bottom: 0px;'>";
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


function pte_make_message_panel($uxMeta) {

	global $wpdb;

	$userInfo = wp_get_current_user();
	$ownerId = $userInfo->data->ID;
	$messageTypeId = $uxMeta['template_type_id'];
	//Lookup templates for this user for this template typed
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT id, short_description, default_item FROM alpn_message_templates WHERE message_type_id = %s AND owner_id = %s;", $messageTypeId, $ownerId)
	);
	$templates = array();
	foreach ($results as $key => $value) {
		$templates[] = array("id" => $value->id, "short_description" => $value->short_description, "default_item" => $value->default_item);
	}
	$uxMeta['templates'] = $templates;
	$topicId = 	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : "";
	$topicName = 	isset($uxMeta['topic_name']) ? $uxMeta['topic_name'] : "";
	$html = "";
	$sendType = "<span class='pte_internal_link' data-topic-id='{$topicId}' data-operation='topic_info' onclick='pte_handle_interaction_link(this);'>{$topicName}</span>"; //TODO this needs to change based on type
	$expirationLineHtml = pte_make_expiration_html();
	$toFromLineHtml = pte_make_data_line('to_from_line', $uxMeta);
	$regardingLineHtml = pte_make_data_line('regarding_line', $uxMeta);
	$messageLineHtml = pte_make_message_line('message_editable_new', $uxMeta);

	$html .= "
	<div class='pte_interaction_information_panel'>
		<div class='proteam_message_panel'>
			<div style='margin-top: 30px; padding: 0 10px; font-size: 13px; font-weight: normal; line-height: 20px; margin-bottom: 0px;'>
				{$toFromLineHtml}
				{$regardingLineHtml}
				{$messageLineHtml}
				{$expirationLineHtml}
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
					pte_handle_message_merge();
				});
				pte_handle_message_merge();
    </script>
		";
	return $html;
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
