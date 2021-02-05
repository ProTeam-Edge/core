<?php
  // Get data from POST
include('/var/www/html/proteamedge/public/wp-blog-header.php');
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

  //$topicName = $post_data["topic_name"];
  //$topicName = gettype($post_data);

  // Make sure POST data is stored as an array; typecast it as such
  $post = (array) $post_data;
  $topicFriendlyName = $post["topic_friendly_name"];
  $field_map = $post['field_map'];

  $end = end($field_map);
  $last_id = $end->id;
	
  	$addition_array = array();
	$addition_array['pte_modified_date']['id']=$last_id+1;
	$addition_array['pte_modified_date']['friendly']="Added Date";
	$addition_array['pte_modified_date']['type']="Date";
	$addition_array['pte_modified_date']['name']="pte_added_date";
	$addition_array['pte_modified_date']['required']="false";
	$addition_array['pte_modified_date']['schema_key']="pte_added_Date";
	$addition_array['pte_modified_date']['hidden']="false";
	$addition_array['pte_modified_date']['hidden_print']="true";
	
	$addition_array['pte_added_date']['id']=$last_id+2;
	$addition_array['pte_added_date']['friendly']="Modified Date";
	$addition_array['pte_added_date']['type']="Date";
	$addition_array['pte_added_date']['name']="pte_modified_date";
	$addition_array['pte_added_date']['required']="false";
	$addition_array['pte_added_date']['schema_key']="pte_modified_Date";
	$addition_array['pte_added_date']['hidden']="false";
	$addition_array['pte_added_date']['hidden_print']="true";
	
	$addition_array['pte_image_logo']['id']=$last_id+3;
	$addition_array['pte_image_logo']['friendly']="Image/Logo";
	$addition_array['pte_image_logo']['type']="image";
	$addition_array['pte_image_logo']['name']="pte_image_logo";
	$addition_array['pte_image_logo']['required']="false";
	$addition_array['pte_image_logo']['schema_key']="pte_image_URL";
	$addition_array['pte_image_logo']['hidden']="false";
	$addition_array['pte_image_logo']['hidden_print']="true";
	$obj = json_decode (json_encode ($addition_array), FALSE);

	$obj_merged = (object) array_merge((array) $field_map, (array) $addition_array);


	
$final_array = array();
  foreach($post as $vals)
  {
	  $final_array['topic_name'] = $vals['topic_name'];
	  $final_array['topic_friendly_name'] = $vals['topic_friendly_name'];
	  $final_array['topic_class'] = $vals['topic_class'];
	  $final_array['field_map'] = $obj_merged;
  } 
echo '<pre>';
print_r($final_array);

$root = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/memberlite-child-master/topics/generatedTopics/';
  // Save list of linked topics to server
 // file_put_contents('generatedTopics/'.$topicFriendlyName.'_main.json', json_encode($post_data));
  file_put_contents($root.$topicFriendlyName.'_main.json', json_encode($post_data));
  //file_put_contents('generatedTopics/tester.txt',$topicName);
  echo 'yay';
?>
