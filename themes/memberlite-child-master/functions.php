<?php
/**
 * Memberlite - Child Theme functions and definitions
 *
 * @package Memberlite 2.0
 * @subpackage Memberlite - Child Theme 1.0
 */
 /* Created by Abstain Solutions 22-12-2020 */
/* function wpf_dev_process_before( $entry, $form_data ) {
 
    echo '<pre>';
		print_r($entry);
		die;
 
    // run code
} */
function wpf_dev_process_before( $entry, $form_data ) {
 
	$passed = 0;
	$nonce = '';
	if(isset($_POST['wp_form_nonce']) && !empty($_POST['wp_form_nonce']))
	{
		$nonce = $_POST['wp_form_nonce'];
		$verify = wp_verify_nonce($nonce, 'wp_form_nonce' );
		if($verify==1) {
			$passed = 1;
		}
	}
	if($passed==0)
		return wp_redirect(site_url().'/invalid-request');
	else 
		return;
   
}
add_action( 'wpforms_process_before', 'wpf_dev_process_before', 10, 2 );
function wpf_dev_display_submit_before( $form_data ) {
  
   // return wp_nonce_field( 'wp_form_nonce', 'wp_form_nonce' );
}
add_action( 'wpforms_display_submit_before', 'wpf_dev_display_submit_before', 10, 1 );

add_action( 'wpforms_process_before', 'wpf_dev_process_before', 10, 2 );
$cookie_name = 'pmpro_visit';
$cookie_value = '0';
$cookie_secure = 'secure';
$cookie_httponly = 'HTTPOnly';


setcookie($cookie_name, $cookie_value, (time()+3600), '/', 'https://alct.pro', $cookie_secure, $cookie_httponly);

include('alpn-shortcodes.php');
include('alpn_common.php');
include('alpn_data.php');
//include('pte_interactions.php');
include('pte_messaging.php');

$wpdb_readonly = new wpdb(DB_USER,DB_PASSWORD,DB_NAME,'sky0001654.mdb0001643.db.skysql.net:5003');  //TODO use this connection anywhere that isn't susceptible to master/slave, add/update lag. Coming from add/update, pass in JSON. use different connection

function pte_dequeue_unnecessary_styles() {
    wp_dequeue_style( 'font-awesome' );
    wp_deregister_style( 'font-awesome' );
}
add_action( 'wp_print_styles', 'pte_dequeue_unnecessary_styles' );

//Enqueue scripts and styles.
function memberlite_child_enqueue_styles() {
	  wp_enqueue_style( 'pte_font_awesome', get_template_directory_uri() . '-child-master/fa/css/all.min.css');
    wp_enqueue_style( 'pte_foxit_pdf', get_template_directory_uri() . '-child-master/foxitpdf/lib/UIExtension.css' );
    wp_enqueue_style( 'memberlite', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'memberlite_child_enqueue_styles' );

//Child theme inherits parent theme settings - based on code by @greenshady from https://core.trac.wordpress.org/ticket/27177#comment:14
function memberlite_child_switch_theme_update_mods() {
	if ( is_child_theme() && false === get_theme_mods() ) {
		$mods = get_option( 'theme_mods_' . get_option( 'template' ) );
		if ( false !== $mods ) {
			foreach ( (array) $mods as $mod => $value ) {
				if ( 'sidebars_widgets' !== $mod )
					set_theme_mod( $mod, $value );
			}
		}
	}
}
add_action( 'switch_theme', 'memberlite_child_switch_theme_update_mods' );

function alpn_select2_enqueue_styles() {

    wp_enqueue_style( 'alpn_uppy', 'https://transloadit.edgly.net/releases/uppy/v1.18.0/uppy.min.css' );
    wp_enqueue_style( 'pte_doka', get_template_directory_uri() . '-child-master/doka/bin/browser/doka.min.css');
    wp_enqueue_style( 'alpn_select2', get_template_directory_uri() . '-child-master/dist/css/select2.min.css' );
		wp_enqueue_style( 'alpn_select2_bootstrap', get_template_directory_uri() . '-child-master/dist/css/select2-bootstrap.css' );
}
add_action( 'wp_enqueue_scripts', 'alpn_select2_enqueue_styles' );

//CSS handled by the template

function alpn_load_script(){
/*
  wp_register_script(
      'alpn_twilio_sync',
      '//media.twiliocdn.com/sdk/js/sync/v0.8/twilio-sync.min.js',
      array()
  );
  wp_enqueue_script( 'alpn_twilio_sync' );
*/

    wp_register_script(
        'alpn_twilio_sync',
        '//media.twiliocdn.com/sdk/js/sync/v0.8/twilio-sync.min.js',
        array()
    );
    wp_enqueue_script( 'alpn_twilio_sync' );

    wp_register_script(
        'alpn_select2',
        get_template_directory_uri() . '/../memberlite-child-master/dist/js/select2.min.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_select2' );

	wp_register_script(   //TODO Remover WAITME?
        'alpn_indicator',
        get_template_directory_uri() . '/../memberlite-child-master/dist/js/progressbar.min.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_indicator' );

    wp_register_script(   //TODO Remover WAITME?
          'alpn_md5',
          get_template_directory_uri() . '/../memberlite-child-master/dist/js/md5.min.js',
          array( 'jquery' )
      );
      wp_enqueue_script( 'alpn_md5' );

	wp_register_script(
  	'alpn_date',
  	'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.9.1/dayjs.min.js',
  	array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_date' );

    wp_register_script(
    	'alpn_date_utc',
    	'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.9.1/plugin/utc.min.js',
    	array( 'alpn_date' )
      );
      wp_enqueue_script( 'alpn_date_utc' );

    wp_register_script(
        'alpn_script',
        get_template_directory_uri() . '/../memberlite-child-master/alpn_client.js',
        array( 'memberlite-script' )
    );
	// Create any data in PHP that we may need to use in our JS file
    $local_arr = array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'security'  => wp_create_nonce( 'alpn_script' )
    );
	 wp_localize_script( 'alpn_script', 'specialObj', $local_arr );
    wp_enqueue_script( 'alpn_script' );

    wp_register_script(
        'alpn_uppy',
        'https://transloadit.edgly.net/releases/uppy/v1.22.0/uppy.min.js',
        array( 'memberlite-script' )
    );
    wp_enqueue_script( 'alpn_uppy' );

    wp_register_script(
        'alpn_doka',
        get_template_directory_uri() . '-child-master/doka/bin/jquery/doka.jquery.min.js',
        array( 'alpn_uppy' )
    );
    wp_enqueue_script( 'alpn_doka' );

    wp_register_script(
        'alpn_tinymce_editor',
				get_template_directory_uri() . '/../memberlite-child-master/dist/js/tinymce/tinymce.min.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_tinymce_editor' );

    if (!is_admin()) {

      wp_register_script(   //TODO Remover WAITME?
            'pte_print_page',
            get_template_directory_uri() . '/../memberlite-child-master/dist/js/jquery.printPage.js',
            array( 'jquery' )
        );
        wp_enqueue_script( 'pte_print_page' );

      	wp_register_script(
              'pte_pdf_license',
      		 get_template_directory_uri() . '/../memberlite-child-master/foxitpdf/examples/license-key.js',
      		array( 'jquery' )
          );
          wp_enqueue_script( 'pte_pdf_license' );

      	wp_register_script(
              'pte_pdf_preloader_core',
      		 get_template_directory_uri() . '/../memberlite-child-master/foxitpdf/lib/preload-jr-worker.js',
      		array( 'pte_pdf_license' )
          );
          wp_enqueue_script( 'pte_pdf_preloader_core' );

      	wp_register_script(
              'pte_ui_extension',
      		 get_template_directory_uri() . '/../memberlite-child-master/foxitpdf/lib/UIExtension.full.js',
      		array( 'pte_pdf_preloader_core' )
          );
          wp_enqueue_script( 'pte_ui_extension' );
    }

}
add_action('wp_enqueue_scripts', 'alpn_load_script');
add_action('admin_enqueue_scripts', 'alpn_load_script');

//Profile Settings

add_filter('show_admin_bar', '__return_false'); //Remove top bar

/*

function set_displayname_as_firstname( $user_id )
{
    $data = get_userdata( $user_id );

    if ($data->display_name != $data->first_name) {
        wp_update_user( array ('ID' => $user_id, 'display_name' =>  $data->first_name));
    }
}




function alpn_meta_tags() {
    echo '<meta name="google-site-verification" content="xf6Va1HR87gZJylvcy95OjB5rytoLUbVeT32DRGSsu8" />';
}
add_action('wp_head', 'alpn_meta_tags');




//Paid Membership Pro Mods
add_filter("pmpro_checkout_confirm_email", "__return_false");  //remove email confirmation on login.
function my_gettext_membership( $output_text, $input_text, $domain ) { //Change "Membership" to "Subscription"
	if ( ! is_admin() && 'paid-memberships-pro' === $domain ) {
		$output_text = str_replace( 'Membership Level', 'Subscription', $output_text );
		$output_text = str_replace( 'membership level', 'subscription', $output_text );
		$output_text = str_replace( 'membership', 'subscription', $output_text );
		$output_text = str_replace( 'Membership', 'Subscription', $output_text );
	}
	return $output_text;
}
add_filter( 'gettext', 'my_gettext_membership', 10, 3 );

//add_action( 'user_register', 'set_displayname_as_firstname' );
//add_action( 'profile_update', 'set_displayname_as_firstname' );



	$uploads['subdir'] = '/tml-avatars';
	$uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
	$uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];


*/
function ptc_pmpro_email_filter($email) {  //Adds our template around email content.

		alpn_log("ptc_pmpro_email_filter");
    $emailTemplateName = PTE_ROOT_PATH . "email_templates/pte_email_template_1.html";
    $emailTemplateHtml = file_get_contents($emailTemplateName);

    $replaceStrings["-{pte_email_body}-"] = $email->body;
    $replaceStrings["-{pte_link_id}-"] = "";
		$replaceStrings["-{pte_email_file_details}-"] = "";
    $replaceStrings["-{pte_email_signature}-"] = "";
    $emailTemplateHtml = str_replace(array_keys($replaceStrings), $replaceStrings, $emailTemplateHtml);

    $email->body = $emailTemplateHtml;

    return $email;
}
add_filter("pmpro_email_filter", "ptc_pmpro_email_filter");


// Use the email address as username with PMPro checkout. Also hide fields where necessary
function my_init_email_as_username()
{
  //check for level as well to make sure we're on checkout page
  if(empty($_REQUEST['level']))
    return;

  if(!empty($_REQUEST['bemail']))
    $_REQUEST['username'] = $_REQUEST['bemail'];

  if(!empty($_POST['bemail']))
    $_POST['username'] = $_POST['bemail'];

  if(!empty($_GET['bemail']))
    $_GET['username'] = $_GET['bemail'];
}
add_action('init', 'my_init_email_as_username');

function remove_tml_profile_fields() {
	tml_remove_form_field( 'profile', 'first_name' );
	tml_remove_form_field( 'profile', 'last_name' );
	tml_remove_form_field( 'profile', 'user_login' );
	tml_remove_form_field( 'profile', 'personal_options_section_header' );
	tml_remove_form_field( 'profile', 'admin_bar_front' );
	tml_remove_form_field( 'profile', 'name_section_header' );
	tml_remove_form_field( 'profile', 'display_name' );
	tml_remove_form_field( 'profile', 'contact_info_section_header' );
	tml_remove_form_field( 'profile', 'url' );
	tml_remove_form_field( 'profile', 'about_yourself_section_header' );
	tml_remove_form_field( 'profile', 'description' );
  tml_remove_form_field( 'profile', 'account_management_section_header' );
}
add_action( 'init', 'remove_tml_profile_fields' );

add_filter("pmpro_checkout_confirm_email", "__return_false");  //remove email confirmation on login.
add_filter("pmpro_checkout_confirm_password", "__return_false");  //remove email confirmation on login.


function sync_alpn_user_info_on_register ($user_id) {   //Runs on New User. Sets up default topics and sample data.
	global $wpdb;
	$now = date ("Y-m-d H:i:s", time());
  $formId = pte_create_default_topics($user_id, true);   //with sample data

	$entry = array(
		'id' => $formId,  //source user template type  Using custom TT
		'new_owner' => $user_id,
		'fields' => array()
	);
	alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user


}
add_action('user_register', 'sync_alpn_user_info_on_register');
//add_action('profile_update', 'sync_alpn_user_info_on_register');

//Database


function alpn_handle_topic_add_edit ($fields, $entry, $form_data, $entry_id ) { //Add record to topic with special handling for user and network

  global $wpdb;
  $returnDetails = array();

  alpn_log("alpn_handle_topic_add_edit");
  alpn_log($fields);

  $fieldsAll = $fields;
  $row_id = $userEmail = '';

	$formId = $entry['id'];
  $fields = $entry['fields'];

  alpn_log($fields);


  //alpn_log('alpn_handle_topic_add_edit_TEXTAREAFIELDS');
  //alpn_log($fieldsAll);

	if (isset($entry['new_owner'])) {
		$userId = $entry['new_owner'];
    $userInfo = get_userdata($userId);
    $userEmail = $userInfo->user_email;
	} else {
		$userInfo = wp_get_current_user();
		$userId = $userInfo->data->ID;
		$userEmail = $userInfo->data->user_email;
	}

  $type = 'form';
	$now = date ("Y-m-d H:i:s", time());
	switch ($type) {
		case 'form':
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT id, name, topic_type_meta, icon, special, schema_key FROM alpn_topic_types WHERE form_id = %s", $formId)
			 );
			if (isset($results[0])) {
				$topicType = $results[0];
        $topicTypeId = $topicType->id;
        $topicSchemaKey = $topicType->schema_key;
				$topicTypeSpecial = $topicType->special;
				$topicTypeMeta = json_decode($topicType->topic_type_meta, true);
				$alpnNormalizeMap = pte_map_extract($topicTypeMeta['field_map']);
				$alpnNormalizeMapFlipped = array_flip($alpnNormalizeMap);
				$nameSource = $topicTypeMeta['alpn_name_source'];
				$aboutSource = $topicTypeMeta['alpn_about_source'];

				if (isset($fields[0])) { //metadata carried with form
					$field = json_decode($fields[0], true);
          $returnDetails = isset($field['return_details']) ? $field['return_details'] : array();
          $row_id = isset($field['row_id']) ? $field['row_id'] : 0;
				}

				$mappedFields = array();
				foreach($alpnNormalizeMapFlipped as $key => $value) {
					if (isset($fields[$key])) {
						$mappedFields[$alpnNormalizeMapFlipped[$key]] = $fields[$key];
					} else {
						$mappedFields[$alpnNormalizeMapFlipped[$key]] = '';
					}
				}

				if (isset($entry['new_owner'])) {
					$topicName = "New Member";
					$topicAbout = "";
				} else if ($fields) {
					$topicName = pte_make_string($nameSource, $fields, $alpnNormalizeMap);
					$topicAbout = pte_make_string($aboutSource, $fields, $alpnNormalizeMap);
				} else {
          $topicName = '';
          $topicAbout = '';
        }

				$last_topic_id = "";
        $altId = ""; //email address for type 4 topics contact and topicid
				$last_record_id = array();
				$topicMeta = array();

				if ($topicTypeSpecial == 'contact') { //Network
          $altId = $mappedFields['person_email'];  //Email
				} else if ($topicTypeSpecial == 'user') { //User
					$mappedFields['person_email'] = $userEmail;  //sets user profile email to WP email
				}

				//TODO Topic Meta. Network: user-editable status: prospect, active, trusted (need better name). Topic: type.
				$topicData = array("alt_id" => $altId, "modified_date" => $now, "owner_id" => $userId, "topic_type_id" => $topicTypeId, "special" => $topicTypeSpecial, "name" => $topicName, "about" => $topicAbout, "topic_content" => json_encode($mappedFields), "topic_meta" => json_encode($topicMeta));

				//TODO topics: lotsa other stats.

				if ($row_id) { //EDIT

          if ($topicSchemaKey == "Person") {  //Recalc the pretty first letter icon placeholder but only if the image isn't an icon yet. TODO any way to know this without hitting db again?
            $userRow = $wpdb->get_results(
              $wpdb->prepare("SELECT image_handle FROM alpn_topics WHERE id = %d", $row_id)
             );
            if (isset($userRow[0])) {
              $currentImageHandle = $userRow[0]->image_handle;
              if ($currentImageHandle == "" || substr($currentImageHandle, 0, 16) == "pte_icon_letter_") {
                $topicData['image_handle'] = "pte_icon_letter_" . strtolower(substr($mappedFields['person_givenname'], 0, 1)) . ".png";
              }
            }
          }
					$topicData['last_op'] = "edit";
					$whereClause['id'] = $row_id;
					$wpdb->update( 'alpn_topics', $topicData, $whereClause );
					$last_record_id['last_topic_add_id'] = $row_id;

          if ($topicTypeSpecial == 'contact') { //Update connection
            $topicData['contact_topic_id'] = $row_id;
            $topicData['contact_email'] = $mappedFields['person_email'];
            $topicData['owner_wp_id'] = $userId;
            pte_manage_user_connection($topicData);
          }
          if ($topicTypeSpecial == 'user') { //update user

            $data['user_id'] = $userId;
            $data['topic_id'] = $row_id;
            $data['topic_name'] = isset($mappedFields['person_givenname']) && $mappedFields['person_givenname'] ? $mappedFields['person_givenname'] : "Welcome";
            pte_manage_cc_groups("update_user", $data);

            //update_user_meta( $userId, "pte_user_network_id",  $row_id); //SH  TODO probably don't need this so I commented it. Because once ID, always id.
            $data = array(
              "sync_type" => "return_create_sync_id",
              "sync_payload" => array()
            );
            pte_manage_user_sync($data);
          }
				} else { //add
          if ($topicSchemaKey == "Person") {$topicData['image_handle'] = "pte_icon_letter_n.png";}  //Dor new user until they edit
          $data = array();
          $topicData['last_op'] = "add";
          $topicData['created_date'] = $now;
          $wpdb->insert( 'alpn_topics', $topicData );
          $row_id = $wpdb->insert_id;
          $last_record_id['last_topic_add_id'] = $row_id;

          //TODO Use Sync to send update to client.

          $isLinkTopic = isset($returnDetails['return_to']) && (substr($returnDetails['return_to'], 0, 5) == "core_");

          if ($isLinkTopic) {  //Add Topic Link
            $subjectToken = $returnDetails['subject_token'];
            $requestData = array(
            	'owner_id' => $userId,
            	'topic_id' => $returnDetails['topic_id'],
            	'connected_id' => $userId,
            	'connection_link_topic_id' => $row_id
            );
            $returnDetails['new_connected_link_details'] = pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData, $subjectToken);
          }

          if ($topicTypeSpecial == 'contact') { //Update connection
            $topicData['contact_topic_id'] = $row_id;
            $topicData['contact_email'] = $mappedFields['person_email'];
            $topicData['owner_wp_id'] = $userId;
            pte_manage_user_connection($topicData);
          }

          if ($topicTypeSpecial == 'user') { //Add user but don't create CHAT channels or CHAT members -- JIT
            $data['owner_id'] = $userId;
            $data['topic_id'] = $row_id;
            $data['topic_name'] = isset($mappedFields['person_givenname']) && $mappedFields['person_givenname'] ? $mappedFields['person_givenname'] : "Welcome";
            pte_manage_cc_groups("add_user", $data);
            update_user_meta( $userId, "pte_user_network_id",  $row_id);
            $data = array(
              "sync_type" => "return_create_sync_id",
              "sync_user_id" => $userId,
              "sync_payload" => array()
            );
            pte_manage_user_sync($data);
				}
      }
				//Update last record metadata for UI/UX purposes
				$last_record_id['id'] = $userId;
        $last_record_id['last_return_to'] = json_encode($returnDetails);
				$wpdb->replace( 'alpn_user_metadata', $last_record_id );
				}
		break;
    }
    return $row_id;

	} // no topic info
add_action( 'wpforms_process_complete', 'alpn_handle_topic_add_edit', 10, 4 );

?>
