<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
require ('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/reports/fax_cover_sheet/faxcoversheet/handlefaxcoversheet.php');
require ('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

$qVars = $_POST;
$sendData = isset($qVars['data']) ? json_decode(stripslashes($qVars['data']), true) : array();    //"owner_network_id" => 11, "process_id" => "test", "owner_id" => 40, "topic_id" => 11

alpn_log('Start pte_handle_fax_send...');
//alpn_log($sendData);

if ($sendData) {
	$processId = $sendData['process_id'];

	$topicContent = array();
	$ownerNetworkId = $sendData['owner_network_id'];   //11 not 40
	$ownerTopicId1 = $ownerNetworkId;
	$ownerId = $sendData['owner_id'];   //40 not 11

	//TODO combine into single query

	$topicContent = array();
	$ownerNetworkId = $sendData['owner_network_id'];

	$results = $wpdb->get_results(
		 $wpdb->prepare("SELECT logo_handle, topic_content FROM alpn_topics WHERE id = %s", $ownerNetworkId)
	 );
	if (isset($results[0])){
	 $rowData = $results[0];
	 $topicContent = json_decode($rowData->topic_content, true);
	 $imageBaseUrl = PTE_IMAGES_ROOT_URL;
	 $topicContent['logo_url'] = $imageBaseUrl . $rowData->logo_handle;
	} else {
		$topicContent['logo_url'] = '';
	}

//Lookup default place data
 $results = $wpdb->get_results(
	$wpdb->prepare("SELECT l.id, t.topic_content FROM alpn_topic_links l LEFT JOIN alpn_topics t ON t.id = l.owner_topic_id_2 WHERE l.owner_id_1 = %d AND l.owner_topic_id_1 = %d AND l.subject_token = %s AND l.list_default = 'yes'", $ownerId, $ownerTopicId1, 'pte_place')
	);
 $placeLinkId = '';
 if (isset($results[0])) {
	 $placeLinkId = $results[0]->id;
	 $placeTopicContent = json_decode($results[0]->topic_content, true);
	 unset($placeTopicContent['pte_meta']);
	 $topicContent = array_merge($topicContent, $placeTopicContent);
 }

 //lookup default org data
 $results = $wpdb->get_results(
	$wpdb->prepare("SELECT l.id, t.topic_content FROM alpn_topic_links l LEFT JOIN alpn_topics t ON t.id = l.owner_topic_id_2 WHERE l.owner_id_1 = %d AND l.owner_topic_id_1 = %d AND l.subject_token = %s AND l.list_default = 'yes'", $ownerId, $ownerTopicId1, 'pte_organization')
	);
 $organizationLinkId = '';
 if (isset($results[0])) {
	 $organizationLinkId = $results[0]->id;
	 $organizationTopicContent = json_decode($results[0]->topic_content, true);
	 unset($organizationTopicContent['pte_meta']);
	 $topicContent = array_merge($topicContent, $organizationTopicContent);
 }

	$objectName = $sendData['vault_pdf_key'] ? $sendData['vault_pdf_key'] : $sendData['vault_file_key'];
	$localStoreName = PTE_ROOT_PATH;
	$localStoreName .= "tmp/{$objectName}";

	try {
		$storage = new StorageClient([
				'keyFilePath' => '/var/www/html/proteamedge/private/proteam-edge-cf8495258f58.json'
		]);
		$storage->registerStreamWrapper();
		$content = file_get_contents("gs://pte_file_store1/{$objectName}");
		file_put_contents ($localStoreName, $content);  //TODO do some checkin
	} catch (\Exception $e) { // Global namespace
			$pte_response = array("topic" => "pte_get_vault_google_exception", "message" => "Problem accessing Google Vailt.", "data" => $e);
			alpn_log($pte_response);
			exit;
	}

	try {
		$image = new Imagick();  //Used to get page count only
		$image->pingImage($localStoreName);
		$combinedPageCount = $image->getNumberImages() + 1;
		$reportSettings = array(
			'orientation' => 'portrait',
			'page_size' => 'letter',
			'highlight_color' => '#696969',
			'topic_id' => $sendData['owner_network_id'],
			'network_contact_name' => $sendData['network_contact_name'],
			'page_count' => $combinedPageCount,
			'pstn_number_formatted' => pte_format_pstn_number($sendData['pstn_number']),
			'company_name' => $sendData['company_name'],
			'template_name' => $sendData['template_name'],
			'message_title' => $sendData['message_title'],
			'message_body' => nl2br($sendData['message_body']),
			'topic_content' => $topicContent
		);
		$coverSheetPath = pteCreateFaxCoverSheetPdf ($reportSettings);

	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
	$sendData['cover_sheet_path'] = $coverSheetPath;
	$sendData['attachment_path'] = $localStoreName;
	$response = pte_call_documo('send_fax', $sendData);  //TODO Change to ASYNC
	$documoResponse = json_decode($response, true);
	$faxId = isset($documoResponse['messageId']) ? $documoResponse['messageId'] : '';   //key to matching fax with callback

	$interactionUpdateData = array (
		'alt_id' => $faxId
	);
	$whereClause = array (
		'process_id' => $processId
	);
	$wpdb->update( 'alpn_interactions', $interactionUpdateData, $whereClause );

	unlink($coverSheetPath);
	unlink($localStoreName);
}



?>
