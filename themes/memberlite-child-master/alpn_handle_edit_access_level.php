<?php
include('../../../wp-blog-header.php');

//TODO beck logged in.

$siteUrl = get_site_url();

$qVars = $_GET;
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$proTeamId = isset($qVars['proTeamId']) ? $qVars['proTeamId'] : '';
$proTeamValue = isset($qVars['proTeamValue']) ? $qVars['proTeamValue'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($proTeamId && $userID) {
	$results = $wpdb->query(
		$wpdb->prepare("UPDATE alpn_proteams SET access_level = '%s' WHERE id = '%s' AND owner_id = '%s'", $proTeamValue, $proTeamId, $userID)
	 );
}

header('Content-Type: application/json');

echo json_encode($results);
?>
