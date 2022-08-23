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

			 // alpn_log($twitterResponse);

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

				//Love you guys! @afangonthemove @WayneMorgan66 @WiscleFungies #besties

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

						//store it

						try {
							$storage = new StorageClient([
									'keyFilePath' => GOOGLE_STORAGE_KEY
							]);
							$bucketName = 'pte_media_store_1';
							$bucket = $storage->bucket($bucketName);
							$object = $bucket->upload(
									fopen($fileUrl, 'r'),
									['name' => $filename]
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
							"nft_description" => $selectedTemplate['quote']['words'],
							"file_id" => $fileName,
							"category" => $selectedTemplate['category'],
							"mint_quantity" => count($cleanUserList) + 1,
							"chain_id" => "polygon",
							"status" => "approved"
						);
						$wpdb->insert( 'alpn_nft_by_service', $nftData );
						$submissionId = $wpdb->insert_id;

						$userMentions[] = array( "id" => $twitterUserId, "screen_name" =>
						$twitterUserScreenName ); $usedIds = array("1551295064348905472",
						"1405291443023872000"); foreach ($userMentions as $key => $mention)
						{ if (!in_array($mention['id'], $usedIds)) { $nftData = array(
						"nft_owned_id" => $submissionId, "twitter_id" => $mention['id'],
						"twitter_screen_name" => $mention['screen_name'] ); $wpdb->insert(
						'alpn_nft_by_service_owners', $nftData );    //TODO turn into single
						INSERT $usedIds[] = $mention['id'];
							}
						}


					}

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
