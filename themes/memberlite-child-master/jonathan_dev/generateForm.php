<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
$passed = 0;
$nonce  = $_POST["security"];
$verify = wp_verify_nonce($nonce, 'form-generate' );
if($verify==1) 
{
	$passed = 1;
}
if($passed==0)
{
	echo 'Not a valid request.';
	die;
}

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

// $formFile = WP_CONTENT_DIR . "/themes/memberlite-child-master/jonathan_dev/formConfig/" . $topicVar . "_form.json"; //Person_form.json";
// if ($json = file_get_contents($formFile)) {
//     error_log("Got contents successfully.", 0);
// }
// else {
//     echo "JSON Get Contents Failed.";
//     error_log("JSON Get Contents Failed.", 0);
// }
// if ($form = json_decode($json, true)) {
//
//     error_log("JSON Decode Successful.", 0);
// }
// else {
//     echo "JSON Decode Failed.";
//     error_log("JSON Decode Failed.", 0);
// }

function evenOddCheck($number){
    if($number % 2 == 0){
      return "even";
    }
    else{
      return "odd";
    }
}

$topicName = strtolower($data["topic_name"]); // Get topic name

// Create constant arrays to reference
$states = array("AK", "AL", "AR", "AS", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "GU", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MN", "MO", "MP", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "PR", "RI", "SC", "SD", "TN", "TX", "UM", "UT", "VA", "VI", "VT", "WA", "WI", "WV", "WY");

// Fill out fields
$fields = array();
$temp = array();

$counter = 0;
$wasPreviousNewLine = "false";

foreach ($data["field_map"] as $key => $value) {


  $keySplit = explode("_", $key, 2);
  $keyTopicClass = $keySplit[0];

//  if ($keyTopicClass == "pte") { // pte_meta
  if ($key == "pte_meta") {
    //if ($key == "pte_meta") {
    $temp = array();
    $temp["id"] = strval($value["id"]);   // ID
    $temp["type"] = "hidden";     // Type
    $temp["label"] = "ID";        // Label
    $temp["label_disable"] = "1"; // Label disable
    $temp["default_value"] = "";  // Default Value
    $temp["css"] = "";           // CSS
    // } else { // user-added non-schema-meaningful linked topic
    //   $temp = array();
    //   $temp["id"] = strval($value["id"]);         // ID
    //   $temp["type"] = "hidden";                   // Type
    //   $temp["label"] = $value["friendly"];        // Label
    //   $temp["label_disable"] = "1";               // Label disable
    //   $temp["default_value"] = "";                // Default Value
    //   $temp["css"] = "";          // CSS
    // }

    $fields[$key] = $temp;           // Store temp array in fields
  } else {
  //} elseif ($keyTopicClass == $topicName) {

    if ($keyTopicClass == $topicName) {

      // Check if hidden value is otherwise
      if (isset($value["hidden"])) {
        $hidden = $value["hidden"];
      } else {
        $hidden = "false";
      }

      if ($hidden == "false") {

        $typeSplit = explode("_", $value["type"]);

        if ($typeSplit[0] != "core") { // Don't include core types
          //if ($value["friendly"] != "") {
          if (true) {
            $temp = array();

            // ID - make outer ID match the internal ID

            // Actually, make ID match mapping JSON
            $temp["id"] = strval($value["id"]);

            // Type
            if (substr($value["type"], 0,4) == "core") { // type is core
              $temp["type"] = "text";
            } else if ($value["type"] == "Person") {
              $temp["type"] = "text";
            } else if ($value["type"] == "Date") {
              $temp["type"] = "date-time";
            } else {

              if ($keySplit[1] == "hasoccupation_occupation_occupationalcategory") {
                //$temp["type"] = strtolower($value["type"]);
                $temp["type"] = "select";
              } else if ($keySplit[1] == "address_postaladdress_addressregion") {
                $temp["type"] = "select";
              } else if ($keySplit[1] == "email") {
                $temp["type"] = "email";
              } else if ($keySplit[1] == "telephone" || $keySplit[1] == "faxnumber") {
                $temp["type"] = "phone";
              } else if ($keySplit[1] == "url") {
                $temp["type"] = "url";
              } else {
                $temp["type"] = strtolower($value["type"]);
              }

            }

            // Label
            $temp["label"] = $value["friendly"];
            if ($key == $topicName . "_description") {
              $temp["type"] = "textarea";
            } elseif ($key == $topicName . "_text") {
              $temp["type"] = "textarea";
            }


            // Format
            if ($temp["type"] == "date-time") {
              $temp["format"] = "date";
            } else if ($temp["type"] == "phone") {
              $temp["format"] = "us";
            } else if ($temp["type"] == "select") {
              $choices = array();


              if ($keySplit[1] == "hasoccupation_occupation_occupationalcategory") {
                $temp["dynamic_choices"] = "post_type";
                $temp["dynamic_post_type"] = "pte_profession";
                $choices["0"] = array(
                                      "label" => "placeholder",
                                      "value" => "",
                                      "image" => ""
                );
              } else if ($keySplit[1] == "address_postaladdress_addressregion") {
                $counter = 1;
                foreach ($states as $state) {
                  $choices[strval($counter)] = array(
                                                      "label" => $state,
                                                      "value" => "",
                                                      "image" => ""
                  );
                  $counter += 1;
                }
              }

              $temp["choices"] = $choices;
            }

            // Description
            $temp["description"] = "";

            // Required
            if ($value["required"] == "true") {
              $temp["required"] = "1";
            }

            // Size
            if ($key == $topicName . "_description") {
              // Medium size for "About" textarea field
              $temp["size"] = "medium";
            } else if ($key == $topicName . "_text") {
              $temp["size"] = "medium";
            } else {
              $temp["size"] = "large";
            }

            // Placeholder
            if ($temp["type"] == "date-time") {
              $temp["date_placeholder"] = "";
              $temp["date_format"] = "F j, Y";
              $temp["date_type"] = "datepicker";
              $temp["time_placeholder"] = "";
              $temp["time_format"] = "g:i A";
              $temp["time_interval"] = "30";
            } else {
              if (count($typeSplit) > 1) {
                if ($typeSplit[1] == "hasoccupation_occupation_occupationalcategory") {
                  $temp["placeholder"] = "Please select...";
                  //$temp["placeholder"] = "";
                } else {
                  $temp["placeholder"] = "";
                }
              } else {
                $temp["placeholder"] = "";
              }

            }

            if ($value["type"] == "Text") {
              $temp["limit_count"] = "256";
              $temp["limit_mode"] = "characters";
            }
            $temp["default_value"] = "";

            // CSS
            //$temp["css"] = $form["field_map"][$key]["style"]; // get css from form file
            if ($key == $topicName . "_description") {  // About
              $temp["css"] = "wpforms-first";
            } else if ($key == $topicName . "_knowsabout") {  // Hashtags
              $temp["css"] = "wpforms-first";
            } else if ($key == $topicName . "_text") {  // Note text
              $temp["css"] = "wpforms-first";
            } else {
              // Check for newline
              if (isset($value["newline"])) {
                $newline = $value["newline"];
              } else {
                $newline = "false";
              }

              if ($newline == "true") {
                $temp["css"] = "wpforms-first wpforms-one-half";
                $wasPreviousNewLine = "true";
              } else {
                if ($wasPreviousNewLine == "true") {
                  $temp["css"] = "wpforms-one-half";
                  $wasPreviousNewLine = "false";
                } else {
                  $temp["css"] = "wpforms-first wpforms-one-half";
                  $wasPreviousNewLine = "true";
                }
              }
            }

            // Set the order
            //$tempKey = $form["field_map"][$key]["order"];
            $fields[$key] = $temp;

          }
        }
      }

    }
  }

  $counter += 1;
}

// Sort $fields by key - old
// ksort($fields, 1);
//
// $keys = array();
// foreach ($fields as $key => $value) {
//   array_push($keys, $value["id"]);
// }
//
// $fields = array_combine($keys,$fields);


// Define form settings
$settings = array();
$settings["form_title"] = "PTE Test Form - 1.1";
$settings["form_desc"] = "";
$settings["form_class"] = "";
$settings["submit_text"] = "Save";
$settings["submit_text_processing"] = "Saving...";
$settings["submit_class"] = "";
$settings["honeypot"] = "1";
$settings["dynamic_population"] = "1";
$settings["ajax_submit"] = "1";
$settings["disable_entries"] = "1";
$settings["notification_enable"] = "0";

// Define form notifications
$notifications = array();
$temp = array();
$temp["notification_name"] = "Default Notification";
$temp["email"] = "{admin_email}";
$temp["subject"] = "New Entry: Test Form";
$temp["sender_name"] = "";
$temp["sender_address"] = "{admin_email}";
$temp["replyto"] = "{field_id=1}";
$temp["message"] = "{all_fields}";
$notifications["1"] = $temp;
$settings["notifications"] = $notifications;

// Define form confirmations
$confirmations = array();
$temp = array();
$temp["name"] = "Default Confirmation";
$temp["type"] = "message";
$temp["message"] = "";
$temp["page"] = "1089";
$temp["redirect"] = "";
$confirmations["1"] = $temp;
$settings["confirmations"] = $confirmations;

//
$form = array();
$form["id"] = "0";
$form["field_id"] = "1";
$form["fields"] = $fields;
$form["settings"] = $settings;
$form["meta"] = array("template"=>"contact");
//array_push($form, $settings);

// $temp["TopicName"] = substr($value["@id"], 18);
// $temp["Comment"] = $value["rdfs:comment"];
// $temp["LinkedTopic"] = "<input type='checkbox' class='linked_topic_checkbox' id='linked_topic_" . substr($value["@id"], 18) . "'>";
// $temp["HiddenTopic"] = "<input type='checkbox' class='hidden_topic_checkbox' id='hidden_topic_" . substr($value["@id"], 18) . "'>";
// $temp["Generate"] = "<button class='disabled' id='add_topic_" . substr($value["@id"], 18) . "'>Generate Topic</button>";
// array_push($classes, $temp);

// Uncomment this line
//echo "[" . json_encode($form) . "]";  //PV Not using the brackets
echo json_encode($form);


//echo "<br>";
//echo implode(',',$data["field_map"]);

?>
