<?php
include('../../../wp-blog-header.php');

//TODO beck logged in.

$siteUrl = get_site_url();

$qVars = $_GET;
$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $pVars['security'], 'alpn_script' );
if($verify==1) {
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
}
else
{
	$results='Not a valid request.';
}
echo json_encode($results);
?>
