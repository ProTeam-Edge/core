<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use transloadit\Transloadit;
use PascalDeVink\ShortUuid\ShortUuid;
use Ramsey\Uuid\Uuid;

//test email abc123rfdsa@files.proteamedge.com

//TODO lots of checking...
global $wpdb;

alpn_log('Received Email From SendGrid...');

//alpn_log($_POST);

$attachmentsCount = isset($_POST['attachments']) ? $_POST['attachments'] : 0;

if ($attachmentsCount) {

	$supportedTypes = array('image/png', 'image/webp', 'image/jpeg', 'image/gif');

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

	switch ($to) {
			case "pets@files.wiscle.com":

				$attachmentTemp = $_FILES["attachment1"]['tmp_name'];
				$attachmentType = $_FILES["attachment1"]['type'];
				$uuid = Uuid::uuid4()->toString();
				$extension = substr($attachmentType, strrpos($attachmentType, '/') + 1);
				$fileName = "{$uuid}.{$extension}";

				$localFile = PTE_ROOT_PATH . "tmp/{$fileName}";
				$result = move_uploaded_file($attachmentTemp, $localFile);

				$fromEmail = "";
				$fromEmailAddress = $from;
				$emailFrom = mailparse_rfc822_parse_addresses($from);

				if (isset($emailFrom[0])) {
					$fromEmail = $emailFrom[0]['display'];
					$fromEmailAddress = $emailFrom[0]['address'];
				}

				$nftData = array(
					"email_address" => $fromEmailAddress,
					"email_display_name" => $fromEmail,
					"nft_name" => $subject,
					"nft_description" => $textBody,
					"file_id" => $fileName,
					"category" => "pets",
					"chain_id" => "polygon"
				);


				alpn_log($nftData );

				$wpdb->insert( 'alpn_nft_by_email', $nftData );
				$nftId = $wpdb->insert_id;




				//Send preview to nftreview@wiscle.com
				//forward to: nft_mint
				//validate FROM email
				//extract db id from email body
				//run the code from mint_nft WW here
				//wallet WISCLE.  Contract Wiscle pets
				//email links back to user with invite to come get it.



				//pte_send_mail();

			break;
			default:
				try {
					$transloaditKey = TRANSLOADIT_KEY;
					$transloaditSecret = TRANSLOADIT_SECRET;
					$transloadit = new Transloadit(array(
						'key'    => $transloaditKey,
						'secret' => $transloaditSecret,
					));

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
									$uuid = Uuid::uuid4();
									$suid = $uuid->toString();
									$fnameSimple = pathinfo($fileName, PATHINFO_FILENAME);
									$fname = $uuid . $fnameSimple;   //Because Uppy Metadata problems.
									$ext = pathinfo($fileName, PATHINFO_EXTENSION);
									$fullName = "{$fname}.{$ext}";
									$localFile = "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/tmp/{$fullName}";
									$result = move_uploaded_file($value['tmp_name'], $localFile);

									if ($result) {

										$mimeType = $value['type'];
										$now = date ("Y-m-d H:i:s", time());

										$rowData = array(
											"owner_id" => $ownerId,
											"upload_id" => $suid,
											"name" => 'File',
											"file_name" => "{$fnameSimple}.{$ext}",
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

										$transloaditTemplateId = "b51ccbe1760d410c8cf9b409228e6139";
										if (PTE_HOST_DOMAIN_NAME == 'alct.pro') {  //dev
											$transloaditTemplateId = "b51ccbe1760d410c8cf9b409228e6139";
										}

										$response = $transloadit->createAssembly(array(
											'files' => array($localFile),
											'params' => array(
												'template_id' => $transloaditTemplateId
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
														'file_name' => "{$fnameSimple}.{$ext}",
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

	//TODO Log all these goodies?

	//alpn_log($dkim);
	//alpn_log($contentIds);
	//alpn_log($to);
	//alpn_log($from);
	//alpn_log($textBody);
	//alpn_log($senderIp);
	//alpn_log($spamScore);
	//alpn_log($spf);

}
alpn_log('DONE Received Email From SendGrid...');

?>
