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

$supportedEmojis = array(
	"🤓" => array(
		"name" => "Academic",
		"contract_address" => "0x41550C277452006d2b7Fe7c84E2441B9C2D2cc92"
	),
	"💙" => array(
		"name" => "Appreciation",
		"contract_address" => "0x95e275B15B8b27011Ba532CBf0dA23E5e3dcAc74"
	),
	"🎨" => array(
		"name" => "Artsy",
		"contract_address" => "0xac56a3a34E87aB6db021Dd8E8366803FADe966e0"
	),
	"⚓" => array(
		"name" => "Body Art",
		"contract_address" => "0x923D684c71904b01AD2e0DcF944a91F956Ce9f44"
	),
	"🤩" => array(
		"name" => "Friends",
		"contract_address" => "0xd84557d4B6a31b980CbbB720e2B7E0f0d85a58C6"
	),
	"🎃" => array(
		"name" => "Halloween",
		"contract_address" => "0x8FE50E080be752FaFF5583AF2054b5d3eD3790a2"
	),
	"❤️" => array(
		"name" => "Love",
		"contract_address" => "0x1d7E16d2ffdd8C6C64898AE6cc949DD886dD2B6A"
	),
	"😇" => array(
		"name" => "Pets",
		"contract_address" => "0xFA983Dc27A0f0f3AE42D77C9773aB9a7721C8917"
	),
	"🚗" => array(
		"name" => "Ride",
		"contract_address" => "0xBB1da9DbEf49254F81DDde72187eA6eCB8297D57"
	),
	"😎" => array(
		"name" => "Selfie",
		"contract_address" => "0x769Aeb7bb67Ce4361A1CdE993C9915CfD77b2799"
	),
	"😎" => array(
		"name" => "Sports",
		"contract_address" => "0xdFF44078cb3f0883c3c50623f3C04751952a726D"
	),
	"🏆" => array(
		"name" => "Wins",
		"contract_address" => "0x4fC54c79395eb4Da93769173e05e921fD6ecae2F"
		)
);

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

			$ownerUserId = false;

			if (isset($twitterResponse['tweet_create_events'])) {

				$mailer = WC()->mailer();

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

				if ($twitterUserId == "1551295064348905472" || $twitterUserId == "1405291443023872000") {
					// alpn_log("DO NOTHING ITS US");
					http_response_code(200);
					exit;
				}

				//alpn_log($twitterResponse);

				alpn_log("START TWEET");

				$emojis = wsc_retrieve_emoji($tweetText);
				$tweetText = wsc_remove_emoji($tweetText);
				$tweetText = trim(substr($tweetText, $twitterCreateEvent['display_text_range'][0]));
				$tweetText = trim(substr($tweetText, 0, strrpos($tweetText, "https://")));
				$tweetText = preg_replace('/(\s+|^)@\S+/', '', $tweetText);  //strip Tagged users use json

				alpn_log($tweetText);

				if (!isset($emojis[0])) {
					alpn_log("NO EMOJI GO ABOUT YOUR BUSINESS");
					http_response_code(200);
					exit;
				}

				$firstEmoji = $emojis[0][0];
				if (!isset($supportedEmojis[$firstEmoji])) {
					alpn_log("DON'T RECOGNIZE THIS EMOJI COMMAND");
					http_response_code(200);
					exit;
				}

				$mediaDescription = false;
				$mediaUrl = false;

				if (isset($twitterCreateEvent['entities']['media'][0])) {
					$mediaDescription = $twitterCreateEvent['entities']['media'][0]['description'];
					$mediaUrl = $twitterCreateEvent['entities']['media'][0]['media_url_https'];
					$mediaType = $twitterCreateEvent['entities']['media'][0]['type'];
				}

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

				if ($tweetText && $mediaUrl && $mediaType == "photo") {

					alpn_log("PROCESSING TWEET START");
					$status = false;

					$filename = substr($mediaUrl, strrpos($mediaUrl, "/") + 1);
					$localFile = PTE_ROOT_PATH . "tmp/{$filename}";
					file_put_contents($localFile, file_get_contents($mediaUrl));

					try {
						$storage = new StorageClient([
								'keyFilePath' => GOOGLE_STORAGE_KEY
						]);
						$bucketName = 'pte_media_store_1';
						$bucket = $storage->bucket($bucketName);
						$object = $bucket->upload(
								fopen($localFile, 'r'),
								['name' => $filename]
						);
						unlink($localFile);
					} catch (Exception $e) {
						alpn_log("FAILED UPLOADING TO GOOGLE TWITTER");
						alpn_log($e);
						http_response_code(200);
						exit; //TODO HANDLE
					}

							$userMentions[] = array(
								"id" => $twitterUserId,
								"screen_name" => $twitterUserScreenName
							);

							$usedIds = array("1551295064348905472", "1405291443023872000");
							$allNames = array();
							foreach ($userMentions as $key => $mention) {
								if (!in_array($mention['id'], $usedIds)) {
									$allNames[] = "@" . $mention['screen_name'];
									$usedIds[] = $mention['id'];
								}
							}

							$serviceMeta = array(
								"tweet_id" => $tweetId
							);

							$nftData = array(
								"service_meta" => json_encode($serviceMeta),
								"nft_name" => $tweetText,
								"nft_description" => $mediaDescription,
								"file_id" => $filename,
								"category" => $supportedEmojis[$firstEmoji]['name'],
								"mint_quantity" => count($allNames),
								"chain_id" => "polygon",
								"status" => "pending"
							);
							$wpdb->insert( 'alpn_nft_by_service', $nftData );
							$nftId = $wpdb->insert_id;

							$usedIds = array("1551295064348905472", "1405291443023872000");
							foreach ($userMentions as $key => $mention) {
								if (!in_array($mention['id'], $usedIds)) {
									$nftData = array(
										"nft_owned_id" => $nftId,
										"twitter_id" => $mention['id'],
										"twitter_screen_name" => $mention['screen_name']
									);
									$wpdb->insert( 'alpn_nft_by_service_owners', $nftData );    //TODO turn into single INSERT
									$usedIds[] = $mention['id'];
								}
							}

							$allNames = implode($allNames, ", ");
							$twitterBody = "
								Hey @{$twitterUserScreenName},\n
								Thanks for your patience while we review your submission\n
								Info: https://wiscle.com/nft-status-page?e2id={$nftId}
							";
							$parameters = [
								'status' => $twitterBody,
								'in_reply_to_status_id' => $tweetId
								];
						 $result = $connection->post('statuses/update', $parameters);

							$fileUrl = PTE_IMAGES_ROOT_URL . $filename;
							$mediaDescription = nl2br($mediaDescription);
							$emailBody = "
								<b>Collection:</b> {$supportedEmojis[$firstEmoji]['name']}<br><br>
								<b>NFT Name:</b> {$tweetText}<br><br>
								<b>NFT Description:</b><br><br>{$mediaDescription}<br><br>
								<b>From Twitter User:</b> {$twitterUserScreenName}<br><br>
								<b>Recipients:</b> {$allNames}<br><br>
								<a href='{$fileUrl}' target='_BLANK'><img src='{$fileUrl}'></a><br><br>
								<a href='https://wiscle.com/nft-status-page?e2id={$nftId}' target='_BLANK'>Status Page</a><br><br>
								<b>Submission Id:</b> [{$nftId}]
							";
							$template = 'vit_generic_email_template.php';
							$content = 	wc_get_template_html( $template, array(
									'email_heading' => $tweetText,
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

						 alpn_log("TWITTER CALLBACK DONE");

						 http_response_code(200);
						exit;

				} else { //missing data
					$twitterBody = "
						Hey @{$twitterUserScreenName},\n
						Your Tweet2NFT is missing required data. Please try again.\n
						Status/Info: https://wiscle.com/nft-status-page\n
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

if (false) {
	pp("GOTIME");
	$twitterData = $wpdb->get_results(
		$wpdb->prepare("SELECT twitter, twitter_meta from alpn_user_metadata WHERE id = %d ", $twitterAccountOwnerId)
	 );
	 if (!isset($twitterData[0]) || $twitterData[0]->twitter == '{}') {
			pp("Re-login to Twitter");
			exit;
	 }
	 $twitterMeta = json_decode($twitterData[0]->twitter_meta, true);
	 $twitterCreds = json_decode($twitterData[0]->twitter, true);

	 $accessToken = $twitterCreds['oauth_token'];
	 $accessTokenSecret = $twitterCreds['oauth_token_secret'];
	 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);


	 $certName = "certificate_test.png";
	 $filePath = PTE_ROOT_PATH . "tmp/{$certName}";
	 $media = $connection->upload('media/upload', ['media' => $filePath]);
	 $fileId = $media->media_id_string;

	 pp($media);

	 $recipientTwitterId = "56469651";

	 $message = "";

	 $data = [
	     'event' => [
	         'type' => 'message_create',
	         'message_create' => [
	             'target' => [
	                 'recipient_id' => $recipientTwitterId
	             ],
	             'message_data' => [
	                 'text' => $message,
									 'attachment.type' => 'media',
									 'attachment.media.id' => $fileId
	             ]
	         ]
	     ]
	 ];
	 //$result = $connection->post('direct_messages/events/new', $data, true);

	 pp($data);


	 // $content = $connection->post("direct_messages/events/new");
	 // pp($content);


	 // $content = $connection->get("account_activity/all/webhooks");
	 // pp($content);

	 // $content = $connection->delete("account_activity/all/DEV/subscriptions/1405291443023872000");
	 // pp($content);


	 // $content = $connection->delete("account_activity/all/DEV/webhooks/1549172051327348736");

	 // $url = "https://wiscle.com/wp-content/themes/memberlite-child-master/alpn_handle_twitter_callback.php";
	 // $content = $connection->post("account_activity/all/dev/webhooks", ["url" => $url]);
	 // $content = $connection->post("account_activity/all/dev/subscriptions");
	 // $content = $connection->get("account_activity/all/webhooks");



		//pp($content);
		pp("COOL");
		http_response_code(200);
	exit;
}






?>
