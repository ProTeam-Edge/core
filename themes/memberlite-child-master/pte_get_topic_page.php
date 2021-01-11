<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$data = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}

$pageNumber = pte_get_page_number($data);
pte_json_out(array("page_number" => $pageNumber));

?>
