<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

alpn_log("About to Update Team Member");

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

$data = isset($qVars['data']) ? json_decode(stripslashes($qVars['data']), true) : array();


	switch ($statusType) {
		case '0':   //Join
			$connectedType = "join";



		break;
		case '1':  //Link
			$connectedType = "link";

			// $proTeamData = array(
			// 	"owner_id" => $linkedTopicId,
			// 	"topic_id" => $linkedTopicId,
			// 	"proteam_member_id" => $linkedTopicId,
			// 	"acess_level" => $linkedTopicId,
			// 	"member_rights" => $linkedTopicId,
			// 	"state" => $linkedTopicId,
			// 	"wp_id" => $linkedTopicId,
			// 	"process_id" => $linkedTopicId,
			// 	"connected_type" => $connectedType,
			// 	"linked_topic_id" => $linkedTopicId,
			// );
			// $wpdb->insert( 'alpn_proteams', $proTeamData, $whereClause );

		break;
	}

$proTeamData = array(
	"connected_type" => $connectedType,
	"linked_topic_id" => $linkedTopicId
);
$whereClause['id'] = $proteamRowId;
$wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );

header('Content-Type: application/json');
echo json_encode($data);

?>
