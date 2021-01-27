<?php
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
  // Get data from POST
  //$post_data = json_decode($_POST['data']);
  $post_data = json_decode(stripslashes($_POST['data']));

  // Make sure POST data is stored as an array; typecast it as such
  $post = (array) $post_data;
  $topicName = $post["topic_name"];

  // Save list of linked topics to server
  file_put_contents('topicConfig/'.$topicName.'_config.json', json_encode($post_data));
  //file_put_contents('generatedTopics/tester.txt',$topicName);
  echo 'yay';
?>
