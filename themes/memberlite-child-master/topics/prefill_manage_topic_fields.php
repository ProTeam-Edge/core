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
$type = $_POST['type'];
$array = array();
$sql = 'select * from alpn_manage_topic where core_topic=1';
$data = $wpdb->get_results($sql);
foreach($data as $vals)
{
	$array['linked_topic'] = 'linked_topic_'.$vals->topic_name;
}
$sql = 'select * from alpn_manage_topic where hide_properties=1';
$data = $wpdb->get_results($sql);
foreach($data as $vals)
{
	$array['hidden_topic'] = 'hidden_topic_'.$vals->topic_name;
}
echo json_encode($array);
?>