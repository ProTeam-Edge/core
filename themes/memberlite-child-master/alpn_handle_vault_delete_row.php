<?php
include('../../../wp-blog-header.php');

//TODO Check logged in, etc. Good Request. User-ID in all mysql
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_GET;
$vaultId = isset($qVars['vault_id']) ? $qVars['vault_id'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;	

global $wpdb;

//Delete at PTE
$pte_response = array();

if ($vaultId != '') {
	try {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT file_key, pdf_key, submission_id, file_source FROM alpn_vault WHERE id = '%s' AND owner_id = '%s'", $vaultId,  $userID)
		 );

		if (isset($results[0])) {
			
			$vaultInfo = $results[0];
			$fileKey = $vaultInfo->file_key;
			$pdfKey = $vaultInfo->pdf_key;
			$fileSource = $vaultInfo->file_source;
			$submissionId = $vaultInfo->submission_id;
			
			$whereClause['id'] = $vaultId;
			$whereClause['owner_id'] = $userID;
			$wpdb->delete( 'alpn_vault', $whereClause );
			
			$jobData = array(
				"bucket_name" => 'pte_file_store1',
				"file_key" => $pdfKey
			);
			pte_register_job('delete_file_from_cloud_storage', $jobData);		
			
			if ($fileKey != $pdfKey) {
				$jobData = array(
					"bucket_name" => 'pte_file_store1',
					"file_key" => $fileKey
				);
				pte_register_job('delete_file_from_cloud_storage', $jobData);					
			}
			
			if ($fileSource == 'pte_form') {
				$jobData = array(
					"submission_id" => $submissionId
				);
				pte_register_job('delete_submission_from_jotform', $jobData);	
				
				
				//TODO schedule delete from Drive job,
				
				
				
				
			}
		
			$pteData = array("query_results" => $results, "last_query" => $wpdb->last_query, "last_error" => $wpdb->last_error);	
			$pte_response = array("topic" => "pte_vault_delete_successful", "message" => "Successfully updated vault DB.", "data" => $pteData);
		
		} else {
			$pteData = array("query_results" => $results, "last_query" => $wpdb->last_query, "last_error" => $wpdb->last_error);	
			$pte_response = array("topic" => "pte_vault_delete_id_not_found", "message" => "Vault ID not found.", "data" => $pteData);
		}
	}
	catch(Exception $e) {
		$pte_response = array("topic" => "pte_google_storage_delete_exception", "message" => "Problem deleting at Google Storage.", "data" => $e);
	}
}

header('Content-Type: application/json');
echo json_encode($pte_response);

?>	