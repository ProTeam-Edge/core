<?php
include('../../../wp-blog-header.php');

alpn_log("VAULT FILES START");

global $wpdb;

//TODO Check logged in, etc. Good Request. User-ID in all mysql
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$qVars = $_POST;
$topicId = isset($qVars['topicId']) ? $qVars['topicId'] : '';
$topicOwnerId = isset($qVars['topic_owner_id']) ? $qVars['topic_owner_id'] : 0;
$description = isset($qVars['description']) ? $qVars['description'] : '';
$pteUploads = isset($qVars['pte_file_data']) ? $qVars['pte_file_data'] : array();
$permissionValue = isset($qVars['permissionValue']) ? $qVars['permissionValue'] : '40';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$pteItems = array();
if (isset($pteUploads[0])) {

	foreach ($pteUploads as $key => $value) {
		$fileName = $value['name'];
		$originalExt = $value['original_ext'];
		$mimeType = $value['mimeType'];
		$fileSource = "";
		$uploadId = $value['pte_uid'];
		$now = date ("Y-m-d H:i:s", time());

		$rowData = array(
			"creator_id" => $userID,
			"owner_id" => $topicOwnerId,
			"upload_id" => $uploadId,
			"name" => 'File',
			"file_name" => $fileName,
			"modified_date" =>  $now,
			"created_date" =>  $now,
			"topic_id" => $topicId,
			"mime_type" => $mimeType,
			"description" => $description,
			"file_source" => $fileSource,
			"access_level" => $permissionValue,
			"original_ext" => $originalExt,
			"status" => 'added'
		);
		$wpdb->insert( 'alpn_vault', $rowData );      //TODO make into a single insert...Optimization

		alpn_log($rowData);
		alpn_log($wpdb->last_query);
		alpn_log($wpdb->last_error);

		$pteItems[] = $rowData;
	}
}

$pte_response = array("topic" => "pte_handle_files_start_success", "message" => "Successfully registered vault items.", "data" => $pteItems);
pte_json_out($pte_response);
?>
