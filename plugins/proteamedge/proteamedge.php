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

// Add ProTeam Edge sidebar menu
function test_plugin_setup_menu(){
    add_menu_page( 'ProTeam Edge Plugin Settings', 'ProTeam Edge', 'manage_options', 'proteamedge', 'draw_page' );
}

// Create the ProTeam Edge Settings admin page HTML
function draw_page(){
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

add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_menu_page('ProTeam Edge', 'ProTeam Edge', 'manage_options', 'proteam-edge-parent', 'proteam_edge_parent' );
    add_submenu_page('proteam-edge-parent', 'Manage Topic Types', 'Manage Topic Types', 'manage_options', 'proteam-edge-parent' );
    add_submenu_page('proteam-edge-parent', 'Generate Topic Parts', 'Generate Topic Parts', 'manage_options', 'generate-topic-parts' );
}
function proteam_edge_parent() {
echo 'parent';
die;
}
?>