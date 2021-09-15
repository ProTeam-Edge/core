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

$statusType = isset($data['id']) ? $data['id'] : 'error';
$currentTopicId = isset($data['current_topic_id']) && $data['current_topic_id'] ? $data['current_topic_id'] : false;
$linkedTopicId = isset($data['selected_topic_id']) && $data['selected_topic_id'] ? $data['selected_topic_id'] : false;
$proteamRowId = isset($data['proteam_row_id']) && $data['proteam_row_id'] ? $data['proteam_row_id'] : false;
$visitingOwnerId = isset($data['visiting_owner_id']) && $data['visiting_owner_id'] ? $data['visiting_owner_id'] : false;
$listChange = isset($data['list_changed']) && $data['list_changed'] ? $data['list_changed'] : false;

$isTopicChange = $isConnectionTypeChange = $connectedType = false;

if ($proteamRowId && $statusType != 'error') {

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.owner_id, p.topic_id, p.wp_id, p.connected_type, p.linked_topic_id, t1.dom_id AS topic_dom_id, t2.dom_id AS linked_topic_dom_id FROM alpn_proteams p LEFT JOIN alpn_topics t1 ON t1.id = p.topic_id LEFT JOIN alpn_topics t2 on t2.id = p.linked_topic_id WHERE p.id = %d", $proteamRowId)
	 );

	 if (isset($results[0])) {
		 $proTeamData = $results[0];
		 $currentTopicId = $proTeamData->topic_id;
		 $currentTopicDomId = $proTeamData->topic_dom_id;
		 $currentLinkedTopicId = $proTeamData->linked_topic_id;
		 $currentLinkedTopicDomId = $proTeamData->linked_topic_dom_id;

		 $linkedTopicDomId = wic_get_domId_from_topicId($linkedTopicId);

	} else {
		alpn_log("Error updating ProTeam - 1");
		die;
	}
} else {
	alpn_log("Error updating ProTeam - 2");
	die;
}

if ($listChange == "topic_id") {
//  Link Topic ID Change. Remove from one, add to others

		$checkDupe =  wsc_check_team_dupe($linkedTopicId, $visitingOwnerId, $proteamRowId);
		if ($checkDupe['is_dupe']) {
			pte_json_out(array("error" => "Already on Team", "current_link_id" => $checkDupe['current_link_id']));
			exit;
		 }

		$isTopicChange = true;

		$ccData = array(
			'owner_id' => $userId,
			'topic_id' => $currentLinkedTopicId,
			'user_id' => $visitingOwnerId
		);
		async_pte_manage_cc_groups("delete_member", $ccData);

		$ccData = array(
			'owner_id' => $userId,
			'topic_id' => $linkedTopicId,
			'user_id' => $visitingOwnerId
		);
		async_pte_manage_cc_groups("add_member", $ccData);

	 $proTeamData = array(
			"linked_topic_id" => $linkedTopicId
		 );
	 $whereClause = array(
			 'id' => $proteamRowId
	 );
 $wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );

 $proTeamData = array(
		"topic_id" => $linkedTopicId
	 );
	 $whereClause = array(
		 'owner_id' => $userId,
		 'topic_id' => $currentLinkedTopicId,
		 'linked_topic_id' => $currentTopicId
	);
$wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );

// alpn_log("DELETING VITUP LIST CHANGE");
// alpn_log($whereClause);
// alpn_log($wpdb->last_query);
// alpn_log($wpdb->last_error);

} else if ($listChange == "connection_type") {

		$isConnectionTypeChange = true;
		//needed to get proper ID of MY Contact (not the network id of the contact)
		 $contact = $wpdb->get_results(
			$wpdb->prepare("SELECT id FROM alpn_topics WHERE connected_id = %d AND owner_id = %d AND special = 'contact'", $visitingOwnerId, $userId)
		 );

		 if (isset($contact[0])) {

			 switch ($statusType) {
			 	case '0':   //From Link to Join. Change link 1. Remove link 2.

			 		$topicState = "30";
			 		$connectedType = "join";

					$ccData = array(
						'owner_id' => $userId,
						'topic_id' => $currentLinkedTopicId,
						'user_id' => $visitingOwnerId
					);
					async_pte_manage_cc_groups("delete_member", $ccData);

					// Delete linked ProTeam Record.
					$whereClause = array(
						'owner_id' => $userId,
						'topic_id' => $currentLinkedTopicId,
						'linked_topic_id' => $currentTopicId
					);
					$wpdb->delete( 'alpn_proteams', $whereClause );

					// alpn_log("DELETING VITUP LINK TO JOIN");
					// alpn_log($whereClause);
					// alpn_log($wpdb->last_query);
					// alpn_log($wpdb->last_error);

					$proTeamData = array(
	 			 	"connected_type" => $connectedType,
	 			 	"linked_topic_id" => NULL,
	 			 	'state' => $topicState
	 			 );
				 $whereClause = array(
						 'id' => $proteamRowId
				 );
	 			 $wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );

			 	break;
			 	case '1':  //From Join to Link

				 $checkDupe =  wsc_check_team_dupe($linkedTopicId, $visitingOwnerId, $proteamRowId);
				 if ($checkDupe['is_dupe']) {
					 pte_json_out(array("error" => "Already on Team", "current_link_id" => $checkDupe['current_link_id']));
					 exit;
					}

			 		$topicState = "40";
			 		$connectedType = "link";

					$isTopicChange = true;

			 		$ccData = array(
			 			'owner_id' => $userId,
			 			'topic_id' => $linkedTopicId,
			 			'user_id' => $visitingOwnerId
			 		);
			 		async_pte_manage_cc_groups("add_member", $ccData);

			 		 $proTeamData = array(
			 				'owner_id' => $userId,  //owner_id
			 				'topic_id' => $linkedTopicId,
			 				'proteam_member_id' => $contact[0]->id,
			 				'wp_id' => $visitingOwnerId,
			 				'connected_type' => $connectedType,
			 				'access_level' => '10',
			 				'state' => $topicState, //linked
			 				'linked_topic_id' => $currentTopicId,
			 				'member_rights' => false  //TODO uses default until we want to specify something here.
			 			);
			 			pte_add_to_proteam($proTeamData);

						//Point Back
						$proTeamData = array(
		 			 	"connected_type" => $connectedType,
		 			 	"linked_topic_id" => $linkedTopicId,
		 			 	'state' => $topicState
		 			 );
					 $whereClause = array(
							 'id' => $proteamRowId
					 );
		 			 $wpdb->update( 'alpn_proteams', $proTeamData, $whereClause );
			 	break;
			 }
		}
}

$returnData = array(
	"is_topic_change" => $isTopicChange,
	"is_connection_type_change" => $isConnectionTypeChange,
	"current_topic_id" => $currentTopicId,
	"current_topic_dom_id" => $currentTopicDomId,
	"current_linked_topic_id" => $currentLinkedTopicId,
	"current_linked_topic_dom_id" => $currentLinkedTopicDomId,
	"new_linked_topic_id" => $linkedTopicId,
	"new_linked_topic_dom_id" => $linkedTopicDomId,
	"connected_type" => $connectedType
);

header('Content-Type: application/json');
echo json_encode($returnData);

?>
