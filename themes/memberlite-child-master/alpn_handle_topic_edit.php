<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();
$html="";
$qVars = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$uniqueRecId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : '';
$returnDetails = isset($qVars['return_details']) ? json_decode(stripslashes($qVars['return_details']), true) : array();

$rightsCheckData = array(
  "topic_dom_id" => $uniqueRecId
);
if (!pte_user_rights_check("topic_dom_edit", $rightsCheckData)) {
  $html = "
  <div class='pte_topic_error_message'>
     You do not have permission to edit this Topic.
  </div>";
  echo $html;
  exit;
}

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//TODO change context to cover User, Network, Topic & Topic Type and Name??

//TODO add userID to query  and/or check if logged in.

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.special = 'user' WHERE t.dom_id = %s", $uniqueRecId)
 );

$topicData = $results[0];
$topicId = $topicData->id;
$topicTypeId = $topicData->topic_type_id;
$topicTypeSpecial = $topicData->special;
$topicTypeName = $topicData->topic_name;
$topicImageHandle = $topicData->image_handle;
$topicProfileHandle = $topicData->profile_handle;
$topicName = $topicData->name;
$topicIcon = $topicData->icon;
$topicDomId = $topicData->dom_id;
$formId = $topicData->form_id;

$topicContent = json_decode($topicData->topic_content, true);
$topicMeta = json_decode($topicData->topic_type_meta, true);

$fieldMap = pte_map_extract($topicMeta['field_map']);

$actualValue = '';
foreach ($topicContent as $key => $value) {
	if (is_array($value)) {
		foreach ($value as $key2 => $value2) {    //Date I believe
			$wpf = "wpf{$formId}_{$fieldMap[$key]}_{$key2}";
			$_GET[$wpf] = $value2;
		}
	} else {
		$wpf = isset($fieldMap[$key]) ? "wpf{$formId}_{$fieldMap[$key]}" : "";
		$_GET[$wpf] = str_replace("\r\n", "*r*n*", $value);   //ENCODES carriage returns because wpforms does not support
	}
}

$topicItemMeta = array(
	"row_id" => $topicId,
	"return_details" => $returnDetails
);

$_GET["wpf{$formId}_0"] = json_encode($topicItemMeta); //handle unique topic id

//pp($_GET);

//$html .= print_r($_GET, true);
$context = $topicTypeName;
$name = $topicName;
$personalTopic = 'false';

if ($topicTypeSpecial == 'contact') {
	$context = "Contact";
}
if ($topicTypeSpecial == 'user') {
	$context = "Personal";
	$personalTopic = 'true';
}

$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";
if ($topicProfileHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 10px; color: rgb(68, 68, 68); font-size: 24px;'></i>";
}


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
							<i class='far fa-pencil-alt pte_title_icon_margin_right'></i>{$name}
						</div>
						<div id='pte_topic_form_title_view' class='pte_vault_right'>
							{$context} <div class='pte_title_topic_icon_container'>{$topicImage}</div>
						</div>
					</div>
			";
$editForm = do_shortcode("[wpforms id='$formId']");
$html .= "
						<div id='pte_editor_container' class='pte_vault_row' data-personal-topic='{$personalTopic}'>
							<div id='pte_topic_form_edit_view_left'>
								{$editForm}
							</div>
							<div id='pte_topic_form_edit_view_right'>
							</div>
						 </div>
						";


echo $html;

?>
