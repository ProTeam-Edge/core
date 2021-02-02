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
    add_submenu_page('manage-topic-types', 'Manage Topic Types DB', 'Manage Topic Types DB', 'manage_options', 'manage-topic-types-db','manage_topic_types_db' );
}
function manage_topic_types_db() {

	global $wpdb;

	/* $inssql = 'insert into alpn_manage_topic(topic_name,core_topic,hide_properties,friendly_name,visibility,cdate,mdate)values("Airline","1","1","Name","1","'.time().'","'.time().'")';
	echo $insdata = $wpdb->query($inssql); */
	/* $sql = 'select * from alpn_manage_topic';
	$data = $wpdb->get_results($sql);
	echo '<pre>';
	print_r($data); */

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
    <script type="text/javascript" charset="utf8" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js
"></script>

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
   <h1>Manage Topic Types</h1>  
    <div style="overflow:hidden;display:none"><button class="button" id="save_linked_topics">Save Topic-level Config</button></div>
    <div style="overflow:hidden"><button class="button" id="empty_previous_configs">Clear Saved Settings From DB</button></div>
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
         
          </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <script type="text/javascript">
		function child_settings_trigger(element){
		getclass =$(element).attr('class');
		split_class = getclass.split(' ');
		topic_name = split_class[0];
		check_hyphen = topic_name.indexOf("_");
		
		if(check_hyphen!='-1')
		{
			split_hyphen = topic_name.split('_');
			topic_name = split_hyphen[0];
		}
		console.log(topic_name);
		saveTopicConfig(topic_name);	
		}
		function additionalchild_settings_trigger(element){
		id =$(element).parent().attr('id');
		split_id = id.split('_');
		topic_name = split_id[0];
		saveTopicConfig(topic_name);	
		}	  
      var dt = null;
      var linkedTopicsOnLoad = null;
      var hiddenTopicsOnLoad = null;
      var topicClassesOnLoad = null;
	
      var dataTypes = ["Text","URL","Distance","QuantitativeValue","Boolean","Date","DateTime","Number","Time","Integer"];

      function fillRow (row) {
        var d = row.data();
        // Get and fill friendly fields
        // Get all topics whose properties we don't want to expand
		var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/prefill_manage_topic_subfields.php";
       // var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/topicConfig/" + d.TopicName + "_config.json";
        var loadedFriendlyFields;
        $.ajax({
            url: url,
			type: "POST",
			data: {topic_name:d.TopicName,security:"<?php echo $nonce ?>"},
			dataType: "json",
			async: false,
			cache: false,
          success: function(data){
		  if(data!='')	 
		  {
			loadedFriendlyFields = data;
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
          },
          error: function() {
            //alert('No checkboxes and friendly fields set.');
          }
        });

      

        

      }
 function useReturnDataFriendly(data){
            loadedAdditionalProperties = data;
        };
      function addAdditionalPropertyRows(topicName) {

        var out = "";
		var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/prefill_manage_topic_subfields.php";
       // var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/topicConfig/" + topicName + "_config.json";
        var loadedAdditionalProperties;
        $.ajax({
           url: url,
			type: "POST",
			data: {topic_name:topicName,security:"<?php echo $nonce ?>"},
			dataType: "json",
			async: false,
			cache: false,
          success: function(data){
			if(data!='')	 
			{
            loadedAdditionalProperties = data;
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
			coreTopicDropdownHTML += "<option value=''>Please Select</option>";
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
            var friendlyHTML = "<input onblur='return additionalchild_settings_trigger(this)' type='text' class='friendly' id='pte_" + pteCoreType + "_" + propertyCount + "friendly' value='" + value["friendly"] + "'>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_friendly" +"'>";
            out += friendlyHTML;
            out += "</div></td>";

            // Required
            var requiredHTML = "<input type='checkbox' onclick='return additionalchild_settings_trigger(this)' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_required'>";
            out += "<td><div id='" + topicName + "_addProperty_" + propertyCount + "_required" +"'>";
            out += requiredHTML;
            out += "</div></td>";

            // Hidden
            var hiddenHTML = "<input type='checkbox' onclick='return additionalchild_settings_trigger(this)' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_hidden'>";
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
		  }}
          },
          error: function() {
            //alert('No additional properties set in config.');
          }
        });

       

     

        return out;

      }

      function getSubproperties (type, d, item) {
		  var checked_ids = [];
		dt.rows().nodes().to$().find(".linked_topic_checkbox:checkbox:checked").each(function(){
			id= $(this).attr('id'); 
			split_id = id.split('_');
		  checked_ids.push(split_id[2]);
	
  });
  var primary_types = ['Time','Text','DateTime','Number','Date','Boolean','URL'];
   console.log('checked_ids');
 
    console.log(checked_ids);
	  console.log('class name');
    console.log( type.slice(18));
		 console.log('getSubproperties type');
		 console.log(type);
        var output="";
        var subPropertyOutput;
        var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/classes/" + type.slice(18) + ".jsonld";
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
            url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + type.slice(18));
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
				console.log('if');
                output += "<tr><td class='subProperty'><input type='checkbox' onclick='return child_settings_trigger(this)' class='" + d.TopicName + " subpropertycheckbox' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "'></td>";
                output += "<td>" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "</td><td>" + subItem["Comment"] + "</td><td><input type='text' class='" + d.TopicName + "_friendly subpropertyfriendly' onblur='return child_settings_trigger(this)'  id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "_" + subType.slice(18) + "friendly'></td>";
                output += "<td><input type='checkbox' class='" + d.TopicName + "_required subpropertyrequired" + "' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "_required" + "'></td>";
                output += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + " subpropertyhidden' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subType.slice(18).toLowerCase() + "_hidden" + "'></td>";
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
			  console.log('else');	
              output += "<tr><td class='subProperty'><input type='checkbox' class='" + d.TopicName + " subpropertycheckbox' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "'></td>";
              output += "<td>" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "</td><td>" + subItem["Comment"] + "</td><td><input type='text' class='" + d.TopicName + "_friendly subpropertyfriendly' onblur='return child_settings_trigger(this)'  id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "_" + subItem["Label"] + "friendly'></td>";
              output += "<td><input type='checkbox' class='" + d.TopicName + "_required subpropertyrequired" + "' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "_required" + "'></td>";
              output += "<td><input type='checkbox' class='" + d.TopicName + "_hidden" + " subpropertyhidden' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_" + subItem["Label"].toLowerCase() + "_" + subItem["ExpectedTypes"].slice(18).toLowerCase() + "_hidden" + "'></td>";
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
	
			console.log(output);
			console.log('consoled output')
		
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
				console.log('expected type more then 1')	
                // Multiple types for a given property
                $.each(expectedTypes, function(index, type) {
					
                  // Check if the type is a core topic
                  var typeIsCore = linkedTopicsOnLoad.includes("linked_topic_"+type.slice(18));
                  var typeIsHidden = hiddenTopicsOnLoad.includes("hidden_topic_"+type.slice(18));
			
					if ((typeIsCore == false) && (typeIsHidden == false)) { // Not a core nor hidden type
                    if (dataTypes.includes(type.slice(18))) { // Render basic datatypes like this
                      out += "<tr><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + " subpropertycheckbox' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                      out += "<td><input type='text' onblur='return child_settings_trigger(this)'  class='" + d.TopicName + "_friendly subpropertyfriendly' id='" + d.TopicName + "_" + item["Label"] + type.slice(18) + "_" + "friendly'></td>";
                      out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_required subpropertyrequired" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_required" + "'></td>";
                      out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_hidden subpropertyhidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_hidden" + "'></td>";
                      out += "<td>" + type.slice(18);
                      out += "<div class='typeIsDataType'><input type='hidden' class='ExpectedType' value='" + type.slice(18) + "'>";
                      out += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'>";
                      out += "</div></td></tr>";
                    } else { // If not a basic datatype, get subproperty fields

                      out += getSubproperties(type,d,item);

                    }
                  } else { // It is a core or hidden type, display a single text field
				  	console.log('expected = 1')	
                    out += "<tr class='propertyTable'><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + " subpropertycheckbox' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                    out += "<td><input type='text' onblur='return child_settings_trigger(this)'  class='" + d.TopicName + "_friendly subpropertyfriendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                    out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_required subpropertyrequired" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_required" + "'></td>";
                    out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_hidden subpropertyhidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + type.slice(18).toLowerCase() + "_hidden" + "'></td>";
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
				console.log(typeName);
				console.log(type);
				console.log('check vals');
				console.log(linkedTopicsOnLoad);
				console.log('check vals');
				console.log("linked_topic_"+typeName);
				
                var typeIsCore = linkedTopicsOnLoad.includes("linked_topic_"+typeName);
                var typeIsHidden = hiddenTopicsOnLoad.includes("hidden_topic_"+typeName);
				console.log('typeIsCore vals');
				console.log(typeIsCore);	
				console.log('typeIsHidden');	
				console.log(typeIsHidden);	
                if ((typeIsCore == false) && (typeIsHidden == false)) { // Neither core nor hidden
                  if (dataTypes.includes(item["ExpectedTypes"].slice(18))) {
					  	console.log('expected if')	
					
                    out += "<tr><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + " subpropertycheckbox' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                    out += "<td><input type='text' onblur='return child_settings_trigger(this)'  class='" + d.TopicName + "_friendly subpropertyfriendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                    out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_required subpropertyrequired" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "_required" + "'></td>";
                    out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_hidden subpropertyhidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + item["ExpectedTypes"].slice(18).toLowerCase() + "_hidden" + "'></td>";
                    out += "<td>" + typeName;
                    out += "<div class='typeIsDataType'><input type='hidden' class='ExpectedType' value='" + typeName + "'>";
                    out += "<input type='hidden' class='schemaKey' value='" + d.TopicName + "_" + item["Label"] + "_" + item["ExpectedTypes"].slice(18) + "'>";
                    out += "</div></td></tr>";
                    //out += "<tr class='propertyTable'><td><input type='checkbox' class='" + d.TopicName + "' id='" + d.TopicName + "_" + item["Label"] + "_" + type.slice(18) + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td><td><input type='text' class='friendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td><td>" + item["ExpectedTypes"] + "</td></tr>";
                  } else { // Otherwise display all subproperties
                    out += getSubproperties(type,d,item);
                  }
                } else {
				  	console.log('expected else')	
                  // It is a core or hidden topic, display a single text field
                  out += "<tr class='propertyTable'><td class='" + first_level_propertyClass + "'><input type='checkbox' class='" + d.TopicName + " subpropertycheckbox' onclick='return child_settings_trigger(this)'  id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "'></td><td>" + item["Label"] + "</td><td>" + item["Comment"] + "</td>";
                  out += "<td><input type='text' onblur='return child_settings_trigger(this)'  class='" + d.TopicName + "_friendly subpropertyfriendly' id='" + d.TopicName + "_" + item["Label"] + "friendly'></td>";
                  out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_required subpropertyrequired" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "_required" + "'></td>";
                  out += "<td><input type='checkbox' onclick='return child_settings_trigger(this)'  class='" + d.TopicName + "_hidden subpropertyhidden" + "' id='" + d.TopicName.toLowerCase() + "_" + item["Label"].toLowerCase() + "_" + typeName.toLowerCase() + "_hidden" + "'></td>";
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
		
		$.LoadingOverlay("hide");
		//alert('Success rows have been added successfully.');
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
        var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/classes/" + d.TopicName + ".jsonld";
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
            //alert('Topic not in filesystem...give it a minute to parse schema.org.');
            $.ajax({
              url: "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + d.TopicName),
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

		console.log(output);
		console.log('output');
        return addRows(row, output);
        //return 'Detail:'+gettype(output);
        //return 'Detail: '+d.Detail+'<br>'+
        //    'The child row can contain any data you wish, including links, images, inner tables etc.';

      }



      function addPropertyRow(tableID, addPropertyCount) {
		  console.log('linkedTopicsOnLoad');
		console.log(linkedTopicsOnLoad);
		
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
		 coreTopicDropdownHTML += "<option value=''>Please Select</option>";
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
		console.log(tableID);
		
        addPropertyCount += 1;
        // Pass tableID to addPropertyRow to add a row
        addPropertyRow(tableID, addPropertyCount);

      });


      function addPropertyChange(selectID) { // Do this when additional property dropdown is changed
        // We want to update the class of the checkox on this row
		  var selectValue = $('select[id='+selectID+'] option').filter(':selected').val();
		if(selectValue!='')
		{
			 var topicName = selectID.split("_")[0];

      
        var pteCoreType = selectValue.split("_")[2];
        var propertyCount = selectID.split("_")[2];

        // Set new checkbox ID
        //$("select[id="+selectID+"]").closest("tr").find('#'+ selectID + "_checkbox").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase());
        $("select[id="+selectID+"]").closest("tr").find("[name='" + selectID + "_checkbox" + "']").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase());
        //$("select[id="+selectID+"]").closest("tr").find('#'+ selectID + "_checkbox").attr('id', "pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase());

        // Comment
        $("div[id="+selectID+"_comment"+"]").html("A " + pteCoreType + ".");

        // Friendly
        var friendlyHTML = "<input onblur='return additionalchild_settings_trigger(this)' type='text' class='friendly' id='pte_" + pteCoreType + "_" + propertyCount + "friendly'>";
        //var friendlyHTML = "<input type='text' class='friendly' id='pte_" + pteCoreType + "friendly'>";
        $("div[id="+selectID+"_friendly"+"]").html(friendlyHTML);

        // Required
        var requiredHTML = "<input onclick='return additionalchild_settings_trigger(this)' type='checkbox' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_required'>";
        //var requiredHTML = "<input type='checkbox' class='" + topicName + "_required' id='pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase() + "_required'>";
        $("div[id="+selectID+"_required"+"]").html(requiredHTML);

        // Hidden
        var hiddenHTML = "<input onclick='return additionalchild_settings_trigger(this)' type='checkbox' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + propertyCount + "_" + pteCoreType.toLowerCase() + "_hidden'>";
        //var hiddenHTML = "<input type='checkbox' class='" + topicName + "_hidden' id='pte_" + pteCoreType.toLowerCase() + "_" + pteCoreType.toLowerCase() + "_hidden'>";
        $("div[id="+selectID+"_hidden"+"]").html(hiddenHTML);

        // Type
        var typeHTML = pteCoreType + "<div class='typeIsLinked'><input type='hidden' class='ExpectedType' value='core_" + pteCoreType + "'></div>";
        typeHTML += "<div><input type='hidden' class='schemaKey' value='pte_" + pteCoreType + "_" + propertyCount + "_" + pteCoreType + "'></div>";
        //typeHTML += "<div><input type='hidden' class='schemaKey' value='pte_" + pteCoreType + "_" + pteCoreType + "'></div>";
        $("div[id="+selectID+"_type"+"]").html(typeHTML);
		}
       
      }

      // Save a JSON list when Save Linked Topics is clicked
	   $(document).on("click", "#empty_previous_configs" , function() {
		   var result = confirm("Are you sure this will delete all settings?");
		if (result) {
			 $.LoadingOverlay("show");
		var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/empty_manage_topic_settings.php";
		 $.ajax({
            url: url,
            type: "POST",
            data: {security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
            alert('All settings have been cleared successfully.');
			  $.LoadingOverlay("hide"); 
            }
          });
		}
				
		 

      });
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
		  console.log('linkedTopicsChecked')	
		  console.log(linkedTopicsChecked);	
          var hiddenTopicsChecked = dt.rows().nodes().to$().find('.hidden_topic_checkbox:checkbox:checked').map(idSelector).get();
		  
		  
		 
		 if(linkedTopicsChecked=='')
		  {
			    var pteScopeIDs = dt.rows().nodes().to$().find('.topic_class').map(idSelector).get();
				var pteScopeValues = dt.rows().nodes().to$().find('.topic_class').map(valSelector).get();
		  }
		  else
		  {
			      var pteScopeIDs = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').parent().parent().find('.topic_class').map(idSelector).get();
          var pteScopeValues = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').parent().parent().find('.topic_class').map(valSelector).get();
		  } 
       /*   var pteScopeIDs = dt.rows().nodes().to$().find('.topic_class').map(idSelector).get();
				var pteScopeValues = dt.rows().nodes().to$().find('.topic_class').map(valSelector).get(); */
		   console.log('pteScopeIDs')	
			console.log(pteScopeIDs);	
			console.log('pteScopeValues')	
			console.log(pteScopeValues);	
          // Store pteScopeID:pteScopeValue pairs
          var pteScopePairs = {};
          var i;
          for (i = 0; i < pteScopeIDs.length; i++) {
            pteScopePairs[pteScopeIDs[i]] = pteScopeValues[i];
          }
			console.log(pteScopePairs);	
          var jsonString = JSON.stringify(linkedTopicsChecked);
          var hiddenJSONString = JSON.stringify(hiddenTopicsChecked);
          var pteScopePairsString = JSON.stringify(pteScopePairs);

          // Save this JSON to server
          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveLinkedTopics.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : jsonString,security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              // Save Hidden Topics
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveHiddenTopics.php";
              $.ajax({
                url: url,
                type: "POST",
                data: {data : hiddenJSONString,security:"<?php echo $nonce ?>"},
                dataType: "json",
                complete: function(){
                  var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/savePteScopeTopics.php";
                  $.ajax({
                    url: url,
                    type: "POST",
                    data: {data : pteScopePairsString,security:"<?php echo $nonce ?>"},
                    dataType: "json",
                    complete: function(){
                      saveLinkedTopicsButton.innerHTML = "Save Topic-level Config";
				    	
					  // alert('Config has been saved successfully.');
						
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
      function saveTopicConfig(id) {
		
          var topicClass = id; // Remove "save_topic_" from topic id

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

          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/update_manage_topic_settings.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {topic_name:topicClass,value : JSON.stringify(config),field_type:'child_fields',security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
             // alert('Saving complete.');
			  
            }
          });

	  }
      // Save Config JSON when "Save Prop. Config" is clicked
      $(document).on("click", ".saveTopicConfig" , function() {
			$.LoadingOverlay("show");
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

          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/generateTopicConfig.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : JSON.stringify(config),security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
             // alert('Saving complete.');
			  	$.LoadingOverlay("hide");
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
          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/generateTopic.php";
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


	function update_manage_topic_settings(field_type,topic_name,value){
			$.ajax({
            url: '<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/update_manage_topic_settings.php',
            type: "POST",
            data: {field_type : field_type,topic_name:topic_name,value:value,security:"<?php echo $nonce ?>"},
            complete: function(){
            
            }
          });
		}
      $(document).ready(function() {

        dt = $('#classes').DataTable( {
          "processing": true,
          "ajax": {
            "url": "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/getClasses.php",
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
              "defaultContent": "<input type='text' class='friendly_name'>",
              "orderable": false },
            { "data": "topic_class",
              "orderable": false },
            { "data": "Generate",
              "orderable": false },
         
          ],
			
          "fnInitComplete": function( oSettings ) {
              // Do someting after table is drawn
              //$('.disabled').attr('disabled','disabled');

              // Check appropriate checkboxes for linked topics
              // First, get list of linked topics from server
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/prefill_manage_topic_fields.php";
              var linkedTopics;
			  var hiddenTopics;
              $.ajax({
                url: url,
                type: "POST",
                data: {security:"<?php echo $nonce ?>"},
                dataType: "json",
                async: false,
                cache: false,
                success: function(data){
					console.log('data.linked_topic');
					console.log(data.linked_topic);
					console.log('data.hidden_topic');
					console.log(data.hidden_topic);
				 
                  useReturnData(data.linked_topic);
                  useReturnDataHidden(data.hidden_topic);
                  useReturnDataFriendly(data.friendly_name);
                  useReturnDataTopicClasses(data.topic_class);
				  
                  //linkedTopics = data;
                },
                error: function() {
                 // alert('Error getting linked topics.');
                }
              });
				 function useReturnDataFriendly(data){
                   $.each(data, function(key, value){
					$('.linked_topic_checkbox').each(function(){
						linked_topic_id_string = $(this).attr('id');
						split_linked_topic_id =  linked_topic_id_string.split('_');
						linked_topic_id =  split_linked_topic_id[2];
						topic_name = value.topic_name;
						friendly_name = value.friendly_name;
						if(linked_topic_id==topic_name)
						{
						dt.rows().nodes().to$().find("#"+linked_topic_id_string).parent().parent().find(".friendly_name").val(friendly_name);

						}
						
					})
				});
              };
			  function useReturnDataTopicClasses(data){
                  topicClasses = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  topicClassesOnLoad = data;
				     $.each(topicClasses, function(key1, value1) {
		
				  key = value1.topic_name;
				  value = value1.visibility_value;
                dt.rows().nodes().to$().find("#"+key).val(value);
              });
              };

              // Fill all the topic_class fields
           
			  
              function useReturnData(data){
                  linkedTopics = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  linkedTopicsOnLoad = data;
				    $.each(linkedTopics, function(index, linked_topic_checkbox_id) {
                dt.rows().nodes().to$().find("#"+linked_topic_checkbox_id).attr("checked", true);

                //$("#"+linked_topic_checkbox_id).attr("checked", true);
              });
              };

              // Now, check all the boxes
            
			  
			  
			  function useReturnDataHidden(data){
                  hiddenTopics = data;
                  // Store list of linkedTopics to signify these in "ExpectedTypes"
                  hiddenTopicsOnLoad = data;
				     $.each(hiddenTopics, function(index, hidden_topic_checkbox_id) {
                dt.rows().nodes().to$().find("#"+hidden_topic_checkbox_id).attr("checked", true);
              });
              };

              // Now, check all the boxes
           
			  
			  
			
			  
			  
				dt.rows().nodes().to$().find(".linked_topic_checkbox").click(function(){
					 field_type = 'linked_topic';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 if($(this).is(':checked') ){
						 value = 1;
					 }
					 else {
						  value = 0;
					 }
					update_manage_topic_settings(field_type,topic_name,value);
					
				});
				dt.rows().nodes().to$().find(".hidden_topic_checkbox").click(function(){
					 field_type = 'hidden_topic';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 if($(this).is(':checked') ){
						 value = 1;
					 }
					 else {
						  value = 0;
					 }
					update_manage_topic_settings(field_type,topic_name,value);
					
				});
				dt.rows().nodes().to$().find(".topic_class").on('change',function(){
					 field_type = 'visibility';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 value = $(this).val();
					update_manage_topic_settings(field_type,topic_name,value);
					
				});
				dt.rows().nodes().to$().find("input[type='text']").blur(function(){
					 field_type = 'friendly_name';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 value = $(this).val();
					update_manage_topic_settings(field_type,topic_name,value);
				});
				
              

             
              

            }
        } );
		
        // Array to track the ids of the details displayed rows
        var detailRows = [];
		function processrows(tr) {
			
		
	
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
		}
        $('#classes tbody').on( 'click', 'tr td.details-control', function () {
			
			/* if(linkedTopicsOnLoad==null)
			{
				alert('Please click Save Topic-level Config and try again.');
				return false;
			
			} */
			var tr = $(this).closest('tr');
			$.LoadingOverlay("show");
			setTimeout(function(){ processrows(tr) }, 1000);
            
        } );

        // On each draw, loop over the `detailRows` array and show any child rows
        dt.on( 'draw', function () {
            $.each( detailRows, function ( i, id ) {
                $('#'+id+' td.details-control').trigger( 'click' );
            } );
        } );
      } );

    </script>
<?php
}
function manage_topic_types() {
	global $wpdb;

	/* $inssql = 'insert into alpn_manage_topic(topic_name,core_topic,hide_properties,friendly_name,visibility,cdate,mdate)values("Airline","1","1","Name","1","'.time().'","'.time().'")';
	echo $insdata = $wpdb->query($inssql); */
	/* $sql = 'select * from alpn_manage_topic';
	$data = $wpdb->get_results($sql);
	echo '<pre>';
	print_r($data); */

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
    <script type="text/javascript" charset="utf8" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js
"></script>

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
   <h1>Manage Topic Types</h1>  
    <div style="overflow:hidden"><button class="button" id="save_linked_topics">Save Topic-level Config</button></div>
    <div style="overflow:hidden"><button class="button" id="empty_previous_configs">Clear Topic-level Config</button></div>
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
        var url = "<?php echo $site_url ?>//wp-content/themes/memberlite-child-master/topics/topicConfig/" + d.TopicName + "_config.json";
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
            //alert('No checkboxes and friendly fields set.');
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
        var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/topicConfig/" + topicName + "_config.json";
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
            //alert('No additional properties set in config.');
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
			coreTopicDropdownHTML += "<option value=''>Please Select</option>";
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
		 console.log('getSubproperties type');
		 console.log(type);
        var output="";
        var subPropertyOutput;
        var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/classes/" + type.slice(18) + ".jsonld";
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
            url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/tester.php?url=" + "http://schema.org/" + type.slice(18);
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
	
			console.log(output);
			console.log('consoled output')
		
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
				console.log('expected type more then 1')	
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
				  	console.log('expected = 1')	
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
				console.log(typeName);
				console.log(type);
				console.log('check vals');
				console.log(linkedTopicsOnLoad);
				console.log('check vals');
				console.log("linked_topic_"+typeName);
				
                var typeIsCore = linkedTopicsOnLoad.includes("linked_topic_"+typeName);
                var typeIsHidden = hiddenTopicsOnLoad.includes("hidden_topic_"+typeName);
				console.log('typeIsCore vals');
				console.log(typeIsCore);	
				console.log('typeIsHidden');	
				console.log(typeIsHidden);	
                if ((typeIsCore == false) && (typeIsHidden == false)) { // Neither core nor hidden
                  if (dataTypes.includes(item["ExpectedTypes"].slice(18))) {
					  	console.log('expected if')	
					
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
				  	console.log('expected else')	
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
		
		$.LoadingOverlay("hide");
		//alert('Success rows have been added successfully.');
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
        var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/classes/" + d.TopicName + ".jsonld";
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
            //alert('Topic not in filesystem...give it a minute to parse schema.org.');
            $.ajax({
              url: "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/tester.php?url=" + encodeURIComponent("http://schema.org/" + d.TopicName),
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

		console.log(output);
		console.log('output');
        return addRows(row, output);
        //return 'Detail:'+gettype(output);
        //return 'Detail: '+d.Detail+'<br>'+
        //    'The child row can contain any data you wish, including links, images, inner tables etc.';

      }



      function addPropertyRow(tableID, addPropertyCount) {
		  console.log('linkedTopicsOnLoad');
		console.log(linkedTopicsOnLoad);
		
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
		 coreTopicDropdownHTML += "<option value=''>Please Select</option>";
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
		console.log(tableID);
		
        addPropertyCount += 1;
        // Pass tableID to addPropertyRow to add a row
        addPropertyRow(tableID, addPropertyCount);

      });


      function addPropertyChange(selectID) { // Do this when additional property dropdown is changed
        // We want to update the class of the checkox on this row
		  var selectValue = $('select[id='+selectID+'] option').filter(':selected').val();
		if(selectValue!='')
		{
			 var topicName = selectID.split("_")[0];

      
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
       
      }

      // Save a JSON list when Save Linked Topics is clicked
	   $(document).on("click", "#empty_previous_configs" , function() {
		   var result = confirm("Are you sure this will empty all configs?");
		if (result) {
			 $.LoadingOverlay("show");
      
		jsonString = [];
          // Save this JSON to server
          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveLinkedTopics.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : jsonString,security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              // Save Hidden Topics
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveHiddenTopics.php";
              $.ajax({
                url: url,
                type: "POST",
                data: {data : jsonString,security:"<?php echo $nonce ?>"},
                dataType: "json",
                complete: function(){
                  var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/savePteScopeTopics.php";
                  $.ajax({
                    url: url,
                    type: "POST",
                    data: {data : jsonString,security:"<?php echo $nonce ?>"},
                    dataType: "json",
                    complete: function(){
                     
				    	$.LoadingOverlay("hide");
					  // alert('Config has been emptied successfully.');
						
                    }
                  });
                 
                }
              });
            }
          });

          // Update global vars
          linkedTopicsOnLoad = null
          hiddenTopicsOnLoad = null;
          topicClassesOnLoad = null;
		}
				
		 

      });
      $(document).on("click", "#save_linked_topics" , function() {
		 $.LoadingOverlay("show");
          // Show a loading icon
          var saveLinkedTopicsButton = document.querySelector('#save_linked_topics');
          saveLinkedTopicsButton.innerHTML = "Saving...";
          //var topicClass = this.id.slice(10); // Remove "add_topic_" from topic id

          // Get checkboxes under this topic class that are checked
          var idSelector = function() { return this.id; };
          var valSelector = function() { return this.value; };
		
          //var linkedTopicsChecked = $(".linked_topic_checkbox:checkbox:checked").map(idSelector).get();
          var linkedTopicsChecked = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').map(idSelector).get();
		  console.log('linkedTopicsChecked')	
		  console.log(linkedTopicsChecked);	
          var hiddenTopicsChecked = dt.rows().nodes().to$().find('.hidden_topic_checkbox:checkbox:checked').map(idSelector).get();
		  
		  
		 
		 if(linkedTopicsChecked=='')
		  {
			    var pteScopeIDs = dt.rows().nodes().to$().find('.topic_class').map(idSelector).get();
				var pteScopeValues = dt.rows().nodes().to$().find('.topic_class').map(valSelector).get();
		  }
		  else
		  {
			      var pteScopeIDs = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').parent().parent().find('.topic_class').map(idSelector).get();
          var pteScopeValues = dt.rows().nodes().to$().find('.linked_topic_checkbox:checkbox:checked').parent().parent().find('.topic_class').map(valSelector).get();
		  } 
       /*   var pteScopeIDs = dt.rows().nodes().to$().find('.topic_class').map(idSelector).get();
				var pteScopeValues = dt.rows().nodes().to$().find('.topic_class').map(valSelector).get(); */
		   console.log('pteScopeIDs')	
			console.log(pteScopeIDs);	
			console.log('pteScopeValues')	
			console.log(pteScopeValues);	
          // Store pteScopeID:pteScopeValue pairs
          var pteScopePairs = {};
          var i;
          for (i = 0; i < pteScopeIDs.length; i++) {
            pteScopePairs[pteScopeIDs[i]] = pteScopeValues[i];
          }
			console.log(pteScopePairs);	
          var jsonString = JSON.stringify(linkedTopicsChecked);
          var hiddenJSONString = JSON.stringify(hiddenTopicsChecked);
          var pteScopePairsString = JSON.stringify(pteScopePairs);

          // Save this JSON to server
          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveLinkedTopics.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : jsonString,security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
              // Save Hidden Topics
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/saveHiddenTopics.php";
              $.ajax({
                url: url,
                type: "POST",
                data: {data : hiddenJSONString,security:"<?php echo $nonce ?>"},
                dataType: "json",
                complete: function(){
                  var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/savePteScopeTopics.php";
                  $.ajax({
                    url: url,
                    type: "POST",
                    data: {data : pteScopePairsString,security:"<?php echo $nonce ?>"},
                    dataType: "json",
                    complete: function(){
                      saveLinkedTopicsButton.innerHTML = "Save Topic-level Config";
				    	$.LoadingOverlay("hide");
					  // alert('Config has been saved successfully.');
						
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
		 
			console.log('linkedTopicsOnLoad');
			console.log(linkedTopicsOnLoad);
			console.log('hiddenTopicsOnLoad');
			console.log(hiddenTopicsOnLoad);
			console.log('topicClassesOnLoad');
			console.log(topicClassesOnLoad);
      });

      // Save Config JSON when "Save Prop. Config" is clicked
      $(document).on("click", ".saveTopicConfig" , function() {
			$.LoadingOverlay("show");
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

          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/generateTopicConfig.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : JSON.stringify(config),security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
             // alert('Saving complete.');
			  	$.LoadingOverlay("hide");
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
           // alert("Please enter a friendly topic name.");
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
          var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/generateTopic.php";
          $.ajax({
            url: url,
            type: "POST",
            data: {data : JSON.stringify(fileContents),security:"<?php echo $nonce ?>"},
            dataType: "json",
            complete: function(){
             // alert('Saving complete.');
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


	function update_manage_topic_settings(field_type,topic_name,value){
			$.ajax({
            url: '<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/update_manage_topic_settings.php',
            type: "POST",
            data: {field_type : field_type,topic_name:topic_name,value:value,security:"<?php echo $nonce ?>"},
            complete: function(){
            
            }
          });
		}
      $(document).ready(function() {

        dt = $('#classes').DataTable( {
          "processing": true,
          "ajax": {
            "url": "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/getClasses.php",
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
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/linkedTopicConfig.json";
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
                 // alert('Error getting linked topics.');
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
				dt.rows().nodes().to$().find(".linked_topic_checkbox").click(function(){
					 field_type = 'linked_topic';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 if($(this).is(':checked') ){
						 value = 1;
					 }
					 else {
						  value = 0;
					 }
					//update_manage_topic_settings(field_type,topic_name,value);
					$('#save_linked_topics').click();
				});
				dt.rows().nodes().to$().find(".hidden_topic_checkbox").click(function(){
					 field_type = 'hidden_topic';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 if($(this).is(':checked') ){
						 value = 1;
					 }
					 else {
						  value = 0;
					 }
					//update_manage_topic_settings(field_type,topic_name,value);
					$('#save_linked_topics').click();
				});
				dt.rows().nodes().to$().find(".topic_class").on('change',function(){
					 field_type = 'visibility';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 value = $(this).val();
					//update_manage_topic_settings(field_type,topic_name,value);
					$('#save_linked_topics').click();
				});
				dt.rows().nodes().to$().find("input[type='text']").blur(function(){
					 field_type = 'friendly_name';
					 topic_name = $(this).parent().parent().find("td:eq(1)").text();
					 value = $(this).val();
					//update_manage_topic_settings(field_type,topic_name,value);
				});
				
              // Get all topics whose properties we don't want to expand
              var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/hiddenTopicConfig.json";
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
                //  alert('Error getting hidden topics.');
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
               var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/topics/pteScopeConfig.json";
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
                //  alert('Error getting topic class data.');
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
		function processrows(tr) {
			
		
	
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
		}
        $('#classes tbody').on( 'click', 'tr td.details-control', function () {
			
			if(linkedTopicsOnLoad==null)
			{
				alert('Please click Save Topic-level Config and try again.');
				return false;
			
			}
			var tr = $(this).closest('tr');
			$.LoadingOverlay("show");
			setTimeout(function(){ processrows(tr) }, 1000);
            
        } );

        // On each draw, loop over the `detailRows` array and show any child rows
        dt.on( 'draw', function () {
            $.each( detailRows, function ( i, id ) {
                $('#'+id+' td.details-control').trigger( 'click' );
            } );
        } );
      } );

    </script>

<?php
}
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

  for ($i = 0; $i < count($items); $i++) {;
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