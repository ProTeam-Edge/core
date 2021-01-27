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

  $post_data = json_decode(stripslashes($_POST['data']));

  // $temp = array();
  // $temp["LinkedTopics"] = $data;
  // $temp["This or that"] = "hey o";
  // array_push($output, $temp);

  // Save list of linked topics to server
  $root = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/memberlite-child-master/topics/';
  file_put_contents(''.$root.'/hiddenTopicConfig.json', json_encode($post_data));
  echo 'Yay';
?>
