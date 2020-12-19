<?php
  // Get data from POST
  $post_data = json_decode(stripslashes($_POST['data']));

  // $temp = array();
  // $temp["LinkedTopics"] = $data;
  // $temp["This or that"] = "hey o";
  // array_push($output, $temp);

  // Save list of linked topics to server
  file_put_contents('linkedTopicConfig.json', json_encode($post_data));
?>
