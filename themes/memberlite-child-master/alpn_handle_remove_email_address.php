<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$results = array();


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}


$qVars = $_POST;
$emailAddresses = '';

$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : '';
$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

if ($ownerId && $topicId) {  //TODO Assumes topic exists

	$routeData = array(
		"email_route_id" => NULL
	);

	$whereClause = array(
		"id" => $topicId,
		"owner_id" => $ownerId
	);
	$wpdb->update( 'alpn_topics', $routeData, $whereClause );
	//TODO add current email address to Sendgrid block list.
	$emailAddresses = get_routing_email_addresses();
}

echo $emailAddresses;

?>
