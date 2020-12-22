<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

alpn_log("Handling Delete Drive File ...");

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//	If on separate machine behind FW, and removed from server farms, safer? Seems like go idea.

$pVars = unserialize($argv[1]);
$submissionId = isset($pVars['submission_id']) ? $pVars['submission_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';

try {
	$client = new Google_Client();
	$client->addScope(Google_Service_Drive::DRIVE);
	$client->setApplicationName('ProTeam Edge');
	$client->setSubject("vault@proteamedge.com");
	$client->setAuthConfig('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-5b82997546cb.json');

	$driveService = new Google_Service_Drive($client);

	$q = "name='{$submissionId}'"; 

	$files = array();
	$fileObj = array();
	
	$files = $driveService->files->listFiles(array(
		'q' => $q
	));		

	foreach ($files as $key => $value) { 
		$fileId = $value->id;	    
		$driveService->files->delete($fileId);
	}

} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_get_drive_google_exception", "message" => "Problem accessing Google Drive.", "data" => $e);
		alpn_log($pte_response);
		exit;
}

$now = date ("Y-m-d H:i:s", time());
$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

$pte_response = array("topic" => "pte_delete_file_from_drive_successful", "message" => "Successfully deleted file from Drive.", "data" => $value);
pte_send_notification($ownerId, "file_received", $pte_response);	


?>