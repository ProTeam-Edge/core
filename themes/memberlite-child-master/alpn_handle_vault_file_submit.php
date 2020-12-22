<?php
include('../../../wp-blog-header.php');

global $wpdb;
$pdfKey = $originalKey = $uploadId = '';

$postdata = file_get_contents("php://input");
$signature = $_SERVER['HTTP_FS_SIGNATURE'];
$timestamp = $_SERVER['HTTP_FS_TIMESTAMP'];

$pte_response = array();

$fileStackData = json_decode($postdata, true);

//alpn_log($fileStackData);

if (isset($fileStackData['action'])) {
	
	$pte_response['filestack_data'] = $fileStackData;
	$action = $fileStackData['action'];
	$rowData = array();
	
	//SCREEN for workflow only BUT why are they sending upload callbacks?
	//TODO Make Secure. NOTE CALLBACKS DON'T have wordpress user info
	
	switch ($action){
		case "fp.upload":	
		break;

		case "fs.workflow":  

			/* TURN THIS BACK ON TODO
			$secret = 'OgzdEy4mQM63h42HVhnB';
			$response = (hash_hmac('sha256', $timestamp . '.' . $postdata, $secret) == $signature) ? "CORRECT" : "INCORRECT";
			if ($response == "INCORRECT") {  
				//$pte_response = array("topic" => "pte_handle_vault_file_response_workflow_failed", "message" => "Bad Workflow Response", "data" => $fileStackData);
				//json_out($pte_response);		 //TODO pass errors back via pusher
			}	
			*/
			
			$fileDetails = $fileStackData['text'];
			$uploadId = $fileDetails['jobid'];
			$workflowSteps = $fileDetails['results'];
			
			$sha256 = '';
			if (isset($workflowSteps['Get MimeType']['data']['sha256'])){
				$sha256 = $workflowSteps['Get MimeType']['data']['sha256'];
			}

			if (isset($workflowSteps['Store PDF in Google Cloud Storage'])){
				
				$pdf = $workflowSteps['Store PDF in Google Cloud Storage']['data'];
				$pdfKey = $pdf['key'];	
				
			} else if (isset($workflowSteps['Convert to PDF']) && isset($workflowSteps['Convert to PDF']['error'])){
				
				//'service temporarily unavailable' schedule a retry on this? Need counter just in case endless loop. TODO
				
				$pte_response = array("topic" => "pte_handle_vault_file_convert_to_pdf_failed", "message" => "Unable to convert original to PDF", "data" => $fileStackData);
				alpn_log($pte_response); //TODO handle error. Give user feedback.
				exit;
			}
			
			if (isset($workflowSteps['Store Original in Google Cloud Storage'])){
				$originalRecipe = $workflowSteps['Store Original in Google Cloud Storage']['data'];
				$originalKey = $originalRecipe['key'];
				if ($pdfKey == "") {
					$pdfKey = $originalKey;
				}
				
			}	
						
			$data = array(
				"sha256" => $sha256,
				"pdf_key" => $pdfKey,
				"file_key" => $originalKey,
				"upload_id" => $uploadId
			);	
			pte_register_job('handle_file_submit_after_callback', $data);
			
			//Delete originals from FileStack

			$fs_api_key = 'ABqSh08IQOOKjUIrb5l5Az';
			$fs_secret = '258337b4585c705175056ca5b01a3edce1cfc8e18c7a749fd4f2d3e2ca6741ff';

			$security = new FilestackSecurity($fs_secret);
			$client = new FilestackClient($fs_api_key, $security);

			
		break;				
	}	

} else {
		$pte_response = array("topic" => "pte_handle_vault_file_invalid_response", "message" => "Invalid action response from Filestack", "data" => $postdata);
		alpn_log($pte_response);
		exit;
}

?>	