<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html="";
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$requestData = array();

$pVars = $_POST;

$linkId = isset($pVars['link_id']) ? $pVars['link_id'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($linkId) {
	$requestData = array(
		'owner_id' => $userID,
		'link_id' => $linkId
	);
	pte_manage_topic_link('delete_topic_bidirectional_link', $requestData);
}
pte_json_out($requestData);

?>
