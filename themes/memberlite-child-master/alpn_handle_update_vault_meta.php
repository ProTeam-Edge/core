<?php
include('../../../wp-blog-header.php');

//TODO add sharing and other met
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_POST;
$vault_id = isset($qVars['vault_id']) ? $qVars['vault_id'] : '';
$description = isset($qVars['description']) ? $qVars['description'] : '';
$permissionValue = isset($qVars['permission_value']) ? $qVars['permission_value'] : '40';
$fieldName = isset($qVars['field_name']) ? $qVars['field_name'] : '';

$rowData = array(
	"description" => $description,
	"file_name" => $fieldName,
	"access_level" => $permissionValue
);

//TODO Make sure filename is a valid filename. Add Extension.

$results = array();

$whereClause['id'] = $vault_id;
//$whereClause['owner_id'] = $userId;

$wpdb->update( 'alpn_vault', $rowData, $whereClause );

$results = array(
	"lq" => $wpdb->last_query,
	"le" => $wpdb->last_error,
	"post" => $qVars
);

header('Content-Type: application/json');
echo json_encode($results);

?>
