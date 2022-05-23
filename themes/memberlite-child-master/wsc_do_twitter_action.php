<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
use Abraham\TwitterOAuth\TwitterOAuth;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
alpn_log("SENDING TO TWITTER");

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;

$qVars = $_POST;
$uniqueId = isset($qVars['unique_id']) ? $qVars['unique_id'] : false;
$twitterActionSlug = isset($qVars['twitter_action_slug']) ? $qVars['twitter_action_slug'] : false;
$twitterTextData = isset($qVars['twitter_text_data']) ? stripslashes($qVars['twitter_text_data']) : "";

if ($userId && $uniqueId && $twitterActionSlug) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT twitter from alpn_user_metadata WHERE id = %d ", $userId)
	 );

	 if (isset($results[0])) {

		 $accessTokenData = json_decode($results[0]->twitter, true);
		 $accessToken = $accessTokenData['oauth_token'];
		 $accessTokenSecret = $accessTokenData['oauth_token_secret'];

		 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);

		 switch ($twitterActionSlug) {
         case "tweet":
				   $fileIds = array();
					 $imageOneUrl = WSC_PREVIEWS_PATH . "{$uniqueId}_0.webp";
	         $imageTwoUrl = WSC_PREVIEWS_PATH . "{$uniqueId}_1.webp";
	         $imageThreeUrl = WSC_PREVIEWS_PATH . "{$uniqueId}_2.webp";
	         $imageFourUrl = WSC_PREVIEWS_PATH  . "{$uniqueId}_3.webp";
					 if (filesize($imageOneUrl)) {
						 $media1 = $connection->upload('media/upload', ['media' => $imageOneUrl]);
						 $fileIds[] = $media1->media_id_string;
					 }
					 if (filesize($imageTwoUrl)) {
						 $media2 = $connection->upload('media/upload', ['media' => $imageTwoUrl]);
						 $fileIds[] = $media2->media_id_string;
					 }
					 if (filesize($imageThreeUrl)) {
						 $media3 = $connection->upload('media/upload', ['media' => $imageThreeUrl]);
						 $fileIds[] = $media3->media_id_string;
					 }
					 if (filesize($imageFourUrl)) {
						 $media4 = $connection->upload('media/upload', ['media' => $imageFourUrl]);
						 $fileIds[] = $media4->media_id_string;
					 }
					$parameters = [
				    'status' => $twitterTextData,
				    'media_ids' => implode(',', $fileIds)
					];
					$result = $connection->post('statuses/update', $parameters);
         break;
         case "pfp":
				 	 $twitterImagePath = WSC_PREVIEWS_PATH . $uniqueId . ".jpeg";
					 $imageData = base64_encode(file_get_contents($twitterImagePath));
					 $result = $connection->post("account/update_profile_image", ["image" => $imageData]);
         break;
         default:   //banner
  				 $twitterImagePath = WSC_PREVIEWS_PATH . $uniqueId . ".webp";
				   $bannerData = base64_encode(file_get_contents($twitterImagePath));
				   $result = $connection->post("account/update_profile_banner", ["banner" => $bannerData]);
     }
   }
	}

$qVars = $_POST;

pte_json_out(array("qvars" => $qVars, "results" => $result));

?>
