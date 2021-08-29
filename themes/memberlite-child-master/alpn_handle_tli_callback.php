<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/reports/zip2pdf/zip2pdf/handlezip2pdf.php');

global $wpdb;

if (isset($_POST['transloadit'])){

	$tli = json_decode(stripslashes($_POST['transloadit']), true);

	//alpn_log("TLI CALLBACK");
	//alpn_log($tli);

	$status = $tli['ok'];
	if (isset($tli['fields']['pte_uid'])) {

		$pteUid = $tli['fields']['pte_uid'];

		switch ($status) {
			case 'ASSEMBLY_CANCELED':
			case 'REQUEST_ABORTED':
				$wpdb->query(
					$wpdb->prepare("DELETE FROM alpn_vault WHERE upload_id = %s", $pteUid)
				);

			break;
			case 'ASSEMBLY_COMPLETED':
				$pteResults = $tli['results'];

				$ptePdfKey = $pteFileKey = $originalMimeType = $newMimeType = "";
				$pteFileSize = $ptePdfSize = 0;

				$now = date ("Y-m-d H:i:s", time());

				$rowData = array(
					"upload_id" => '',
					"status" => "ready",
					"ready_date" => $now
				);

				if (isset($pteResults[':original'])) {  //get the info from original file
					$type = $pteResults[':original'][0];
					$pteFileKey = $type['id'] . "." . $type['ext'];
					$pteFileSize = $type['size'];
					$mimeType = $type['mime'];
					$baseFileName = $type['basename'];
					$fullFileName = $type['basename'] . "." . $type['ext'];
				}

				if (isset($pteResults['zipped_unsupported_types_1'])) {  //Anything we can't convert, zip
					$type = $pteResults['zipped_unsupported_types_1'][0];
					$pteFileKey = $type['id'] . "." . "zip";
					$pteFileSize = $type['size'];
					$mimeType = 'application/zip';
					$fullFileName = $baseFileName . ".zip";
				}

				if (isset($pteResults['zipped_unsupported_types_2'])) {  //Certain extensions and mimetypes pass our first filters so here are some more
					$type = $pteResults['zipped_unsupported_types_2'][0];
					$pteFileKey = $type['id'] . "." . "zip";
					$pteFileSize = $type['size'];
					$mimeType = 'application/zip';
					$fullFileName = $baseFileName . ".zip";
				}

				if (isset($pteResults['converted_doc_types'])) {   //after conversion doc tyoes
					$type = $pteResults['converted_doc_types'][0];
					$ptePdfKey = $type['id'] . ".pdf";
					$ptePdfSize = $type['size'];
				}

				if (isset($pteResults['converted_image_types'])) { //after conversion video tyoes
					$type = $pteResults['converted_image_types'][0];
					$ptePdfKey = $type['id'] . ".pdf";
					$ptePdfSize = $type['size'];
				}

				if ($mimeType == 'application/zip') {
					$ptfZip = pte_zip_structure_pdf($pteFileKey);
					$ptePdfKey = $ptfZip['pte_pdf_key'];
					$ptePdfSize = $ptfZip['pte_pdf_size'];
				}

					$results = $wpdb->get_results(
					$wpdb->prepare("SELECT id, owner_id, dom_id FROM alpn_vault WHERE upload_id = %s", $pteUid)
				 );

				 if (isset($results[0])) {
				 		$rowData["pdf_key"] = $ptePdfKey;
				 		$rowData["file_key"] = $pteFileKey;
						$rowData["size_bytes"] = $ptePdfSize + $pteFileSize;
						$rowData["mime_type"] = $mimeType;
				 		//$rowData["file_name"] = $fullFileName;  //There already from beginning. Don't overwite

				 		$whereClause['upload_id'] = $pteUid;
				 		$wpdb->update( 'alpn_vault', $rowData, $whereClause );

						$topicData = $results[0];
						$rowData['owner_id'] = $topicData->owner_id;
						$rowData['dom_id'] = $topicData->dom_id;
						$rowData['vault_id'] = $topicData->id;

						$pte_response = array("topic" => "pte_handle_vault_file_submit_successful", "message" => "File submitted successfully, job complete", "data" => $rowData); //TODO move back to this?

						$data = array(
							"sync_type" => 'add_update_section',
							"sync_section" => 'file_workflow_update',
							"sync_user_id" => $topicData->owner_id,
							"sync_payload" => array(
								"dom_id" => $topicData->dom_id,
								"vault_id" => $topicData->id
							)
						);
						pte_manage_user_sync($data);
				 }
			break;
		}
}


}

?>
