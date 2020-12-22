<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc. Good Request. User-ID in all mysql

$qVars = $_GET;
$vaultId = isset($qVars['vaultId']) ? $qVars['vaultId'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;	

global $wpdb;

//Delete at PTE
$statusPte = array();
if ($vaultId != '') {

	try {
			$whereClause['id'] = $vaultId;  //TODO AND $userId
			$statusPte = $wpdb->delete( 'alpn_vault', $whereClause );	
	}
	catch(Exception $e) {
	$statusPte = $e;
	}
}

header('Content-Type: application/json');
echo json_encode($statusPte);

?>	