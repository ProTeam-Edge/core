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
	$array['linked_topic'][] = 'linked_topic_'.$vals->topic_name;
}
$sql = 'select * from alpn_manage_topic where hide_properties=1';
$data = $wpdb->get_results($sql);
foreach($data as $vals)
{
	$array['hidden_topic'][] = 'hidden_topic_'.$vals->topic_name;
}
$sql = 'select * from alpn_manage_topic where friendly_name!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['friendly_name'][$keys]['topic_name'] = $vals->topic_name;
	$array['friendly_name'][$keys]['friendly_name'] = $vals->friendly_name;
}
$sql = 'select * from alpn_manage_topic where visibility!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['topic_class'][$keys]['topic_name'] = 'topic_class_'.$vals->topic_name.'';
	$array['topic_class'][$keys]['visibility_value'] = $vals->visibility;
}
$sql = 'select * from alpn_manage_topic where child_fields!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['child_fields'][$keys][$vals->topic_name] = $vals;
}
echo json_encode($array);
?>