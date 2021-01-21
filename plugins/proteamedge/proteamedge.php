<?php
/**
 * Plugin Name:       ProTeam Edge
 * Description:       Displays schema.org master JSON file.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jonathan Manni
 * Author URI:        https://www.jonathanmanni.com
 */
use Brick\StructuredData\Reader\RdfaLiteReader;
use Brick\StructuredData\HTMLReader;
use Brick\StructuredData\Item;
function download_schema_types() {
    // Initialize a file URL to the variable
    $url = 'https://schema.org/version/latest/schemaorg-current-http.jsonld';

    // // Use basename() function to return the base name of file
    // $file_name = basename($url);
    // error_log("Stored basename.", 0);
    // // Use file_get_contents() function to get the file
    // // from url and use file_put_contents() function to
    // // save the file by using base name
    // if(file_put_contents( $file_name,file_get_contents($url))) {
    //     echo "File downloaded successfully";
    //     error_log("File downloaded successfully.", 0);
    // }
    // else {
    //     echo "File downloading failed.";
    //     error_log("File downloading failed.", 0);
    // }

    // Use wp_remote_get to fetch the data
    $data = wp_remote_get($url);

    // Save the body part to a variable
    $body = wp_remote_retrieve_body($data);
    $http_code = wp_remote_retrieve_response_code($data);

    // Create the name of the file and the declare the directory and path
    $file = WP_CONTENT_DIR . "/plugins/proteamedge/schema.jsonId";

    // Write the file using put_contents instead of fopen(), etc.
    global $wp_filesystem;
    // Initialize the WP filesystem
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $wp_filesystem->put_contents($file, $body, 0644);
    error_log("File downloaded successfully.", 0);
}

// Activation Hook
register_activation_hook( __FILE__, 'download_schema_types' );

// Add action to include ProTeam Edge Settings page in admin sidebar
add_action('admin_menu', 'test_plugin_setup_menu');

// public static function jsonToDebug($jsonText = '') {
//     $arr = json_decode($jsonText, true);
//     $html = "";
//     if ($arr && is_array($arr)) {
//         $html .= self::_arrayToHtmlTableRecursive($arr);
//     }
//     return $html;
// }
//
// private static function _arrayToHtmlTableRecursive($arr) {
//     $str = "<table><tbody>";
//     foreach ($arr as $key => $val) {
//         $str .= "<tr>";
//         $str .= "<td>$key</td>";
//         $str .= "<td>";
//         if (is_array($val)) {
//             if (!empty($val)) {
//                 $str .= self::_arrayToHtmlTableRecursive($val);
//             }
//         } else {
//             $str .= "<strong>$val</strong>";
//         }
//         $str .= "</td></tr>";
//     }
//     $str .= "</tbody></table>";
//
//     return $str;
// }



add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_menu_page('ProTeam Edge', 'ProTeam Edge', 'manage_options', 'manage-topic-types', 'manage_topic_types' );
    add_submenu_page('manage-topic-types', 'Manage Topic Types', 'Manage Topic Types', 'manage_options', 'manage-topic-types' );
    add_submenu_page('manage-topic-types', 'Generate Topic Parts', 'Generate Topic Parts', 'manage_options', 'generate-topic-parts','generate_topic_parts' );
}
function manage_topic_types() {
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
$data = getSchemaProperties($url);

echo '<pre>';
print_r($data);
}
function getSchemaProperties($url)
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

  if (count($items) > 0 ) {
    // Get the name of the Class
    foreach ($items[0]->getProperties() as $name => $values) {
      if ($name == "http://www.w3.org/2000/01/rdf-schema#label") {
        foreach ($values as $value) {
            if ($value instanceof Item) {
                // We're only displaying the class name in this example; you would typically
                // recurse through nested Items to get the information you need
                $value = '(' . implode(', ', $value->getTypes()) . ')';
            }
            // If $value is not an Item, then it's a plain string
            // Set name of the class to the value of the label
            $className = $value;
        }
      }
    }
  }



  //echo "URL Input: " . strval($url) . "<br>";
  //echo "Class Name: " . strval($className) . "<br>";

  // Build a table with Properties
  //echo "<table id='example' class='display'><thead><tr><th id='top'>Label</th><th id='top'>Comment</th><th id='top'>Expected Type(s)</th></tr></thead>";
  //echo "<tbody>";

  // Create empty array to store information in
  $data = array();
  $labels = array('Label', 'Comment', 'ExpectedTypes');
  // Get an array of Properties
  for ($i = 1; $i < count($items); $i++) {
    //echo "Types include: " . implode(',', $items[$i]->getTypes()) . "<br>" . PHP_EOL;
    //echo "<tr>";

    // Initialize label counter
    $j = 0;
    // Create empty array to story information
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



    // Get the expected values and output them to the table
    //echo "<td>" . $expectedTypes . "</td>";
    echo "</tr>";

    // Push temp array into data array
    array_push($data, $temp);
  }
  //echo "</tbody></table>";
  return $data;
}
function generate_topic_parts() {
$nonce = wp_create_nonce( 'form-generate' );

$site_url = site_url();
    $rootUrl = "".$site_url."/wp-content/themes/memberlite-child-master/";
    echo "<h1>ProTeam Edge Topic Parts</h1>";
    $html = "";
    $html .= "
          <style>
            #pte_topic_part_container {
              font-family: 'Lato', sans-serif;
              display: flex;
              flex-wrap: wrap;
              padding-right: 20px;
            }
            #pte_topic_part_row_100 {
              flex-grow: 1;
            	flex-basis: 100%;
            }
            .pte_topic_part_textarea {
              padding: 5px 10px;
              font-size: 12px;
              line-height: 18px;
            	border: solid 1px rgb(204, 204, 204);
            	height: 200px;
              width: 100%;
            	color: #444;
            }
            .pte_topic_part_title{
              font-size: 14px;
              line-height: 20px;
              font-weight: bold;
              margin-bottom: 5px;
          }
            #pte_topic_part_text_buttons {
              padding: 10px 0;
            }
            .pte_link_button {
              margin: 0px 10px;
              color: rgb(0, 116, 187);
              cursor: pointer;
            }
          </style>
          <script>
            var alpn_templatedir = '{$rootUrl}';
            function pte_get_part(type){
              var fieldContents = jQuery('#pte_topic_part_text_input').val();
              //console.log(fieldContents)
              var endPoint = (type == 'html') ? 'generateHTML.php' : 'generateForm.php';
              jQuery.ajax({
            		url: alpn_templatedir + 'topics/' + endPoint,
            		type: 'POST',
            		data: {
            			payload: fieldContents,
            			security: '".$nonce."',
            		},
            		dataType: 'html',
            		success: function(html) {
                  //console.log(html);
                  var fieldTarget = jQuery('#pte_topic_part_text_output');
                  fieldTarget.val(html);
            		},
            		error: function() {
            			console.log('generatePart FAILED');
            		//TODO
            		}
            	});
            }
          </script>
    ";


    $html .= "
          <div id='pte_topic_part_container'>
            <div class='pte_topic_part_title'>Topic JSON</div>
            <textarea id='pte_topic_part_text_input' class='pte_topic_part_textarea'></textarea>
            <div id='pte_topic_part_text_buttons'>
              <a class='pte_link_button' onclick='pte_get_part(\"form\");'>Get Form</a>
              <a class='pte_link_button' onclick='pte_get_part(\"html\");'>Get HTML</a>
            </div>
            <textarea id='pte_topic_part_text_output' class='pte_topic_part_textarea' readonly></textarea>
          </div>
    ";


    echo $html;

    /*
    $schema_master_file = WP_CONTENT_DIR . "/plugins/proteamedge/schema.jsonId";
    if ($json = file_get_contents($schema_master_file)) {
        error_log("Got contents successfully.", 0);
    }
    else {
        error_log("JSON Get Contents Failed.", 0);
    }
    if ($data = json_decode($json)) {
        error_log("JSON Decode Successful.", 0);
    }
    else {
        error_log("JSON Decode Failed.", 0);
    }
    */
    //echo jsonToDebug($json);
    //echo gettype($data);
    //echo "<h6>" . $data . "</h6>";
}
?>