<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/reports/zip2pdf/zip2pdf/handlezip2pdf.php');

global $wpdb;
$vaultItems = array();

if (isset($_POST['transloadit'])){

	$tli = json_decode(stripslashes($_POST['transloadit']), true);

	alpn_log("TLI CALLBACK");
	// alpn_log($tli);

	$status = $tli['ok'];
	$uploads = $tli['uploads'];

	$results = $tli['results'];
	$original = isset($results[':original']) ? wsc_org_result_by_id($results[':original']) : false;
	$zippedUnsupportedTypes1 = isset($results['zipped_unsupported_types_1']) ? wsc_org_result_by_id($results['zipped_unsupported_types_1']) : false;
	$zippedUnsupportedTypes2 = isset($results['zipped_unsupported_types_2']) ? wsc_org_result_by_id($results['zipped_unsupported_types_2']) : false;
	$convertedDocTypes = isset($results['converted_doc_types']) ? wsc_org_result_by_id($results['converted_doc_types']) : false;
	$convertedImageTypes = isset($results['converted_image_types']) ? wsc_org_result_by_id($results['converted_image_types']) : false;

	//  alpn_log($original);
	//  alpn_log($zippedUnsupportedTypes1);
	//  alpn_log($zippedUnsupportedTypes2);
	// alpn_log($convertedDocTypes);
	// alpn_log($convertedImageTypes);

	foreach ($uploads as $key => $value) { //processes entire list at once.
		$originalExtension = "";
		$isUnsupported = false;
		$olfilename = $value['basename'];
		$fileId = $value['id'];
		$uuid = substr($olfilename, 0, 36);
		$filename = substr($olfilename, 36);
		// alpn_log($uuid);
		// alpn_log($filename);
		//alpn_log($value);

		switch ($status) {
			case 'ASSEMBLY_CANCELED':
			case 'REQUEST_ABORTED':
				$wpdb->query(
					$wpdb->prepare("DELETE FROM alpn_vault WHERE upload_id = %s", $uuid)
				);

			break;
			case 'ASSEMBLY_COMPLETED':
				$ptePdfKey = $pteFileKey = $originalMimeType = $newMimeType = "";
				$pteFileSize = $ptePdfSize = 0;
				$now = date ("Y-m-d H:i:s", time());
				$rowData = array(
					"upload_id" => '',
					"status" => "ready",
					"ready_date" => $now
				);
				if (isset($original[$uuid])) {  //get the info from original file
					$type = $original[$uuid];
					$pteFileKey = $type['id'] . "." . $type['ext'];
					$pteFileSize = $type['size'];
					$mimeType = $type['mime'];
					$fullFileName = $filename . "." . $type['ext'];
					$originalExtension = $type['ext'];
				}
				if (isset($zippedUnsupportedTypes1[$uuid])) {  //get the info from original file
					$type = $zippedUnsupportedTypes1[$uuid];
					$pteFileKey = $type['id'] . "." . "zip";
					$pteFileSize = $type['size'];
					$mimeType = 'application/zip';
					$fullFileName = $filename . ".zip";
					$isUnsupported = true;
				}
				if (isset($zippedUnsupportedTypes2[$uuid])) {
					$type = $zippedUnsupportedTypes2[$uuid];
					$pteFileKey = $type['id'] . "." . "zip";
					$pteFileSize = $type['size'];
					$mimeType = 'application/zip';
					$fullFileName = $filename . ".zip";
					$isUnsupported = true;
				}
				if (isset($convertedDocTypes[$uuid])) {
					$type = $convertedDocTypes[$uuid];
					$ptePdfKey = $type['id'] . ".pdf";
					$ptePdfSize = $type['size'];
				}
				if (isset($convertedImageTypes[$uuid])) {
					$type = $convertedImageTypes[$uuid];
					$ptePdfKey = $type['id'] . ".pdf";
					$ptePdfSize = $type['size'];
				}
				if ($mimeType == 'application/zip') {
					$ptfZip = pte_zip_structure_pdf($pteFileKey);
					$ptePdfKey = $ptfZip['pte_pdf_key'];
					$ptePdfSize = $ptfZip['pte_pdf_size'];
				}
				$results = $wpdb->get_results(
					$wpdb->prepare("SELECT id, owner_id, dom_id, creator_id, file_name, description FROM alpn_vault WHERE upload_id = %s", $uuid)
				 );
				 if (isset($results[0])) {
						 $topicData = $results[0];
						 if ($isUnsupported) {
							 $description = $topicData->description;
							 if ($description) {
								 $description =  "{$description} -- Original ("  . strtoupper($originalExtension) . ")";
							 } else {
								 $description =  "Original ("  . strtoupper($originalExtension) . ")";
							 }
						 }
						 $vaultItems[] = array(
							 "dom_id" => $topicData->dom_id,
							 "vault_id" => $topicData->id,
							 "description" => $description,
							 "file_name" => $fullFileName,
							 "mime_type" => $mimeType
						 );
						$rowData["pdf_key"] = $ptePdfKey;
						$rowData["file_key"] = $pteFileKey;
						$rowData["size_bytes"] = $ptePdfSize + $pteFileSize;
						$rowData["mime_type"] = $mimeType;
						$rowData["original_ext"] = $originalExtension;
						$rowData["file_name"] = $fullFileName;  //Updated for type change
						$rowData["description"] = $description;  //Possibly Updated with feedback
						$whereClause['upload_id'] = $uuid;
						$wpdb->update( 'alpn_vault', $rowData, $whereClause );
				 }
			break;
		}
	}
	if ($vaultItems) {
		$sendNotificationTo = ($topicData->owner_id == $topicData->creator_id) ? $topicData->owner_id : $topicData->creator_id;
		$data = array(
			"sync_type" => 'add_update_section',
			"sync_section" => 'file_workflow_update',
			"sync_user_id" => $sendNotificationTo,
			"sync_payload" => $vaultItems
		);
		pte_manage_user_sync($data);
	}
} else {
	alpn_log("TLI CALLBACK ISSUE");
}

function wsc_org_result_by_id($result) {
	$newArray = array();
	foreach ($result as $key => $value) {
		$newArray[substr($value['basename'], 0, 36)] = $value;
	}
	return $newArray;
}

?>
