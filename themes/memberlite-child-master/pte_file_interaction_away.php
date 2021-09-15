<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$processId = isset($qVars['process_id']) ? $qVars['process_id'] : '';

$userInfo = wp_get_current_user();
$owner_id = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $owner_id, 'pte_user_network_id', true ); //Owners Topic ID

//TODO determine what file away means per type. On unstarted or messages, means delete and backout a state thing. STATE -- we need a state table that tracks states for things like active sending faxes and invitations



$interactionData = array();
$whereClause = array();

$interactionData['state'] = 'filed';
$whereClause['process_id'] = $processId;
$wpdb->update( 'alpn_interactions', $interactionData, $whereClause );

pte_json_out($whereClause);

?>
