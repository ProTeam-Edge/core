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

			//alpn_log($twitterResponse);

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

				$tweetId = $twitterCreateEvent['id'];
				$tweetText = trim($twitterCreateEvent['text']);
				$replyToId = $twitterCreateEvent['in_reply_to_user_id'];
				$replyToScreenName = $twitterCreateEvent['in_reply_to_screen_name'];
				$twitterUserId = $twitterCreateEvent['user']['id'];
				$twitterUserScreenName = $twitterCreateEvent['user']['screen_name'];
				$twitterUserProfileImageUrl = $twitterCreateEvent['user']['profile_image_url_https'];
				$userMentions = $twitterCreateEvent['entities']['user_mentions'];
				$hashTags = $twitterCreateEvent['entities']['hashtags'];

				if ($twitterUserId == "1551295064348905472" || $twitterUserId == "1405291443023872000") {
					// alpn_log("DO NOTHING ITS US");
					http_response_code(200);
					exit;
				}

				// alpn_log($twitterResponse);

				//Check out this dalle NFT I made for you! @afangonthemove @WiscleFungies #inspo1

				alpn_log("START TWEET");
				$tweetText = preg_replace('/(\s+|^)@\S+/', '', $tweetText);  //strip Tagged users use json
				$tweetText = trim(substr($tweetText, 0, strrpos($tweetText, "https://")));
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

				 $mediaUrl = false;
				 $mediaType = false;
				 $selectedTemplateName = false;

				 if (isset($twitterCreateEvent['entities']['media'][0])) {
					 $mediaDescription = $twitterCreateEvent['entities']['media'][0]['description'];
					 $mediaUrl = $twitterCreateEvent['entities']['media'][0]['media_url_https'];
					 $mediaType = $twitterCreateEvent['entities']['media'][0]['type'];
				 }

				 if (!$mediaUrl || $mediaType == "video") {
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
				 }

					// if ($selectedTemplateName && $tweetText && count($cleanUserList) >= $selectedTemplate['minimum_tags'] && !checkProfanity($tweetText)) {
					if ($selectedTemplateName || $mediaUrl) {

						alpn_log("PROCESSING TWEET START");
						// alpn_log($selectedTemplateName);
						// alpn_log($tweetText);

						if ($selectedTemplateName) {

							$cleanUserList = array();
							$usedIds = array("1551295064348905472", "1405291443023872000");
							foreach ($userMentions as $key => $mention) {
								if (!in_array($mention['id'], $usedIds)) {
									$cleanUserList[] = $mention['id'];
									$usedIds[] = $mention['id'];
								}
							}

							$connection->setApiVersion('2');
							$response = $connection->get('users', ["user.fields" => "profile_image_url", 'ids' => implode(",", $cleanUserList)]);
							$userData = (array) $response->data;

							$selectedTemplate['tag_count'] =  1;  //TODO support multiple recipients
							$selectedTemplate['tweet_text']['words'] = $tweetText;
							$selectedTemplate['date']['words'] = date("F j, Y");

							$selectedTemplate['sender']['image_url'] = substr($twitterUserProfileImageUrl, 0, strrpos($twitterUserProfileImageUrl, "_")) . substr($twitterUserProfileImageUrl, strrpos($twitterUserProfileImageUrl, "."));
							$selectedTemplate['sender']['words'] = $twitterUserScreenName;

							$tagged1 = (array) $userData[0];
							$selectedTemplate['tagged_1']['item_1']['image_url'] = substr($tagged1['profile_image_url'], 0, strrpos($tagged1['profile_image_url'], "_")) . substr($tagged1['profile_image_url'], strrpos($tagged1['profile_image_url'], "."));
							$selectedTemplate['tagged_1']['item_1']['words'] = $tagged1['username'];

							$nftDescription = $selectedTemplate['quote']['words'];
							$nftCategory = $selectedTemplate['category'];

							//CREATE CARD
							$fileName = wsc_create_wiscle_fungie($selectedTemplate);
							$filePath = PTE_ROOT_PATH . "tmp/{$fileName}";

						} else if ($mediaUrl) {
							$fileName = substr($mediaUrl, strrpos($mediaUrl, "/") + 1);
							$filePath = PTE_ROOT_PATH . "tmp/{$fileName}";
							$nftDescription = $mediaDescription;
							$nftCategory = 'Custom';
							file_put_contents($filePath, file_get_contents($mediaUrl));
						}
						//store it

						try {
							$storage = new StorageClient([
									'keyFilePath' => GOOGLE_STORAGE_KEY
							]);
							$bucketName = 'pte_media_store_1';
							$bucket = $storage->bucket($bucketName);
							$object = $bucket->upload(
									fopen($filePath, 'r'),
									['name' => $fileName]
							);
						} catch (Exception $e) {
							alpn_log("FAILED UPLOADING TO GOOGLE TWITTER");
							alpn_log($e);
							http_response_code(200);
							exit; //TODO HANDLE
						}

						$serviceMeta = array(
							"tweet_id" => $tweetId
						);
						$nftData = array(
							"service_meta" => json_encode($serviceMeta),
							"nft_name" => $tweetText,
							"nft_description" => $nftDescription,
							"file_id" => $fileName,
							"category" => $nftCategory,
							"mint_quantity" => 1,
							"chain_id" => "polygon",
							"status" => "approved"
						);
						$wpdb->insert( 'alpn_nft_by_service', $nftData );
						$submissionId = $wpdb->insert_id;

						$userMentions[] = array(
							"id" => $twitterUserId,
							"screen_name" => $twitterUserScreenName,
							"role" => "creator"
						);
						$usedIds = array("1551295064348905472", "1405291443023872000");
						$twitterRecipientId = false;
						foreach ($userMentions as $key => $mention) {
							if (!in_array($mention['id'], $usedIds)) {

								$userRole = isset($mention['role']) ? $mention['role'] : 'recipient';

								if ($userRole == 'creator' || !$twitterRecipientId) {
									$nftData = array(
										"nft_owned_id" => $submissionId,
										"twitter_id" => $mention['id'],
										"twitter_screen_name" => $mention['screen_name'],
										"role" => $userRole
									);
									$wpdb->insert( 'alpn_nft_by_service_owners', $nftData );    //TODO turn into single INSERT
									$usedIds[] = $mention['id'];
									if ($userRole == 'recipient') {   //selects a single recipient //TODO consider adding multiple recipients back for network effects
										$twitterRecipientId = $mention['id'];
										$twitterRecipientScreenName = $mention['screen_name'];
									}
								}
							}
						}

						if ($twitterRecipientScreenName) {

								$connection->setApiVersion('1.1');

								 try {
				 						$media = $connection->upload('media/upload', ['media' => $filePath]);
				 						$fileId = $media->media_id_string;
				 						unlink($filePath);
				 					} catch (Exception $error) {
					 					 alpn_log("UNABLE TO HANDLE TWITTER MEDIA");
					 					 alpn_log($error);
				 					}

								$twitterBody = "Congratulations @{$twitterRecipientScreenName}! @{$twitterUserScreenName} gifted you a Fungie NFT. Approve and collect it on Wiscle for free: https://wiscle.com/nft-claim?i={$submissionId}";
								$parameters = [
									'status' => $twitterBody,
									'in_reply_to_status_id' => $tweetId,
									'auto_populate_reply_metadata' => true,
									'media_ids' => $fileId
									];
							 $result = $connection->post('statuses/update', $parameters);
						 }
					 }
				 }
			 }
alpn_log("HANDLE TWITTER CALLBACK DONE");
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
