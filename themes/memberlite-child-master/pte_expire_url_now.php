<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();
$qVars = $_POST;
$linkId = isset($qVars['link_id']) ? $qVars['link_id'] : '';
$html = "";
$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//Add lots of checks: Logged in, etc.

//TODO Check for dupes and don't insert -- enforce here important with logged in,. ALSO in Edit
$now = date ("Y-m-d H:i:s", time());

	$linkInfo = array(
		'link_id' => $linkId,
		'owner_id' => $userID
	);
pte_manage_link('expire_link', $linkInfo);
pte_json_out($linkInfo);

?>
