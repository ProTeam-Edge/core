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

if ($userId ) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT twitter from alpn_user_metadata WHERE id = %d ", $userId)
	 );

	 if (isset($results[0])) {

		 $accessTokenData = json_decode($results[0]->twitter, true);
		 $accessToken = $accessTokenData['oauth_token'];
		 $accessTokenSecret = $accessTokenData['oauth_token_secret'];

		 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);

		 $tempFileName = "123_456.jpeg";

		 $twitterImagePath =  PTE_ROOT_PATH . "tmp/" . $tempFileName;

		 // $bannerData = base64_encode(file_get_contents($twitterImagePath));
		 // $updateProfileResult = $connection->post("account/update_profile_banner", ["banner" => $bannerData]);

		 $imageData = base64_encode(file_get_contents($twitterImagePath));
		 $updateProfileResult = $connection->post("account/update_profile_image", ["image" => $imageData]);


	 }

}

$qVars = $_POST;

pte_json_out(array("qvars" => $qVars, "results" => $updateProfileResult));

?>
