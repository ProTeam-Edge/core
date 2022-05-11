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
alpn_log("Getting New Twitter Preview");

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;

$previewHtml = "<div>Error</div>";

$qVars = $_POST;
$twitterActionSlug = isset($qVars['twitter_action_slug']) ? $qVars['twitter_action_slug'] : false;
$nftSetSlug = isset($qVars['nft_set_slug']) ? $qVars['nft_set_slug'] : false;
$uniqueId = isset($qVars['unique_id']) ? $qVars['unique_id'] : false;
$textData = isset($qVars['text_data']) ? $qVars['text_data'] : "";

if ($userId && $twitterActionSlug && $nftSetSlug && $uniqueId) {
	$hasWordsArray = array("tweet", "2xwords", "8xwords", "18xwords", "32xwords");  //move somewhere and stop repeating
	if (!in_array($twitterActionSlug, $hasWordsArray)) {
		$textData = "";
	}
	$previewHtml = wsc_get_twitter_preview_art($nftSetSlug, $twitterActionSlug, $uniqueId, $textData);
}
echo $previewHtml;
?>
