<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;
$topicTypeId = isset($qVars['topicTypeId']) ? $qVars['topicTypeId'] : '';
$pteUserTimezoneOffset = isset($qVars['pte_user_timezone_offset']) ? $qVars['pte_user_timezone_offset'] : '';
$topicDomId = isset($qVars['previous_topic']) ? $qVars['previous_topic'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//TODO check logged in

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT name, form_id, icon, topic_type_meta FROM alpn_topic_types WHERE id = %s", $topicTypeId) 
 );

if (array_key_exists(0, $results)) {
	$topicType = $results['0'];
	$formId = $topicType->form_id;
	$name = $topicType->name;
	$icon = $topicType->icon; 

$html = "";
	
$topicMeta = json_decode($topicType->topic_type_meta, true);
$uniqueFieldId = $topicMeta['pte.meta'];	
	
$topicItemMeta = array(
	"row_id" => "",
	"pte_user_timezone_offset" => $pteUserTimezoneOffset
);
	
$_GET["wpf{$formId}_{$uniqueFieldId}"] = json_encode($topicItemMeta); //handle unique topic id	

//Add lots of checks: Logged in, etc.

//$html = print_r($results, true);
	
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
			<div class='alpn_container_2_left'><i class='far fa-plus-circle' style='width: 30px; margin-bottom: 5px; font-size: 1.2em; color: rgb(68, 68, 68);'></i>&nbsp;&nbsp;New</div>
			<div class='alpn_container_2_right'>{$name}<i class='{$icon}' style='margin-left: 15px; color: rgb(68, 68, 68);'></i></div>
		  </div>
		  <div style='clear: both;'></div>";
$html .= do_shortcode("[wpforms id='$formId']");
} else {
	$html = "<div>Error</div>";
}
echo $html;

?>	