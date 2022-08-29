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
alpn_log("HANDLING MINT");


if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$content = file_get_contents($fileUrl);

$metaDataArray = array(
	"name" => $tweetText,
	"description" => $mediaDescription,
	"wiscle_category" => $selectedTemplate['category'],
	"wiscle_submission" => $submissionId,
	"wiscle_mint_quantity" => count($cleanUserList) + 1,
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

																		$contractData = $wpdb->get_results(
																			$wpdb->prepare("SELECT s.nft_name, s.file_id, s.service_meta, o.twitter_id, o.twitter_screen_name FROM alpn_nft_by_service s LEFT JOIN alpn_nft_by_service_owners o ON o.nft_owned_id = s.id WHERE s.id = %d ORDER BY o.id DESC LIMIT 1", $submissionId)
																		);

																		if (isset($contractData[0])) {

																			$openInScan = "https://polygonscan.com/token/{$contractAddress}?a={$tokenId}";
																			$openInOpenSea = "https://opensea.io/assets/matic/{$contractAddress}/{$tokenId}";

																			if ($contractData[0]->twitter_screen_name) {

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

																					 $serviceMeta = json_decode($contractData[0]->service_meta, true);
																					 $tweetId = isset($serviceMeta['tweet_id']) ? $serviceMeta['tweet_id'] : "";

																					 $twitterUserScreenName = $contractData[0]->twitter_screen_name;
																					 $nftName = $contractData[0]->nft_name;

																					 try {
																							$filePath = PTE_ROOT_PATH . "tmp/" . $contractData[0]->file_id;
																	 						$media = $connection->upload('media/upload', ['media' => $filePath]);
																	 						$fileId = $media->media_id_string;
																	 						unlink($filePath);
																	 					} catch (Exception $error) {
																		 					 alpn_log("UNABLE TO HANDLE TWITTER MEDIA");
																		 					 alpn_log($error);
																	 					}

																					 $twitterBody = "
																					 Hey @{$twitterUserScreenName},\nYour NFT on Wiscle: https://wiscle.com/nft-status-page?e2id={$submissionId}\nOn Opensea: {$openInOpenSea}\nOn Polygon: {$openInScan}
																					";
																					$parameters = [
																						'status' => $twitterBody,
																						'in_reply_to_status_id' => $tweetId,
																						'auto_populate_reply_metadata' => true,
																						'media_ids' => $fileId
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



?>
