<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$topicVar = isset($_GET["topic"]) ? $_GET["topic"] : false;
$topicPost = isset($_POST["payload"]) ? $_POST["payload"] : false;

if (!$topicVar && !$topicPost) {
  $error = "Topic Not Provided";
  echo "<div>{$error}</div>";
  error_log($error, 0);
  exit;
}

if ($topicVar) {
  $topicFile = WP_CONTENT_DIR . "/themes/memberlite-child-master/jonathan_dev/generatedTopics/" . $topicVar . "_main.json"; //Person_main.json";
  if ($json = file_get_contents($topicFile)) {
      error_log("Got contents successfully.", 0);
      if ($data = json_decode($json, true)) {
          error_log("JSON Decode Successful.", 0);
      }
      else {
          echo "JSON Decode Failed.";
          error_log("JSON Decode Failed.", 0);
      }
  }
  else {
      echo "JSON Get Contents Failed.";
      error_log("JSON Get Contents Failed.", 0);
  }
} else if ($topicPost) {
  $data = json_decode(stripslashes($topicPost), true);
}
  /* PV Most changes made above this line. Also Final echo $string. */

$topicName = strtolower($data["topic_name"]); // Get topic name

// Fill out fields
$fields = array();


foreach ($data["field_map"] as $key => $value) {
  $keySplit = explode("_", $key, 2);
  $keyTopicClass = $keySplit[0];

  if ($keyTopicClass == "pte") { // pte prefixes
    $output = "";
    if ($key == "pte_meta") {
      // Don't output HTML
    } else {
      // Check if hidden value is otherwise
      if (isset($value["hidden"])) {
        $hidden = $value["hidden"];
      } else {
        $hidden = "false";
      }

      if ($hidden == "false") {
        $typeSplit = explode("_", $value["type"]);

        if ($typeSplit[0] != "core") { // Don't include core types
          if ($value["friendly"] != "") {
            // Generate HTML here
            $output = $output = "<div class='outer_topic_html_container'>";
            $output = $output . "<div class='pte_vault_row pte_topic_html_row'>";
            $output = $output . "<div class='pte_vault_row_35 pte_topic_table_label'>";
            $output = $output . "-{" . $key . "_title}-</div>";
            $output = $output . "<div class='pte_vault_row_65 pte_topic_table_value'>";
            $output = $output . "-{" . $key . "}-</div></div></div>";
            $fields[$key] = $output;
          }
        }
      }
    }
  } elseif ($keyTopicClass == $topicName) {
    $output = "";

    // Check if hidden value is otherwise
    if (isset($value["hidden"])) {
      $hidden = $value["hidden"];
    } else {
      $hidden = "false";
    }

    if ($hidden == "false") {

      $typeSplit = explode("_", $value["type"]);

      if ($typeSplit[0] != "core") { // Don't include core types

        if ($value["friendly"] != "") {

          // Generate HTML here
          $output = $output = "<div class='outer_topic_html_container'>";
          $output = $output . "<div class='pte_vault_row pte_topic_html_row'>";
          $output = $output . "<div class='pte_vault_row_35 pte_topic_table_label'>";
          $output = $output . "-{" . $key . "_title}-</div>";
          $output = $output . "<div class='pte_vault_row_65 pte_topic_table_value'>";

          // Identify if key is a special type for rendering differently
          if ($keySplit[1] == "email" || $keySplit[1] == "telephone" || $keySplit[1] == "description" || $keySplit[1] == "url") {
            if ($keySplit[1] == "description") {
              $output = $output . "<textarea class='pte_html_template_textarea' readonly>-{" . $key . "}-</textarea></div></div></div>";
            } elseif ($keySplit[1] == "url") {
              $output = $output . "<a href='-{" . $key . "}-' target='_blank'>-{" . $key . "}-</a></div></div></div>";
            } elseif ($keySplit[1] == "telephone") {
              $output = $output . "<a href='tel:-{" . $key . "}-'>-{" . $key . "}-</a></div></div></div>";
            } elseif ($keySplit[1] == "email") {
              $output = $output . "<a href='mailto:-{" . $key . "}-'>-{" . $key . "}-</a></div></div></div>";
            }
          } else { // Otherwise just render it normally
            $output = $output . "-{" . $key . "}-</div></div></div>";
          }


          //$tempKey = $order[$key];
          //$tempKey = $form["field_map"][$key]["order"];
          //$fields[$tempKey] = $output;
          $fields[$key] = $output;

        }
      }
    }
  }
}
$output = "";

// Sort $fields by key
// ksort($fields, 1);
//
foreach ($fields as $key => $value) {
  $output = $output . $value;
}

//echo htmlspecialchars($output);  PV -- were you always doing this?
echo $output;

?>
