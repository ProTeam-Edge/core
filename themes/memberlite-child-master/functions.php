<?php
/**
 * Memberlite - Child Theme functions and definitions
 *
 * @package Memberlite 2.0
 * @subpackage Memberlite - Child Theme 1.0
 */

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

    wp_enqueue_style( 'alpn_uppy', 'https://transloadit.edgly.net/releases/uppy/v1.14.1/uppy.min.css' );
    wp_enqueue_style( 'alpn_doka', get_template_directory_uri() . '-child-master/doka/bin/browser/doka.min.css' );
    wp_enqueue_style( 'alpn_select2', get_template_directory_uri() . '-child-master/dist/css/select2.min.css' );
    wp_enqueue_style( 'alpn_select2_bootstrap', get_template_directory_uri() . '-child-master/dist/css/select2-bootstrap.css' );
    wp_enqueue_style( 'alpn_wait', get_template_directory_uri() . '-child-master/wait/waitMe.min.css' );
}
add_action( 'wp_enqueue_scripts', 'alpn_select2_enqueue_styles' );

//CSS handled by the template

function alpn_load_script(){

  wp_register_script(
      'alpn_twilio_sync',
      '//media.twiliocdn.com/sdk/js/sync/v0.8/twilio-sync.min.js',
      array( )
  );
  wp_enqueue_script( 'alpn_twilio_sync' );

    wp_register_script(
        'alpn_select2',
        get_template_directory_uri() . '/../memberlite-child-master/dist/js/select2.min.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_select2' );

	wp_register_script(
        'alpn_wait',
        get_template_directory_uri() . '/../memberlite-child-master/wait/waitMe.min.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_wait' );

	wp_register_script(
  	'alpn_date',
  	'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.8.20/dayjs.min.js',
  	array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_date' );

    wp_register_script(
        'alpn_script',
        get_template_directory_uri() . '/../memberlite-child-master/alpn_client.js',
        array( 'memberlite-script' )
    );
    wp_enqueue_script( 'alpn_script' );

    wp_register_script(
        'alpn_uppy',
        'https://transloadit.edgly.net/releases/uppy/v1.14.1/uppy.min.js',
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
        'alpn_cloud_sponge',
        'https://api.cloudsponge.com/widget/3hpw5Va8RoQVbrAimOVwwg.js',
        array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_cloud_sponge' );

	wp_register_script(
        'pte_pdf_adaptive',
		 get_template_directory_uri() . '/../memberlite-child-master/foxitpdf/lib/adaptive.js',
		array( 'jquery' )
    );
    wp_enqueue_script( 'pte_pdf_adaptive' );

	wp_register_script(
        'pte_pdf_license',
		 get_template_directory_uri() . '/../memberlite-child-master/foxitpdf/examples/license-key.js',
		array( 'pte_pdf_adaptive' )
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


/*
personal_options_section_header
admin_bar_front
locale
name_section_header
user_login
first_name
last_name
nickname
display_name
contact_info_section_header
email
url
aim (As a result of wp_get_user_contact_methods())
yim (As a result of wp_get_user_contact_methods())
jabber (As a result of wp_get_user_contact_methods())
about_yourself_section_header
description
avatar
account_management_section_header
pass1
pass2
show_user_profile (This is the action hook)
submit

*/


add_filter("pmpro_checkout_confirm_email", "__return_false");  //remove email confirmation on login.


function sync_alpn_user_info_on_register ($user_id) {

	global $wpdb;
	$now = date ("Y-m-d H:i:s", time());

	$entry = array(
		'id' => '1104',  //source user template type
		'new_owner' => $user_id,
		'fields' => array()
	);
	alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user

	//add default forms
	$wpdb->query(
			"INSERT INTO alpn_forms
            (owner_id, form_id, sort_order)
            VALUES
            ('{$user_id}', '2', '0'),
            ('{$user_id}', '5', '100')
			");
}
add_action('user_register', 'sync_alpn_user_info_on_register');
//add_action('profile_update', 'sync_alpn_user_info_on_register');

//Database


function alpn_handle_topic_add_edit ($fields, $entry, $form_data, $entry_id ) { //Add record to topic with special handling for user and network

	global $wpdb;
	$pteUserTimezoneOffset = 0;

	$formId = $entry['id'];

	if (isset($entry['new_owner'])) {
		$userId = $entry['new_owner'];
	} else {
		$userInfo = wp_get_current_user();
		$userId = $userInfo->data->ID;
		$userEmail = $userInfo->data->user_email;
	}
	$formId = $entry['id'];
	$fields = $entry['fields'];

	$formData = json_decode($form_data['settings']['form_desc'], true);
	$type = isset($formData['type']) ? $formData['type'] : 'form';

	$now = date ("Y-m-d H:i:s", time());
	switch ($type) {
		case 'list':
		case 'formlet':
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT id, name, meta FROM alpn_topic_tabs WHERE form_id = %s", $formId)
			 );
			if (isset($results[0])) {
				$result = $results[0];
				$tabId = $result->id;
				$tabName = $result->name;
				$tabMeta = json_decode($result->meta, true);
				$topicId = $row_id = "";
				$hiddenFieldId = $tabMeta['pte.meta'];
				$alpnNormalizeMap = $tabMeta['field_map'];
				$alpnNormalizeMapFlipped = array_flip($alpnNormalizeMap);
				$aboutSource = $tabMeta['alpn_about_source'];
				$nameSource = $tabMeta['alpn_name_source'];
				$sortSource = $tabMeta['alpn_sort_source'];

				if (isset($fields[$hiddenFieldId])) {
					$field = json_decode($fields[$hiddenFieldId], true);
					if (isset($field['topic_id'])) {
						$topicId = $field['topic_id'];
					}
					if (isset($field['row_id'])) {
						$row_id = $field['row_id'];
					}
					if (isset($field['tab_type_id'])) {
						$tabTypeId = $field['tab_type_id'];
					}
					if (isset($field['pte_user_timezone_offset'])) {
						$pteUserTimezoneOffset = $field['pte_user_timezone_offset'];
					}
				}
				$mappedFields = array();
				foreach($alpnNormalizeMapFlipped as $key => $value) {
					if (isset($fields[$key])) {
						$mappedFields[$alpnNormalizeMapFlipped[$key]] = $fields[$key];
					} else {
						$mappedFields[$alpnNormalizeMapFlipped[$key]] = '';
					}
				}
				$topicName = pte_make_string($nameSource, $fields, $alpnNormalizeMap, $pteUserTimezoneOffset);
				$topicAbout = pte_make_string($aboutSource, $fields, $alpnNormalizeMap, $pteUserTimezoneOffset);
				$topicSort = pte_make_string($sortSource, $fields, $alpnNormalizeMap, $pteUserTimezoneOffset);
				$topicData = array("modified_date" => $now, "topic_id" => $topicId, "owner_id" => $userId, "name" => $topicName, "description" => $topicAbout, "content" => json_encode($mappedFields), "tab_type_id" => $tabTypeId, "sort_value" => $topicSort);

				if ($row_id) { //edit
					$whereClause['id'] = $row_id;
					$wpdb->update( 'alpn_topic_items', $topicData, $whereClause );
				} else { //add
					$wpdb->insert( 'alpn_topic_items', $topicData );
					$row_id = $wpdb->insert_id;
				}
			}
		break;

		case 'form':

			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT id, name, topic_type_meta, icon FROM alpn_topic_types WHERE form_id = %s", $formId)
			 );

			if (isset($results[0])) {

				$topicType = $results['0'];
				$topicTypeId = $topicType->id;
				$topicTypeMeta = json_decode($topicType->topic_type_meta, true);
				$alpnNormalizeMap = $topicTypeMeta['field_map'];
				$alpnNormalizeMapFlipped = array_flip($alpnNormalizeMap);
				$dbTableName = $topicTypeMeta['alpn_db_table_name'];
				$hiddenFieldId = $topicTypeMeta['pte.meta'];
				$nameSource = $topicTypeMeta['alpn_name_source'];
				$aboutSource = $topicTypeMeta['alpn_about_source'];

				if (isset($fields[$hiddenFieldId])) {
					$field = json_decode($fields[$hiddenFieldId], true);
					if (isset($field['row_id'])) {
						$row_id = $field['row_id'];
					}
					if (isset($field['pte_user_timezone_offset'])) {
						$pteUserTimezoneOffset = $field['pte_user_timezone_offset'];
					}
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
				} else {
					$topicName = pte_make_string($nameSource, $fields, $alpnNormalizeMap, $pteUserTimezoneOffset);
					$topicAbout = pte_make_string($aboutSource, $fields, $alpnNormalizeMap, $pteUserTimezoneOffset);
				}

				$last_topic_id = "";
        $altId = ""; //email address for type 4 topics contact and topicid
				$last_record_id = array();
				$topicMeta = array();

				if ($topicTypeId == '4') { //Network
          $altId = $mappedFields['patient.telecom.1.value'];  //Email
				} else if ($topicTypeId == '5') { //User
					$mappedFields['patient.telecom.1.value'] = $userEmail;  //sets user profile email to WP email
				}

				//TODO Topic Meta. Network: user-editable status: prospect, active, trusted (need better name). Topic: type.
				$topicData = array("alt_id" => $altId, "modified_date" => $now, "owner_id" => $userId, "topic_type_id" => $topicTypeId, "name" => $topicName, "about" => $topicAbout, "topic_content" => json_encode($mappedFields), "topic_meta" => json_encode($topicMeta));

				//TODO topics: lotsa other stats.

				if ($row_id) { //edit
					$topicData['last_op'] = "edit";
					$whereClause['id'] = $row_id;
					$wpdb->update( 'alpn_topics', $topicData, $whereClause );
					$last_record_id['last_topic_add_id'] = $row_id;

          if ($topicTypeId == 4) { //Update connection
            $topicData['contact_topic_id'] = $row_id;
            $topicData['contact_email'] = $mappedFields['patient.telecom.1.value'];
            pte_manage_user_connection($topicData);
          }

          if ($topicTypeId == 5) { //update user
            $data['topic_name'] = $mappedFields['patient.name.0.given'];
            pte_manage_cc_groups("update_user", $data);
            update_user_meta( $userId, "pte_user_network_id",  $row_id); //SH
            $data = array(
              "sync_type" => "create_sync_id",
              "sync_payload" => array()
            );
            pte_manage_user_sync($data);
          }


				} else { //add
          $data = array();
          $topicData['last_op'] = "add";
          $topicData['created_date'] = $now;
          $wpdb->insert( 'alpn_topics', $topicData );

          $row_id = $wpdb->insert_id;
          $last_record_id['last_topic_add_id'] = $row_id;

          if ($topicTypeId == 4) { //Update connection
            $topicData['contact_topic_id'] = $row_id;
            $topicData['contact_email'] = $mappedFields['patient.telecom.1.value'];
            pte_manage_user_connection($topicData);
          }

          if ($topicTypeId == 5) { //Add user but don't create channels or members -- JIT
            $data['user_id'] = $userId;
            $data['topic_name'] = $mappedFields['patient.name.0.given'];
            $last_record_id['wp_id'] = $row_id;
            pte_manage_cc_groups("add_user", $data);
            update_user_meta( $userId, "pte_user_network_id",  $row_id);
            $data = array(
              "sync_type" => "create_sync_id",
              "sync_payload" => array()
            );
            pte_manage_user_sync($data);
				}
      }
				//Update last record for UI purposes
				$last_record_id['id'] = $userId;
				$wpdb->replace( 'alpn_user_metadata', $last_record_id );
				}
		break;
    }

	} // no topic info
add_action( 'wpforms_process_complete', 'alpn_handle_topic_add_edit', 10, 4 );

?>
