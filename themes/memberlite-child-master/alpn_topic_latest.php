<?php
include('../../../wp-blog-header.php');

$userId = get_current_user_id();

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT n.* FROM alpn_user_metadata m LEFT JOIN alpn_topics n ON m.last_topic_add_id = n.id WHERE m.id = %s", $userId)
	);

if (array_key_exists("0", $results)){
	$results = $results[0];
}

header('Content-Type: application/json');
echo json_encode($results);

?>	