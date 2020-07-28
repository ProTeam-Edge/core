<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;
$uniqueRecId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : '';
$pteUserTimezoneOffset = isset($qVars['pte_user_timezone_offset']) ? $qVars['pte_user_timezone_offset'] : '';

$html="";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//TODO change context to cover User, Network, Topic & Topic Type and Name??

//TODO add userID to query  and/or check if logged in.

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.topic_type_id=5 WHERE t.dom_id = %s", $uniqueRecId)
 );

$topicData = $results[0];
$topicId = $topicData->id;
$topicTypeId = $topicData->topic_type_id;
$topicTypeName = $topicData->topic_name;
$topicImageHandle = $topicData->image_handle;
$topicProfileHandle = $topicData->profile_handle;
$topicName = $topicData->name;
$topicIcon = $topicData->icon;
$topicDomId = $topicData->dom_id;
$formId = $topicData->form_id;

$topicContent = json_decode($topicData->topic_content, true);
$topicMeta = json_decode($topicData->topic_type_meta, true);

$fieldMap = $topicMeta['field_map'];
$uniqueFieldId = $topicMeta['pte.meta'];

$actualValue = '';
foreach ($topicContent as $key => $value) {
	if (is_array($value)) {
		foreach ($value as $key2 => $value2) {
			$wpf = "wpf{$formId}_{$fieldMap[$key]}_{$key2}";
			$_GET[$wpf] = $value2;
		}
	} else {
		$wpf = "wpf{$formId}_{$fieldMap[$key]}";
		$_GET[$wpf] = $value;
	}
}

$topicItemMeta = array(
	"row_id" => $topicId,
	"pte_user_timezone_offset" => $pteUserTimezoneOffset
);

$_GET["wpf{$formId}_{$uniqueFieldId}"] = json_encode($topicItemMeta); //handle unique topic id

//$html .= print_r($_GET, true);

$context = $topicTypeName;
$name = $topicName;

if ($topicTypeId == '4') {
	$context = "Network";
}
if ($topicTypeId == '5') {$context = "Me";}

$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";
if ($topicProfileHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 15px; margin-top: 2px; color: rgb(68, 68, 68); font-size: 1.2em;'></i>";
}

$html .= "<div class='outer_button_line'>
			  <i class='far fa-lock-alt pte_icon_button' title='Vault' onclick='alpn_mission_control(\"vault\", \"{$topicDomId}\")' style='font-size: 28px; width: 40px; float: left; margin-left: 10px;'></i>
			  <div id='alpn_message_area' class='alpn_message_area'>
			  </div>
			  <div id='alpn_vault_button_bar' class='alpn_vault_button_bar'>
			  </div>
			  <div style='clear: both;'></div>
		  </div>
		  ";

$html .= "<div class='alpn_container_title_2' style='margin-bottom: 30px;'>
			<div class='alpn_container_2_left'><i class='far fa-pencil-alt' style='width: 30px; margin-bottom: 5px; font-size: 1.2em; color: rgb(68, 68, 68);'></i>&nbsp;&nbsp;{$name}</div>
			<div class='alpn_container_2_right'><div style='display: inline-block; vertical-align: middle;'>{$context}</div><div style='display: inline-block;  vertical-align: middle; height: 35px;'>{$topicImage}</div></div>
		  </div>
		  <div style='clear: both;'></div>";
$html .= do_shortcode("[wpforms id='$formId']");
echo $html;

?>
