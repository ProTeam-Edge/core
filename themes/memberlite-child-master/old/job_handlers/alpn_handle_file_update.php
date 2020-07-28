<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

echo("Start Handle File Update...");

$pVars = unserialize($argv[1]);
$sha256 = isset($pVars['original_hash']) ? $pVars['original_hash'] : '';
$pdfKey = isset($pVars['pdf_key']) ? $pVars['pdf_key'] : '';
$fileKey = isset($pVars['file_key']) ? $pVars['file_key'] : '';
$uploadId = isset($pVars['upload_id']) ? $pVars['upload_id'] : '';
$vaultId = isset($pVars['vault_id']) ? $pVars['vault_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';
$mimeType = isset($pVars['mime_type']) ? $pVars['mime_type'] : '';
$fileName = isset($pVars['file_name']) ? $pVars['file_name'] : '';

$now = date ("Y-m-d H:i:s", time());
if ($uploadId && $fileKey) {

	if ($mimeType == 'application/zip') {
		$status = 'processing';
		$channel = 'debug';
	} else {
		$status = 'ready';
		$channel = 'file_received';
	}

	try {
		
		$rowData = array(
			"original_hash" => $sha256,
			"pdf_key" => $pdfKey,
			"file_key" => $fileKey,
			"upload_id" => '',
			"status" => $status,
			"ready_date" => $now
		);
		$whereClause['upload_id'] = $uploadId; 	
		$wpdb->update( 'alpn_vault', $rowData, $whereClause );	
		
		$rowData1 = array(
			"status" => 'closed',
			"reason" => 'success',
			"completed_date" => $now
		);	
		
		$whereClause1['id'] = $jobId;
		$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );
		
		$rowData['owner_id'] = $ownerId;
		$rowData['job_id'] = $jobId;
		$rowData['dom_id'] = $domId;
		$rowData['vault_id'] = $vaultId;
		
		$pte_response = array("topic" => "pte_handle_vault_file_submit_successful", "message" => "File submitted successfully, job complete", "data" => $rowData);		
		pte_send_notification($ownerId, $channel, $pte_response);	
		
		if ($mimeType == 'application/zip') {
			$data = array(
				"pdf_key" => $pdfKey,
				"file_key" => $fileKey,
				"upload_id" => $uploadId,
				"vault_id" => $vaultId,
				"dom_id" => $domId,
				"owner_id" => $ownerId,
				"file_name" => $fileName
			);			
			pte_register_job('handle_create_zip_preview_pdf', $data);	
		}
		
	} catch(Exception $e) {
		$pte_response = array("topic" => "pte_handle_file_update_exception", "message" => "File Update Exception", "data" => $pVars);
		alpn_log($pte_response);
	}
	
} else {
		$pte_response = array("topic" => "pte_handle_file_update_missing_data", "message" => "File Update Missing Data", "data" => $pVars);
		alpn_log($pte_response);	
}

?>	