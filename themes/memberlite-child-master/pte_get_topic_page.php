<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$data = $_POST;
$verify = 0;
if(isset($data['security']) && !empty($data['security']))
	$verify = wp_verify_nonce( $data['security'], 'alpn_script' );
if($verify==1) {
$pageNumber = pte_get_page_number($data);
pte_json_out(array("page_number" => $pageNumber));
}else
{
	$html = 'Not a valid request.';
	echo $html;
	die;
}
?>
