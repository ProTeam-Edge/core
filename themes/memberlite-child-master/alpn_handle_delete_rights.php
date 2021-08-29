<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

alpn_log("About to Delete Team Member");

//TODO check logged in. query
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	alpn_log("Not a valid request 1");

	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
	 alpn_log("Not a valid request 2");
   die;
}
$qVars = $_POST;
$rowToDelete = isset($qVars['rowToDelete']) ? $qVars['rowToDelete'] : '';

$proTeamMemberResults = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT p.owner_id, p.topic_id, p.wp_id, p.connected_type, p.linked_topic_id, t1.dom_id AS topic_dom_id, t2.dom_id AS linked_topic_dom_id FROM alpn_proteams p LEFT JOIN alpn_topics t1 ON t1.id = p.topic_id LEFT JOIN alpn_topics t2 on t2.id = p.linked_topic_id WHERE p.id = %d", $rowToDelete)
 );

$results = $ptRow = array();

if (isset($proTeamMemberResults[0])) {

	$ptRow = $proTeamMemberResults[0];
	$ownerId = $ptRow->owner_id;
	$wpId = $ptRow->wp_id;
	$topicId = $ptRow->topic_id;
	$linkedTopicId = $ptRow->linked_topic_id;
	$connectedType = $ptRow->connected_type;

	$isOwner = $userId == $ownerId;
	$isMember = $userId == $wpId;

	//Only logged in user or
	if (!$isOwner && !$isMember) {
		echo 'Not a valid request.';
		alpn_log("Not a valid request 3");
		die;
	}
	//if link, change connection

		if ($connectedType == "link") {

			$proTeamData = array(
			"connected_type" => "join",
			"linked_topic_id" => NULL,
			'state' => "30"
		 );
		 $whereClause = array(
			 'owner_id' => $wpId,
			 'topic_id' => $linkedTopicId,
			 'linked_topic_id' => $topicId
		 );
		 $wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );

		 // alpn_log("DELETING AHDELETERIGHTS");
		 // alpn_log($whereClause);
		 // alpn_log($wpdb->last_query);
		 // alpn_log($wpdb->last_error);

	}

	$ccData = array(
		'owner_id' => $userId,
		'topic_id' => $topicId,
		'user_id' => $wpId
	);
	$roomEmpty = pte_manage_cc_groups("delete_member", $ccData);

	// Delete this ProTeam Record.
	$whereClause = array(
		'id' => $rowToDelete
	);
	$wpdb->delete( 'alpn_proteams', $whereClause );

}

header('Content-Type: application/json');
echo json_encode(array(
	"room_empty" => $roomEmpty,
	"tt_data" => $ptRow,
	"is_owner" => $isOwner,
	"is_member" => $isMember
));

?>
