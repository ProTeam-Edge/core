<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_GET;
$tableID = isset($qVars['tableId']) ? $qVars['tableId'] : 0;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : 'false';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$context = ($alpn_selected_type == 'user') ? "Me" : "Network";

$alpnNormalizeNetworkMap = array(
	"alpn_profile_first_name" => 3,
	"alpn_profile_last_name" => 4,
	"alpn_profile_primary_email" => 29,
	"alpn_profile_linkedin_url" => 21,
	"alpn_profile_cell_phone" => 5,
	"alpn_profile_desk_phone" => 7,
	"alpn_profile_fax_phone" => 8,
	"alpn_profile_title" => 35,
	"alpn_profile_linkedin_url" => 21,
	"alpn_profile_business_name" => 6,
	"alpn_profile_business_type" => 36,
	"alpn_profile_address_1" => 9,
	"alpn_profile_address_2" => 10,
	"alpn_profile_city" => 11,
	"alpn_profile_state" => 16,
	"alpn_profile_postalcode" => 17,
	"alpn_profile_website_url" => 20,
	"alpn_profile_about" => 27,
	"id" => 37
);


$results = $wpdb->get_results(
	$wpdb->prepare("SELECT * FROM alpn_network WHERE dom_id = %s", $recordId) 
 );

$record = $results[0];

unset($_GET['uniqueRecId']);
unset($_GET['tableId']);

foreach ($record as $key => $value) {
	if (array_key_exists($key, $alpnNormalizeNetworkMap)) {
		$wpf = "wpf{$tableID}_{$alpnNormalizeNetworkMap[$key]}";
		$_GET[$wpf] = $value;
	}
}

//Add lots of checks: Logged in, etc.

$html="";

$html .= "<div class='alpn_container_title_2'>
			<div class='alpn_container_2_left'>{$record->alpn_profile_last_name}, {$record->alpn_profile_first_name}</div>
			<div class='alpn_container_2_right'><i class='fa fa-edit' style='margin-bottom: 5px; font-size: 1.2em; color: #4499d7;'></i>&nbsp;&nbsp;{$context}</div>
		  </div>";
$html .= do_shortcode("[wpforms id='$tableID']");
echo $html;

?>	

