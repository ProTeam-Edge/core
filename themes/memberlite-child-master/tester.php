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
if ($graphData = json_decode($json, true)) {
    error_log("JSON Decode Successful.", 0);
    //echo "JSON Decode Successful.";
}
else {
    error_log("JSON Decode Failed.", 0);
    //echo "JSON Decode Failed.";
}


use Brick\StructuredData\Reader\RdfaLiteReader;
use Brick\StructuredData\Reader\JsonLdReader;
use Brick\StructuredData\HTMLReader;
use Brick\StructuredData\Item;

$url = $_GET["url"];

// Define function to

// Define function to get range (expected types) of a Property
function getExpectedTypes($url)
{
  // Let's read Rdfa here
  $rdfaReader = new RdfaLiteReader();
  
  // Wrap into HTMLReader to be able to read HTML strings or files directly,
  // i.e. without manually converting them to DOMDocument instances first
  $htmlReader = new HTMLReader($rdfaReader);
  // Read $url
  $html = file_get_contents($url);

  // Read the document and return the top-level items found
  // Note: the URL is only required to resolve relative URLs; no attempt will be made to connect to it
  $items = $htmlReader->read($html, $url);

  for ($i = 0; $i < count($items); $i++) {
    //echo count($items[$i]->getProperties()) . " items in ITEM" . strval($i) .". <br>";
    //echo "Types include: " . implode(',', $items[$i]->getTypes()) . "<br>" . PHP_EOL;
    foreach ($items[$i]->getProperties() as $name => $values) {
      if ($name == 'http://schema.org/rangeIncludes') {
        $output = array();
        foreach ($values as $value) {
          if ($value instanceof Item) {
              // We're only displaying the class name in this example; you would typically
              // recurse through nested Items to get the information you need
              $value = '(' . implode(', ', $value->getTypes()) . ')';
          }
          array_push($output, $value);
        }
        return implode(', ', $output);
      }
    }
  }
}

// Define function to input schema.org URL and output properties and their Types
function getSchemaProperties($url, $graphData)
{
  // Let's read Rdfa here
  $rdfaReader = new RdfaLiteReader();

  // Wrap into HTMLReader to be able to read HTML strings or files directly,
  // i.e. without manually converting them to DOMDocument instances first
  $htmlReader = new HTMLReader($rdfaReader);
  // Read $url
  $html = file_get_contents($url);
  // Read the document and return the top-level items found
  // Note: the URL is only required to resolve relative URLs; no attempt will be made to connect to it
  $items = $htmlReader->read($html, $url);

  // Get the name of the Class
  // foreach ($items[0]->getProperties() as $name => $values) {
  //   if ($name == "http://www.w3.org/2000/01/rdf-schema#label") {
  //     foreach ($values as $value) {
  //         if ($value instanceof Item) {
  //             // We're only displaying the class name in this example; you would typically
  //             // recurse through nested Items to get the information you need
  //             $value = '(' . implode(', ', $value->getTypes()) . ')';
  //         }
  //         // If $value is not an Item, then it's a plain string
  //         // Set name of the class to the value of the label
  //         $className = $value;
  //     }
  //   }
  // }

  //echo "URL Input: " . strval($url) . "<br>";
  //echo "Class Name: " . strval($className) . "<br>";

  // Build a table with Properties
  //echo "<table id='example' class='display'><thead><tr><th id='top'>Label</th><th id='top'>Comment</th><th id='top'>Expected Type(s)</th></tr></thead>";
  //echo "<tbody>";

  // Create empty array to store information in
  $data = array();
  $labels = array('Label', 'Comment', 'ExpectedTypes','FirstLevel');

  // Loop through all items and create an array of Properties
  for ($i = 1; $i < count($items); $i++) {
    //echo "Types include: " . implode(',', $items[$i]->getTypes()) . "<br>" . PHP_EOL;
    //echo "<tr>";

    // Initialize label counter
    $j = 0;

    // Create empty array to store information
    $temp = array();

    foreach ($items[$i]->getProperties() as $name => $values) {

      // Get right label for table
      $label = $labels[$j];

      // Loop through all values
      foreach ($values as $value) {
        // Look for label and store it to do type lookup
        if ($name == 'http://www.w3.org/2000/01/rdf-schema#label') {
          $expectedTypeUrl = 'http://schema.org/' . $value;
        }
        if ($value instanceof Item) {
            // We're only displaying the class name in this example; you would typically
            // recurse through nested Items to get the information you need
            $value = '(' . implode(', ', $value->getTypes()) . ')';
        }
        // Push name value(s) pair onto temporary array
        $temp[$label] = $value;
        //array_push($temp, $label=>$value);
        // Echo a cell in the table
        //echo "<td>$value</td>";
        //echo "  - $name: $value <br>", PHP_EOL;
      }
      $j += 1;
    }
    // Get right label for table
    $label = $labels[$j];

    $expectedTypes = getExpectedTypes($expectedTypeUrl);
    $temp[$label] = $expectedTypes;
    //array_push($temp, $label=>$expectedTypes);

    $firstLevel = 'False';
    // Determine whether the property is first level
    // THIS WILL SHOW ALL CONTENTS OF THE JSON FILE
    foreach ($graphData["@graph"] as $val) {

      // Check if object has type "rdfs:Class" and push onto array
      if ($val["@type"] == "rdf:Property") {
        if ($val["@id"] == ("http://schema.org/" . $temp["Label"])) {
          // We found the property in the master schema file
          // Check if domainIncludes contains the overarching Class
          $domainIncludes = $val["http://schema.org/domainIncludes"];

          // Create counter to check if FirstLevel property is found
          $fl = 0;
          // Iterate through values if there are Multiple
          if (count($domainIncludes) > 1) {
            foreach ($domainIncludes as $domain) {
              if ($domain["@id"] == $url) {
                // Domain includes the Class, add one to counter
                $fl += 1;
              }
            }
          } else {
            if ($domainIncludes["@id"] == $url) {
              // Domain includes the Class, add one to counter
              $fl += 1;
            }
          }

          // If counter > 0, then it includes the Class; set firstLevel property
          if ($fl > 0) {
            $firstLevel = 'True';
          }
        }
      }
    }

    $temp[$labels[3]] = $firstLevel;

    // Get the expected values and output them to the table
    //echo "<td>" . $expectedTypes . "</td>";
    //echo "</tr>";

    // Push temp array into data array
    array_push($data, $temp);
  }
  //echo "</tbody></table>";
  return $data;
}

if ($url == '') {
  $data = array(
    array('Label'=>'', 'Comment'=>'', 'ExpectedTypes'=>'')
  );
}
else {
  $data = getSchemaProperties($url, $graphData);
}

echo json_encode($data);
?>
