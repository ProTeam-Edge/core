<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();
$qVars = $_POST;
$data = isset($qVars['data']) ? $qVars['data'] : array();
$html = "";
$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//Add lots of checks: Logged in, etc.

//TODO Check for dupes and don't insert -- enforce here important with logged in,. ALSO in Edit

	$newUrl = array(
		'link_type' => 'file',
		'owner_id' => $userID,
		'vault_id' => $data['vault_id'],
		'link_interaction_password' => $data['link_password'],
		'link_interaction_expiration' => $data['link_expiration'],
		'link_interaction_options' => $data['link_options'],
		'link_about' => $data['link_about']
	);
$newUrl['uid'] = pte_manage_link('create_link', $newUrl);
pte_json_out($newUrl);

?>
