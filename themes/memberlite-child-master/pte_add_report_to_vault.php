<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;
use Ramsey\Uuid\Uuid;

//TODO Check logged in, etc. Good Request. User-ID in all mysql

alpn_log('ADD REPORT');
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_POST;
$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : '';
$topicName = isset($qVars['topic_name']) ? $qVars['topic_name'] : '';
$topicDomId = isset($qVars['topic_dom_id']) ? $qVars['topic_dom_id'] : '';
$topicDescription = isset($qVars['topic_description']) ? $qVars['topic_description'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$templateDirectory = get_template_directory();

if ($topicId) {
		$uuid = Uuid::uuid4();
		$uuidString = $uuid->toString();
		$permissionValue = '40';
		$fileName = pte_filename_sanitizer("Topic Report - {$topicName}.pdf");
		$description = $topicDescription;
		$mimeType ="application/pdf";
		$fileSource = "Topic Report";
		$uploadId = $uuidString;
		$fileKey = "{$uuidString}.pdf";
		$localFileKey = "{$topicDomId}.pdf";
		$sizeBytes = 0;
		$now = date ("Y-m-d H:i:s", time());
		$localFile = "{$templateDirectory}-child-master/quick_report_tmp/{$localFileKey}";
		$pdfFileData = array(
			"pdf_key" => $fileKey,
			"local_file" => $localFile,
			"do_not_unlink_local" => true
		);
		$fileInfo = storePdf($pdfFileData);
		$sizeBytes = $fileInfo['pdf_size'];
		$rowData = array(
			"owner_id" => $userID,
			"upload_id" => $uploadId,
			"name" => 'Report',
			"file_name" => $fileName,
			"modified_date" =>  $now,
			"created_date" =>  $now,
			"topic_id" => $topicId,
			"mime_type" => $mimeType,
			"description" => $description,
			"file_source" => $fileSource,
			"access_level" => $permissionValue,
			"status" => 'ready',
			"pdf_key" => '',
			"file_key" => $fileKey,
			"ready_date" => $now,
			"size_bytes" => $sizeBytes,
			"original_ext" => "pdf"
		);
		$wpdb->insert( 'alpn_vault', $rowData );      //TODO make into a single insert...Optimization
}
$pte_response = array("topic" => "pte_handle_report_to_vault", "message" => "Successfully uploaded report to vault.", "data" => $rowData);
pte_json_out($pte_response);

?>
