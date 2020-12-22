<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("Handling Form Update After Callback...");

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs
//TODO Make this non-blocking while we wait for the file to get from jotform to google drive. Schedule in the future. Or get drive notifications to work. I couldn't
//TODO Check logged in, etc. Good Request. User-ID in all mysql

global $wpdb;

$pVars = unserialize($argv[1]);
$alpn_submission_id = isset($pVars['submission_id']) ? $pVars['submission_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$alpn_vault_id = isset($pVars['vault_id']) ? $pVars['vault_id'] : '';
$alpn_uid = isset($pVars['alpn_uid']) ? $pVars['alpn_uid'] : '';
$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';
$operation = isset($pVars['operation']) ? $pVars['operation'] : '';

$fileName = "{$alpn_submission_id}.pdf";
$pdfKey = "pte_{$fileName}";

$rowData = array(
	"pdf_key" => $pdfKey,
	"file_key" => $pdfKey,
	"file_name" => $fileName,
	"upload_id" => '',
	"submission_id" => $alpn_submission_id,
	"status" => 'verified'
);

$whereClause['id'] = $alpn_vault_id; 
$wpdb->update( 'alpn_vault', $rowData, $whereClause );		

$now = date ("Y-m-d H:i:s", time());
$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

$jobData = array(
	"submission_id" => $alpn_submission_id,
	"user_id" => $alpn_uid,
	"vault_id" => $alpn_vault_id,
	"dom_id" => $domId,
	"alpn_uid" => $alpn_uid,
	"owner_id" => $ownerId,
	"operation" => $operation
);

pte_register_job('copy_file_from_drive_to_storage', $jobData);

?>	