<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//alpn_log("after db..." . pte_time_elapsed(microtime() - $nowtime));
//$nowtime = microtime();	

$pVars = unserialize($argv[1]);
$alpn_submission_id = isset($pVars['submission_id']) ? $pVars['submission_id'] : '';
$userID = isset($pVars['alpn_uid']) ? $pVars['alpn_uid'] : '';
$vaultId = isset($pVars['vault_id']) ? $pVars['vault_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$fileId = isset($pVars['file_id']) ? $pVars['file_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';
$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';
$operation = isset($pVars['operation']) ? $pVars['operation'] : '';
 
$driveFileName = "{$alpn_submission_id}.pdf";
$formFileName = "pte_{$alpn_submission_id}.pdf";

if (!$alpn_submission_id) {
	$pte_response = array("topic" => "pte_copy_from_drive_to_storage_data_missing_error", "message" => "Copy file missing data.", "data" => $_POST);
	alpn_log($pte_response);
	exit;	
}

//Get Drive File

try {
	$client = new Google_Client();
	$client->addScope(Google_Service_Drive::DRIVE);
	$client->setApplicationName('ProTeam Edge');
	$client->setSubject("vault@proteamedge.com");
	$client->setAuthConfig('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-5b82997546cb.json');

	$driveService = new Google_Service_Drive($client);

	$file = $driveService->files->get($fileId, array(
		'alt' => 'media'
	));
	
	$fileContent = $file->getBody()->getContents();

} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_get_drive_google_exception", "message" => "Problem accessing Google Drive.", "data" => $e);
		alpn_log($pte_response);
		exit;
}

try {
	$storage = new StorageClient([
    	'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
	]);
	$storage->registerStreamWrapper();	
	$options = ['gs' => ['Content-Type' => "application/pdf"]];
	$context = stream_context_create($options);
	file_put_contents("gs://pte_file_store1/{$formFileName}", $fileContent, 0, $context);	
	
} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_google_storage_exception", "message" => "Problem saving to Google Storage.", "data" => $e);
		alpn_log($pte_response);
		exit;
}	

$now = date ("Y-m-d H:i:s", time());
$rowData = array(
	"upload_id" => '',
	"status" => 'ready',
	"ready_date" => $now
);

$whereClause['id'] = $vaultId; 	
$wpdb->update( 'alpn_vault', $rowData, $whereClause );	

$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

$rowData['vault_id'] = $vaultId;
$rowData['upload_id'] = $uploadId;
$rowData['submission_id'] = $alpn_submission_id;
$rowData['owner_id'] = $ownerId;
$rowData['dom_id'] = $domId;


$data = array(
	"submission_id" => $alpn_submission_id,
	"owner_id" => $ownerId
);	
pte_register_job('delete_file_from_drive', $data);

$pte_response = array("topic" => "pte_copy_from_drive_to_gcsstorage_successful", "message" => "Successfully copied file to storage.", "data" => $rowData);
pte_send_notification($ownerId, "file_received", $pte_response);	


?>