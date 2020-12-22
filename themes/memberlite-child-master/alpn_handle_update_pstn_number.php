<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$results = array();
$qVars = $_POST;
$phoneNumber = isset($qVars['phone_number']) ? $qVars['phone_number'] : '';
$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : '';

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

if ($ownerId && $phoneNumber && $topicId) {

	$numberData = array(
		"topic_id" => $topicId
	);

	$whereClause['pstn_number'] = $phoneNumber;
	$whereClause['owner_id'] = $ownerId;

	$wpdb->update( 'alpn_pstn_numbers', $numberData, $whereClause );

	$results['success'] = true;

}

pte_json_out($results);
?>
