<?php
include('../../../wp-blog-header.php');
require_once 'google-api/vendor/autoload.php';
include('./jotform/JotForm.php');

//TODO Check logged in, etc. Good Request. User-ID in all mysql

$qVars = $_GET;
$submissionId = isset($qVars['submissionId']) ? $qVars['submissionId'] : '';
$vaultId = isset($qVars['vaultId']) ? $qVars['vaultId'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;	

global $wpdb;

//TODO USER LOGGED IN CHECKING

//Delete at JF

try {

	$jotformAPI = new JotForm("20c6392fb493bcd212c4db53452cd42e");
	$statusJF = $jotformAPI->deleteSubmission($submissionId);
}
catch(Exception $e) {
	$statusJF = $e;
}

//Delete at Google

$statusGoogle = array();	
/*
try {
	$client = new Google_Client();
	$client->addScope(Google_Service_Drive::DRIVE);
	$client->setApplicationName('ProTeam Edge');
	$client->setSubject("vault@proteamedge.com");
	$client->setAuthConfig('vault-268122-d9b658133189.json');

	$driveService = new Google_Service_Drive($client);
	
	$q = "name = '{$submissionId}' and mimeType = 'application/vnd.google-apps.folder'"; //find the folder first -- required because submission ID Folder/FileName forms the unique path for the file
	
	$folderObj = array();
	for ($i = 0; $i < $retryGoogleCount; $i++){
	    $folder = $driveService->files->listFiles(array(
        	'q' => $q
		));		
		if (array_key_exists ('files', $folder) && count($folder['files']) > 0){
			$folderObj= $folder['files'][0];
			//$statusGoogle = $driveService->files->delete($folderObj->getId());			//TODO. Delete not working Why????? Guess is permissions? A setting with service account? Impersonation.
			$statusGoogle['File Id'] = $folderObj->getId();	
			$statusGoogle['Submission Id'] = $submissionId;	
			$statusGoogle['Count'] = $i;	
			break;
		}
		sleep ( $retryGoogleWait );		
	}		
	
	$statusGoogle['alpn'] = $folder;
	
} catch (\Exception $e) { // <<<<<<<<<<< You must use the backslash
	$statusGoogle = $e;
}	
*/
$results = array("JotForms" => $statusJF, "Google Drive" => $statusGoogle);

header('Content-Type: application/json');
echo json_encode($results);

?>	