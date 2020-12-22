<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
use PascalDeVink\ShortUuid\ShortUuid;

$results = array();
$qVars = $_POST;
$emailAddresses = '';

$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : '';

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

if ($ownerId && $topicId) {  //TODO Assumes topic exists
	$shortUuid = new ShortUuid();
	$routeData = array(
		"email_route_id" => $shortUuid->uuid4()
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
