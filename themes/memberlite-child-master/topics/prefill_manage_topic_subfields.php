<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
global $wpdb;
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
$passed = 0;
$nonce  = $_POST["security"];
$verify = wp_verify_nonce($nonce, 'admin_test' );
if($verify==1) {
	$passed = 1;
}
if($passed==0) {
	echo 'Not a valid request.';
	die;
}

$array = array();
$topic_name = $_POST['topic_name'];
$sql = 'select child_fields from alpn_manage_topic where topic_name="'.$topic_name.'"';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array = $vals->child_fields;
}
echo json_encode($array, JSON_UNESCAPED_SLASHES);
?>