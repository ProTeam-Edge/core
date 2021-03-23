<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;
$html = "";
// verifying nonce


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

	$topicTypeId = isset($qVars['topicTypeId']) ? pte_digits($qVars['topicTypeId']) : '';
	$topicTypeSpecial = isset($qVars['topicTypeSpecial']) ? $qVars['topicTypeSpecial'] : '';
	$topicDomId = isset($qVars['previous_topic']) ? $qVars['previous_topic'] : '';
	$returnDetails = isset($qVars['return_details']) ? json_decode(stripslashes($qVars['return_details']), true) : array();

	$userInfo = wp_get_current_user();
	$userID = $userInfo->data->ID;

	//TODO check logged in

	$isLinkedTopic = (substr($topicTypeId, 0, 5) == 'core_');

	if ($isLinkedTopic) {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT name, form_id, icon, topic_type_meta FROM alpn_topic_types WHERE type_key = %s", $topicTypeId)
		 );

	} else {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT name, form_id, icon, topic_type_meta FROM alpn_topic_types WHERE id = %s", $topicTypeId)
		 );
	}

	if (array_key_exists(0, $results)) {
		$topicType = $results['0'];
		$formId = $topicType->form_id;
		$name = $topicType->name;
		$icon = $topicType->icon;



	$topicItemMeta = array(
		"row_id" => "",
		"return_details" => $returnDetails
	);

	$_GET["wpf{$formId}_0"] = json_encode($topicItemMeta); //handle unique topic id

	$html .= "
						<div class='outer_button_line'>
							<div class='pte_vault_row_35'>
							</div>
							<div class='pte_vault_row_65'>
							</div>
							<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
					</div>
		  ";

	$html .= "
						<div class='alpn_container_title_2'>
							<div id='pte_topic_form_title_view'>
								<i class='far fa-plus-circle pte_title_icon_margin_right'></i>New
							</div>
							<div id='pte_topic_form_title_view' class='pte_vault_right'>
								{$name}<i class='{$icon} pte_title_icon_margin_left'></i>
							</div>
						</div>
				";
	$newForm = do_shortcode("[wpforms id='$formId']");
	$columnEditor = "
							<div class='pte_vault_row'>
								<div id='pte_topic_form_edit_view_left'>
									{$newForm}
								</div>
								<div id='pte_topic_form_edit_view_right'>
								</div>
							 </div>
							";

	$html .=	$columnEditor;
	} else {
		$html = "<div>Error</div>";
	}


echo $html;

?>
