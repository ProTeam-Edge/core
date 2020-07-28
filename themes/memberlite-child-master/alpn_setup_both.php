<?php
include('../../../wp-blog-header.php');
alpn_log("Start Setup...");


//require_once 'google-api/vendor/autoload.php';


pp('about to require...');

require_once 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

pp('about to client...');

$client = new Google_Client();
$client->addScope(Google_Service_Drive::DRIVE);
$client->setApplicationName('ProTeam Edge');
$client->setSubject("vault@proteamedge.com");
$client->setAuthConfig('proteam-edge-5b82997546cb.json');

$driveService = new Google_Service_Drive($client);

$folder = $driveService->files->listFiles(array(
		'q' => $q
	));		

$q = "name = '{$driveFileName}'"; 

$fileObj = array();
for ($i = 0; $i < $tryTimes; $i++){

	$folder = $driveService->files->listFiles(array(
		'q' => $q
	));			
	if (array_key_exists ('files', $folder) && count($folder['files']) > 0){
		$fileObj= $folder['files'][0];
		break;
	}
	sleep ($retryEvery);
}

if ($i == $tryTimes) { //Fail
	$pte_response = array("topic" => "pte_vault_form_submit_drive_file_not_found", "message" => "Submission Drive File not found", "data" => $folder);
	alpn_log($pte_response);	
} 

$fileId	= $fileObj->id;
$file = $driveService->files->get($fileId, array(
	'alt' => 'media'));


pp($driveService);
pp('about to storage...');

$storage = new StorageClient([
	'projectId' => 'proteam-edge'
]);

pp($storage);
pp('about to client...');

?>	