<?php
  // Get data from POST
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


  // Save list of linked topics to server
  file_put_contents('generatedTopics/'.$topicFriendlyName.'_main.json', json_encode($post_data));
  //file_put_contents('generatedTopics/tester.txt',$topicName);
  echo 'yay';
?>
