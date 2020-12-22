<?php
include('../../../wp-blog-header.php');

global $wpdb;

//TODO Check logged in, etc. Good Request. User-ID in all mysql

$qVars = $_GET;
$topicId = isset($qVars['topicId']) ? $qVars['topicId'] : '';
$formId = isset($qVars['formId']) ? $qVars['formId'] : '';
$formName = isset($qVars['formName']) ? $qVars['formName'] : '';
$description = isset($qVars['description']) ? $qVars['description'] : '';
$vaultId = isset($qVars['vaultId']) ? $qVars['vaultId'] : '';
$addGuid = isset($qVars['addGuid']) ? $qVars['addGuid'] : '';
$permissionValue = isset($qVars['permissionValue']) ? $qVars['permissionValue'] : '40';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$alpn_uid = $userID;
$alpn_topic_id = $topicId;
$alpn_form_id = $formId;
$alpn_form_name = $formName;
$alpn_file_name = '';
$now = date ("Y-m-d H:i:s", time());

if ($vaultId) {
	$rowData = array(
		"submission_id" => '',
		"modified_date" => $now,
		"upload_id" => '',
		"description" => $description,
		"access_level" => $permissionValue
	);	
	$whereClause['id'] = $vaultId; 
	$wpdb->update( 'alpn_vault', $rowData, $whereClause );	
} else { 	
	$rowData = array(
		"owner_id" => $alpn_uid,
		"submission_id" => '',
		"form_id" => $alpn_form_id,
		"name" => $alpn_form_name,
		"file_name" => $alpn_file_name,
		"modified_date" =>  $now,
		"created_date" =>  $now,
		"topic_id" => $alpn_topic_id,
		"upload_id" => $addGuid,
		"description" => $description,
		"mime_type" => "application/pdf",
		"file_source" => "pte_form",
		"access_level" => $permissionValue
	);	
	$wpdb->insert( 'alpn_vault', $rowData );
}
	
$pte_response = array("topic" => "pte_get_add_edit_vault_item_successful", "message" => "Added or edited vault item successfully", "data" => $rowData);
pte_json_out($pte_response);


?>	