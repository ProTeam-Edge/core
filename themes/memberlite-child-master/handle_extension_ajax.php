<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
echo get_template_directory();
$passed = 0;
$nonce  = $_POST["security"];
$verify = wp_verify_nonce($nonce, 'handle_extension' );
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
echo '<h3>Converted blob to form file instance ready for uploading</h3>';
print_r($_POST);
print_r($_FILES);