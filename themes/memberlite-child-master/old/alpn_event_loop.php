<?php
include('../../../wp-blog-header.php');
require "vendor/autoload.php";

use Amp\Loop;
use Google\Cloud\Storage\StorageClient;

$pte_response = array();

$pte_response = array("topic" => "pte_job_scheduler_started", "message" => "Successfully started job scheduler.", "data" => array());
pte_send_notification('2', 'debug', $pte_response);
echo ("Starting Event Loop..." . PHP_EOL);

function checkExpiration($jobId, $expirationDateTime) {
	
	global $wpdb;
	
	try{

		$now = time();
		if ($now > $expirationDateTime) {     //expired, fail
			$rowData = array(
				"status" => 'closed',
				"completed_date" => date("Y-m-d H:i:s", time()),
				"reason" => 'expired'
			);	
			$whereClause['id'] = $jobId;
			$wpdb->update( 'alpn_jobs', $rowData, $whereClause );
			$rowData['job_id'] = $jobId;
			$pte_response = array("topic" => "pte_job_expired", "message" => "Job Expired", "data" => $rowData);
			pte_send_notification('2', 'debug', $pte_response);	
			return (true);
		}
		return (false);

	} catch(Exception $e) {
		$pte_response = array("topic" => "pte_job_exired_write_exception", "message" => "Job Expired Exception", "data" => $rowData);
		pte_send_notification('2', 'debug', $pte_response);	
	}
}


function scheduleJobs() {
	
	$maxJobs = '100';
	
	$now = date ("Y-m-d H:i:s", time());
	try {
		global $wpdb;
  		$wpdb->query('START TRANSACTION');
		$checkJobsSql = "SELECT * FROM alpn_jobs WHERE status = 'open' ORDER BY id ASC LIMIT {$maxJobs} FOR UPDATE;";       //TODO tune maxjobs. Should I select into a variable then update the rows in the variable rather than a where clause which may grab other rows. IMPORTANT CHECK WITH MDB
		$jobs = $wpdb->get_results( $checkJobsSql );
		$rowData = array(
			"status" => 'running'
		);	
		$whereClause['status'] = 'open';
		$wpdb->update( 'alpn_jobs', $rowData, $whereClause );
		$wpdb->query('COMMIT');		
							
		if (isset($jobs[0])) {
			foreach ($jobs as $key => $value) {
				$handled = false;
				$jobId = $value->id;
				$type = $value->type;
				$scheduledDate = $value->scheduled_date;
				$meta = json_decode($value->meta, true);
				switch ($type) {
					case 'pte_stop_scheduler':	
						echo('Stopping Event Loop...' . PHP_EOL);
						Loop::stop();						
						$rowData = array(
							"status" => 'closed',
							"completed_date" => $now,
							"reason" => "requested"
						);	
						$whereClause['id'] = $jobId;
						$wpdb->update( 'alpn_jobs', $rowData, $whereClause );	
						$pte_response = array("topic" => "pte_job_scheduler_stopped", "message" => "Successfully stopped the scheduler.", "data" => $jobs);
						pte_send_notification('2', 'debug', $pte_response);		
						$handled = true;
					exit;
					case 'copy_file_from_drive_to_storage':
						echo('Handling: copy_file_from_drive_to_storage...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];
							$submissionId = $meta['submission_id'];
							$driveFileName = "{$submissionId}.pdf";
							$fileResponse = getDriveFileExists($driveFileName);
							$fileId = $fileResponse['data'];
							if ($fileId) {	
								print_r($fileResponse);								
								$data = array(
									"file_id" => $fileId,
									"vault_id" => $meta['vault_id'],
									"alpn_uid" => $meta['alpn_uid'],
									"owner_id" => $meta['owner_id'],
									"submission_id" => $meta['submission_id'],
									"dom_id" => $meta['dom_id'],
									"operation" => $meta['operation'],
									"job_id" => $jobId
								);					
								pte_async_job($url, $data);
								$pte_response = array("topic" => "copy_file_from_drive_to_storage_handled", "message" => "Handled copy file from drive to storage", "data" => $data);
								pte_send_notification('2', 'debug', $pte_response);		
								$handled = true;
							} 			
						} else {
							$handled = true;
						}						
					break;	
					case 'handle_form_submit_copy_after_callback':	
						echo('Handling: handle_form_submit_copy_after_callback...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){						
							$url = $meta['url'];						
							$query = $meta['query'];
							$results = $wpdb->get_results( $query );							
							if (isset($results[0])) {
								$queryResults = $results[0];								
								$data = array(
									"submission_id" => $meta['submission_id'],
									"vault_id" => $queryResults->id,
									"dom_id" => $queryResults->dom_id,
									"owner_id" => $queryResults->owner_id,
									"operation" => $queryResults->operation,
									"alpn_uid" => $meta['alpn_uid'],
									"job_id" => $jobId
								);					
								pte_async_job($url, $data);
								$pte_response = array("topic" => "handle_form_submit_copy_after_callback", "message" => "File Submit Job Started", "data" => $data);
								pte_send_notification('2', 'debug', $pte_response);		
								$handled = true;
							} 							
						} else {
							$handled = true;
						} 						
					break;	
					case 'handle_file_submit_after_callback':	
						echo('Handling: handle_file_submit_after_callback...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];		
							$query = $meta['query'];							
							$results = $wpdb->get_results( $query );
							if (isset($results[0])) {
								$queryResults = $results[0];
								$data = array(
									"original_hash" => $meta['sha256'],
									"upload_id" => $meta['upload_id'],
									"pdf_key" => $meta['pdf_key'],
									"file_key" => $meta['file_key'],
									"mime_type" => $queryResults->mime_type,
									"file_name" => $queryResults->file_name,
									"vault_id" => $queryResults->id,
									"owner_id" => $queryResults->owner_id,
									"dom_id" => $queryResults->dom_id,
									"job_id" => $jobId
								);
								pte_async_job($url, $data);
								$data['url'] = $url;
								$pte_response = array("topic" => "pte_job_submit_file_started", "message" => "File Submit Job Started", "data" => $data);
								pte_send_notification('2', 'debug', $pte_response);		
								$handled = true;
							} 							
						} else {
							$handled = true;
						}
					break;	
					case 'delete_file_from_cloud_storage':	
						echo('Handling: delete_file_from_cloud_storage...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];						
							$data = array(
								"bucket_name" => $meta['bucket_name'],
								"file_key" => $meta['file_key'],
								"job_id" => $jobId
							);
							pte_async_job($url, $data);
							$pte_response = array("topic" => "pte_job_delete_from_google_storage", "message" => "Google storage delete started...", "data" => $data);
							pte_send_notification('2', 'debug', $pte_response);		
							$handled = true;
						} else {
							$handled = true;
						}
					break;
					case 'delete_submission_from_jotform':	
						echo('Handling: delete_submission_from_jotform...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];						
							$data = array(
								"submission_id" => $meta['submission_id'],
								"job_id" => $jobId
							);
							pte_async_job($url, $data);
							$pte_response = array("topic" => "delete_submission_from_jotform_start_successful", "message" => "Jotform Delete Started...", "data" => $data);
							pte_send_notification('2', 'debug', $pte_response);		
							$handled = true;
						} else {
							$handled = true;
						}
					break;
					case 'delete_file_from_drive':	
						echo('Handling: delete_file_from_drive...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];						
							$data = array(
								"submission_id" => $meta['submission_id'],
								"user_id" => $meta['user_id'],
								"job_id" => $jobId
							);
							pte_async_job($url, $data);
							$pte_response = array("topic" => "delete_file_from_drive_successful", "message" => "Delete Drive File job started...", "data" => $data);
							pte_send_notification('2', 'debug', $pte_response);		
							$handled = true;
						} else {
							$handled = true;
						}
					break;	
					case 'handle_create_zip_preview_pdf':	
						echo('Handling: handle_create_zip_preview_pdf...' . PHP_EOL);
						$expirationDateTime = strtotime($scheduledDate) + $meta['attempt_seconds'];	
						if (!checkExpiration($jobId, $expirationDateTime)){
							$url = $meta['url'];						
							$data = array(
								"pdf_key" => $meta['pdf_key'],
								"file_key" => $meta['file_key'],
								"file_name" => $meta['file_name'],
								"vault_id" => $meta['vault_id'],
								"owner_id" => $meta['owner_id'],
								"dom_id" => $meta['dom_id'],
								"job_id" => $jobId
							);
							pte_async_job($url, $data);
							$pte_response = array("topic" => "handle_create_zip_preview_pdf_successful", "message" => "Successfully started zip preview pdf job...", "data" => $data);
							pte_send_notification('2', 'debug', $pte_response);		
							$handled = true;
						} else {
							$handled = true;
						}
					break;							
				}
				
				if (!$handled) {  //re-open if not handled
					echo('re-opening...' . PHP_EOL);
					$whereClause = array();
					$rowData = array(
						"status" => 'open'
					);	
					$whereClause['id'] = $jobId;
					$wpdb->update( 'alpn_jobs', $rowData, $whereClause );
				}	
			}
		}
	} catch (Exception $e) {
		$pte_response = array("topic" => "pte_job_scheduler_exception", "message" => "Exception in scheduler.", "data" => $e);
		pte_send_notification('2', 'debug', $pte_response);
	}
}

Loop::run(function() {
    Loop::repeat($msInterval = 3000, "scheduleJobs");
});

?>	