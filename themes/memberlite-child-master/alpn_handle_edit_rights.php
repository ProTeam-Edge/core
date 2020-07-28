<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;
$rightsInfo = isset($qVars['rightsInfo']) ? $qVars['rightsInfo'] : '';

$rightsInfoArr = json_decode(stripslashes($rightsInfo), true);

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//$html .= print_r($rightsInfoArr, true);

//Add lots of checks: Logged in, etc.

//Check for dupes and don't insert -- enforce here important with logged in,. ALSO in Edit

if (isset($rightsInfoArr['id'])) {

	$proTeamMemberId = $rightsInfoArr['id'];
	$key = $rightsInfoArr['key'];
	$checkState = ($rightsInfoArr['check_state'] == 'set') ? '0' : '1';   //opposite since sending current state.

	$results = $wpdb->query(
		$wpdb->prepare("UPDATE alpn_proteams SET member_rights = JSON_SET(member_rights, '$.{$key}', '{$checkState}') WHERE id = '%s' AND owner_id = '%s'", $proTeamMemberId, $userID)
	 );
}

header('Content-Type: application/json');
echo json_encode($results);

?>
