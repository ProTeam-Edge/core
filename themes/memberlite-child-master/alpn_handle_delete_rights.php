<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

//TODO check logged in. query
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_POST;
$rowToDelete = isset($qVars['rowToDelete']) ? pte_digits($qVars['rowToDelete']) : '';

$proTeamMemberResults = $wpdb->get_results(
	$wpdb->prepare("SELECT topic_id, wp_id FROM alpn_proteams WHERE id = '%s' AND owner_id = '%s'", $rowToDelete, $userId)
 );

$results = array();
if (isset($proTeamMemberResults[0])) {
	$ptRow = $proTeamMemberResults[0];
	$wpId = $ptRow->wp_id;
	$topicId = $ptRow->topic_id;

	$deletedChannelToo = false;
	if ($wpId) {
		$data = array(
			'topic_id' => $topicId,
			'user_id' => $wpId
		);
		$deletedChannelToo = pte_manage_cc_groups("delete_member", $data);
	}
	$deleteResults = $wpdb->delete('alpn_proteams', array('id' => $rowToDelete, 'owner_id' => $userId));
	$results = array(
			'deleted_channel_too' => $deletedChannelToo
	);
}

header('Content-Type: application/json');
echo json_encode($results);

?>
