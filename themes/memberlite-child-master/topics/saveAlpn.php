<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
global $wpdb;
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
$passed = 0;
$nonce  = $_POST["security"];
$save_alpn  = $_POST["save_alpn"];
$textclass  = $_POST["textclass"];
$verify = wp_verify_nonce($nonce, 'form-generate' );
if($verify==1) {
	$passed = 1;
}
if($passed==0) {
	echo 'Not a valid request.';
	die;
}
echo $update = update_option($textclass,$save_alpn);
?>
