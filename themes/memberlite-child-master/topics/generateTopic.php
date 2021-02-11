<?php
  // Get data from POST
include('/var/www/html/proteamedge/public/wp-blog-header.php');
global $wpdb;
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
$passed = 0;
$nonce  = $_POST["security"];
$verify = wp_verify_nonce($nonce, 'admin_test' );
if($verify==1) 
{
	$passed = 1;
}
if($passed==0)
{
	echo 'Not a valid request.';
	die;
}
  //$post_data = json_decode($_POST['data']);
  $post_data = json_decode(stripslashes($_POST['data']));
	$get_option = stripslashes(get_option('save_extra_fields'));
 $decode_db_string = json_decode($get_option);

  //$topicName = gettype($post_data);
$topicName  =  $post_data->topic_name;
$alpn_sql = 'select alpn_about_source,alpn_name_source from alpn_manage_topic where topic_name = "'.$topicName.'"';
$alpn_data = $wpdb->get_row($alpn_sql);


  // Make sure POST data is stored as an array; typecast it as such
  $post = (array) $post_data;
  $topicFriendlyName = $post["topic_friendly_name"];
  $field_map = $post['field_map'];

  $end = end($field_map);
  $last_id = $end->id;
	
  	$addition_array = array();
	$i = $last_id+1;

foreach($decode_db_string as $keys=>$vals)
{
	$addition_array[$keys] = $vals;
	$addition_array[$keys]->id=$i;
	$i++;
}

/* 	$addition_array['pte_added_date']['id']=$last_id+1;
	$addition_array['pte_added_date']['friendly']="Added Date";
	$addition_array['pte_added_date']['type']="Date";
	$addition_array['pte_added_date']['name']="pte_added_date";
	$addition_array['pte_added_date']['required']="false";
	$addition_array['pte_added_date']['schema_key']="pte_added_Date";
	$addition_array['pte_added_date']['hidden']="false";
	$addition_array['pte_added_date']['hidden_print']="true";

	$addition_array['pte_modified_date']['id']=$last_id+2;
	$addition_array['pte_modified_date']['friendly']="Modified Date";
	$addition_array['pte_modified_date']['type']="Date";
	$addition_array['pte_modified_date']['name']="pte_modified_date";
	$addition_array['pte_modified_date']['required']="false";
	$addition_array['pte_modified_date']['schema_key']="pte_modified_Date";
	$addition_array['pte_modified_date']['hidden']="false";
	$addition_array['pte_modified_date']['hidden_print']="true";

	$addition_array['pte_image_logo']['id']=$last_id+3;
	$addition_array['pte_image_logo']['friendly']="Image/Logo";
	$addition_array['pte_image_logo']['type']="image";
	$addition_array['pte_image_logo']['name']="pte_image_logo";
	$addition_array['pte_image_logo']['required']="false";
	$addition_array['pte_image_logo']['schema_key']="pte_image_URL";
	$addition_array['pte_image_logo']['hidden']="false";
	$addition_array['pte_image_logo']['hidden_print']="true"; */
	$obj = json_decode (json_encode ($addition_array), FALSE);
	$obj_merged = (object) array_merge((array) $field_map, (array) $obj);
	$final_array = array();
if(isset($alpn_data->alpn_about_source) && !empty($alpn_data->alpn_about_source))
{
	$about_source = $alpn_data->alpn_about_source;
	$alpn_about_source_obj = json_decode($about_source,true);
	$alpn_about_source_obj_final = json_decode (json_encode ($alpn_about_source_obj), FALSE);
	$final_array['alpn_about_source'] =  (object) $alpn_about_source_obj_final;
}
if(isset($alpn_data->alpn_name_source) && !empty($alpn_data->alpn_name_source))
{
	$name_source = $alpn_data->alpn_name_source;
	$alpn_name_source_obj = json_decode($name_source,true);
	$alpn_name_source_obj_final = json_decode (json_encode ($alpn_name_source_obj), FALSE);
	$final_array['alpn_name_source'] = (object) $alpn_name_source_obj_final;
}
foreach($post as $keys=>$vals)
{
  $final_array[$keys] = $post[$keys];

}  

$final_array['field_map'] = $obj_merged;
	
/* $final_array = array();
  foreach($post as $vals)
  {
	  $final_array['topic_name'] = $vals['topic_name'];
	  $final_array['topic_friendly_name'] = $vals['topic_friendly_name'];
	  $final_array['topic_class'] = $vals['topic_class'];
	 
  }  */

$root = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/memberlite-child-master/topics/generatedTopics/';
  // Save list of linked topics to server
 // file_put_contents('generatedTopics/'.$topicFriendlyName.'_main.json', json_encode($post_data));
  file_put_contents($root.$topicFriendlyName.'_main.json', json_encode($final_array));
  //file_put_contents('generatedTopics/tester.txt',$topicName);
  echo json_encode($final_array);
?>
