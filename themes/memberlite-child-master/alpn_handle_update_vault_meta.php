<?php
include('../../../wp-blog-header.php');

//TODO add sharing and other met
				
$qVars = $_GET;
$vault_id = isset($qVars['vault_id']) ? $qVars['vault_id'] : '';
$description = isset($qVars['description']) ? $qVars['description'] : '';
$permissionValue = isset($qVars['permission_value']) ? $qVars['permission_value'] : '40';

$userId = get_current_user_id();
	
$rowData = array(
	"description" => $description,
	"access_level" => $permissionValue
);

$results = array();

$whereClause['id'] = $vault_id; 
$whereClause['owner_id'] = $userId; 

$wpdb->update( 'alpn_vault', $rowData, $whereClause );	

$results['last_query'] = $wpdb->last_query;
$results['last_error'] = $wpdb->last_error;

header('Content-Type: application/json');
echo json_encode($results);

?>