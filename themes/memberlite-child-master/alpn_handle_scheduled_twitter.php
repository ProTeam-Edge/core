<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("TWITTER SCHEDULER");

use PascalDeVink\ShortUuid\ShortUuid;
use Ramsey\Uuid\Uuid;
use Google\Cloud\Storage\StorageClient;
use Abraham\TwitterOAuth\TwitterOAuth;

$twitterAccountOwnerId = 164;
$reloadFollowers = false;

$targetOverrideId = "1402274770658471936";


$allHeaders = getallheaders();
// $isSecure = isset($allHeaders['MORALIS_EXTRA_SECURITY']) && $allHeaders['MORALIS_EXTRA_SECURITY'] == MORALIS_EXTRA_SECURITY && isset($allHeaders['User-Agent']) && $allHeaders['User-Agent'] == "Google-Cloud-Scheduler" ? true : false;

if (!$isSecure) {
	alpn_log("INSECURE");
	exit;
}

	$data = array(
		"messages" => array(
			"Hey! @afangonthemove and I would be incredibly thrilled if you would try out our new Tweet2NFT Beta (< 60 seconds) and could amplify it with a retweet. Thank you!",
			"Would you mind trying out our new Tweet2NFT Beta (< 60 seconds) and amplify it with a retweet? Thank you!",
			"Our new Tweet2NFT Beta is ready! @afangonthemove and I would really appreciate it if you would try it (< 60 seconds) and retweet!",
			"Could you help us expand web3 by trying our new Tweet2NFT Beta? it's fun, safe and helps us get the word out. Thank you!",
			"Can you help @afangonthemove and me grow web3 to more people? Our new Tweet2NFT Beta is ready and we'd be thrilled if your tried it (<60 seconds) and retweeted!",
			"We're on our way and could really use your help trying out our new Tweet2NFT Beta. Mind taking 60 seconds and trying it out and retweeting? Thank you!",
			"We're growing web3 in safe and fun ways. Starting with our new Tweet2NFT Beta. 60 seconds and a retweet helps us with our mission. Thanks!",
			"Imagine a safe and fun web3 where everyone is welcome. Tweet2NFT Beta is the beginning. Would you kindly take less than 60 seconds to try it and retweet? Thank you!"
		),
		"link" => "https://twitter.com/wiscletweet2nft/status/1552360861150240768",
	  "description" => "For helping us build a larger, more diverse, equal and inclusive web3 by trying out Tweet2NFT BETA. It lets anyone mint Magic Moment Forever NFTs on 0xPolygon and gift them to friends and loved-ones using only Twitter. Safe, easy and free. It takes about a minute and helps us get the word out. No wallet, currency or web3 experience necessary. Thank you!",
	  "template_file" => "wiscle_certificate_of_appreciation_template.png",
	  "x" => 1460,
	  "y" => 184,
	  "w" => 275,
	  "h" => 275
	);

	 $twitterData = $wpdb->get_results(
		$wpdb->prepare("SELECT twitter, twitter_meta, JSON_VALUE(twitter_followers, '$[0]') AS target_id from alpn_user_metadata WHERE id = %d ", $twitterAccountOwnerId)
	 );

	 if (!isset($twitterData[0]) || $twitterData[0]->twitter == '{}') {
			alpn_log("Re-login to Twitter");
			exit;
	 }

	 if (!$twitterData[0]->target_id) {  //No More. Log, notify, exit
		 alpn_log("NO MORE FOLLOWERS -- RELOAD");
		 http_response_code(200);
		 exit;
	 }

	 if ($targetOverrideId) {
		 	$targetId = $targetOverrideId;
	 } else {
		  $query = "UPDATE alpn_user_metadata SET twitter_followers = JSON_REMOVE(twitter_followers, '$[0]') WHERE id = %d";
		  $sql = $wpdb->prepare($query, $twitterAccountOwnerId);
		  $wpdb->query($sql);
		  $targetId = $twitterData[0]->target_id;
	 }

	 if ($targetId) {

		 $twitterMeta = json_decode($twitterData[0]->twitter_meta, true);
		 $twitterCreds = json_decode($twitterData[0]->twitter, true);

		 $accessToken = $twitterCreds['oauth_token'];
		 $accessTokenSecret = $twitterCreds['oauth_token_secret'];
		 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);

		 if ($reloadFollowers) {
			 $cursor = -1;
			 while ($cursor != 0) {   //TODO MOdify after 5000 since this won't work
			     $ids = $connection->get("followers/ids", array("screen_name" => "pvermont", "cursor" => $cursor));
					 $cursor = $ids->next_cursor;
			 }
			 $allIds = $ids->ids;
			 $userMetadata = array(
			    'twitter_followers' => json_encode($allIds)
			 );
			 $whereClause = array(
				  'id' => $twitterAccountOwnerId
			 );
			 $wpdb->update( 'alpn_user_metadata', $userMetadata, $whereClause );
		 }

		 $userInfo = $connection->get("users/lookup", ["user_id" => $targetId]);


		 pp($userInfo);
		 exit;

		 if (isset($userInfo[0])) {

			$userData = $userInfo[0];
			$userScreenName = $userData->screen_name;
			$data['pfp_url'] = $userData->profile_image_url_https;
			$data['screen_name'] = $userScreenName;
			$certificateFileId = wsc_create_dm_certificate($data);

			$filePath =  PTE_ROOT_PATH . "tmp/{$certificateFileId}";
			$media = $connection->upload('media/upload', ['media' => $filePath]);
			$fileId = $media->media_id;
			unlink($filePath);

			$messageKey = array_rand($data['messages']);
			$message = $data['messages'][$messageKey] . " " . $data['link'];

			$data = [
					'event' => [
							'type' => 'message_create',
							'message_create' => [
									'target' => [
											'recipient_id' => $targetId
									],
									'message_data' => [
											'text' => $message,
											'attachment' => [
												 'type' => 'media',
												 'media' => [
													 'id' => $fileId
												 ]
											]
									]
							]
					]
			];
		  $result = $connection->post('direct_messages/events/new', $data, true);
			alpn_log($result);
			alpn_log("DONE TWITTER DM");

	 } else {
		 alpn_log("UPDATING TWITTER FOLLOWERS FAIL");
		 alpn_log($wpdb->last_query);
		 alpn_log($wpdb->last_error);
	 }
	}

?>
