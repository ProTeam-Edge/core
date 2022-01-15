<?php
include('../../../wp-blog-header.php');

alpn_log("Handle Chat Preview");

global $wpdb;

//TODO Check logged in, etc. Good Request. User-ID in all mysql
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}

if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$qVars = $_POST;

$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : false;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($topicId) {

//handle the blob file upload -- move-uploaded fiel, etc.
//upload the image to google external storage. TODO How to delete these when no longer needed in the message.
// store in message attributes.
// return so they can replace temp image on client temp
// show temp to be replaced




}

pte_json_out(array(
	"imageUrl" => $imageUrl
));
?>
