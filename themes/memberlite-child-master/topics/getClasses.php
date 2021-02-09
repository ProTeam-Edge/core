<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$schema_master_file = WP_CONTENT_DIR . "/plugins/proteamedge/schema.jsonId";
if ($json = file_get_contents($schema_master_file)) {
    error_log("Got contents successfully.", 0);
    //echo "Got contents successfully.";
}
else {
    error_log("JSON Get Contents Failed.", 0);
    //echo "JSON Get Contents Failed.";
}
if ($data = json_decode($json, true)) {
    error_log("JSON Decode Successful.", 0);
    //echo "JSON Decode Successful.";
}
else {
    error_log("JSON Decode Failed.", 0);
    //echo "JSON Decode Failed.";
}

$classes = array();

// THIS WILL SHOW ALL CONTENTS OF THE JSON FILE
foreach ($data["@graph"] as $value) {

  // Check if object has type "rdfs:Class" and push onto array
  if ($value["@type"] == "rdfs:Class") {
    $temp = array();
    $temp["TopicName"] = substr($value["@id"], 18);
    $temp["Comment"] = $value["rdfs:comment"];
    $temp["LinkedTopic"] = "<input type='checkbox' class='linked_topic_checkbox' id='linked_topic_" . substr($value["@id"], 18) . "'><br/><br/><br/><input type='checkbox' class='hidden_topic_checkbox' id='hidden_topic_" . substr($value["@id"], 18) . "'>";
    $temp["HiddenTopic"] = "&nbsp;";
    $temp["extra_fields"] = "<textarea id='alpn_about_source_".substr($value["@id"], 18)."' placeholder='alpn_about_source' class='all_area' data='alpn_about_source' rel='".substr($value["@id"], 18)."'></textarea></br><textarea  id='alpn_name_source_".substr($value["@id"], 18)."'   placeholder='alpn_name_source' class='all_area' data='alpn_name_source' rel='".substr($value["@id"], 18)."'></textarea>";
    $temp["topic_class"] = "<input type='text' class='friendly_name'>
	<br/><br/><br/><select class='topic_class' id='topic_class_" . substr($value["@id"], 18) . "'><option value='topic' selected>Topic Type</option><option value='link'>Topic Link Type</option><option value='record'>Topic Link Record Type</option></select>";
    $temp["Generate"] = "<button class='disabled' id='add_topic_" . substr($value["@id"], 18) . "'>Generate Topic</button>";
    $temp["SaveConfig"] = "<button class='saveTopicConfig' id='save_topic_" . substr($value["@id"], 18) . "'>Save<br>Prop. Config</button>";
    array_push($classes, $temp);
  }

  // echo "Objects: " . count($value) . "; ";
  // foreach ($value as $key => $values) {
  //   // If "values" is an array, unpack it; otherwise, print the string
  //   if (gettype($values) == "array") {
  //     if (count($values) <= 1) {
  //       echo "Key: " . $key . ", Value: " . $values["@id"] . "<br>";
  //     }
  //     else {
  //       echo "Key: " . $key . ", Values: ";
  //       // If key is a category, don't use @id
  //       if ($key == "http://schema.org/category") {
  //         foreach ($values as $values_key => $values_item) {
  //           echo $values_item . ", ";
  //         }
  //       }
  //       else {
  //         foreach ($values as $values_key => $values_item) {
  //           echo $values_item["@id"] . ", ";
  //         }
  //       }
  //       echo "<br>";
  //     }
  //   }
  //   else {
  //     echo "Key: " . $key . ", Value: " . $values ."; ";
  //     echo "<br>";
  //   }
  //   //echo "Key: " . $key . ", Value: " . $values ."; ";
  //   //echo "<br>";
  // }
  // echo "<br>";
  //echo "@id: " . $value["@id"] . ", @type: " . $value["@type"] . "<br>";
}

echo json_encode($classes,JSON_UNESCAPED_SLASHES);

?>
