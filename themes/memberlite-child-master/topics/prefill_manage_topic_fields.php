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
if(!empty($data))
{
	foreach($data as $vals)
	{
		$array['linked_topic'][] = 'linked_topic_'.$vals->topic_name;
	}
}
else
{
	$array['linked_topic']= '';
}
$sql1 = 'select * from alpn_manage_topic where hide_properties=1';
$data1 = $wpdb->get_results($sql1);
if(!empty($data1))
{
	foreach($data1 as $vals1)
	{
		$array['hidden_topic'][] = 'hidden_topic_'.$vals1->topic_name;
	}
}
else
{
	$array['hidden_topic'] = '';
}
$sql = 'select * from alpn_manage_topic where friendly_name!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['friendly_name'][$keys]['topic_name'] = $vals->topic_name;
	$array['friendly_name'][$keys]['friendly_name'] = $vals->friendly_name;
}
$sql = 'select * from alpn_manage_topic where alpn_about_source!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['alpn_about_source'][$keys]['topic_name'] = $vals->topic_name;
	$array['alpn_about_source'][$keys]['alpn_about_source'] = $vals->alpn_about_source;
}
$sql = 'select * from alpn_manage_topic where alpn_name_source!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['alpn_name_source'][$keys]['topic_name'] = $vals->topic_name;
	$array['alpn_name_source'][$keys]['alpn_name_source'] = $vals->alpn_name_source;
}
$sql = 'select * from alpn_manage_topic where visibility!=""';
$data = $wpdb->get_results($sql);
foreach($data as $keys=>$vals)
{
	$array['topic_class'][$keys]['topic_name'] = 'topic_class_'.$vals->topic_name.'';
	$array['topic_class'][$keys]['visibility_value'] = $vals->visibility;
}

echo json_encode($array);
?>