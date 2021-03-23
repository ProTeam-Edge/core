<?php
include('../../../wp-blog-header.php');

//TODO add sharing and other metrics

//TODO Can we cache more of this stuff?


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$channelId = "";
$qVars = $_GET;
$recordId = isset($qVars['record_id']) ? $qVars['record_id'] : '';
$indexType = isset($qVars['index_type']) ? $qVars['index_type'] : 'dom_id';

$results = array();
if ($indexType == "topic_id") {
	$recordId = pte_digits($recordId );
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT t.connected_id, t.connected_topic_id, t.id AS topic_id, t.dom_id, t.owner_id, t.channel_id, t.image_handle, t.name AS topic_name, t.about, tt.special, tt.id AS topic_type_id, tt.name AS topic_type_name, tt.icon, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.special = 'user' WHERE t.id = %s", $recordId)
	 );

} else {   //dom_id
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT t.connected_id, t.connected_topic_id, t.id AS topic_id, t.dom_id, t.owner_id, t.channel_id, t.image_handle, t.name AS topic_name, t.about, tt.special, tt.id AS topic_type_id, tt.name AS topic_type_name, tt.icon, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.special = 'user' WHERE t.dom_id = %s", $recordId)
	 );
}

//Sort out Contacts
if (isset($results[0]) && $results[0]->special == 'contact') {
	$topicData = $results[0];

	$userInfo = wp_get_current_user();
	$userId = $userInfo->data->ID;

	if ($userId != $topicData->owner_id) {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT t.connected_id, t.connected_topic_id, t.id AS topic_id, t.dom_id, t.owner_id, t.channel_id, t.image_handle, t.name AS topic_name, t.about, tt.special, tt.id AS topic_type_id, tt.name AS topic_type_name, tt.icon, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.special = 'user' WHERE t.id = %d", $topicData->connected_topic_id)
		 );
	}

}

// $results['last_query'] = $wpdb->last_query;
// $results['last_error'] = $wpdb->last_error;

header('Content-Type: application/json');
echo json_encode($results);

?>
