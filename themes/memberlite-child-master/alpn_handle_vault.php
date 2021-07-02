<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

//TODO Data Driven also permissions

$interactionChooser = "<select id='alpn_selector_interaction_selector' class='alpn_selector_interaction_selector'>";
$interactionChooser .= "<option value='email' data-icon='far fa-envelope'>Send xLink by Email</option>";
$interactionChooser .= "<option value='sms' data-icon='far fa-sms'>Send xLink by SMS</option>";

if(!pmpro_hasMembershipLevel('1')) {   //Fax Available to all levels other than Community  //TODO make this dynamic
	$interactionChooser .= "<option value='fax' data-icon='far fa-fax'>Send as Fax</option>";
}

$interactionChooser .= "</select>";


$siteUrl = get_site_url();
$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";

$qVars = $_POST;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : false;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : false;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$rightsCheckData = array(
  "topic_dom_id" => $recordId
);
if (!pte_user_rights_check("topic_dom_view", $rightsCheckData)) {
  $html = "
  <div class='pte_topic_error_message'>
     You do not have permission to access this Topic. Please check with the Topic Owner.
  </div>";
  echo $html;
  exit;
}

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.special, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.dom_id = %s", $recordId)
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

//				<i id='alpn_vault_download' class='far fa-cloud-download-alt pte_icon_button' title='Get Original or PDF Item' onclick='alpn_vault_control(\"download\")'></i>
//				<i id='alpn_vault_edit_original' class='fab fa-google-drive pte_icon_button' title='Open Item in Original Cloud Service' onclick='alpn_vault_control(\"open_original\")'></i>




$html="";

$html .= "
					<div class='outer_button_line'>
						<div class='pte_vault_row_25'>
							<span class='fa-stack pte_icon_button_nav' title='Data View' data-operation='to_info' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-info fa-stack-1x' style='font-size: 16px;'></i>
							</span>
							<span class='fa-stack pte_icon_button_nav' title='Design View' data-operation='to_report' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-drafting-compass fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
							</span>
							<span class='fa-stack pte_icon_button_nav pte_icon_report_selected' title='Vault View' data-operation='to_vault' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
							</span>
						</div>
						<div class='pte_vault_row_75 pte_vault_right pte_toolbar_container'>
							{$interactionChooser} <i id='alpn_vault_interaction_start' class='far fa-arrow-circle-right alpn_icons_toolbar pte_ipanel_button_disabled' title='Start this Interaction' onclick='pte_handle_interaction_start(this);'></i>
							<div style='display: inline-block; width: 20px;'></div>
							<i id='alpn_vault_print' class='far fa-print pte_icon_button pte_ipanel_button_disabled' title='Print File' onclick='alpn_vault_control(\"print\")'></i>
							<i id='alpn_vault_download_original' class='far fa-file-download pte_icon_button pte_ipanel_button_disabled' title='Download Original File' onclick='alpn_vault_control(\"download_original\")'></i>
							<i id='alpn_vault_download_pdf' class='far fa-file-pdf pte_icon_button pte_ipanel_button_disabled' title='Download PDF File' onclick='alpn_vault_control(\"download_pdf\")'></i>
							<div style='display: inline-block; width: 20px;'></div>
							<i id='alpn_vault_chat' class='far fa-comment-alt-lines pte_icon_button pte_ipanel_button_disabled' title='Send an link to this vault item in Chat.' onclick='alpn_vault_control(\"insert_chat_vault_item\")' ></i>
							<i id='alpn_vault_links' class='far fa-link pte_icon_button pte_ipanel_button_disabled' title='Manage xLinks for this File' onclick='alpn_vault_control(\"links\")'></i>
						  <i id='alpn_vault_new' class='far fa-plus-circle pte_icon_button' title='Add New Vault Files' onclick='alpn_vault_control(\"add\")'></i>
							<i id='alpn_vault_edit' class='far fa-pencil-alt pte_icon_button pte_ipanel_button_disabled' title='Edit Vault File Settings' onclick='alpn_vault_control(\"edit\")'></i>
							<i id='alpn_vault_delete' class='far fa-trash-alt pte_icon_button pte_ipanel_button_disabled' title='Delete File from Vault' onclick='alpn_vault_control(\"delete\")'></i>
						</div>
						<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
	  			</div>

					<div id='pte_selected_topic_meta' class='alpn_container_title_2' data-mode='vault' data-topic-id='{$topicId}' data-tid='{$topicId}' data-ttid='{$topicTypeId}' data-special='{$topicTypeSpecial}' data-tdid='{$topicDomId}' data-oid='{$topicOwnerId}' data-pl='{$permissionLevel}'>
						<div id='pte_topic_form_title_view'>
							<span class='fa-stack pte_stacked_icon'>
								<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
								<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
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
				 ";
$html .= do_shortcode("[wpdatatable id=5 var1='{$topicId}' var2='{$permissionLevel}']");

//TODO add @media to CSS to remove padding when stacked. Also make table center or wider. And button layout toolbar.

$html .= "	</div>
						<div id='pte_vault_container' class='pte_outer_vault_small'>
							<div id='alpn_vault_work_area' class='alpn_vault_work_area'>
								<div id='alpn_vault_work_inner' class='alpn_vault_work_inner'></div>
							</div>
							<div id='alpn_add_edit_outer_container' class='alpn_add_edit_outer_container'></div>
							<div id='alpn_vault_preview_embedded'>
								<div id='pte_overlay_viewer'><div id='pte_overlay_message'></div></div>
								<div id='pte_pdf_ui'></div>
								{$pdfViewer}
							</div>
						</div>
						<div style='clear: both;'></div>
					</div>
				";
$html = str_replace('table_1', 'table_vault', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);

$html .= "
<script>
jQuery('#alpn_selector_interaction_selector').select2({
	theme: 'bootstrap',
	width: '180px',
	allowClear: false,
	templateSelection: iformat,
	templateResult: iformat,
	escapeMarkup: function(text) {
		return text;
	}
});
</script>
";

echo $html;

?>
