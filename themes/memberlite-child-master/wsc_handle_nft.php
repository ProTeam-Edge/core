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

// wsc_track_accounts("0xEB889d3FFD7170cD1E25A3B2cB0D522b8EAA5CB7");

// pp($userID);
// pp($userMeta);

$rightsCheckData = array(
  "topic_dom_id" => $recordId
);
if (!pte_user_rights_check("topic_dom_edit", $rightsCheckData)) {
  $html = "
  <div class='pte_topic_error_message'>
     You do not have permission to see this NFT view.
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
	$topicTypeSpecial = $topicSpecial = $record->special;
	$topicIcon = $record->icon;
	$topicName = $record->topic_name;
	$topicId = $record->id;
	$topicDomId = $record->dom_id;
	$topicImageHandle = $record->image_handle;
	$topicProfileHandle = $record->profile_handle;
	$topicOwnerId = $record->owner_id;
	$context = $topicName;
	$accessLevel = $record->access_level;

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
		$ownerFirstName = "<div id='pte_interaction_owner_outer'><div id='pte_interaction_owner_inner_message'>Visiting</div><div id='pte_interaction_owner_inner_name'>Owner --  {$ownerFirst}</div></div>";
	  $showIconAccordian = "none";
		$showLogoAccordion = "none";
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

	$context = "Topic";
	if ($topicSpecial == 'contact' || $topicSpecial == 'user' ) {   //user or network

		if ($topicSpecial == 'contact') {
			$context = "Contact";
		}
		if ($topicSpecial == 'user') {
			$context = "Personal";
		}
	}

$nftToolbar = wsc_get_nft_view_toolbar();

$html .= "
				<div id='pte_selected_topic_meta' class='alpn_container_title_2' data-mode='design' data-topic-id='{$topicId}' data-tid='{$topicId}' data-ttid='{$topicTypeId}' data-special='{$topicTypeSpecial}' data-tdid='{$topicDomId}' data-tkey='{$typeKey}' data-oid='{$topicOwnerId}' data-wal='{$accessLevel}' data-con='{$isConnected}'>
					<div id='pte_topic_form_title_view'>
						<span class='fa-stack pte_icon_button_nav ' title='Data View' data-operation='to_info' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
							<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
							<i class='fas fa-info fa-stack-1x' style='font-size: 16px;'></i>
						</span>
						<span class='fa-stack pte_icon_button_nav' title='Vault View' data-operation='to_vault' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
							<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
							<i class='fas fa-lock-alt fa-stack-1x' style='font-size: 16px; top: -1px;'></i>
						</span>
						<span class='fa-stack pte_icon_button_nav pte_icon_report_selected' title='NFT View' data-operation='to_nft' onclick='event.stopPropagation(); pte_handle_interaction_link_object(this);'>
							<i class='far fa-circle fa-stack-1x' style='font-size: 30px;'></i>
							<i class='fas fa-cube fa-stack-1x' style='font-size: 16px;'></i>
						</span>
						<span id='pte_topic_name'>{$context}{$ownerFirstName}</span>
					</div>
					<div id='pte_topic_form_title_view' class='pte_vault_right'>
						{$record->name} <div class='pte_title_topic_icon_container'>{$topicImage}</div>
					</div>
				</div>

				<div class='outer_button_line' style='min-height: 42px;'>
					<div class='pte_vault_row_100'>
						{$nftToolbar}
					</div>
					<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
				</div>
				<div id='outer-gallery-container' style='text-align: center;'>
					<div id='nft-gallery-container' class='nft-gallery-container'>
					</div>
				</div>
				<script>
					var nft_state = {$record->nft_view_state};
					wsc_change_nfts('', nft_state);
				</script>
";

echo $html;

?>
