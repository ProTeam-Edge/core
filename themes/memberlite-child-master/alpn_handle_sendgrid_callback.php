<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use transloadit\Transloadit;
use PascalDeVink\ShortUuid\ShortUuid;
use Ramsey\Uuid\Uuid;
use Google\Cloud\Storage\StorageClient;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

use Abraham\TwitterOAuth\TwitterOAuth;

alpn_log('Received Email From SendGrid...');

//alpn_log($_POST);

$attachmentsCount = isset($_POST['attachments']) ? $_POST['attachments'] : 0;
$from = isset($_POST['from']) ? stripslashes($_POST['from']) : '';

$rightBracketPos = strrpos($from, ">");
if ($rightBracketPos) {
	$leftBracketPos = strrpos($from, "<");
	$fromEmailAddress = substr($from, $leftBracketPos + 1, $rightBracketPos - $leftBracketPos - 1);
} else {
	$fromEmailAddress = $from;
}

if ($attachmentsCount || $fromEmailAddress == "nftreview@wiscle.com") {

	$mailer = WC()->mailer();

	$supportedTypes = array('image/png', 'image/webp', 'image/jpeg', 'image/gif');

	$dkim = isset($_POST['dkim']) ? json_decode(stripslashes($_POST['dkim']), true) : array();
	$contentIds = isset($_POST['content-ids']) ? json_decode(stripslashes($_POST['content-ids']), true) : array();
	$to = isset($_POST['to']) ? stripslashes($_POST['to']) : '';
	$subject = isset($_POST['subject']) ? stripslashes($_POST['subject']) : '';
	$htmlBody = isset($_POST['html']) ? $_POST['html'] : '';
	$textBody = isset($_POST['text']) ? stripslashes($_POST['text']) : '';
	$senderIp = isset($_POST['sender_ip']) ? stripslashes($_POST['sender_ip']) : '';
	$attachmentInfo = isset($_POST['attachment-info']) ? json_decode(stripslashes($_POST['attachment-info']), true) : array();
	$spamScore = isset($_POST['spam_score']) ? stripslashes($_POST['spam_score']) : 0;
	$spf = isset($_POST['spf']) ? stripslashes($_POST['spf']) : 0;

	switch ($to) {

			case "mint_nft@files.wiscle.com":

				alpn_log("HANDLING MINT");

				if ($fromEmailAddress == "nftreview@wiscle.com") {
					$submissionIdLeft = strrpos($textBody, "*Submission Id:* [") + 18;
					$submissionIdRight = strpos($textBody, "]", $submissionIdLeft);
					$submissionId = substr($textBody, $submissionIdLeft, $submissionIdRight - $submissionIdLeft);
					$alreadyExists = $wpdb->get_results(
						$wpdb->prepare("SELECT * FROM alpn_nft_by_service WHERE id = %d", $submissionId)
					 );
					 if (isset($alreadyExists[0]) && $alreadyExists[0]->status != 'approved') {
							$fileInfo = $alreadyExists[0];
							$fileName = $fileInfo->file_id;
							$fileUrl = PTE_IMAGES_ROOT_URL . $fileName;
							$content = file_get_contents($fileUrl);

							$metaDataArray = array(
								"name" => $alreadyExists[0]->nft_name,
								"description" => $alreadyExists[0]->nft_description,
								"wiscle_category" => $alreadyExists[0]->category,
								"wiscle_submission" => $submissionId,
								"wiscle_mint_quantity" => $alreadyExists[0]->mint_quantity,
								"attributes" => array()
							);
							$contractTemplateId = 4;
							$smartContractData = array(
								"submission_id" => $submissionId,
								"contract_template_id" => $contractTemplateId,
								"nft_metadata" => json_encode($metaDataArray)
							);
							$wpdb->insert( 'alpn_smart_contracts', $smartContractData );
							$tokenId = $wpdb->insert_id;

							$fileArray = array(
								array(
									"path" => "{$tokenId}/{$fileName}",
									"content" => base64_encode($content)
								)
							);
							$fileArray = json_encode($fileArray);
							$client = new Client([
									'timeout'  => 60
							]);
							$fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
							$moralisApiKey = MORALIS_API_KEY;
							$headers = array(
												"Accept" => "application/json",
												"x-api-key" => $moralisApiKey,
												"Content-Type" => "application/json"
											);
							$request = new Request('POST', $fullUrl, $headers, $fileArray);
							$client->sendAsync($request)->then(
								 function ($value) {  //Promise
									 global $wpdb;
									 $results = json_decode($value->getBody()->getContents(), true);
									 $mediaUrl = $results[0]['path'];
										if ($mediaUrl) {
			 							 $tokenIdRight = strrpos($mediaUrl, "/");   //TODO cookies or passing in variables. Need someone to show me how.
			 							 $tokenId = substr($mediaUrl, 0, $tokenIdRight);
			 							 $tokenIdLeft = strrpos($tokenId, "/");
			 							 $tokenId = substr($tokenId, $tokenIdLeft + 1);
										 $tokenData = $wpdb->get_results(
						 					$wpdb->prepare("SELECT nft_metadata, contract_template_id FROM alpn_smart_contracts WHERE token_id = %d", $tokenId)
						 				 );
										 if (isset($tokenData[0])) {
											 $metaDataArray = json_decode($tokenData[0]->nft_metadata, true);

											 $source = "https://ipfs.moralis.io:2053/ipfs/";
											 $sourceLen = strlen($source);
											 if (substr($mediaUrl, 0, $sourceLen) == $source) {
												 $mediaUrlGateway = "https://gateway.moralisipfs.com/ipfs/" . substr($mediaUrl, $sourceLen);
											 }
											 $metaDataArray['image'] = $mediaUrlGateway;
											 $metaDataArray['image_url'] = $mediaUrlGateway;

											 $smartContractData = array(
				 								"nft_metadata" => json_encode($metaDataArray)
				 								);
												$whereClause = array(
													"token_id" => $tokenId
												);
												$wpdb->update( 'alpn_smart_contracts', $smartContractData, $whereClause );
												$certArray = $metaDataArray;
												$certArray['wscProcessId'] =  Uuid::uuid4()->toString(); //UniqueId
												$certArray['wsc_title_box'] =  "Wiscle Pets";
												$certArray['wsc_media_type'] =  "Image NFT";
												//$certificate64 = wsc_create_nft_certificate("wiscle_nft_certificate_{$tokenData[0]->contract_template_id}", $certArray);
												$certificate64 = "";
						 						$fileArray = array(
						 							array(
						 								"path" => "{$tokenId}/certificate.png",
						 								"content" => ""
						 							)
						 						);
					 						$fileArray = json_encode($fileArray);
					 						$client = new Client([
					 								'timeout'  => 90
					 						]);
					 						$fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
					 						$moralisApiKey = MORALIS_API_KEY;
					 						$headers = array(
					 											"Accept" => "application/json",
					 											"x-api-key" => $moralisApiKey,
					 											"Content-Type" => "application/json"
					 										);
					 						$request = new Request('POST', $fullUrl, $headers, $fileArray);
					 						$client->sendAsync($request)->then(
					 							 function ($value) {  //Promise
													 global $wpdb;
					 								 $results = json_decode($value->getBody()->getContents(), true);
					 								 $certificateUrl = $results[0]['path'];
					 								 if ($certificateUrl) {
															$tokenIdRight = strrpos($certificateUrl, "/");   //TODO cookies or passing in variables. Need someone to show me how.
															$tokenId = substr($certificateUrl, 0, $tokenIdRight);
															$tokenIdLeft = strrpos($tokenId, "/");
															$tokenId = substr($tokenId, $tokenIdLeft + 1);
															$tokenData = $wpdb->get_results(
															 $wpdb->prepare("SELECT nft_metadata FROM alpn_smart_contracts WHERE token_id = %d", $tokenId)
															);
															if (isset($tokenData[0])) {
																 $metaDataArray = json_decode($tokenData[0]->nft_metadata, true);
							 									 // $source = "https://ipfs.moralis.io:2053/ipfs/";
							 									 // $sourceLen = strlen($source);
							 									 // if (substr($certificateUrl, 0, $sourceLen) == $source) {
							 										//  $certificateUrlGateway = "https://gateway.moralisipfs.com/ipfs/" . substr($certificateUrl, $sourceLen);
							 									 // }
							 									 // $metaDataArray['nft_certificate_url'] = $certificateUrlGateway;
																 // $metaDataArray['description'] .= "\n\nCertificate: " . $certificateUrlGateway;
							 									 $client = new GuzzleHttp\Client([
							 											'timeout'  => 90
							 									 ]);
							 									 $fileArray = array(
							 										array(
							 											"path" => "{$tokenId}/wiscleNft.json",
							 											"content" => base64_encode(json_encode($metaDataArray))
							 										)
							 									 );
							 									$fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
							 									$moralisApiKey = MORALIS_API_KEY;
							 									$headers = array(
							 														"Accept" => "application/json",
							 														"x-api-key" => $moralisApiKey,
							 														"Content-Type" => "application/json"
							 													);
							 									$uriRequest = new GuzzleHttp\Psr7\Request('POST', $fullUrl, $headers, json_encode($fileArray));
							 									$client->sendAsync($uriRequest)->then(
							 										 function ($value) {  //Promise

																		 		global $wpdb;

																				$accountAddress = "0xa1455225154b13B1809F653a6364B2DE03E5a850";  //wiscle PLus PV Sender

									 											$results = json_decode($value->getBody()->getContents(), true);
									 											$tokenUri = $results[0]['path'];
																				$tokenIdRight = strrpos($tokenUri, "/");   //TODO cookies or passing in variables. Need someone to show me how.
					 															$tokenId = substr($tokenUri, 0, $tokenIdRight);
					 															$tokenIdLeft = strrpos($tokenId, "/");
					 															$tokenId = substr($tokenId, $tokenIdLeft + 1);

																				$tokenData = $wpdb->get_results(
																				 $wpdb->prepare("SELECT nft_metadata FROM alpn_smart_contracts WHERE token_id = %d", $tokenId)
																				);
																				if (isset($tokenData[0])) {

																					 $metaDataArray = json_decode($tokenData[0]->nft_metadata, true);

																					 $imageUrl = $metaDataArray['image'];

																					 $categorySlug =  $metaDataArray["wiscle_category"];
																					 $submissionId =  $metaDataArray["wiscle_submission"];
																					 $nftQuantity =  $metaDataArray["wiscle_mint_quantity"];

																					 $supportedEmojis = array(    //Move to DB
	 																					"Academic" => array(
	 																						"name" => "Academic",
	 																						"contract_address" => "0x41550C277452006d2b7Fe7c84E2441B9C2D2cc92",
																							"url_slug" => "wiscle-academic"
	 																					),
	 																					"Appreciation" => array(
	 																						"name" => "Appreciation",
	 																						"contract_address" => "0x95e275B15B8b27011Ba532CBf0dA23E5e3dcAc74",
																							"url_slug" => "wiscle-appreciation"
	 																					),
	 																					"Artsy" => array(
	 																						"name" => "Artsy",
	 																						"contract_address" => "0xac56a3a34E87aB6db021Dd8E8366803FADe966e0",
																							"url_slug" => "wiscle-artsy"
	 																					),
	 																					"Body Art" => array(
	 																						"name" => "Body Art",
	 																						"contract_address" => "0x923D684c71904b01AD2e0DcF944a91F956Ce9f44",
																							"url_slug" => "wiscle-body-art"
	 																					),
	 																					"Friends" => array(
	 																						"name" => "Friends",
	 																						"contract_address" => "0xd84557d4B6a31b980CbbB720e2B7E0f0d85a58C6",
																							"url_slug" => "wiscle-friends"
	 																					),
	 																					"Halloween" => array(
	 																						"name" => "Halloween",
	 																						"contract_address" => "0x8FE50E080be752FaFF5583AF2054b5d3eD3790a2",
																							"url_slug" => "wiscle-halloween"
	 																					),
	 																					"Love" => array(
	 																						"name" => "Love",
	 																						"contract_address" => "0x1d7E16d2ffdd8C6C64898AE6cc949DD886dD2B6A",
																							"url_slug" => "wiscle-love"
	 																					),
	 																					"Pets" => array(
	 																						"name" => "Pets",
	 																						"contract_address" => "0xFA983Dc27A0f0f3AE42D77C9773aB9a7721C8917",
																							"url_slug" => "wiscle-pets"
	 																					),
	 																					"Ride" => array(
	 																						"name" => "Ride",
	 																						"contract_address" => "0xBB1da9DbEf49254F81DDde72187eA6eCB8297D57",
																							"url_slug" => "wiscle-ride"
	 																					),
	 																					"Selfie" => array(
	 																						"name" => "Selfie",
	 																						"contract_address" => "0x769Aeb7bb67Ce4361A1CdE993C9915CfD77b2799",
																							"url_slug" => "wiscle-selfie"
	 																					),
	 																					"Sports" => array(
	 																						"name" => "Sports",
	 																						"contract_address" => "0xdFF44078cb3f0883c3c50623f3C04751952a726D",
																							"url_slug" => "wiscle-sports"
	 																					),
	 																					"Wins" => array(
	 																						"name" => "Wins",
	 																						"contract_address" => "0x4fC54c79395eb4Da93769173e05e921fD6ecae2F",
																							"url_slug" => "wiscle-wins"
	 																						)
	 																				);

																					$contractAddress = $supportedEmojis[$categorySlug]["contract_address"];
																					$urlSlug = $supportedEmojis[$categorySlug]["url_slug"];
	 																				$collectionName = $supportedEmojis[$categorySlug]["name"];
																				}

																				if (!$contractAddress || !$submissionId || !$accountAddress) {
																					alpn_log("COULD NOT FIND SUBMISSION ID AND/OR ADDRESSES");
																					exit;
																				}

																				$custodialAccount = $wpdb->get_results(
																					$wpdb->prepare("SELECT pk_enc, enc_key FROM alpn_wallet_meta WHERE account_address = %s", $accountAddress)
																				);

																				if (isset($custodialAccount[0])) {

																					$data = array(
																						'cloud_function' => 'wsc_mint_nft',
																						'pk_enc' => $custodialAccount[0]->pk_enc,
																						'enc_key' => $custodialAccount[0]->enc_key,
																						"process_id" => "",
																						"nft_token_uri" => $tokenUri,
																						"nft_account_address" => $accountAddress,
																						"nft_token_id" => $tokenId,
																						"nft_quantity" => $nftQuantity,
																						"nft_recipient_id" => $accountAddress,
																						"nft_contract_address" => $contractAddress,
																						"chain_id" => wsc_to_0xid('polygon'),
																						"nft_template_key" => "hyuxxVrjwREjj2KCrXTerc7q"
																					);
																					$nftInfo = json_decode(wsc_call_cloud_function($data), true);
																					$nftData = isset($nftInfo['result']) ? $nftInfo['result'] : false;
																					if (isset($nftData['transaction_hash'])) {
																						try {
																							$nftDeployedData = array(
																								"contract_address" => $contractAddress,
																								"token_id" => $tokenId,
																								"wallet_address" => $accountAddress,
																								"transaction_hash" => $nftData['transaction_hash'],
																								"process_id" => "",
																								"chain_id" => 'polygon',
																								"quantity" => $nftQuantity,
																								"state" => 'processing',
																								"recipient_address" => $accountAddress,
																								"template_id" => "4"
																							);
																							$wpdb->insert( 'alpn_nfts_deployed', $nftDeployedData );

																								if ($submissionId) {
																									$submissionData = array(
																										"account_address" => $accountAddress,
																										"contract_address" => $contractAddress,
																										"token_id" => $tokenId,
																										"status" => "approved"
													 				 								);
													 												$whereClause = array(
													 													"id" => $submissionId
													 												);
													 												$wpdb->update( 'alpn_nft_by_service', $submissionData, $whereClause );

																									$contactData = $wpdb->get_results(
																										$wpdb->prepare("SELECT s.nft_name, s.service_meta, o.twitter_id, o.twitter_screen_name, o.email_address FROM alpn_nft_by_service s LEFT JOIN alpn_nft_by_service_owners o ON o.nft_owned_id = s.id WHERE s.id = %d ORDER BY o.id DESC LIMIT 1", $submissionId)
																									);

																									if (isset($contactData[0])) {

																										$openInScan = "https://polygonscan.com/token/{$contractAddress}?a={$tokenId}";
																										$openInOpenSea = "https://opensea.io/assets/matic/{$contractAddress}/{$tokenId}";
																										$openinOpenSeaPetCollection = "https://opensea.io/collection/{$urlSlug}";

																										if ($contactData[0]->email_address) {

																											$mailer = WC()->mailer();
																											$emailSubject = "About your Wiscle Email2NFT Submission";
																											$emailHeading = "Congratulations, your NFT was approved";
																											$emailBody = "
																											Here is your NFT on <a href='{$openInOpenSea}'>OpenSea</a><br>
																											Here are all NFTs on OpenSea <a href='{$openinOpenSeaPetCollection}'>Wiscle Pets</a><br>
																											Here is your NFT on <a href='{$openInScan}'>Polygon Blockchain Explorer</a><br>
																											<a href='https://wiscle.com/nft-status-page?e2id={$submissionId}'>Create a free account</a> with this email address to collect your NFT and mint more free NFTs with community-owned Wiscle.<br><br>
																											It may take a few minutes for your NFT to show up on the blockchain services.
																											";
																											 $template = 'vit_generic_email_template.php';
																											 $content = 	wc_get_template_html( $template, array(
																													 'email_heading' => $emailHeading,
																													 'email'         => $mailer,
																													 'email_body'    => $emailBody
																												 ), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
																											 try {
																												 $mailer->send( $contactData[0]->email_address, $emailSubject, $content );
																											 } catch (Exception $e) {
																													 alpn_log ('Caught email exception: '. $e->getMessage());
																											 }

																										} else if ($contactData[0]->twitter_screen_name) {

																											$twitterAccountOwnerId = 343;

																											$twitterData = $wpdb->get_results(
																												$wpdb->prepare("SELECT twitter, twitter_meta from alpn_user_metadata WHERE id = %d ", $twitterAccountOwnerId)
																											 );

																											 if ($twitterData[0] && $twitterData[0]->twitter != '{}') {

																												 $twitterMeta = json_decode($twitterData[0]->twitter_meta, true);
																												 $twitterCreds = json_decode($twitterData[0]->twitter, true);
																												 $accessToken = $twitterCreds['oauth_token'];
																												 $accessTokenSecret = $twitterCreds['oauth_token_secret'];
																												 $ownerUserId = $twitterCreds['user'];
																												 $ownerScreenName = $twitterCreds['screen_name'];
																												 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);

																												 $serviceMeta = json_decode($contactData[0]->service_meta, true);
																												 $tweetId = isset($serviceMeta['tweet_id']) ? $serviceMeta['tweet_id'] : "";

																												 $twitterUserScreenName = $contactData[0]->twitter_screen_name;
																												 $nftName = $contactData[0]->nft_name;

																												 $twitterBody = "
																												 Hey @{$twitterUserScreenName},\n{$nftName} on Wiscle: https://wiscle.com/nft-status-page?e2id={$submissionId}\n{$nftName} on Opensea: {$openInOpenSea}\nWiscle {$collectionName} Collection on OpenSea: {$openinOpenSeaPetCollection}
																							 					";
																							 					$parameters = [
																							 						'status' => $twitterBody,
																							 						'in_reply_to_status_id' => $tweetId,
																													'auto_populate_reply_metadata' => true
																							 						];
																							 				 $result = $connection->post('statuses/update', $parameters);

																											 alpn_log("TWEETED RESPONSE");
																							 				 alpn_log($result);
	 																										 http_response_code(200);
	 																										 exit;

																											 } else {
																												 alpn_log("LOST TWITTER AUTH");
																												 http_response_code(200);
																												 exit;
																											 }
																										}
																									}
																								}

																						} catch (Exception $e) {
																							alpn_log($e);
																						}

																					} else {
																						alpn_log("NFT CREATE EMAIL2NFT ERROR");
																						alpn_log($nftData);
																					}
																				}
							 											},
							 											function ($reason) {
							 												alpn_log( 'The final promise was rejected.' );
							 												alpn_log( $reason );
							 												//TODO HANDLE THIS

							 											}
							 									)->wait();
															}
					 								 }
					 								},
					 								function ($reason) {
					 									alpn_log( 'The certificate promise was rejected.' );
					 									alpn_log( $reason );
					 								}
					 						)->wait();
										 }
			 						 }

							    },
							    function ($reason) {
							      alpn_log( 'The promise was rejected.' );
										alpn_log( $reason );
							    }
							)->wait();
							http_response_code(200);
							exit;

					} else {
						alpn_log("NOTHING TO SEND OR ALREADY APPROVED");
						http_response_code(200);
						exit;
					}

				} else {
					alpn_log("ERROR MINTING -- SENDER NOT APPROVED");
					http_response_code(200);
					exit;
				}

			break;
			case "pets@files.wiscle.com":

				alpn_log("HANDLING PETS");

				$status = false;

				$attachmentTemp = $_FILES["attachment1"]['tmp_name'];
				$attachmentType = $_FILES["attachment1"]['type'];

				$uuid = Uuid::uuid4()->toString();
				$extension = substr($attachmentType, strrpos($attachmentType, '/') + 1);
				$fileName = "{$uuid}.{$extension}";
				$localFile = PTE_ROOT_PATH . "tmp/{$fileName}";
				$result = move_uploaded_file($attachmentTemp, $localFile);

				if (!in_array($attachmentType, $supportedTypes) || !$textBody || !$subject) {

					$emailSubject = "About your Email-to-NFT Pet Submission";
					$emailHeading = "The submission is missing information or the attachment file type isn't supported";
					$emailBody = "Please resend another email with your pet's name in the email Title a little bit about your pet in the message Body. The email Attachment must be a jpeg, png, gif or webm file only. See https://wiscle.com/nft-status-page for more information.";

					 $template = 'vit_generic_email_template.php';
 					 $content = 	wc_get_template_html( $template, array(
 							 'email_heading' => $emailHeading,
 							 'email'         => $mailer,
 							 'email_body'    => $emailBody
 						 ), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
 					 try {
 						 $mailer->send( $from, $emailSubject, $content );
 					 } catch (Exception $e) {
 							 alpn_log ('Caught email exception: '. $e->getMessage());
 					 }
					 unlink($localFile);
					 http_response_code(200);
					 exit;
				}

				$rightBracketPos = strrpos($from, ">");
				if ($rightBracketPos) {
					$leftBracketPos = strrpos($from, "<");
					$fromEmailAddress = substr($from, $leftBracketPos + 1, $rightBracketPos - $leftBracketPos - 1);
					$fromEmailName = trim(substr($from, 0, $leftBracketPos - 1));
				} else {
					$fromEmailAddress = $from;
					$fromEmailName = "";
				}

				if ($fromEmailAddress) {

					$alreadyExists = $wpdb->get_results(
						$wpdb->prepare("SELECT id, status FROM alpn_nft_by_service WHERE email_address = %s", $fromEmailAddress)
					 );

					 if (isset($alreadyExists[0]) && $alreadyExists[0]->status == "approved") {
						 $status = $alreadyExists[0]->status;
						 $nftId = $alreadyExists[0]->id;
						 $emailSubject = "About your Email-to-NFT Pet Submission";
						 $emailHeading = "Your previous submission was Approved";
						 $emailBody = "<a href='https://wiscle.com/nft-status-page?e2id={$nftId}'>Create a free account</a> with this email address to collect your NFT and mint more free NFTs.";

						 $template = 'vit_generic_email_template.php';
						 $content = 	wc_get_template_html( $template, array(
								 'email_heading' => $emailHeading,
								 'email'         => $mailer,
								 'email_body'    => $emailBody
							 ), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
						 try {
							 $mailer->send( $from, $emailSubject, $content );
						 } catch (Exception $e) {
								 alpn_log ('Caught email exception: '. $e->getMessage());
						 }
						 unlink($localFile);
						 http_response_code(200);
						 exit;
					 }

				  if (!$status || isset($alreadyExists[0])) {

						$status = $alreadyExists[0]->status;
						$nftId = $alreadyExists[0]->id;

			 			try {
			 				$storage = new StorageClient([
			 						'keyFilePath' => GOOGLE_STORAGE_KEY
			 				]);
			 				$bucketName = 'pte_media_store_1';
			 				$bucket = $storage->bucket($bucketName);
			 				$object = $bucket->upload(
			 						fopen($localFile, 'r'),
			 						['name' => $fileName]
			 				);
			 				unlink($localFile);
			 			} catch (Exception $e) {
			 				alpn_log("FAILED UPLOADING TO GOOGLE");
			 				alpn_log($e);
							http_response_code(200);
							exit; //TODO HANDLE
			 			}
						 $nftData = array(
				 			"email_address" => $fromEmailAddress,
				 			"email_display_name" => $fromEmailName,
				 			"nft_name" => $subject,
				 			"nft_description" => $textBody,
				 			"file_id" => $fileName,
				 			"category" => "pets",
				 			"chain_id" => "polygon",
							"status" => "pending"
				 		);
						if ($status == "rejected") {
							$whereClause = array(
								"id" => $nftId
							);
							$wpdb->update( 'alpn_nft_by_service', $nftData, $whereClause );
						} else {
							$wpdb->insert( 'alpn_nft_by_service', $nftData );
					 		$nftId = $wpdb->insert_id;
						}
						$fileUrl = PTE_IMAGES_ROOT_URL . $fileName;
						$textBody = nl2br($textBody);
						$emailBody = "
							<b>NFT Name:</b> {$subject}<br><br>
							<b>NFT Description:</b><br><br>{$textBody}<br><br>
							<b>From Address:</b> {$fromEmailAddress}<br><br>
							<b>From Name:</b> {$fromEmailName}<br><br>
							<a href='{$fileUrl}' target='_BLANK'><img src='{$fileUrl}'></a><br><br>
							<a href='https://wiscle.com/nft-status-page?e2id={$nftId}' target='_BLANK'>Status Page</a><br><br>
							<b>Submission Id:</b> [{$nftId}]
						";
						$template = 'vit_generic_email_template.php';
						$content = 	wc_get_template_html( $template, array(
								'email_heading' => $subject,
								'email'         => $mailer,
								'email_body'    => $emailBody
							), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
						try {
							$mailer->send( "nftreview@wiscle.com", "Pet NFT Request", $content );
						} catch (Exception $e) {
								alpn_log ('Caught email exception: '. $e->getMessage());
								http_response_code(200);
								exit;
						}

						//SENDER
						$emailBody = "
							<a href='https://wiscle.com/nft-status-page?e2id={$nftId}' target='_BLANK'>Status Page</a><br><br>
							<b>NFT Name:</b> {$subject}<br><br>
							<b>NFT Description:</b><br><br>{$textBody}<br><br>
							<img src='{$fileUrl}'><br><br>
						";
						$template = 'vit_generic_email_template.php';
						$content = 	wc_get_template_html( $template, array(
								'email_heading' => "Pending manual curation",
								'email'         => $mailer,
								'email_body'    => $emailBody
							), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
						try {
							$mailer->send( $from, "Pet NFT Request Received", $content );
						} catch (Exception $e) {
								alpn_log ('Caught email exception: '. $e->getMessage());
								http_response_code(200);
								exit;
						}
					 }

				}

				//wallet WISCLE.  Contract Wiscle pets
				//email links back to user with invite to come get it.

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
