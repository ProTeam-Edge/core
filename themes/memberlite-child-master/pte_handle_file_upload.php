<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use transloadit\Transloadit;
use PascalDeVink\ShortUuid\ShortUuid;

//TODO lots of checking...
global $wpdb;

alpn_log('Received Upload Request From DropZone...');

alpn_log($_FILES);

pte_json_out($_FILES);

exit;


$attachmentsCount = isset($_POST['attachments']) ? $_POST['attachments'] : 0;

if ($attachmentsCount) {

	$dkim = isset($_POST['dkim']) ? json_decode(stripslashes($_POST['dkim']), true) : array();
	$contentIds = isset($_POST['content-ids']) ? json_decode(stripslashes($_POST['content-ids']), true) : array();
	$to = isset($_POST['to']) ? stripslashes($_POST['to']) : '';
	$from = isset($_POST['from']) ? stripslashes($_POST['from']) : '';
	$subject = isset($_POST['subject']) ? stripslashes($_POST['subject']) : '';
	$htmlBody = isset($_POST['html']) ? $_POST['html'] : '';
	$textBody = isset($_POST['text']) ? stripslashes($_POST['text']) : '';
	$senderIp = isset($_POST['sender_ip']) ? stripslashes($_POST['sender_ip']) : '';
	$attachmentInfo = isset($_POST['attachment-info']) ? json_decode(stripslashes($_POST['attachment-info']), true) : array();
	$spamScore = isset($_POST['spam_score']) ? stripslashes($_POST['spam_score']) : 0;
	$spf = isset($_POST['spf']) ? stripslashes($_POST['spf']) : 0;


	//TODO Log all these goodies?

	//alpn_log($dkim);
	//alpn_log($contentIds);
	//alpn_log($to);
	//alpn_log($from);
	//alpn_log($textBody);
	//alpn_log($senderIp);
	//alpn_log($spamScore);
	//alpn_log($spf);


try {
	$transloaditKey = TRANSLOADIT_KEY;
	$transloaditSecret = TRANSLOADIT_SECRET;
	$transloadit = new Transloadit(array(
		'key'    => $transloaditKey,
		'secret' => $transloaditSecret,
	));
	$shortUuid = new ShortUuid();

	$emailRoute = mailparse_rfc822_parse_addresses($to);
	if (isset($emailRoute[0])) {
		$toEmail = $emailRoute[0]['address'];
		$toKey = $toEmail ? substr($toEmail, 0, strpos($toEmail, "@")) : '';
	}

	$emailFrom = mailparse_rfc822_parse_addresses($from);
	if (isset($emailFrom[0])) {
		$fromEmail = $emailFrom[0]['display'];
		$fromEmailAddress = $emailFrom[0]['address'];
	}

	$results = $wpdb->get_results(
		 $wpdb->prepare("SELECT id, owner_id, topic_type_id FROM alpn_topics WHERE email_route_id = %s", $toKey)   //Case sensitive
	 );

	if (isset($results[0])) {

				$rowData = $results[0];
				$topicId = $rowData->id;
				$topiTypeId = $rowData->topic_type_id;
				$ownerId = $rowData->owner_id;
				$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true ); //Owners Topic ID

				$permissionValue = '40';
				foreach ($_FILES as $key => $value) {
					$fileName = $value['name'];
					$suid = $shortUuid->uuid4();
					$fname = pathinfo($fileName, PATHINFO_FILENAME);
					$ext = pathinfo($fileName, PATHINFO_EXTENSION);
					$fullName = "{$fname}.{$suid}.{$ext}";
					$localFile = "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/tmp/{$fullName}";
					$result = move_uploaded_file($value['tmp_name'], $localFile);

					if ($result) {
						$mimeType = $value['type'];
						$now = date ("Y-m-d H:i:s", time());

						$rowData = array(
							"owner_id" => $ownerId,
							"upload_id" => $suid,
							"name" => 'File',
							"file_name" => "{$fname}.{$ext}",
							"modified_date" =>  $now,
							"created_date" =>  $now,
							"topic_id" => $topicId,
							"mime_type" => $mimeType,
							"description" => "File received by email from {$fromEmail}",
							"file_source" => '',
							"access_level" => $permissionValue,
							"status" => 'added'
						);
						$wpdb->insert( 'alpn_vault', $rowData );
						$vaultId = $wpdb->insert_id;

						$response = $transloadit->createAssembly(array(
							'files' => array($localFile),
							'fields' => array(
								'pte_source' => "Email Routing",
								'pte_uid' => $suid
							),
							'params' => array(
								'template_id' => '3b83f38410d744caa3060af90cd64bc0'
							),
						));

						if (isset($response->data)) {
							$data = $response->data;
							unlink($localFile);
							//Async File Received Interaction
							$data = array(
								'process_id' => "",
								'process_type_id' => "file_received",
								'owner_network_id' => $ownerNetworkId,
								'owner_id' => $ownerId,
								'process_data' => array(
										'topic_id' => $topicId,
										'vault_id' => $vaultId,
										'file_name' => "{$fname}.{$ext}",
										'static_name' => "{$fromEmail}",
										'message_title' => $subject,
										'message_body' => trim($textBody)
									)
							);
							pte_manage_interaction($data);
						}
					}
				}

		}

		http_response_code(200);

} catch (\Exception $e) { // Global namespace
		alpn_log($e);
}

}
alpn_log('DONE Received Email From SendGrid...');

?>
