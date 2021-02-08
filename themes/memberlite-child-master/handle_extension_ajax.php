<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
$root = $_SERVER['DOCUMENT_ROOT'];
$site_url = site_url();
$child_theme_path = $root.'/wp-content/themes/memberlite-child-master/attachments/'; 
$passed = 0;
$nonce  = $_POST["security"];
$name  = $_POST["name"];
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
/* print_r($_POST);
print_r($_FILES); */

if (move_uploaded_file($_FILES['file']['tmp_name'], $child_theme_path.$name)) {
    echo "<h3>File Uploaded Successfully following is url.</h3>";
    echo "<h3>".$site_url."/wp-content/themes/memberlite-child-master/attachments/".$name."</h3>";
}
else
	echo "<h3>There was some issue please try again later.</h3>";