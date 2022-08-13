<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("TWITTER CALLBACK");

use transloadit\Transloadit;
use PascalDeVink\ShortUuid\ShortUuid;
use Ramsey\Uuid\Uuid;
use Google\Cloud\Storage\StorageClient;
use Abraham\TwitterOAuth\TwitterOAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

$twitterAccountOwnerId = 343;

if (isset($_REQUEST['crc_token'])) {
			alpn_log("VALIDATING TWITTER CRC");
			$signature = hash_hmac('sha256', $_REQUEST['crc_token'], TWITTER_CONSUMER_SECRET, true);
			$response['response_token'] = 'sha256='.base64_encode($signature);
			echo json_encode($response);
			http_response_code(200);
			exit;
} else {


		 	$eventJSON = file_get_contents('php://input');
			$twitterResponse = json_decode($eventJSON, true);

			 alpn_log($twitterResponse);

			$ownerUserId = false;

			if (isset($twitterResponse['tweet_create_events'])) {


				$connection = false;

				$forUserId = $twitterResponse['for_user_id'];

				if ($forUserId != "1551295064348905472") {  //only interested in tweets to us
					// alpn_log("DO NOTHING ITS US");
					http_response_code(200);
					exit;
				}

				$twitterCreateEvent = $twitterResponse['tweet_create_events'][0];

				// alpn_log($twitterCreateEvent);

				$tweetId = $twitterCreateEvent['id'];
				$tweetText = trim($twitterCreateEvent['text']);
				$replyToId = $twitterCreateEvent['in_reply_to_user_id'];
				$replyToScreenName = $twitterCreateEvent['in_reply_to_screen_name'];
				$twitterUserId = $twitterCreateEvent['user']['id'];
				$twitterUserScreenName = $twitterCreateEvent['user']['screen_name'];
				$userMentions = $twitterCreateEvent['entities']['user_mentions'];
				$hashTags = $twitterCreateEvent['entities']['hashtags'];

				if ($twitterUserId == "1551295064348905472" || $twitterUserId == "1405291443023872000") {
					// alpn_log("DO NOTHING ITS US");
					http_response_code(200);
					exit;
				}

				// alpn_log($twitterResponse);

				//[TEST] Love my Besties! @afangonthemove @WayneMorgan66 @WiscleFungies #besties

				alpn_log("START TWEET");
				$tweetText = preg_replace('/(\s+|^)@\S+/', '', $tweetText);  //strip Tagged users use json
				alpn_log($tweetText);

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
				 } else {
					 alpn_log("LOST TWITTER AUTH");
					 http_response_code(200);
					 exit;
				 }

					$selectedTemplateName = false;
					$tagList = array();
					foreach ($hashTags as $hashTagKey => $hashTag) {
						$tagList[] = $hashTag['text'];
					}

					$templateSlugs = '"' . implode('","', $tagList) . '"';
					$templates = $wpdb->get_results(
						$wpdb->prepare("SELECT id, name, template_json from alpn_fungie_templates WHERE name IN ({$templateSlugs}) LIMIT 1")
					 );

					 if (isset($templates[0])) {

			 			 $selectedTemplateData = $templates[0];
			 			 $selectedTemplateName = $selectedTemplateData->name;
			 			 $selectedTemplate = json_decode($selectedTemplateData->template_json, true);
			 			 $hashTagPosition = strpos($tweetText, "#" . $selectedTemplateName);
			 			 $hashTagLength = strlen("#" . $selectedTemplateName);
			 			 $tweetText = trim(substr($tweetText, 0, $hashTagPosition)) . trim(substr($tweetText, $hashTagPosition + $hashTagLength)); //remove the tag used to select template

					 } else {
						 //error no template
					 }

					$cleanUserList = array();
					$usedIds = array("1551295064348905472", "1405291443023872000");
					$allNames = array();
					foreach ($userMentions as $key => $mention) {
						if (!in_array($mention['id'], $usedIds)) {
							$cleanUserList[] = $mention['id'];
							$usedIds[] = $mention['id'];
						}
					}

					// if ($selectedTemplateName && $tweetText && count($cleanUserList) >= $selectedTemplate['minimum_tags'] && !checkProfanity($tweetText)) {
					if ($selectedTemplateName && $tweetText && count($cleanUserList) >= $selectedTemplate['minimum_tags']) {

						alpn_log("PROCESSING TWEET START");
						alpn_log($selectedTemplateName);
						alpn_log($tweetText);

						$selectedTemplate['tweet_text']['words'] = $tweetText;
						$selectedTemplate['tagged'] = $cleanUserList;
						$selectedTemplate['creator'] = $twitterUserId;

						$connection->setApiVersion('2');
						$response = $connection->get('users', ["user.fields" => "profile_image_url", 'ids' => implode(",", $cleanUserList) . ",{$twitterUserId}",]);

						$userData = (array) $response->data;

						$itemCount = count($userData);
						if (!$itemCount) {
							//FAIL
						}

						$sender = (array) $userData[$itemCount - 1];
						$selectedTemplate['tag_count'] = $itemCount - 1;
						$selectedTemplate['tweet_text']['words'] = $tweetText;
						$selectedTemplate['date']['words'] = date("F j, Y");
						$selectedTemplate['sender']['image_url'] = substr($sender['profile_image_url'], 0, strrpos($sender['profile_image_url'], "_")) . substr($sender['profile_image_url'], strrpos($sender['profile_image_url'], "."));
						$selectedTemplate['sender']['words'] = $sender['username'];

						if ($itemCount == 2) {
							$tagged1 = (array) $userData[0];
							$selectedTemplate['tagged_1']['item_1']['image_url'] = substr($tagged1['profile_image_url'], 0, strrpos($tagged1['profile_image_url'], "_")) . substr($tagged1['profile_image_url'], strrpos($tagged1['profile_image_url'], "."));
							$selectedTemplate['tagged_1']['item_1']['words'] = $tagged1['username'];
						}

						if ($itemCount == 3) {
							$tagged1 = (array) $userData[0];
							$selectedTemplate['tagged_2']['item_1']['image_url'] = substr($tagged1['profile_image_url'], 0, strrpos($tagged1['profile_image_url'], "_")) . substr($tagged1['profile_image_url'], strrpos($tagged1['profile_image_url'], "."));
							$selectedTemplate['tagged_2']['item_1']['words'] = $tagged1['username'];

							$tagged2 = (array) $userData[1];
							$selectedTemplate['tagged_2']['item_2']['image_url'] = substr($tagged2['profile_image_url'], 0, strrpos($tagged2['profile_image_url'], "_")) . substr($tagged2['profile_image_url'], strrpos($tagged2['profile_image_url'], "."));
							$selectedTemplate['tagged_2']['item_2']['words'] = $tagged2['username'];
						}

						if ($itemCount == 4) {
							$tagged1 = (array) $userData[0];
							$selectedTemplate['tagged_3']['item_1']['image_url'] = substr($tagged1['profile_image_url'], 0, strrpos($tagged1['profile_image_url'], "_")) . substr($tagged1['profile_image_url'], strrpos($tagged1['profile_image_url'], "."));
							$selectedTemplate['tagged_3']['item_1']['words'] = $tagged1['username'];

							$tagged2 = (array) $userData[1];
							$selectedTemplate['tagged_3']['item_2']['image_url'] = substr($tagged2['profile_image_url'], 0, strrpos($tagged2['profile_image_url'], "_")) . substr($tagged2['profile_image_url'], strrpos($tagged2['profile_image_url'], "."));
							$selectedTemplate['tagged_3']['item_2']['words'] = $tagged2['username'];

							$tagged3 = (array) $userData[2];
							$selectedTemplate['tagged_3']['item_3']['image_url'] = substr($tagged3['profile_image_url'], 0, strrpos($tagged3['profile_image_url'], "_")) . substr($tagged3['profile_image_url'], strrpos($tagged3['profile_image_url'], "."));
							$selectedTemplate['tagged_3']['item_3']['words'] = $tagged3['username'];
						}

						//CREATE CARD

					  $fileName = wsc_create_wiscle_fungie($selectedTemplate);
						$fileUrl = PTE_ROOT_PATH . "tmp/" . $fileName;

						$serviceMeta = array(
							"tweet_id" => $tweetId
						);
						$nftData = array(
							"service_meta" => json_encode($serviceMeta),
							"nft_name" => $tweetText,
							"nft_description" => $selectedTemplate['quote'],
							"file_id" => "",
							"category" => $selectedTemplate['category'],
							"mint_quantity" => count($cleanUserList),
							"chain_id" => "polygon",
							"status" => "approved"
						);
						$wpdb->insert( 'alpn_nft_by_service', $nftData );
						$submissionId = $wpdb->insert_id;

						$userMentions[] = array(
							"id" => $twitterUserId,
							"screen_name" => $twitterUserScreenName
						);
						$usedIds = array("1551295064348905472", "1405291443023872000");
						foreach ($userMentions as $key => $mention) {
							if (!in_array($mention['id'], $usedIds)) {
								$nftData = array(
									"nft_owned_id" => $submissionId,
									"twitter_id" => $mention['id'],
									"twitter_screen_name" => $mention['screen_name']
								);
								$wpdb->insert( 'alpn_nft_by_service_owners', $nftData );    //TODO turn into single INSERT
								$usedIds[] = $mention['id'];
							}
						}

						$mediaDescription = nl2br($selectedTemplate['quote']);

						alpn_log("HANDLING MINT");

						$content = file_get_contents($fileUrl);
						// unlink ($fileUrl);

						$metaDataArray = array(
							"name" => $tweetText,
							"description" => $mediaDescription,
							"wiscle_category" => $selectedTemplate['category'],
							"wiscle_submission" => $submissionId,
							"wiscle_mint_quantity" => count($cleanUserList),
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

																				 $templates = $wpdb->get_results(
															 						$wpdb->prepare("SELECT id, name, template_json from alpn_fungie_templates WHERE name = %s", $categorySlug)
															 					 );

															 					 if (isset($templates[0])) {

																					 	$selectedTemplate = json_decode($templates[0]->template_json, true);

																						alpn_log("RIGHT HERE");
																						alpn_log($selectedTemplate);

																						$contractAddress = $selectedTemplate['contract_address'];
																						$urlSlug = isset($selectedTemplate['url_slug']) ? $selectedTemplate['url_slug'] : strtolower($selectedTemplate['name']);
																		 				$collectionName = $selectedTemplate['name'];
																				}

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

																									if ($contactData[0]->twitter_screen_name) {

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
																					alpn_log("NFT HASH NOT RECEIVED");
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

						 alpn_log("TWITTER CALLBACK DONE");

						 http_response_code(200);
						exit;

				} else { //missing data

					$twitterBody = "
						Hey @{$twitterUserScreenName},\n
						Your Fungie must include a #hashtag for your design, no profanity and have {$selectedTemplate['minimum_tags']} to {$selectedTemplate['maximum_tags']} @tags. Please try again.
					";
					$parameters = [
						'status' => $twitterBody,
						'in_reply_to_status_id' => $tweetId
						];
				 $result = $connection->post('statuses/update', $parameters);
				 http_response_code(200);
 				 exit;
				}

			} else { //end create events
				//not interested in event
				http_response_code(200);
				exit;
			}
	}
alpn_log("ERROR CALLBACK TWITTER");
http_response_code(200);
exit;
//
// if (false) {
// 	pp("GOTIME");
// 	$twitterData = $wpdb->get_results(
// 		$wpdb->prepare("SELECT twitter, twitter_meta from alpn_user_metadata WHERE id = %d ", $twitterAccountOwnerId)
// 	 );
// 	 if (!isset($twitterData[0]) || $twitterData[0]->twitter == '{}') {
// 			pp("Re-login to Twitter");
// 			exit;
// 	 }
// 	 $twitterMeta = json_decode($twitterData[0]->twitter_meta, true);
// 	 $twitterCreds = json_decode($twitterData[0]->twitter, true);
//
// 	 $accessToken = $twitterCreds['oauth_token'];
// 	 $accessTokenSecret = $twitterCreds['oauth_token_secret'];
// 	 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);
//
//
// 	 $certName = "certificate_test.png";
// 	 $filePath = PTE_ROOT_PATH . "tmp/{$certName}";
// 	 $media = $connection->upload('media/upload', ['media' => $filePath]);
// 	 $fileId = $media->media_id_string;
//
// 	 pp($media);
//
// 	 $recipientTwitterId = "56469651";
//
// 	 $message = "";
//
// 	 $data = [
// 	     'event' => [
// 	         'type' => 'message_create',
// 	         'message_create' => [
// 	             'target' => [
// 	                 'recipient_id' => $recipientTwitterId
// 	             ],
// 	             'message_data' => [
// 	                 'text' => $message,
// 									 'attachment.type' => 'media',
// 									 'attachment.media.id' => $fileId
// 	             ]
// 	         ]
// 	     ]
// 	 ];
// 	 //$result = $connection->post('direct_messages/events/new', $data, true);
//
// 	 pp($data);
//
//
// 	 // $content = $connection->post("direct_messages/events/new");
// 	 // pp($content);
//
//
// 	 // $content = $connection->get("account_activity/all/webhooks");
// 	 // pp($content);
//
// 	 // $content = $connection->delete("account_activity/all/DEV/subscriptions/1405291443023872000");
// 	 // pp($content);
//
//
// 	 // $content = $connection->delete("account_activity/all/DEV/webhooks/1549172051327348736");
//
// 	 // $url = "https://wiscle.com/wp-content/themes/memberlite-child-master/alpn_handle_twitter_callback.php";
// 	 // $content = $connection->post("account_activity/all/dev/webhooks", ["url" => $url]);
// 	 // $content = $connection->post("account_activity/all/dev/subscriptions");
// 	 // $content = $connection->get("account_activity/all/webhooks");
//
//
//
// 		//pp($content);
// 		pp("COOL");
// 		http_response_code(200);
// 	exit;
// }






?>
