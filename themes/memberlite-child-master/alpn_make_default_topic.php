<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );
$results = array();

$qVars = $_POST;
$uniqueRecordId = isset($qVars['unique_record_id']) ? $qVars['unique_record_id'] : "";
$newLinkId = isset($qVars['new_link_id']) ? pte_digits($qVars['new_link_id']) : 0;
$ownerTopicId1 = isset($qVars['owner_topic_id_1']) ? pte_digits($qVars['owner_topic_id_1']) : 0;
$topicSubjectToken = isset($qVars['topic_subject_token']) ? $qVars['topic_subject_token'] : "";

$oldLinkId = '';

if ($newLinkId && $ownerTopicId1 && $topicSubjectToken) {

	try {
		//Get old id. Could have stuck to update but need ID on client.
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT id FROM alpn_topic_links WHERE owner_id_1 = %d AND owner_topic_id_1 = %d AND subject_token = %s AND list_default = 'yes'", $userID, $ownerTopicId1, $topicSubjectToken)
		 );

		 alpn_log($results);
		 alpn_log($wpdb->last_query);
		 alpn_log($wpdb->last_error);

		 if (isset($results[0])) {

			 $oldLinkId = $results[0]->id;

			 $linkUpdateData = array(
	 			'list_default' => "no"
	 			);
		 		$whereClause = array(
		 			'id' => $oldLinkId
		 		);
				$wpdb->update( 'alpn_topic_links', $linkUpdateData, $whereClause );

				alpn_log('FIRST');
				alpn_log($wpdb->last_query);
				alpn_log($wpdb->last_error);

		 }

		$linkUpdateData = array(
			'list_default' => "yes"
		);
		$whereClause = array(
			'id' => $newLinkId
		);

		//set new to yest
	$wpdb->update( 'alpn_topic_links', $linkUpdateData, $whereClause );

	alpn_log('SECOND');
	alpn_log($wpdb->last_query);
	alpn_log($wpdb->last_error);

	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
}
pte_json_out(array("qVars" => $qVars, 'old_link_id' => $oldLinkId));
?>
