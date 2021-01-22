<?php
/*
Template Name: pte_admin_test
*/

include('/var/www/html/proteamedge/public/wp-blog-header.php');

//echo "<h1>ProTeam Edge Settings</h1>";

$schema_master_file = WP_CONTENT_DIR . "/plugins/proteamedge/schema.jsonId";
if ($json = file_get_contents($schema_master_file)) {
    error_log("Got contents successfully.", 0);
}
else {
    error_log("JSON Get Contents Failed.", 0);
}
if ($data = json_decode($json, true)) {
    error_log("JSON Decode Successful.", 0);
}
else {
    error_log("JSON Decode Failed.", 0);
}
//echo '<textarea rows="30" cols="100">' . pp($data) . '</textarea>';

//echo '<p>There are ' . count($data["@graph"]) . ' items in the @graph.</p><br>';
//echo '<p id="demo">'. print_r($data["@graph"][0]["@id"]) . '</p>';

//for ($i = 1; $i <= 10; $i++) {
//    $value = $data["@graph"][$i];
//    echo $value["@id"] . ", " . $value["@type"] . "<br>";
//}

// THIS WILL SHOW ALL CONTENTS OF THE JSON FILE
// foreach ($data["@graph"] as $value) {
//   echo "Objects: " . count($value) . "; ";
//   foreach ($value as $key => $values) {
//     // If "values" is an array, unpack it; otherwise, print the string
//     if (gettype($values) == "array") {
//       if (count($values) <= 1) {
//         echo "Key: " . $key . ", Value: " . $values["@id"] . "<br>";
//       }
//       else {
//         echo "Key: " . $key . ", Values: ";
//         // If key is a category, don't use @id
//         if ($key == "http://schema.org/category") {
//           foreach ($values as $values_key => $values_item) {
//             echo $values_item . ", ";
//           }
//         }
//         else {
//           foreach ($values as $values_key => $values_item) {
//             echo $values_item["@id"] . ", ";
//           }
//         }
//         echo "<br>";
//       }
//     }
//     else {
//       echo "Key: " . $key . ", Value: " . $values ."; ";
//       echo "<br>";
//     }
//     //echo "Key: " . $key . ", Value: " . $values ."; ";
//     //echo "<br>";
//   }
//   echo "<br>";
//   //echo "@id: " . $value["@id"] . ", @type: " . $value["@type"] . "<br>";
// }

use Brick\StructuredData\Reader\RdfaLiteReader;
use Brick\StructuredData\HTMLReader;
use Brick\StructuredData\Item;

// Let's read Microdata here;
// You could also use RdfaLiteReader, JsonLdReader,
// or even use all of them by chaining them in a ReaderChain
$microdataReader = new RdfaLiteReader();

// Wrap into HTMLReader to be able to read HTML strings or files directly,
// i.e. without manually converting them to DOMDocument instances first
$htmlReader = new HTMLReader($microdataReader);

// Replace this URL with that of a website you know is using Microdata
$url = 'https://schema.org/Person';
$html = file_get_contents($url);

// Read the document and return the top-level items found
// Note: the URL is only required to resolve relative URLs; no attempt will be made to connect to it
$items = $htmlReader->read($html, $url);

//echo "There were " . count($items) . " items read from the HTML.";

//echo gettype($items);
//$item0 = $items[0];
//$item1 = $items[1];
//$item2 = $items[2];
//echo count($item0->getProperties()) . " items in ITEM0 <br>";

//for ($i = 1; $i <= 10; $i++) {
//    $value = $data["@graph"][$i];
//    echo $value["@id"] . ", " . $value["@type"] . "<br>";
//}

// for ($i = 0; $i < count($items); $i++) {
//   echo strval($i);
//   echo count($items[$i]->getProperties()) . " items in ITEM" . strval($i) .". <br>";
//   echo "Types include: " . implode(',', $items[$i]->getTypes()) . "<br>" . PHP_EOL;
//   foreach ($items[$i]->getProperties() as $name => $values) {
//     foreach ($values as $value) {
//       if ($value instanceof Item) {
//           // We're only displaying the class name in this example; you would typically
//           // recurse through nested Items to get the information you need
//           $value = '(' . implode(', ', $value->getTypes()) . ')';
//       }
//       echo "  - $name: $value <br>", PHP_EOL;
//     }
//   }
// }

// Define function to get range (expected types) of a Property


// Define function to input schema.org URL and output properties and their Types



$data = getSchemaProperties($url);

$url = '';
if(isset($_POST['SubmitButton'])){ //check if form was submitted
  $url = $_POST['URL']; //get input text
}
$site_url = site_url();
$nonce = wp_create_nonce( 'admin_test');
?>

<html>
  <head>
    <script data-require="jquery@*" data-semver="3.0.0" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.js"></script>
    <!-- <link rel="stylesheet" type="text/css" href="https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" /> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/plug-ins/1.10.21/integration/font-awesome/dataTables.fontAwesome.css" />
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.js"></script>
    <style>
      @base:#212121;
      @color:silver;
      @accent:#27ae60;
      @borderRadius:4px;
      td.details-control, td.addProperty {
          background: url('<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/fa/svgs/light/plus-circle.svg') no-repeat center center;
          background-size: 16px 16px;
          cursor: pointer;
      }
      tr.details td.details-control {
          background: url('<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/fa/svgs/light/minus-circle.svg') no-repeat center center;
          background-size: 16px 16px;
      }
      #save_linked_topics {
          float: right;
      }
      div.typeIsLinked {
          float:right;
          background: url('<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/fa/svgs/light/link.svg') no-repeat center center;
          background-size: 12px 12px;
          height: 12px;
          width: 12px;
      }
      div.typeIsDataType {
          float:right;
          background: url('<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/fa/svgs/light/shapes.svg') no-repeat center center;
          background-size: 12px 12px;
          height: 12px;
          width: 12px;
      }
      div.typeIsHidden {
          float:right;
          background: url('<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/fa/svgs/light/eye-slash.svg') no-repeat center center;
          background-size: 12px 12px;
          height: 12px;
          width: 12px;
      }
      .button {
          background-color: rgb(53,116,180);
          border: none;
          border-radius: 4px;
          color: white;
          padding: 10px 20px;
          text-align: center;
          text-decoration: none;
          display: inline-block;
          font-size: 14px;
      }
      .button:hover {
          background-color: rgb(9,86,135);
      }
      .first_level_property {
        border-left: 2px solid #479CD9;
        /* margin-left: -2px; */
        padding-left: 9px !important;
      }
      .subProperty {
        border-left: 6px solid #479CD9;
        border-left-style: double;
        /* margin-left: -2px; */
        padding-right: 9px !important;
      }
    </style>
  </head>
  <body>
    <div style="overflow:hidden"><button class="button" id="save_linked_topics">Save Topic-level Config</button></div>
    <br>
    <table id="classes" class="display" style="width:100%">
        <thead>
          <tr>
            <th></th>
            <th>TopicName</th>
            <th>Comment</th>
            <th>Core Topic</th>
            <th>Hide Properties</th>
            <th>Friendly Name</th>
            <th>Visibility</th>
            <th>Generate</th>
            <th>Save</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <script type="text/javascript">

      var dt = null;
      var linkedTopicsOnLoad = null;
      var hiddenTopicsOnLoad = null;
      var topicClassesOnLoad = null;
      var dataTypes = ["Text","URL","Distance","QuantitativeValue","Boolean","Date","DateTime","Number","Time","Integer"];

      function fillRow (row) {
        var d = row.data();
        // Get and fill friendly fields
        // Get all topics whose properties we don't want to expand
        var url = "/wp-content/themes/memberlite-child-master/topics/topicConfig/" + d.TopicName + "_config.json";
        var loadedFriendlyFields;
        $.ajax({
          url: url,
          type: "GET",
          dataType: "json",
          async: false,
          cache: false,
          success: function(data){
            useReturnDataFriendly(data);
          },
          error: function() {
            alert('No checkboxes and friendly fields set.');
          }
        });

        function useReturnDataFriendly(data){
            loadedFriendlyFields = data;
        };

        // Fill field ids with friendly text
        $.each(loadedFriendlyFields["friendly_fields"], function(key, value) {
          //document.getElementById('output').setAttribute("value", "100");
          $("#"+key).val(value);
        });

        // Check checkboxes if they have associated data
        // $.each(loadedFriendlyFields["checkedBoxes"], function(key, value) {
        //   //document.getElementById('output').setAttribute("value", "100");
        //   if (key != "pte_meta") {
        //     $("#"+key).prop( "checked", true );
        //   }
        // });

        $.each(loadedFriendlyFields["checkedBoxes"], function(index, value) {
          $("#"+value).prop( "checked", true );
        });

        $.each(loadedFriendlyFields["requiredCheckedBoxes"], function(index, value) {
          $("#"+value).prop( "checked", true );
        });

        $.each(loadedFriendlyFields["hiddenCheckedBoxes"], function(index, value) {
          $("#"+value).prop( "checked", true );
        });

      }

      function addAdditionalPropertyRows(topicName) {

        var out = "";
        var url = "/wp-content/themes/memberlite-child-master/topics/topicConfig/" + topicName + "_config.json";
        var loadedAdditionalProperties;
        $.ajax({
          url: url,
          type: "GET",
          dataType: "json",
          async: false,
          cache: false,
          success: function(data){
            useReturnDataFriendly(data);
          },
          error: function() {
            alert('No additional properties set in config.');
          }
        });

        function useReturnDataFriendly(data){
            loadedAdditionalProperties = data;
        };

        if (typeof loadedAdditionalProperties !== 'undefined') {
          $.each(loadedAdditionalProperties["additionalProperties"], function(key, value) {
            var pteCoreType = value["schema_key"].split("_")[3];
            var propertyCount = value["name"].split("_")[2];
            // Update global property count
            addPropertyCount = parseInt(propertyCount, 10);


            // Checkbox
            out += "<tr><td><input type='checkbox' class='"+topicName+" additionalProperty' name='"+ topicName + "_addProperty_" + propertyCount + "_checkbox' id='" + value["name"] + "'></td>";

            // Dropdown
            var coreTopicDropdownHTML = "<select onchange='addPropertyChange(this.id)' class='addPropertyDropdown' id='" + topicName + "_addProperty_" + propertyCount + "'>";
            var valueParsed = "";
            $.each(linkedTopicsOnLoad, function(index, value) {
              valueParsed = value.split("_")[2];
              // Add these to dropdown options
              if (valueParsed == pteCoreType) {
                coreTopicDropdownHTML += "<option value='" + value + "' selected>";
              } else {
                coreTopicDropdownHTML += "<option value='" + value + "'>";
              }
              coreTopicDropdownHTML += valueParsed + "</option>";
            });
            coreTopicDropdownHTML += "</select>";
            out += "<td>" + coreTopicDropdownHTML + "</td>";

            // Comment
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_comment" +"'>";
            out += "A " + pteCoreType + ".";
            out += "</div></td>";

            // Friendly
            var friendlyHTML = "<input type='text' class='friendly' id='pte_" + pteCoreType + "_" + propertyCount + "friendly' value='" + value["friendly"] + "'>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_friendly" +"'>";
            out += friendlyHTML;
            out += "</div></td>";

            // Required
            var requiredHTML = "<input type='checkbox' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_required'>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_required" +"'>";
            out += requiredHTML;
            out += "</div></td>";

            // Hidden
            var hiddenHTML = "<input type='checkbox' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_hidden'>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_hidden" +"'>";
            out += hiddenHTML;
            out += "</div></td>";

            // Type
            var typeHTML = pteCoreType + "<div class='typeIsLinked'><input type='hidden' class='ExpectedType' value='" + value["type"] + "'></div>";
            typeHTML += "<div><input type='hidden' class='schemaKey' value='" + value["schema_key"] + "'></div>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_type" +"'>";
            out += typeHTML;
            out += "</div></td>";

            out += "</tr>";

          });
        }

        return out;

      }

      function getSubproperties (type, d, item) {
        var output="";
        var subPropertyOutput;
        var url = "/wp-content/themes/memberlite-child-master/topics/classes/" + type.slice(18) + ".jsonld";
        $.ajax({
          url: url,
          type: "GET",
          dataType: "json",
          async: false,
          cache: false,
          success: function(data){
            //output = JSON.stringify(data);
            subPropertyOutput = data;
          },
          error: function() {
            url = "/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + type.slice(18));
            $.ajax({
              url: url,
              type: "GET",
              dataType: "json",
              async: false,
              cache: false,
              success: function(data){
                //output = JSON.stringify(data);
                subPropertyOutput = data;
              }
            });
          }
        });

        var ii;
        for (ii = 0; ii < subPropertyOutput.length; ii++) {
          // Get sub property name
          var subItem = subPropertyOutput[ii];
          if (subItem["FirstLevel"] == "True") {
            // Append only first level properties

            // Check if subProperty has multiple types
            var expectedTypes = subItem["ExpectedTypes"].split(', ');
            if (expectedTypes.length > 1) {
              $.each(expectedTypes, function(index, subType) {
                output += "<tr><td class='subProperty'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "'></td>";
                output += "<td>" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "</td><td>" + subItem["Comment"] + "</td><td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "_" + subType.slice(18) + "friendly'></td>";
                output += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "_required" + "'></td>";
                output += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "_hidden" + "'></td>";
                output += "<td>" + subType.slice(18);
                //output += "<input type='hidden' class='ExpectedType' value='" + subType.slice(18) + "'>";
                if (dataTypes.includes(subType.slice(18))) {
                  output += "<div class='typeIsDataType'>";
                  output += "<input type='hidden' class='ExpectedType' value='" + subType.slice(18) + "'>";
                } else if (linkedTopicsOnLoad.includes("linked_topic_"+subType.slice(18))) { // Core type
                  output += "<div class='typeIsCore'>";
                  output += "<input type='hidden' class='ExpectedType' value='core_" + subType.slice(18) + "'>";
                } else if (hiddenTopicsOnLoad.includes("hidden_topic_"+subType.slice(18))) { // Hidden type
                  output += "<div class='typeIsHidden'><input type='hidden' class='ExpectedType' value='hidden_" + subType.slice(18) + "'></div>";
                } else {
                  output += "<div><input type='hidden' class='ExpectedType' value='" + subType.slice(18) + "'>";
                }
                output += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "_" + subType.slice(18) + "'>";
                output += "</div></td></tr>";
              });


            } else {
              output += "<tr><td class='subProperty'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "'></td>";
              output += "<td>" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "</td><td>" + subItem["Comment"] + "</td><td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "friendly'></td>";
              output += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "_required" + "'></td>";
              output += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "_hidden" + "'></td>";
              output += "<td>" + subItem["ExpectedTypes"].slice(18);
              //output += "<input type='hidden' class='ExpectedType' value='" + subItem["ExpectedTypes"].slice(18) + "'>";
              if (dataTypes.includes(subItem["ExpectedTypes"].slice(18))) {
                output += "<div class='typeIsDataType'><input type='hidden' class='ExpectedType' value='" + subItem["ExpectedTypes"].slice(18) + "'>";
              } else if (linkedTopicsOnLoad.includes("linked_topic_"+subItem["ExpectedTypes"].slice(18))) { // Core type
                output += "<div class='typeIsCore'><input type='hidden' class='ExpectedType' value='core_" + subItem["ExpectedTypes"].slice(18) + "'>";
              } else if (hiddenTopicsOnLoad.includes("hidden_topic_"+subItem["ExpectedTypes"].slice(18))) { // Hidden type
                output += "<div class='typeIsHidden'><input type='hidden' class='ExpectedType' value='hidden_" + subItem["ExpectedTypes"].slice(18) + "'>";
              } else {
                output += "<div><input type='hidden' class='ExpectedType' value='" + subItem["ExpectedTypes"].slice(18) + "'>";
              }
              output += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "_" + subItem["ExpectedTypes"].slice(18) + "'>";
              output += "</div></td></tr>";
            }

          }
        }
        return output;
      }

      function addRows ( row , output) {
        var d = row.data();

        var out = "<table id='" + d.TopicName + "_properties'>" + "<thead><th></th><th>Label</th><th>Comment</th><th>Friendly Property Name</th><th>Required</th><th>Hidden</th><th>ExpectedTypes</th></thead>";

        // Add rows to table
        var i;
        for (i = 0; i < output.length; i++) { // Loop through all properties

            var item = output[i]; // Get property name

            var first_level_propertyClass = '';
            if (item["FirstLevel"] == "True") {
              first_level_propertyClass = 'first_level_property';
            }
            // Get an array of expected types (for use later if there are multiple)
            var expectedTypes = item["ExpectedTypes"].split(', ');

            if (true) {
              if (expectedTypes.length > 1) {
                // Multiple types for a given property
                $.each(expectedTypes, function(index, type) {
                  // Check if the type is a core topic
                  var typeIsCore = linkedTopicsOnLoad.includes("linked_topic_"+type.slice(18));
                  var typeIsHidden = hiddenTopicsOnLoad.includes("hidden_topic_"+type.slice(18));

                  if ((typeIsCore == false) && (typeIsHidden == false)) { // Not a core nor hidden type
                    if (dataTypes.includes(type.slice(18))) { // Render basic datatypes like this
                      out += "<tr><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                      out += "<td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + type.slice(18) + "_" + "friendly'></td>";
                      out += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_required" + "'></td>";
                      out += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_hidden" + "'></td>";
                      out += "<td>" + type.slice(18);
                      out += "<div class='typeIsDataType'><input type='hidden' class='ExpectedType' value='" + type.slice(18) + "'>";
                      out += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'>";
                      out += "</div></td></tr>";
                    } else { // If not a basic datatype, get subproperty fields

                      out += getSubproperties(type,d,item);

                    }
                  } else { // It is a core or hidden type, display a single text field
                    out += "<tr class='propertyTable'><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                    out += "<td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                    out += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_required" + "'></td>";
                    out += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_hidden" + "'></td>";
                    out += "<td>" + type.slice(18);
                    if (typeIsCore == true) {
                      out += "<div class='typeIsLinked'><input type='hidden' class='ExpectedType' value='core_" + type.slice(18) + "'></div>";
                    }
                    if (typeIsHidden == true) {
                      out += "<div class='typeIsHidden'><input type='hidden' class='ExpectedType' value='" + type.slice(18) + "'></div>";
                    }
                    out += "<div><input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'>";
                    out += "</div></td></tr>";
                  }
                });

              } else { // We only have one type
                var typeName = item["ExpectedTypes"].slice(18);
                var type = item["ExpectedTypes"];

                var typeIsCore = linkedTopicsOnLoad.includes("linked_topic_"+typeName);
                var typeIsHidden = hiddenTopicsOnLoad.includes("hidden_topic_"+typeName);

                if ((typeIsCore == false) && (typeIsHidden == false)) { // Neither core nor hidden
                  if (dataTypes.includes(item["ExpectedTypes"].slice(18))) {
                    out += "<tr><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                    out += "<td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                    out += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "_required" + "'></td>";
                    out += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "_hidden" + "'></td>";
                    out += "<td>" + typeName;
                    out += "<div class='typeIsDataType'><input type='hidden' class='ExpectedType' value='" + typeName + "'>";
                    out += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + item["ExpectedTypes"].slice(18) + "'>";
                    out += "</div></td></tr>";
                    //out += "<tr class='propertyTable'><td><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td><td><input type='text' class='friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td><td>" + item["ExpectedTypes"] + "</td></tr>";
                  } else { // Otherwise display all subproperties
                    out += getSubproperties(type,d,item);
                  }
                } else {
                  // It is a core or hidden topic, display a single text field
                  out += "<tr class='propertyTable'><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                  out += "<td><input type='text' class='" + d.TopicName + "_friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                  out += "<td><input type='checkbox' class='" + d.TopicName + "_required" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "_required" + "'></td>";
                  out += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "_hidden" + "'></td>";
                  out += "<td>" + typeName;
                  if (typeIsCore == true) {
                    out += "<div class='typeIsLinked'><input type='hidden' class='ExpectedType' value='core_" + typeName + "'></div>";
                  }
                  if (typeIsHidden == true) {
                    out += "<div class='typeIsHidden'><input type='hidden' class='ExpectedType' value='hidden_" + typeName + "'></div>";
                  }
                  out += "<div><input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + typeName + "'></div>";
                  out += "</td></tr>";
                  //out += "<tr><td class='propertyTable'><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td><td><input type='text' class='friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td><td>" + type + "<div class='typeIsLinked'></div></td></tr>";
                }
              }
            }
        }

        // If we have some additional property rows, add those here
        out += addAdditionalPropertyRows(d.TopicName);

        // Add "plus" row to add properties that aren't schema meaningful
        out += "<tr><td class='addProperty'></td><td>Add property</td><td></td><td></td><td></td><td></td><td></td></tr>";

        out += '</table>';
        return out;
      }

      function format ( row ) {
        var d = row.data();
        // Make ajax request to php to get properties for this class
        var output;
        // Original php method to get properties
        // var url = "/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + d.TopicName);
        // $.ajax({
        //   url: "/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + d.TopicName),
        //   type: "GET",
        //   dataType: "json",
        //   async: false,
        //   success: function(data){
        //     //output = JSON.stringify(data);
        //     output = data;
        //   }
        // });

        // New filesystem method to get properties
        var url = "/wp-content/themes/memberlite-child-master/topics/classes/" + d.TopicName + ".jsonld";
        $.ajax({
          url: url,
          type: "GET",
          dataType: "json",
          async: false,
          cache: false,
          success: function(data){
            //output = JSON.stringify(data);
            output = data;
          },
          error: function() {
            alert('Topic not in filesystem...give it a minute to parse schema.org.');
            $.ajax({
              url: "/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + d.TopicName),
              type: "GET",
              dataType: "json",
              async: false,
              cache: false,
              success: function(data){
                //output = JSON.stringify(data);
                output = data;
              }
            });
          }
        });


        return addRows(row, output);
        //return 'Detail:'+gettype(output);
        //return 'Detail: '+d.Detail+'<br>'+
        //    'The child row can contain any data you wish, including links, images, inner tables etc.';

      }



      function addPropertyRow(tableID, addPropertyCount) {

        // Get the overarching class to which this table belongs
        var topicName = tableID.split("_")[0];

  			var table = document.getElementById(tableID);

  			var rowCount = table.rows.length;
  			var row = table.insertRow(rowCount-1);

        // Checkbox
        var cell0 = row.insertCell(0);
        cell0.innerHTML = "<input type='checkbox' class='"+topicName+" additionalProperty' name='"+ topicName + "_addProperty_" + addPropertyCount.toString() + "_checkbox' checked>";

        // Property name dropdown
        var cell1 = row.insertCell(1);
        var coreTopicDropdownHTML = "<select onchange='addPropertyChange(this.id)' class='addPropertyDropdown' id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "'>";
        //var coreTopicDropdownHTML = "<select class='addPropertyDropdown' id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "'>";
        var valueParsed = "";
        $.each(linkedTopicsOnLoad, function(index, value) {
          valueParsed = value.split("_")[2];
          // Add these to dropdown options
          coreTopicDropdownHTML += "<option value='" + value + "'>" + valueParsed + "</option>";
        });
        coreTopicDropdownHTML += "</select>";
        cell1.innerHTML = coreTopicDropdownHTML;

        var cell2 = row.insertCell(2);
        cell2.innerHTML = "<div id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "_comment" +"'></div>";
        var cell3 = row.insertCell(3);
        cell3.innerHTML = "<div id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "_friendly" +"'></div>";
        var cell4 = row.insertCell(4);
        cell4.innerHTML = "<div id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "_required" +"'></div>";
        var cell5 = row.insertCell(5);
        cell5.innerHTML = "<div id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "_hidden" +"'></div>";
        var cell6 = row.insertCell(6);
        cell6.innerHTML = "<div id='" + topicName + "_addProperty_" + addPropertyCount.toString() + "_type" +"'></div>";
  		}

      var addPropertyCount = 0;
      $(document).on("click", ".addProperty", function() {
        // Get the table ID
        tableID = $(this).closest("table").attr('id');

        addPropertyCount += 1;
        // Pass tableID to addPropertyRow to add a row
        addPropertyRow(tableID, addPropertyCount);

      });


      function addPropertyChange(selectID) { // Do this when additional property dropdown is changed
        // We want to update the class of the checkox on this row
        var topicName = selectID.split("_")[0];

        var selectValue = $('select[id='+selectID+'] option').filter(':selected').val();
        var pteCoreType = selectValue.split("_")[2];
        var propertyCount = selectID.split("_")[2];

        // Set new checkbox ID
        //$("select[id="+selectID+"]").closest("tr").find('#'+ selectID + "_checkbox").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase());
        $("select[id="+selectID+"]").closest("tr").find("[name='" + selectID + "_checkbox" + "']").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase());
        //$("select[id="+selectID+"]").closest("tr").find('#'+ selectID + "_checkbox").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase());

        // Comment
        $("div[id="+selectID+"_comment"+"]").html("A " + pteCoreType + ".");

        // Friendly
        var friendlyHTML = "<input type='text' class='friendly' id='pte_" + pteCoreType + "_" + propertyCount + "friendly'>";
        //var friendlyHTML = "<input type='text' class='friendly' id='pte_" + pteCoreType + "friendly'>";
        $("div[id="+selectID+"_friendly"+"]").html(friendlyHTML);

        // Required
        var requiredHTML = "<input type='checkbox' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_required'>";
        //var requiredHTML = "<input type='checkbox' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase() + "_required'>";
        $("div[id="+selectID+"_required"+"]").html(requiredHTML);

        // Hidden
        var hiddenHTML = "<input type='checkbox' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_hidden'>";
        //var hiddenHTML = "<input type='checkbox' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase() + "_hidden'>";
        $("div[id="+selectID+"_hidden"+"]").html(hiddenHTML);

        // Type
        var typeHTML = pteCoreType + "<div class='typeIsLinked'><input type='hidden' class='ExpectedType' value='core_" + pteCoreType + "'></div>";
        typeHTML += "<div><input type='hidden' class='schemaKey' value='pte_" + pteCoreType + "_" + propertyCount + "_" + pteCoreType + "'></div>";
        //typeHTML += "<div><input type='hidden' class='schemaKey' value='pte_" + pteCoreType + "_" + pteCoreType + "'></div>";
        $("div[id="+selectID+"_type"+"]").html(typeHTML);
      }

      // Save a JSON list when Save Linked Topics is clicked
      $(document).on("click", "#save_linked_topics" , function() {
          // Show a loading icon
          var saveLinkedTopicsButton = document.querySelector('#save_linked_topics');
          saveLinkedTopicsButton.innerHTML = "Saving...";
          //var topicClass = this.id.slice(10); // Remove "add_topic_" from topic id

          // Get checkboxes under this topic class that are checked
          var idSelector = function() { return this.id; };
          var valSelector = function() { return this.value; };
          //var linkedTopicsChecked = $(".linked_topic_checkbox:checkbox:checked").map(idSelector).get();
          var linkedTopicsChecked = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').map(idSelector).get();
          var hiddenTopicsChecked = dt.rows().nodes().to$().find('.hidden_topic_checkbox:checkbox:checked').map(idSelector).get();
          var pteScopeIDs = dt.rows().nodes().to$().find('.topic_class').map(idSelector).get();
          var pteScopeValues = dt.rows().nodes().to$().find('.topic_class').map(valSelector).get();

          // Store pteScopeID:pteScopeValue pairs
          var pteScopePairs = {};
          var i;
          for (i = 0; i < pteScopeIDs.length; i++) {
            pteScopePairs[pteScopeIDs[i]] = pteScopeValues[i];
          }

          var jsonString = JSON.stringify(linkedTopicsChecked);
          var hiddenJSONString = JSON.stringify(hiddenTopicsChecked);
          var pteScopePairsString = JSON.stringify(pteScopePairs);

          // Save this JSON to server
          var url = "/wp-content/themes/memberlite-child-master/topics/saveLinkedTopics.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : jsonString,security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              // Save Hidden Topics
              var url = "/wp-content/themes/memberlite-child-master/topics/saveHiddenTopics.php";
              $.ajax({
                url: url,
                type: "POST",
                data: {data : hiddenJSONString,security:"<?php echo $nonce ?>"},
                dataType: "json",
                complete: function(){
                  var url = "/wp-content/themes/memberlite-child-master/topics/savePteScopeTopics.php";
                  $.ajax({
                    url: url,
                    type: "POST",
                    data: {data : pteScopePairsString,security:"<?php echo $nonce ?>"},
                    dataType: "json",
                    complete: function(){
                      saveLinkedTopicsButton.innerHTML = "Save Topic-level Config";
                    }
                  });
                  //saveLinkedTopicsButton.innerHTML = "Save Topic-level Config";
                }
              });
            }
          });
	
          // Update global vars
          linkedTopicsOnLoad = linkedTopicsChecked;
          hiddenTopicsOnLoad = hiddenTopicsChecked;
          topicClassesOnLoad = pteScopePairs;


      });

      // Save Config JSON when "Save Prop. Config" is clicked
      $(document).on("click", ".saveTopicConfig" , function() {

          var topicClass = this.id.slice(11); // Remove "save_topic_" from topic id

          var friendlyFields = {};
          var checkedBoxes = [];
          var requiredCheckedBoxes = [];
          var hiddenCheckedBoxes = [];

          var topicPropertyTable = topicClass + "_friendly";

          // Get a list of the friendly text boxes which aren't empty
          var friendlyTextBoxes = $("."+topicPropertyTable).map(function () {
            if (this.value.length > 0) {
              return $(this).val();
            }
          }).get();

          // Get friendly text box IDs
          var friendlyTextBoxIDs= $("."+topicPropertyTable).map(function() {
            if (this.value.length > 0) {
              return $(this).attr("id");
            }
          }).get();

          // Store ID:friendly field pairs
          var i;
          for (i = 0; i < friendlyTextBoxes.length; i++) {
            friendlyFields[friendlyTextBoxIDs[i]] = friendlyTextBoxes[i];
          }

          // Get checkboxes under this topic class that are checked
          var idSelector = function() { return this.id; };
          var boxesChecked = $("."+topicClass+":checkbox:checked").map(idSelector).get();
          var requiredBoxesChecked = $("."+topicClass+"_required:checkbox:checked").map(idSelector).get();
          var hiddenBoxesChecked = $("."+topicClass+"_hidden:checkbox:checked").map(idSelector).get();

          //var textFields = $(".friendly:input:").map(idSelector).get();
          $.each(boxesChecked, function(index, value) {
            // Add ID of boxes that are checked for saving to config
            checkedBoxes.push(value.toLowerCase());
          });

          $.each(requiredBoxesChecked, function(index, value) {
            // Add ID of required boxes that are checked for saving to config
            requiredCheckedBoxes.push(value.toLowerCase());
          });

          $.each(hiddenBoxesChecked, function(index, value) {
            // Add ID of hidden boxes that are checked for saving to config
            hiddenCheckedBoxes.push(value.toLowerCase());
          });

          // Get additional property fields that have been added
          //var nameSelector = function() { return this.name; };
          var additionalProperties = {};
          //var additionalPropIDs = dt.rows().nodes().to$().find('.additionalProperty').map(idSelector).get();
          var additionalPropIDs = $(".additionalProperty").map(idSelector).get();
          $.each(additionalPropIDs, function(index, value) {
            // Add ID of additional property checkboxes along with their selected type and required/hidden checkboxes
            // Get each of the checked boxes and their corresponding friendly text fields
            var checkbox = document.querySelector('[id='+value+']');
            friendly = $(checkbox).closest("tr").find('input[type="text"]').val();

            // Get corresponding required checkbox
            required = $(checkbox).closest("tr").find("#"+value.toLowerCase()+"_required").is(':checked');

            // Get corresponding hidden checkbox
            hidden = $(checkbox).closest("tr").find("#"+value.toLowerCase()+"_hidden").is(':checked');

            //type = $(checkbox).closest("tr").find('input[type="hidden"]').val();
            type = $(checkbox).closest("tr").find('input.ExpectedType:hidden').val();
            schemaKey = $(checkbox).closest("tr").find('input.schemaKey:hidden').val();

            additionalProperties[value.toLowerCase()] = Object.fromEntries(new Map([ ["friendly", friendly],["type", type.toLowerCase()],["name", value.toLowerCase()],["required", required.toString()],["schema_key", schemaKey],["hidden", hidden.toString()]]));
          });

          // Create config file to store checked boxes and friendly fields
          var config = {};
          config["topic_name"] = topicClass;
          config["friendly_fields"] = friendlyFields;
          config["checkedBoxes"] = checkedBoxes;
          config["requiredCheckedBoxes"] = requiredCheckedBoxes;
          config["hiddenCheckedBoxes"] = hiddenCheckedBoxes;
          config["additionalProperties"] = additionalProperties;

          var url = "/wp-content/themes/memberlite-child-master/topics/generateTopicConfig.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : JSON.stringify(config),security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              alert('Saving complete.');
            }
          });

      });

      function checkDuplicates (value, list) {
        if (list.includes(value)) {
          var valueSplit = value.split("_");
          if (!isNaN(valueSplit[valueSplit.length-1])) { // if there's a number on the end already
            // Rebuild the value
            var temp = "";
            var j;
            for (j = 0; j < valueSplit.length-1; j++) {
              temp += valueSplit[j] + "_";
            }
            // Tack on the next number
            var nextNumber = parseInt(valueSplit[valueSplit.length-1],10)+1;
            value = temp + nextNumber.toString();
            return checkDuplicates(value,list);
          } else {
            value += "_1";
            return checkDuplicates(value, list);
          }
        } else {
          return value;
        }

      }

      // Post a JSON list to a PHP file when "Generate Topic" button is clicked
      $(document).on("click", ".disabled" , function() {

          var fileContents = {};  // Create top-level array to add items to
          var field_map = {};     // Create array to hold fields
          var topicClass = this.id.slice(10); // Remove "add_topic_" from topic id

          var friendlyFields = {};
          var checkedBoxes = [];
          var uniqueTokensCheck = [];
          var uniqueSchemaKeyCheck = [];

          var duplicateCounter = 0;

          var topicPropertyTable = topicClass + "_friendly";
          // var idSelector = function() { return this.id; };
          // var textBoxes = $("."+topicPropertyTable+ " .friendly").map(idSelector).get();

          // Get contents of friendly text boxes
          // var friendlyTextBoxes= $("."+topicPropertyTable+ " .friendly").map(function() {
          //  return $(this).val();
          // }).get();

          // Get a list of the friendly text boxes which aren't empty
          var friendlyTextBoxes = $("."+topicPropertyTable).map(function () {
            if (this.value.length > 0) {
              return $(this).val();
            }
          }).get();

          // Get friendly text box IDs
          var friendlyTextBoxIDs= $("."+topicPropertyTable).map(function() {
            if (this.value.length > 0) {
              return $(this).attr("id");
            }
          }).get();

          // Store ID:friendly field pairs
          var i;
          for (i = 0; i < friendlyTextBoxes.length; i++) {
            friendlyFields[friendlyTextBoxIDs[i]] = friendlyTextBoxes[i];
          }

          // Get friendly topic name
          friendlyTopicName = $(this).closest("tr").find('input[type="text"]').val();
          if (friendlyTopicName == "") {
            alert("Please enter a friendly topic name.");
            return false;
          }

          // Get topic_class
          topicClassValue = $(this).closest("tr").find('select').val();

          // Get checkboxes under this topic class that are checked
          var idSelector = function() { return this.id; };
          var boxesChecked = $("."+topicClass+":checkbox:checked").map(idSelector).get();
          //var requiredBoxesChecked = $("."+topicClass+"_required:checkbox:checked").map(idSelector).get();
          var textFields = [];
          var textOut;
          var required = 'false';

          field_map["pte_meta"] = Object.fromEntries(new Map([ ["id", 0]]));

          var counter = 1;

          //var textFields = $(".friendly:input:").map(idSelector).get();
          $.each(boxesChecked, function(index, value) {
            // Get each of the checked boxes and their corresponding friendly text fields
            var checkbox = document.querySelector('[id='+value+']');
            textOut = $(checkbox).closest("tr").find('input[type="text"]').val();
            textFields.push(textOut);

            // Get corresponding required checkbox
            required = $(checkbox).closest("tr").find("#"+value.toLowerCase()+"_required").is(':checked');

            // Get corresponding hidden checkbox
            hidden = $(checkbox).closest("tr").find("#"+value.toLowerCase()+"_hidden").is(':checked');

            //type = $(checkbox).closest("tr").find('input[type="hidden"]').val();
            type = $(checkbox).closest("tr").find('input.ExpectedType:hidden').val();
            schemaKey = $(checkbox).closest("tr").find('input.schemaKey:hidden').val();

            // Add ID of boxes that are checked for saving to config
            checkedBoxes.push(value.toLowerCase());


            var removeA = 1;
            var removeB = 2;
            // Check if checkbox is additionalProperty
            if ($(checkbox).hasClass("additionalProperty")) {
              // Remove the number from the ID, too
              var removeA = 2;
              var removeB = 3;
            }

            // Remove either only  "type" or number and type from end of checkbox ID before saving
            var checkboxNoUnderscores = value.split("_");
            value = "";
            var i;
            for (i = 0; i < checkboxNoUnderscores.length-removeA; i++) {
              value += checkboxNoUnderscores[i];
              if (i < checkboxNoUnderscores.length-removeB ) {
                value += "_";
              }
            }

            // Remove either only "type" or number and type from end of schemaKey before saving
            var schemaKeyNoUnderscores = schemaKey.split("_");
            schemaKey = "";
            var i;
            for (i = 0; i < schemaKeyNoUnderscores.length-removeA; i++) {
              schemaKey += schemaKeyNoUnderscores[i];
              if (i < schemaKeyNoUnderscores.length-removeB) {
                schemaKey += "_";
              }
            }

            value = checkDuplicates(value, uniqueTokensCheck);
            schemaKey = checkDuplicates(schemaKey, uniqueSchemaKeyCheck);

            // Check new token against list to avoid duplicates
            // if (uniqueTokensCheck.includes(value.toLowerCase())) {
            //   // We have a duplicate, do something
            //   //value += "_" + checkboxNoUnderscores[checkboxNoUnderscores.length-1];
            //   //alert("Duplicate found.");
            //   //var duplicateCounter = 1;
            //   value += "_" + duplicateCounter.toString();
            // }
            // Push token value to checker list
            uniqueTokensCheck.push(value.toLowerCase());
            uniqueSchemaKeyCheck.push(schemaKey);


            field_map[value.toLowerCase()] = Object.fromEntries(new Map([ ["id", counter],["friendly", textOut],["type", type.toLowerCase()],["name", value.toLowerCase()],["required", required.toString()],["schema_key", schemaKey],["hidden", hidden.toString()]]));
            counter += 1;
          });

          fileContents["topic_name"] = topicClass;
          fileContents["topic_friendly_name"] = friendlyTopicName;
          fileContents["topic_class"] = topicClassValue;
          fileContents["field_map"] = field_map;
          //alert(JSON.stringify(fileContents));

          // Create config file to store checked boxes and friendly fields
          var config = {};
          config["topic_name"] = topicClass;
          config["friendly_fields"] = friendlyFields;
          config["checkedBoxes"] = checkedBoxes;

          // Save this JSON to server
          var url = "/wp-content/themes/memberlite-child-master/topics/generateTopic.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : JSON.stringify(fileContents),security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              alert('Saving complete.');
              // Save this JSON to server
              // var url = "/wp-content/themes/memberlite-child-master/topics/generateTopicConfig.php";
              // $.ajax({
              //   url: url,
              //   type: "POST",
              //   data: {data : JSON.stringify(config)},
              //   dataType: "json",
              //   complete: function(){
              //     alert('Saving complete.');
              //   }
              // });
            }
          });
      });



      $(document).ready(function() {

        dt = $('#classes').DataTable( {
          "processing": true,
          "ajax": {
            "url": "/wp-content/themes/memberlite-child-master/topics/getClasses.php",
            "dataSrc": ""
          },
          "columns": [
            {
              "class":          "details-control",
              "data":           null,
              "defaultContent": "",
              "orderable": false
            },
            { "data": "TopicName" },
            { "data": "Comment" },
            { "data": "LinkedTopic",
              "class": "dt-body-center",
              "orderable": false},
            { "data": "HiddenTopic",
              "class": "dt-body-center",
              "orderable": false},
            { "data": null,
              "defaultContent": "<input type='text'>",
              "orderable": false },
            { "data": "topic_class",
              "orderable": false },
            { "data": "Generate",
              "orderable": false },
            { "data": "SaveConfig",
              "orderable": false }
          ],

          "fnInitComplete": function( oSettings ) {
              // Do someting after table is drawn
              //$('.disabled').attr('disabled','disabled');

              // Check appropriate checkboxes for linked topics
              // First, get list of linked topics from server
              var url = "/wp-content/themes/memberlite-child-master/topics/linkedTopicConfig.json";
              var linkedTopics;
              $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                async: false,
                cache: false,
                success: function(data){
                  useReturnData(data);
                  //linkedTopics = data;
                },
                error: function() {
                  alert('Error getting linked topics.');
                }
              });

              function useReturnData(data){
                  linkedTopics = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  linkedTopicsOnLoad = data;
              };

              // Now, check all the boxes
              $.each(linkedTopics, function(index, linked_topic_checkbox_id) {
                dt.rows().nodes().to$().find("#"+linked_topic_checkbox_id).attr("checked", true);
                //$("#"+linked_topic_checkbox_id).attr("checked", true);
              });

              // Get all topics whose properties we don't want to expand
              var url = "/wp-content/themes/memberlite-child-master/topics/hiddenTopicConfig.json";
              var hiddenTopics;
              $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                async: false,
                cache: false,
                success: function(data){
                  useReturnDataHidden(data);
                  //linkedTopics = data;
                },
                error: function() {
                  alert('Error getting hidden topics.');
                }
              });

              function useReturnDataHidden(data){
                  hiddenTopics = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  hiddenTopicsOnLoad = data;
              };

              // Now, check all the boxes
              $.each(hiddenTopics, function(index, hidden_topic_checkbox_id) {
                dt.rows().nodes().to$().find("#"+hidden_topic_checkbox_id).attr("checked", true);
              });

              // Get all topic_class fields
              var url = "/wp-content/themes/memberlite-child-master/topics/pteScopeConfig.json";
              var topicClasses;
              $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                async: false,
                cache: false,
                success: function(data){
                  useReturnDataTopicClasses(data);
                  //linkedTopics = data;
                },
                error: function() {
                  alert('Error getting topic class data.');
                }
              });

              function useReturnDataTopicClasses(data){
                  topicClasses = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  topicClassesOnLoad = data;
              };

              // Fill all the topic_class fields
              $.each(topicClasses, function(key, value) {
                dt.rows().nodes().to$().find("#"+key).val(value);
              });

            }
        } );

        // Array to track the ids of the details displayed rows
        var detailRows = [];

        $('#classes tbody').on( 'click', 'tr td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = dt.row( tr );
            var idx = $.inArray( tr.attr('id'), detailRows );

            if ( row.child.isShown() ) {
                tr.removeClass( 'details' );
                row.child.hide();

                // Remove from the 'open' array
                detailRows.splice( idx, 1 );
            }
            else {
                tr.addClass( 'details' );
                row.child( format( row ) ).show();

                // Add to the 'open' array
                if ( idx === -1 ) {
                    detailRows.push( tr.attr('id') );
                }

                // Fill checkboxes and friendly fields dynamically
                fillRow(row);

            }
        } );

        // On each draw, loop over the `detailRows` array and show any child rows
        dt.on( 'draw', function () {
            $.each( detailRows, function ( i, id ) {
                $('#'+id+' td.details-control').trigger( 'click' );
            } );
        } );
      } );

    </script>


  </body>
  </html>
