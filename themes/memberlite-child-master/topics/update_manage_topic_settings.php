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
$field_type = $_POST['field_type'];
$topic_name = $_POST['topic_name'];
$value = $_POST['value'];
$sql = 'select * from alpn_manage_topic where topic_name= "'.$topic_name.'"';
$data = $wpdb->get_row($sql);
if(empty($data)) {
	$core_topic = 0;
	$hide_properties = 0;
	$friendly_name = '';
	$child_fields = '';
	$alpn_about_source = '';
	$alpn_name_source = '';
	$visibility = 'topic';
}
else {
	$core_topic = $data->core_topic;
	$hide_properties = $data->hide_properties;
	$friendly_name = $data->friendly_name;
	$child_fields = $data->child_fields;
	$visibility =  $data->visibility;
	$alpn_about_source =  $data->alpn_about_source;
	$alpn_name_source =  $data->alpn_name_source;
}

if($field_type=='linked_topic') {
	$core_topic = $value ;
}
if($field_type=='hidden_topic') {
	$hide_properties = $value ;
}
if($field_type=='friendly_name') {
	$friendly_name = $value ;
}
if($field_type=='visibility') {
	$visibility = $value ;
}
if($field_type=='child_fields') {
	$child_fields = $value ;
}
if($field_type=='alpn_about_source') {
	$alpn_about_source = $value ;
}
if($field_type=='alpn_name_source') {
	$alpn_name_source = $value ;
}


if(empty($data)) {
	$sql = "insert into alpn_manage_topic(topic_name,core_topic,hide_properties,friendly_name,visibility,child_fields,alpn_about_source,alpn_name_source,cdate,mdate)values('".$topic_name."','".$core_topic."','".$hide_properties."','".$friendly_name."','".$visibility."','".$child_fields."','".$alpn_about_source."','".$alpn_name_source."','".time()."','".time()."')";
	
}
else {
	$sql = "update alpn_manage_topic set core_topic='".$core_topic."',hide_properties='".$hide_properties."',friendly_name='".$friendly_name."',visibility='".$visibility."',mdate='".time()."' , child_fields='".$child_fields."' ,alpn_about_source='".$alpn_about_source."' ,alpn_name_source='".$alpn_name_source."' where topic_name='".$topic_name."'";
}
$data = $wpdb->query($sql);
?>
