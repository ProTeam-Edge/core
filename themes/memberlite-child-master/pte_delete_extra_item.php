<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$pVars = $_POST;
$recordId = isset($pVars['uid']) ? $pVars['uid'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($recordId) {
	$wpdb->delete( "alpn_topic_items", array( 'dom_id' => $recordId, 'owner_id' => $userID ) );
}

pte_json_out($recordId);


?>	