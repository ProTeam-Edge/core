<?php
include('../../../wp-blog-header.php');
require_once 'google-api/vendor/autoload.php';

pp('Starting...');

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs

//TODO do not check over and over in failure situations.

$siteUrl = get_site_url();	
	
try {
	
	$client = new Google_Client();
	$client->addScope(Google_Service_Drive::DRIVE);
	$client->setApplicationName('ProTeam Edge');
	$client->setSubject("vault@proteamedge.com");
	$client->setAuthConfig('vault-268122-d9b658133189.json');

	$driveService = new Google_Service_Drive($client);
	
	$data = sprintf(
			"%s %s %s\n\nHTTP headers:\n",
			$_SERVER['REQUEST_METHOD'],
			$_SERVER['REQUEST_URI'],
			$_SERVER['SERVER_PROTOCOL']
		);

		foreach ($_SERVER as $name => $value) {
			$data .= $name . ': ' . $value . "\n";
		}

		$data .= "\nRequest body:\n";
	
	alpn_log($_REQUEST);
	alpn_log($data . file_get_contents('php://input') . "\n\n\n\n");
);
	
	exit;
	
	
	
	/*
	//alpn_log("client initialized..." . pte_time_elapsed(microtime() - $nowtime));
	//$nowtime = microtime();	
	
	$q = "name = '{$sId}' and mimeType = 'application/vnd.google-apps.folder'"; //find the folder first -- required because submission ID Folder/FileName forms the unique path for the file
	
	//Tries according to settings above. TODO Handle Fail.
	
	$folderObj = array();
	
	$folder = $driveService->files->listFiles(array(
			'q' => $q
		));		
	if (array_key_exists ('files', $folder) && count($folder['files']) > 0){
		$folderObj= $folder['files'][0];
		
		//alpn_log("folder found..." . pte_time_elapsed(microtime() - $nowtime));
		//$nowtime = microtime();			
		
	} else {
		
		//alpn_log("folder not found..." . pte_time_elapsed(microtime() - $nowtime));
		//$nowtime = microtime();	
		
		$pte_response = array("topic" => "pte_get_vault_file_not_found", "message" => "Vault file not found", "data" => $folder);
		pte_json_out($pte_response);
		exit;
	}
	
	$folderID = $folderObj->id;	
	$q = "'{$folderID}' in parents"; //Now find all the files in the folder TODO -- can this an previous query be combined? I tried a few things but gave up

	$files = $driveService->files->listFiles(array( 
			'q' => $q
	));	
	
	if (array_key_exists ('files', $files) && count($files['files']) > 0){
		$folderObj= $files['files'];
		
		//alpn_log("file found..." . pte_time_elapsed(microtime() - $nowtime));
		//$nowtime = microtime();				
		
	} else {
		
		//alpn_log("file not found..." . pte_time_elapsed(microtime() - $nowtime));
		//$nowtime = microtime();	
		
		$pte_response = array("topic" => "pte_get_vault_folder_not_found", "message" => "Vault folder not found", "data" => $files);
		pte_json_out($pte_response);
		exit;
	}
	
		//alpn_log("success..." . pte_time_elapsed(microtime() - $nowtime));
		//$nowtime = microtime();		
	
	
	foreach ($folderObj as $key => $value) {
		if ($fName == $value->name) {
			$fileId = $value->id;
		}	
	}
	
	$content = $driveService->files->get($fileId, array(
		'alt' => 'media'));

	$headers = $content->getHeaders();  //make headers look exactly like the file from the cloud
	foreach ($headers as $name => $values) {
		header($name . ': ' . implode(', ', $values));
	}
	header('Content-Disposition: inline; filename="' . $fName . '"');
	echo $content->getBody();
	
		alpn_log("about to download..." . pte_time_elapsed(microtime() - $nowtime));
	
	
	exit;		
*/	
} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_get_vault_google_exception", "message" => "Problem accessing Google Vailt.", "data" => $e);
		pp($pte_response);
		exit;
}



?>