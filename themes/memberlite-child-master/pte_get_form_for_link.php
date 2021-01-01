<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html="";
$pVars = $_POST;
$verify = 0;
if(isset($pVars['security']) && !empty($pVars['security']))
	$verify = wp_verify_nonce( $pVars['security'], 'alpn_script' );

if($verify==1) {
$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($domId) {
	$html = pte_get_linked_form($domId);

}
//pte_json_out($topicMeta);
}
else {
	$html = 'Not a valid request please hard refresh and try again.';
}
echo $html;


?>
