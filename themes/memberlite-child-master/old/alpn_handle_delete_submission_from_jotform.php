<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/jotform/JotForm.php');

alpn_log('Start fire and forget jotform delete...');

//TODO Check logged in, etc. Good Request. User-ID in all mysql

$pVars = unserialize($argv[1]);
$submissionId = isset($pVars['submission_id']) ? $pVars['submission_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';

global $wpdb;

try {

	$jotformAPI = new JotForm("20c6392fb493bcd212c4db53452cd42e");
	$statusJF = $jotformAPI->deleteSubmission($submissionId);
}
catch(Exception $e) {
	alpn_log($e);
}

$now = date ("Y-m-d H:i:s", time());

$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

?>