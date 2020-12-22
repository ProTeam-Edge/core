<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$topicVar = $_GET["topic"];

$topicFile = WP_CONTENT_DIR . "/themes/memberlite-child-master/jonathan_dev/generatedTopics/" . $topicVar . "_main.json"; //Person_main.json";
if ($json = file_get_contents($topicFile)) {
    error_log("Got contents successfully.", 0);
}
else {
    echo "JSON Get Contents Failed.";
    error_log("JSON Get Contents Failed.", 0);
}
if ($data = json_decode($json, true)) {

    error_log("JSON Decode Successful.", 0);
}
else {
    echo "JSON Decode Failed.";
    error_log("JSON Decode Failed.", 0);
}

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

$topicName = strtolower($data["topic_name"]); // Get topic name

// if ($topicName == "person") {
//   // Create order of fields for Person
//   $order = array();
//   $order["person_givenname"] = 1;
//   $order["person_familyname"] = 2;
//   $order["person_email"] = 3;
//   $order["person_telephone"] = 4;
//   $order["person_hasoccupation_occupation_occupationalcategory"] = 5;
//   $order["person_jobtitle"] = 6;
//   $order["person_url"] = 7;
//   $order["person_faxnumber"] = 8;
//   $order["person_knowsabout"] = 9;
//   $order["person_description"] = 10;
//
//   $style = array();
//   $style["person_givenname"] = "wpforms-one-half wpforms-first";
//   $style["person_familyname"]  = "wpforms-one-half";
//   $style["person_email"] = "wpforms-one-half wpforms-first";
//   $style["person_telephone"] = "wpforms-one-half";
//   $style["person_hasoccupation_occupation_occupationalcategory"] = "wpforms-one-half wpforms-first";
//   $style["person_jobtitle"] = "wpforms-one-half";
//   $style["person_url"] = "wpforms-one-half wpforms-first";
//   $style["person_faxnumber"] = "wpforms-one-half";
//   $style["person_knowsabout"] = "";
//   $style["person_description"] = "";
//
//
// } elseif ($topicName == "place") {
//   // Create order of fields for Place
//   $order = array();
//
//   $order["place_name"] = 1;
//   $order["place_url"] = 2;
//   $order["place_address_postaladdress_streetaddress"] = 3;
//   $order["place_address_postaladdress_addresslocality"] = 4;
//   $order["place_address_postaladdress_addressregion"] = 5;
//   $order["place_address_postaladdress_postalcode"] = 6;
//   $order["place_telephone"] = 7;
//   $order["place_faxnumber"] = 8;
//   $order["place_description"] = 9;
//
//   $style = array();
//   $style["place_name"] = "wpforms-one-half wpforms-first";
//   $style["place_url"] = "wpforms-one-half";
//   $style["place_address_postaladdress_streetaddress"] = "";
//   $style["place_address_postaladdress_addresslocality"] = "wpforms-two-fourths wpforms-first";
//   $style["place_address_postaladdress_addressregion"] = "wpforms-one-fourth";
//   $style["place_address_postaladdress_postalcode"] = "wpforms-one-fourth";
//   $style["place_telephone"] = "wpforms-one-half wpforms-first";
//   $style["place_faxnumber"] = "wpforms-one-half";
//   $style["place_description"] = "";
//
//   $states = array("AK", "AL", "AR", "AS", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "GU", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MN", "MO", "MP", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "PR", "RI", "SC", "SD", "TN", "TX", "UM", "UT", "VA", "VI", "VT", "WA", "WI", "WV", "WY");
// } elseif ($topicName == "thing") {
//
//   $order = array();
//
//   $order["thing_name"] = 1;
//   $order["thing_description"] = 2;
//
// } elseif ($topicName == "organization") {
//   $order = array();
//
//   $order["organization_name"] = 1;
//   $order["organization_url"] = 2;
//   $order["organization_email"] = 3;
//   $order["organization_telephone"] = 4;
//   $order["organization_logo"] = 5;
//   $order["organization_faxnumber"] = 6;
//   $order["organization_taxid"] = 7;
//   $order["organization_duns"] = 8;
//   $order["organization_description"] = 9;
// }


// Fill out fields
$fields = array();

$output = "";

foreach ($data["field_map"] as $key => $value) {

  $keySplit = explode("_", $key, 2);
  $keyTopicClass = $keySplit[0];

  if ($keyTopicClass == "pte") { // pte_meta
    // Don't output HTML for this one

  } elseif ($keyTopicClass == $topicName) {

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
  $output = "";
}

// Sort $fields by key
// ksort($fields, 1);
//
foreach ($fields as $key => $value) {
  $output = $output . $value;
}

echo htmlspecialchars($output);

?>
