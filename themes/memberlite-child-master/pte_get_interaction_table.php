<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;
$html ='';

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}

$showType = isset($qVars['show_type']) ? $qVars['show_type'] : '';


alpn_log('Getting interaction Table...');
alpn_log($showType);

$userInfo = wp_get_current_user();
$owner_id = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $owner_id, 'pte_user_network_id', true ); //Owners Topic ID

$interactionFilterTypes = array(
  "0" => "All",
  "1" => "For Selected Topic",
  "2" => "Faxes Sent",
  "3" => "Faxes Received",
  "4" => "ProTeam Invite",
  "5" => "Form Fill Requests"
);

$optionsStr = '';
foreach ($interactionFilterTypes as $key => $value){
  $optionsStr .= "<option value='{$key}'>{$value}</option>";
}
$filterContainer = "<div id='pte_interaction_table_filter_container' class='alpn_selector_container_left'><select id='pte_interaction_table_filter' class='alpn_selector'>{$optionsStr}</select></div>";

$html = do_shortcode("[wpdatatable id=4 var1='{$ownerNetworkId}' var2='{$showType}']");
$html = str_replace('table_1', 'table_interactions', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);

$results = array(
  'table_html' => $html,
  'filter_html' => ""
);

pte_json_out($results);

?>
