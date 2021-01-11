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
$pVars = $_POST;

$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($domId) {
	$html = pte_get_linked_form($domId);

}
//pte_json_out($topicMeta);


echo $html;


?>
