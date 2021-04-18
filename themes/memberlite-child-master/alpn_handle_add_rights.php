<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;

$html = "";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//Add lots of checks: Logged in, etc.

//TODO Check for dupes and don't insert -- enforce here important with logged in,. ALSO in Edit

if ($topicNetworkId) {

	$html = pte_create_topic_team_member();

}
echo $html;

?>
