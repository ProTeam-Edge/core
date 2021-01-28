<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
$passed = 0;
$nonce  = $_POST["security"];
$verify = wp_verify_nonce($nonce, 'admin_test' );
if($verify==1) 
{
	$passed = 1;
}
if($passed==0)
{
	echo 'Not a valid request.';
	die;
}
 echo '<pre>';
 print_r($_POST);
 DIE;
?>
