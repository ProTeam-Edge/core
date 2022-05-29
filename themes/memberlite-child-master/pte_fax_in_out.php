<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

alpn_log('Handling Documo Callback Inbound & Outbound');

$data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();

if (isset($data['direction'])) {

  if ($data['direction'] == 'inbound') {

    try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES['attachment']['tmp_name']),
            array(
                'pdf' => 'application/pdf'
            ),
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }

        $fileName = $_FILES['attachment']['name'];
    		$localFile = "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/tmp/{$fileName}";
    		$result = move_uploaded_file($_FILES['attachment']['tmp_name'], $localFile);

        if ($result) {
          $pdfFileData = array(
            "pdf_key" => $fileName,
            "local_file" => $localFile
          );
          $fileInfo = storePdf($pdfFileData);
          $pdfSize = $fileInfo['pdf_size'];
          $pdfKey = $fileInfo['pdf_key'];

          http_response_code(200);

          $faxNumber = $data['faxNumber'];
          $status = $data['status'];
          $pagesCount = $data['pagesCount'];

          $faxNumberFrom  = preg_replace( '/[^0-9]/', '', $data['faxCsid'] );
          if (strlen($faxNumberFrom) >= 10) {
            $senderNumber = substr(pte_format_pstn_number($data['faxCsid']), 3);
          } else {
            $senderNumber = 'Fax Service';
          };

          $results = $wpdb->get_results(
    				$wpdb->prepare("SELECT topic_id, owner_id FROM alpn_pstn_numbers WHERE pstn_number = %s", $faxNumber)
    			 );

           if (isset($results[0])) {

             $pageString = ($pagesCount == 1) ? "{$pagesCount} Page" : "{$pagesCount} Pages";

             $mimeType = 'application/pdf';
             $fileSource = "Documo";
             $now = date ("Y-m-d H:i:s", time());
             $ownerId = $results[0]->owner_id;
             $topicId = $results[0]->topic_id;
             $description = "{$senderNumber}";
             $permissionValue = 40;  //TODO default private. May want this user configurable
             $friendlyFileName = "Fax - {$pageString} - Received.pdf";

             $rowData = array(
               "owner_id" => $ownerId,
               "name" => "Fax",
               "file_name" => $friendlyFileName,
               "modified_date" =>  $now,
               "created_date" =>  $now,
               "topic_id" => $topicId,
               "mime_type" => $mimeType,
               "description" => $description,
               "file_source" => $fileSource,
               "access_level" => $permissionValue,
               "pdf_key" => $pdfKey,
               "size_bytes" => $pdfSize,
               "status" => 'ready'
             );
             $wpdb->insert( 'alpn_vault', $rowData );
             $vaultId = $wpdb->insert_id;

             $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true ); //Owners Topic ID
             $data = array(
               'process_id' => "",
               'process_type_id' => "fax_received",
               'owner_network_id' => $ownerNetworkId,
               'owner_id' => $ownerId,
               'process_data' => array(
                   'topic_id' => $topicId,
                   'vault_id' => $vaultId,
                   'formatted_number' => $senderNumber,
                   'page_count_string' => $pageString
                 )
             );
             pte_manage_interaction($data);  //start new interaction (empty processId)

           }


        } else {
          throw new RuntimeException('Error Moving File');
        }



    } catch (RuntimeException $e) {

    	alpn_log($e->getMessage());

    }

  } else {  //outbound

    alpn_log('Handling Outbound Events...');

    $documoProcessingStatusName = $data['processingStatusName'];
    if ($documoProcessingStatusName == 'success') {
      $results = $wpdb->get_results(
    		 $wpdb->prepare("SELECT process_id, ux_meta FROM alpn_interactions WHERE alt_id = %s", $data['messageId'])
    	 );
    	if (isset($results[0])){
        $processData = $results[0];
        $uxMeta = json_decode($processData->ux_meta, true);
        $processId = $processData->process_id;
        $data = array(
          'process_id' => $processId,
          'process_type_id' => "fax_send",
          'owner_network_id' => $uxMeta['owner_network_id'],
          'owner_id' => $uxMeta['owner_id'],
          'process_data' => array(
              'documo_processing_status_name' => $documoProcessingStatusName
            )
        );
        pte_manage_interaction($data);
      }


      http_response_code(200);

    }


  }
} else {//no direction
  alpn_log('No direction Found...');
}



?>
