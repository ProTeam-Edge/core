<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
echo '<pre>';
echo '<h3>Converted blob to form file instance now ready to be uploaded</h3>';
print_r($_POST);
print_r($_FILES);