<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$deleteChannelData = $chatRoomRemoveList = array();

$qVars = $_POST;
$returnDetails = isset($qVars['return_details']) ? json_decode(stripslashes($qVars['return_details']), true) : array();

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;
$userTopicId = get_user_meta( $userId, 'pte_user_network_id', true );

$topicId = $returnDetails['topic_id'];
$topicSpecial = $returnDetails['topic_special'];

if ($userTopicId == $topicId) {
	pte_json_out(array("error" => "Cannot delete your personal topic."));
	return;
}

if ($topicId) {
//Delete Topic Records and Lists because they are Topic Specific. Should be OK because we will dissallow all other teaming and what not.
	$wpdb->get_results(
		$wpdb->prepare("DELETE FROM alpn_topics WHERE id IN (SELECT connected_topic_id FROM alpn_topics_linked_view WHERE owner_topic_id = %d AND topic_class = 'list' OR topic_class = 'record')", $topicId)
	);
	//Store vault object keys to batch delete
	$wpdb->get_results(
		$wpdb->prepare("INSERT INTO alpn_object_keys_to_delete SELECT pdf_key AS object_key FROM alpn_vault WHERE owner_id = %d AND topic_id = %d AND pdf_key <> ''", $userId, $topicId)
	);
	$wpdb->get_results(
		$wpdb->prepare("INSERT INTO alpn_object_keys_to_delete SELECT file_key AS object_key FROM alpn_vault WHERE owner_id = %d AND topic_id = %d AND file_key <> ''", $userId, $topicId)
	);
	//Delete Vault Items for this Topic
	$whereclause = array('owner_id' => $userId, "topic_id" => $topicId);
	$wpdb->delete( "alpn_vault", $whereclause );
	//Any topic links to or from this Topic
	$whereclause = array('owner_topic_id_1' => $topicId);
	$wpdb->delete( "alpn_topic_links", $whereclause );
	$whereclause = array('owner_topic_id_2' => $topicId);
	$wpdb->delete( "alpn_topic_links", $whereclause );
	//Prioritization Lists
	$whereclause = array('owner_id' => $userId, "item_id" => $topicId);
	$wpdb->delete( "alpn_user_lists", $whereclause );
	//Fax routes
	$whereclause = array('owner_id' => $userId, "topic_id" => $topicId);
	$pstnData = array('topic_id' => NULL);
	$wpdb->update( "alpn_pstn_numbers", $pstnData, $whereclause );
	if ($topicSpecial == 'contact') {

		$connectedContactData = $wpdb->get_results($wpdb->prepare("SELECT id, channel_id, connected_topic_id, owner_id FROM alpn_topics WHERE connected_topic_id = %d", $topicId));

		if (isset($connectedContactData[0])) {
			$connectedChannelId = $connectedContactData[0]->channel_id;
			$connectedOwnerId = $connectedContactData[0]->owner_id;
			$connectedTopicId = $connectedContactData[0]->id;

			//Remove members from other rooms
			$chatRoomsToHandle = $wpdb->get_results($wpdb->prepare("SELECT topic_id FROM alpn_proteams WHERE proteam_member_id = %d AND wp_id <> ''", $topicId));
			foreach ($chatRoomsToHandle as $value) {
				$chatRoomRemoveList[] = array(
					"topic_id" => $topicId,
					"user_id_to_remove" => $connectedOwnerId
				);
			}
			foreach ($chatRoomRemoveList as $value) {
				$deleteMemberData = array(
					"user_id" => $value['user_id_to_remove'],
					"owner_id" => $userId,
					"topic_id" => $value['topic_id']
				);
				pte_manage_cc_groups("delete_member", $deleteMemberData);
			}
			//delete this room

			$deleteRoomData = array(
				"channel_id" => $connectedChannelId
			);
			pte_manage_cc_groups("delete_channel_by_channel_id", $deleteRoomData);

			//take them out of any proteams they may be on.
			$whereclause = array('owner_id' => $userId, 'proteam_member_id' => $topicId);
			$wpdb->delete( "alpn_proteams", $whereclause );
			//reset records of my connections.
			$whereclause = array('id' => $connectedTopicId);
			$topicData = array('connected_id' => NULL, 'connected_topic_id' => NULL, 'connected_network_id' => NULL, 'channel_id' => NULL);
			$wpdb->update( "alpn_topics", $topicData, $whereclause );

			alpn_log($wpdb->last_query);
			alpn_log($wpdb->last_error);

		}

		$notifyData = array('contact_id' => $userId);
		wcl_notify_contact_of_request($notifyData);
	}

	if ($topicSpecial == 'topic') {
		$connectedMember = $wpdb->get_results($wpdb->prepare("SELECT channel_id FROM alpn_topics WHERE id = %d AND channel_id IS NOT NULL", $topicId));
		if (isset($connectedMember[0])) {
			$channelId = $connectedMember[0]->channel_id;
			$deleteChannelData = array(
				"channel_id" => $channelId
			);
			pte_manage_cc_groups("delete_channel_by_channel_id", $deleteChannelData);
		}
	}

	//ProTeams for this Topic
	$whereclause = array('topic_id' => $topicId);
	$wpdb->delete( "alpn_proteams", $whereclause );

	//Delete the Topic


	$whereclause = array('owner_id' => $userId, "id" => $topicId);
	$wpdb->delete( "alpn_topics", $whereclause );

}

pte_json_out(array("error" => false));
?>
