<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Abraham\TwitterOAuth\TwitterOAuth;

if (!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if (!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}
alpn_log("DISCONNECTING FROM TWITTER");

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;

$connectToTwitter = "error";

if ($ownerId) {

	$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_CALLBACK));

	if (isset($request_token['oauth_token'])) {

		$userMeta = array('twitter' => json_encode($request_token));
		$whereClause = array('id' => $ownerId);
		$wpdb->update( 'alpn_user_metadata', $userMeta, $whereClause );

		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
		$connectToTwitter = "Status: Disconnected -- <a class='wsc_external_links' onclick='window.open(`{$url}`, `_blank`, `top=100,left=500,width=640,height=480`)'>Connect to Twitter</a>";

	} else {
		$settingsData = array("twitter" => NULL);
		$whereClause = array("id" => $ownerId);
		$wpdb->update( 'alpn_user_metadata', $settingsData, $whereClause );
	}

}
pte_json_out(array("connection_string" => $connectToTwitter));

?>
