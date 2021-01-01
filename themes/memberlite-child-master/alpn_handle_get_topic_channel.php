<?php
include('../../../wp-blog-header.php');

//TODO add sharing and other metrics

//TODO Can we cache more of this stuff?


$channelId = "";
$qVars = $_GET;
$recordId = isset($qVars['record_id']) ? $qVars['record_id'] : '';
$indexType = isset($qVars['index_type']) ? $qVars['index_type'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;


$results = array();
if ($indexType == "topic_id") {
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT t.channel_id, t.image_handle, t.name AS topic_name, t.about, tt.id AS topic_type_id, tt.name AS topic_type_name, tt.icon, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.topic_type_id=5 WHERE t.id = %s", $recordId)
	 );

} else {   //dom_id
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT t.channel_id, t.image_handle, t.name AS topic_name, t.about, tt.id AS topic_type_id, tt.name AS topic_type_name, tt.icon, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.topic_type_id=5 WHERE t.dom_id = %s", $recordId)
	 );
}
//$results['last_query'] = $wpdb->last_query;
//$results['last_error'] = $wpdb->last_error;

header('Content-Type: application/json');
echo json_encode($results);

?>
