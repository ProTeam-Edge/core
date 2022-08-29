<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Abraham\TwitterOAuth\TwitterOAuth;

$html = "There was an error, please close this window and try again.";
if (isset($_REQUEST['oauth_token'])) {

	$html = "Worked";

	$request_token = vit_get_kvp($_REQUEST['oauth_token']);


	alpn_log($request_token);


	$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
	$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);


	alpn_log($access_token);


	// if (isset($access_token['user_id'])) {
	// 	$data = array(
	// 		"sync_type" => 'add_update_section',
	// 		"sync_section" => 'twitter_oauth_success',
	// 		"sync_user_id" => $ownerId,
	// 		"sync_payload" => array("disconnection_string" => "Status: Connected as @<a class='wsc_external_links' href='https://twitter.com/{$twitterScreenName}' target='_blank'>{$twitterScreenName}</a> -- <a class='wsc_external_links' onclick='wsc_twitter_disconnect();'>Disconnect</a>")
	// 	);
	// 	pte_manage_user_sync($data);
	// }

}

?>
