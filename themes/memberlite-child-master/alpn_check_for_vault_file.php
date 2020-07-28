<?php
include('../../../wp-blog-header.php');
require_once 'google-api/vendor/autoload.php';

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs

//TODO do not check over and over in failure situations.

$siteUrl = get_site_url();

$qVars = $_GET;
$dom_id = isset($qVars['dom_id']) ? $qVars['dom_id'] : '';
$vId = isset($qVars['v_id']) ? $qVars['v_id'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$nowtime = microtime();
alpn_log("start check for file...");

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT * FROM alpn_vault WHERE id = %s", $vId)   //TODO had to backout userId because 
 );

if (array_key_exists('0', $results)) {
	
	//TODO is owner_id the user who is logged in? If so, go on. Is owner_id not me. lookup the grant for this vault item. also for get vault file. Do a permissions check here too???
	//select from proteams to see if user was granted access. OR can I create a join from alpn_vault and skip the extra query?

	//alpn_log("after db..." . pte_time_elapsed(microtime() - $nowtime));
	//$nowtime = microtime();

	$sId = $results[0]->submission_id;
	$fName = $results[0]->file_name;
	if (!$fName || $fName == '') {$fName = $sId . '.pdf';}	
	
try {
	
	$client = new Google_Client();
	$client->addScope(Google_Service_Drive::DRIVE);
	$client->setApplicationName('ProTeam Edge');
	$client->setSubject("vault@proteamedge.com");
	$client->setAuthConfig('vault-268122-d9b658133189.json');

	$driveService = new Google_Service_Drive($client);
	
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
	}	
		alpn_log("success..." . pte_time_elapsed(microtime() - $nowtime));
	$pte_response = array("topic" => "pte_file_found", "message" => "File Found.", "data" => $folderObj );
	pte_json_out($pte_response);
	
} catch (\Exception $e) { // Global namespace
		alpn_log("Google Exception..." . pte_time_elapsed(microtime() - $nowtime));
		$nowtime = microtime();			
		$pte_response = array("topic" => "pte_get_vault_google_exception", "message" => "Problem accessing Google Vault.", "data" => $e);
		pte_json_out($pte_response);
}

} else {
		alpn_log("problem with db.." . pte_time_elapsed(microtime() - $nowtime));
		$nowtime = microtime();			
		$pte_response = array("topic" => "pte_get_vault_record_not_found", "message" => "Vault record not found.", "data" => $results);
		pte_json_out($pte_response);
}


?>