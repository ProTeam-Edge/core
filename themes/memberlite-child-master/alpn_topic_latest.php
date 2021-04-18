<?php
include('../../../wp-blog-header.php');

$userId = get_current_user_id();


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT n.special, n.dom_id, n.last_op, n.topic_type_id, n.id, n.name, n.about, m.last_return_to FROM alpn_user_metadata m LEFT JOIN alpn_topics n ON n.id = m.last_topic_add_id WHERE m.id = %s", $userId)
	);

if (isset($results[0])){
	$results = $results[0];
}

header('Content-Type: application/json');
echo json_encode($results);

?>
