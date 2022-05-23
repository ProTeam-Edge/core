<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Abraham\TwitterOAuth\TwitterOAuth;

$html = "There was an error, please close this window and try again.";
if (isset($_REQUEST['oauth_token'])) {
$results = $wpdb->get_results(
	$wpdb->prepare("SELECT twitter, id FROM alpn_user_metadata WHERE json_extract(twitter, '$.oauth_token') = %s", $_REQUEST['oauth_token'])
 );
 if (isset($results[0])) {
	$ownerId = $results[0]->id;
	$request_token = json_decode($results[0]->twitter, true);
	$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
	$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
	if (isset($access_token['user_id'])) {
		$twitterScreenName = $access_token['screen_name'];
		$settingsData = array("twitter" => json_encode($access_token));
		$whereClause = array('id' => $ownerId);
		$wpdb->update( 'alpn_user_metadata', $settingsData, $whereClause );
		$message = "Hi @{$twitterScreenName}, you're all set!<br><br>Please close this browser window to complete your Wiscle Workflow.";
		$html = "<div style='border: 1px solid #D3D3D3; margin: 0 0 5px 0; padding: 100px 50px; background-color: rgb(250, 250, 250); min-height: 150px; text-align: center; font-family: Trebuchet, Arial, sans-serif; font-size: 24px;'>{$message}</div>";
		$data = array(
			"sync_type" => 'add_update_section',
			"sync_section" => 'twitter_oauth_success',
			"sync_user_id" => $ownerId,
			"sync_payload" => array("disconnection_string" => "Status: Connected as @<a class='wsc_external_links' href='https://twitter.com/{$twitterScreenName}' target='_blank'>{$twitterScreenName}</a> -- <a class='wsc_external_links' onclick='wsc_twitter_disconnect();'>Disconnect</a>")
		);
		pte_manage_user_sync($data);
	}
}
}

echo $html;

?>
