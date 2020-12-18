<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/reports/zip2pdf/zip2pdf/handlezip2pdf.php');



global $wpdb;

if (isset($_POST['transloadit'])){

	$tli = json_decode(stripslashes($_POST['transloadit']), true);

	alpn_log($tli);

	if (isset($tli['fields']['pte_uid'])) {

		$pteUid = $tli['fields']['pte_uid'];
		$pteResults = $tli['results'];

		$ptePdfKey = $pteFileKey = $originalMimeType = $newMimeType = "";
		$pteFileSize = $ptePdfSize = 0;

		$now = date ("Y-m-d H:i:s", time());

		$rowData = array(
			"upload_id" => '',
			"status" => "ready",
			"ready_date" => $now
		);
		if (isset($pteResults[':original'])) {
			$type = $pteResults[':original'][0];
			$pteFileKey = $type['id'] . "." . $type['ext'];
			$pteFileSize = $type['size'];
			$originalMimeType = $type['mime'];
		}

		if (isset($pteResults['zipped_unsupported_types'])) {
			$type = $pteResults['zipped_unsupported_types'][0];
			$pteFileKey = $type['id'] . "." . $type['ext'];
			$pteFileSize = $type['size'];
		}

		if (isset($pteResults['converted_doc_types'])) {
			$type = $pteResults['converted_doc_types'][0];
			$ptePdfKey = $type['id'] . ".pdf";
			$ptePdfSize = $type['size'];
		}

		if (isset($pteResults['converted_image_types'])) {
			$type = $pteResults['converted_image_types'][0];
			$ptePdfKey = $type['id'] . ".pdf";
			$ptePdfSize = $type['size'];
		}

		if (isset($pteResults['zipped_unsupported_types'])) {
			$rowData["mime_type"] = "application/zip";
			$newMimeType = "application/zip";
		}

		if ($originalMimeType == 'application/zip' || $newMimeType == 'application/zip') {
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
	}
}

?>
