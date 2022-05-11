<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Abraham\TwitterOAuth\TwitterOAuth;

$siteUrl = get_site_url();
$userId = get_current_user_id();

if (!is_user_logged_in() ) {
	pp("Not a valid request");
	die;
}
if (!check_ajax_referer('alpn_script', 'security', FALSE)) {
	 pp("REFERRER TODO");
   //die;
}

$qVars = $_POST;
// $nftId = isset($qVars['nft_id']) && $qVars['nft_id'] ? $qVars['nft_id'] : false;
// $setName = isset($qVars['set_name']) && $qVars['set_name'] ? $qVars['set_name'] : false;

pp("STARTING");

$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);

if (isset($connection)) {

	try {
		$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_CALLBACK));
		 //STORE these. Check validitity. Don't repeat login. Show logout link.
		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

		$html = "<a class='wsc_authorize_link' onclick='window.open(`{$url}`, ``, `left=480,top=100,width=640,height=480`)'>Authorize Wiscle to do Stuff</a>";

		 pp("DONE");


	} catch (Exception $error) {


		pp("TWITTER ACTIONS PROBLEMO");


	}
} else {

}


echo $html;

?>
