<?php

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;

$mediaIcons = array(
	"audio" => "📻",
	"video" => "📺",
	"image" => "🖼️",
);

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
include_once('pte_config.php');
$domainName = PTE_HOST_DOMAIN_NAME;
ini_set('memory_limit', '512M');

$logId = 'some';
$logId = 'all';

// if ( php_uname('n') == 'wp4' ) {
//     define('DISABLE_WP_CRON', true);
// }

// verify added nonce before submission for wpforms
use PascalDeVink\ShortUuid\ShortUuid;

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
	{

		die('Not a valid request');
		/* wp_redirect(site_url().'/invalid-request');
		exit; */
	}
	else
		return;

}
// add nonce field before submit button for all wpforms
add_action( 'wpforms_process_before', 'wpf_dev_process_before', 10, 2 );
function wpf_dev_display_submit_before( $form_data ) {
   return wp_nonce_field( 'wp_form_nonce', 'wp_form_nonce' );
}
add_action( 'wpforms_display_submit_before', 'wpf_dev_display_submit_before', 10, 1 );

add_action( 'wpforms_process_before', 'wpf_dev_process_before', 10, 2 );
$cookie_name = 'pmpro_visit';
$cookie_value = '0';
$cookie_secure = 'secure';
$cookie_httponly = 'HTTPOnly';
setcookie($cookie_name, $cookie_value, (time()+3600), '/',$domainName, $cookie_secure, $cookie_httponly);

add_filter( 'nonce_life','modify_timeslot' );  //4 - 8 hours. Default is 24 hours
function modify_timeslot () {
return 48 * 3600;
}

include('alpn-shortcodes.php');
include('alpn_common.php');
//include('pte_messaging.php');

$wpdb_readonly = $wpdb;  //TODO not needed anymore

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


		//Needed everywhere. Wasn't loading at times. Will load additional if version numbers change in wpdatables.
		wp_enqueue_style( 'vit-wpdatatables-bootstrap', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/wpdatatables-bootstrap.css' );
		wp_enqueue_style( 'vit-bootstrap-select', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-select/bootstrap-select.min.css' );
		wp_enqueue_style( 'vit-bootstrap-tagsinput', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.css' );
		wp_enqueue_style( 'vit-bootstrap-datetimepicket', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css' );
		wp_enqueue_style( 'vit-bootstrap-nouislider', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.css' );
		wp_enqueue_style( 'vit-wdt-bootstrap-datetimepicker', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-datetimepicker/wdt-bootstrap-datetimepicker.min.css' );
		wp_enqueue_style( 'vit-bootstrap-colorpicker', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.css' );
		wp_enqueue_style( 'vit-wpd-style', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/style.min.css' );
		wp_enqueue_style( 'vit-wpd-animate', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/animate/animate.min.css' );
		wp_enqueue_style( 'vit-wpd-uikit', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/uikit/uikit.css' );
		wp_enqueue_style( 'vit-wpd-frontend', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/wdt.frontend.min.css' );
		wp_enqueue_style( 'vit-wpd-skin', get_template_directory_uri() . '/../../plugins/wpdatatables/assets/css/wdt-skins/light.css' );

		wp_enqueue_style( 'vit-wpforms-full', get_template_directory_uri() . '/../../plugins/wpforms/assets/css/wpforms-full.min.css' );


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

//doka 3F23266F-8964439E-83E60218-871E75DF



function alpn_select2_enqueue_styles() {
    wp_enqueue_style( 'alpn_uppy', 'https://releases.transloadit.com/uppy/v2.1.0/uppy.min.css' );
    wp_enqueue_style( 'pte_doka', get_template_directory_uri() . '-child-master/doka_86/pintura/pintura.css');
    wp_enqueue_style( 'alpn_select2', get_template_directory_uri() . '-child-master/dist/css/select2.min.css' );
		wp_enqueue_style( 'wsc_lightgallery', get_template_directory_uri() . '-child-master/dist/assets/lighthouse/css/lightgallery-bundle.css' );
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
  	'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.3/dayjs.min.js',
  	array( 'jquery' )
    );
    wp_enqueue_script( 'alpn_date' );

    wp_register_script(
    	'alpn_date_utc',
    	'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.3/plugin/utc.min.js',
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
        'https://releases.transloadit.com/uppy/v2.1.0/uppy.min.js',
        array( 'memberlite-script' )
    );
    wp_enqueue_script( 'alpn_uppy' );

    wp_register_script(
        'alpn_pintura1',
        get_template_directory_uri() . '-child-master/doka_86/jquery-pintura/pintura.js',
        array( 'alpn_uppy' )
    );
  wp_enqueue_script( 'alpn_pintura1' );

		// wp_register_script(
		// 		'alpn_pintura2',
		// 		get_template_directory_uri() . '-child-master/doka_86/jquery-pintura/useEditorWithJQuery-iife.js',
		// 		array( 'alpn_pintura1' )
		// );
		// wp_enqueue_script( 'alpn_pintura2' );


    if (!is_admin()) {

			global $post;
			$pageSlug = $post->post_name;

			if ($pageSlug == "templates") {
				wp_register_script(
						'alpn_tinymce_editor',
						get_template_directory_uri() . '/../memberlite-child-master/dist/js/tinymce_5/tinymce.min.js',
						array( 'jquery' )
				);
				wp_enqueue_script( 'alpn_tinymce_editor' );
			}

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

					wp_register_script(
	          'walletconnect',
						get_template_directory_uri() . '/../memberlite-child-master/dist/js/web3-provider.min.js',
	      		array( 'jquery' )
	          );
	          wp_enqueue_script( 'walletconnect' );

						wp_register_script(
		          'moralis_sdk',
		      		'https://unpkg.com/moralis/dist/moralis.js',
		      		array( 'jquery' )
		          );
		          wp_enqueue_script( 'moralis_sdk' );

							wp_register_script(
			          'lighthouse_gallery',
								get_template_directory_uri() . '/../memberlite-child-master/dist/assets/lighthouse/lightgallery.min.js',
			      		array( 'jquery' )
			          );
			          wp_enqueue_script( 'lighthouse_gallery' );


								wp_register_script(
				          'lighthouse_gallery_zoom',
									get_template_directory_uri() . '/../memberlite-child-master/dist/assets/lighthouse/plugins/zoom/lg-zoom.min.js',
				      		array( 'lighthouse_gallery' )
				          );
				          wp_enqueue_script( 'lighthouse_gallery_zoom' );

							wp_register_script(
			          'lighthouse_gallery_thumbnail',
								get_template_directory_uri() . '/../memberlite-child-master/dist/assets/lighthouse/plugins/thumbnail/lg-thumbnail.min.js',
			      		array( 'lighthouse_gallery' )
			          );
			          wp_enqueue_script( 'lighthouse_gallery_thumbnail' );

								wp_register_script(
									'lighthouse_gallery_video',
									get_template_directory_uri() . '/../memberlite-child-master/dist/assets/lighthouse/plugins/video/lg-video.min.js',
									array( 'lighthouse_gallery' )
									);
									wp_enqueue_script( 'lighthouse_gallery_video' );

								wp_register_script(
									'lighthouse_gallery_share',
									get_template_directory_uri() . '/../memberlite-child-master/dist/assets/lighthouse/plugins/share/lg-share.min.js',
									array( 'lighthouse_gallery' )
									);
									wp_enqueue_script( 'lighthouse_gallery_share' );

    }

}
add_action('wp_enqueue_scripts', 'alpn_load_script');
add_action('admin_enqueue_scripts', 'alpn_load_script');

//Profile Settings

function bbp_enable_visual_editor( $args = array() ) {
	$args['tinymce'] = true;
  return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );

remove_action( 'wp_head', 'rel_canonical' );

function vit_translate__strings( $translated, $untranslated, $domain ) {

   if ( ! is_admin() ) {
      switch ( $untranslated ) {
      	case 'Username or email address':
            $translated = 'Email address';
        break;
				case 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.':
            $translated = 'Lost your password? Please enter your email address. You will receive a link to create a new password via email.';
        break;
				case 'Username or email':
            $translated = 'Email address';
        break;
				case 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.':
					$translated = 'A password reset email has been sent to the specified email address.';
				break;
				case 'My Subscription':
            $translated = 'Subscriptions';
        break;
				case 'Select options':
            $translated = 'Select Industry';
        break;

      }
   }
   return $translated;
}
add_filter( 'gettext', 'vit_translate__strings', 999, 3 );


function wpb_custom_billing_fields( $fields = array() ) {

	unset($fields['billing_first_name']);
	unset($fields['billing_last_name']);
	unset($fields['billing_email']);
	unset($fields['billing_company']);
	unset($fields['billing_address_1']);
	unset($fields['billing_address_2']);
	unset($fields['billing_state']);
	unset($fields['billing_city']);
	unset($fields['billing_phone']);
	unset($fields['billing_postcode']);
	unset($fields['billing_country']);

	return $fields;
}
add_filter('woocommerce_billing_fields','wpb_custom_billing_fields');
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );


function custom_add_subscription_name_to_table( $subscription ) {
    foreach ( $subscription->get_items() as $item_id => $item ) {
        $_product  = apply_filters( 'woocommerce_subscriptions_order_item_product', $subscription->get_product_from_item( $item ), $item );
        if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
            echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', sprintf( '<br><a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item ) );
        }
    }
}
add_action( 'woocommerce_my_subscriptions_after_subscription_id', 'custom_add_subscription_name_to_table', 35 );



function filter_woocommerce_get_price_html( $price, $instance ) {
    // make filter magic happen here...

		//alpn_log($instance);

    return $price;
};

// add the filter
add_filter( 'woocommerce_get_price_html', 'filter_woocommerce_get_price_html', 10, 2 );


//Override and reformat bbPress notification emails to match and use wc email.
function vit_wrap_bbpress_forum_subscription_email($content, $topicId, $forumId, $userId) {
	$smallWiscleWhite = PTE_ROOT_URL . "dist/assets/wiscle_small_w.png";
	$mailer = WC()->mailer();
	$template = 'vit_generic_email_template.php';
	$newContent = wc_get_template_html( $template, array(
			'email_heading' => "<img style='vertical-align: middle;' src='{$smallWiscleWhite}'>Wiscle Conversations",
			'email'         => $mailer,
			'email_body'    => $content
		), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
		$toEmail = get_userdata( $userId )->user_email;
		$emailSubject = strip_tags( bbp_get_topic_title( $topicId ) );
		$mailer->send( $toEmail, $emailSubject, $newContent );
	return false;
}
add_filter( 'bbp_forum_subscription_mail_message', 'vit_wrap_bbpress_forum_subscription_email', 10, 4 );

function vit_wrap_bbpress_subscription_email($content, $replyId, $topicId) {
	$smallWiscleWhite = PTE_ROOT_URL . "dist/assets/wiscle_small_w.png";
	$mailer = WC()->mailer();
	$template = 'vit_generic_email_template.php';
	$css = wc_get_template_html( 'emails/email-styles.php' );
	$newContent = wc_get_template_html( $template, array(
		  'email_heading' => "<img style='vertical-align: middle;' src='{$smallWiscleWhite}'>Wiscle Conversations",
			'email'         => $mailer,
			'email_body'    => $content
		), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
		$userId = bbp_get_reply_author_id( $replyId );
		$toEmail = get_userdata( $userId )->user_email;
		$emailSubject = strip_tags( bbp_get_topic_title( $topicId ) );
		$mailer->send( $toEmail, $emailSubject, $newContent );
	return false;
}
add_filter( 'bbp_subscription_mail_message', 'vit_wrap_bbpress_subscription_email', 10, 3 );


function add_loginout_link( $items, $args ) {

	// alpn_log($items);
	// alpn_log($args);

  if (is_user_logged_in() && ($args->theme_location == 'meta' || !$args->theme_location)) {

		$cartHtml = "";
		if (!WC()->cart->is_empty()){
			$cartHtml .= ' &nbsp;&nbsp;  <a class="wsc_nav_icon" href="' . get_permalink( wc_get_page_id( 'cart' ) ) . '"><i class="fal fa-shopping-cart"></i></a>';
		}
		$items .= '<li  title="Use the Tools" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 859 ) . '">Mission Control</a></li>';
		$items .= '<li  title="Join the Conversation" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 3306 ) . '">Conversations</a></li>';
		$items .= '<li title="Help us Thrive, Become an Owner" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 7260 ) . '">Community</a></li>';
		$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 146 ) . '">Blog</a></li>';
		//$items .= '<li  title="Do More with Your Assets" class="menu-item menu-item-type-post_type menu-item-object-page"><a class="wsc_nav_icon" href="' . get_permalink( 4275 ) . '">Marketplace</a> ' . $cartHtml . '</li>';
		//$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a class="wsc_nav_icon" href="' . get_permalink( 1514 ) . '"><i title="Help Center" class="fal fa-question"></i></a> &nbsp;&nbsp;  <a class="wsc_nav_icon" href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '"><i title="Account Dashboard" class="fal fa-id-card"></i></a> &nbsp;&nbsp;  <a title="Log out" class="wsc_nav_icon" href="'. wp_logout_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ) .'"><i class="fal fa-sign-out-alt"></i></a></li>';
    $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a class="wsc_nav_icon" href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '"><i title="Account Dashboard" class="fal fa-id-card"></i></a> &nbsp;&nbsp;  <a title="Log out" class="wsc_nav_icon" href="'. wp_logout_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ) .'"><i class="fal fa-sign-out-alt"></i></a></li>';
  }
   elseif (!is_user_logged_in() && ($args->theme_location == 'meta' || !$args->theme_location)) {
		 //$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 6995 ) . '">Pricing</a></li>';
		 $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 3306 ) . '">Conversations</a></li>';
		 $items .= '<li title="Help us Thrive, Become an Owner" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 7260 ) . '">Community</a></li>';
		 $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( 146 ) . '">Blog</a></li>';
		// $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a class="wsc_nav_icon" href="' . get_permalink( 1514 ) . '"><i title="Help Center" class="fal fa-question"></i></a></li>';
	   $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">Log In &nbsp;-&nbsp; Register</a></li>';
  }
   return $items;
}
add_filter( 'wp_nav_menu_items', 'add_loginout_link', 10, 2 );


function pte_set_avatar_url( $url, $id_or_email, $args ) {
	$url = PTE_ROOT_URL . "dist/assets/blm-avatar.png";
	$pteUserIcon = get_user_meta( $id_or_email, 'pte_user_icon', true );
	if ($pteUserIcon) {
		$url = PTE_IMAGES_ROOT_URL . $pteUserIcon;
	}
	return esc_url_raw($url);
}
add_filter( 'get_avatar_url', 'pte_set_avatar_url', 10, 3 );

function vit_register ($user_id, $userData) {   //Runs on New User. Sets up default topics and sample data.
	 alpn_log("Creating New User Info...");
	// alpn_log($user_id);
	 //alpn_log($userData);

	global $wpdb;
	$shortUuid = new ShortUuid();
	$now = date ("Y-m-d H:i:s", time());
	$userInfo = get_user_by('id', $user_id);
  $defaultTopicData = pte_create_default_topics($user_id, true);   //with sample data

	wsc_create_web3_support($user_id);

	$coreUserFormId = $defaultTopicData['core_user_form_id'];
	$samplePlace1Id = $defaultTopicData['sample_place_id_1'];
	$samplePlace2Id = $defaultTopicData['sample_place_id_2'];
	$sampleOrganization1Id = $defaultTopicData['sample_organization_id_1'];
	$samplePerson1Id = $defaultTopicData['sample_person_id_1'];
	$samplePerson1EmailId = $defaultTopicData['sample_person_email_id_1'];  //route

	$userEmailRouteId = $shortUuid->uuid4();
	$entry = array(
		'id' => $coreUserFormId,  //source user template type  Using custom TT
		'new_owner' => $user_id,
		"create_email_route" => $userEmailRouteId,
		'fields' => array("2" => $userInfo->user_lastname, "4" => $userInfo->user_firstname)    //4 for user topic is person_givenname, 2 = person_familynname
	);
	$newUserTopicId = alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user
	//Create linkS
	$subjectToken = 'pte_place';
	$linkData = array(
		'owner_id' => $user_id,
		'topic_id' => $newUserTopicId,
		'connected_id' => $user_id,
		'connection_link_topic_id' => $samplePlace1Id,
		'subject_token' => $subjectToken,
		'list_default' => 'no'
	);
	pte_manage_topic_link('add_edit_topic_bidirectional_link', $linkData, $subjectToken);
	$linkData = array(
		'owner_id' => $user_id,
		'topic_id' => $newUserTopicId,
		'connected_id' => $user_id,
		'connection_link_topic_id' => $samplePlace2Id,
		'subject_token' => $subjectToken,
		'list_default' => 'yes'
	);
	pte_manage_topic_link('add_edit_topic_bidirectional_link', $linkData, $subjectToken);

	$subjectToken = 'pte_organization';
	$linkData = array(
		'owner_id' => $user_id,
		'topic_id' => $newUserTopicId,
		'connected_id' => $user_id,
		'connection_link_topic_id' => $sampleOrganization1Id,
		'subject_token' => $subjectToken,
		'list_default' => 'yes'
	);
	pte_manage_topic_link('add_edit_topic_bidirectional_link', $linkData, $subjectToken);

	$toEmail = $userInfo->user_email;
	$myAccount = make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) );
	$userPass = esc_html( $userData['user_pass'] );
	$emailTemplateHtml = "
		<p>Hi {$userInfo->user_firstname},</p>
		<p>Use your new Wiscle account to publish data, share files and to collaborate with anyone, anywhere on anything.</P>
		<p>Log in with your email address: <strong>{$toEmail}</strong><br>And secure password: <strong>{$userPass}</strong></p>
		<p>Log in here: {$myAccount}</p>
	";
	$emailSubject = "Your account has been created!";
	$emailHeader = "From: Wiscle Support";
	$mailer = WC()->mailer();
	$template = 'vit_generic_email_template.php';
	$content = 	wc_get_template_html( $template, array(
			'email_heading' => $emailHeader,
			'email'         => $mailer,
			'email_body'    => $emailTemplateHtml
		), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
	try {
		$mailer->send( $toEmail, $emailSubject, $content );
	} catch (Exception $e) {
			alpn_log ('Caught exception: '. $e->getMessage());
	}

	//send Personalized Email Attachment to the new Topic
	// alpn_log('New User Sample Data -- Send some Emails here');
	// alpn_log($samplePerson1Id);
	// alpn_log($samplePerson1EmailId);
	// alpn_log($userEmailRouteId);

}
add_action( 'user_register', 'vit_register', 5, 2 );


function vit_subscription_status_updated($subscription, $new_status, $old_status) {
	alpn_log("SUB UPDATED");
	//alpn_log($subscription);
}
add_action( 'woocommerce_subscription_status_updated', 'vit_subscription_status_updated', 10, 3 );

function vit_order_status_completed($orderId) {
	alpn_log("Order Status Completed");
	//alpn_log($orderId);

}
add_action( 'woocommerce_order_status_completed', 'vit_order_status_completed', 10, 1 );

// woocommerce_order_status_pending
// woocommerce_order_status_failed
// woocommerce_order_status_on-hold
// woocommerce_order_status_processing
// woocommerce_order_status_completed
// woocommerce_order_status_refunded
// woocommerce_order_status_cancelled

function vit_breadcrumbs($trail, $crumbs, $r) {
	return "<a href='https://" . PTE_HOST_DOMAIN_NAME . "/conversations'><img id='vit_breadcrumb_image' src='https://storage.googleapis.com/pte_media_store_1/734d618b-bc2.png'></a> " . $trail;
}
add_filter( 'bbp_get_breadcrumb', 'vit_breadcrumbs', 10, 3 );


function add_name_woo_account_registration() {
    ?>
    <p class="form-row form-row-first">
    <label for="first_name"><?php _e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="first_name" id="first_name" value="<?php if ( ! empty( $_POST['first_name'] ) ) esc_attr_e( $_POST['first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
    <label for="last_name"><?php _e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="last_name" id="last_name" value="<?php if ( ! empty( $_POST['last_name'] ) ) esc_attr_e( $_POST['last_name'] ); ?>" />
    </p>
    <div class="clear"></div>
    <?php
}
add_action( 'woocommerce_register_form_start', 'add_name_woo_account_registration' );

function validate_register_fields( $errors, $username, $email ) {
    if ( isset( $_POST['first_name'] ) && empty( $_POST['first_name'] ) ) {
        $errors->add( 'first_name_error', __( 'First name is required.', 'woocommerce' ) );
    }
    if ( isset( $_POST['last_name'] ) && empty( $_POST['last_name'] ) ) {
        $errors->add( 'last_name_error', __( 'Last name is required.', 'woocommerce' ) );
    }
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'validate_register_fields', 10, 3 );


add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name', 1000, 3 );
function custom_wp_mail_from_name( $original_email_from ) {
		global $senderName;
    return $original_email_from;  //Weird doing the other thing
}


// function save_name_fields( $customer_id ) {
//     if ( isset( $_POST['billing_first_name'] ) ) {
//         update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
//         update_user_meta( $customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']) );
//     }
//     if ( isset( $_POST['billing_last_name'] ) ) {
//         update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
//         update_user_meta( $customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']) );
//     }
// }
// add_action( 'woocommerce_created_customer', 'save_name_fields' );


function vit_wc_customer_data( $data ) {
	$shortUuid = new ShortUuid();
	$data['user_login'] = $shortUuid->uuid4();
	$data['user_nicename'] = $_POST['user_email'];
	if (isset($_POST['first_name'])) {
		$data['first_name'] = $_POST['first_name'];
		$data['display_name'] = $_POST['first_name'];
		$data['user_nicename'] = $_POST['first_name'];
	}
	if (isset($_POST['last_name'])) {
		$data['last_name'] = $_POST['last_name'];
	}
	$data['role'] = 'subscriber';
	return $data;
}
add_filter( 'woocommerce_new_customer_data', 'vit_wc_customer_data', 10, 1 );
add_filter('show_admin_bar', '__return_false'); //Remove top bar
add_filter( 'woocommerce_registration_auth_new_customer', '__return_false' );


function vit_profile_update( $user_id ) {
	$data = $_POST;
	if (is_user_logged_in() && isset($data['action']) && $data['action'] == 'save_account_details') {
		$emailAddress = $data['account_email'];
		vit_update_contacts_new_email ($user_id, $emailAddress);
	}
}
add_action( 'profile_update', 'vit_profile_update', 10, 1 );


function wsc_parse_login($user_login, $user) {
//	use wp to get global data? Or get it from db?
	global $wpdb;
	$userId = $user->data->ID;
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT parse_user_name, parse_password FROM alpn_topics WHERE owner_id = %d AND special = 'user'", $userId)
	 );

	 if (isset($results[0]) && $results[0]->parse_user_name && $results[0]->parse_password) {
		 try {
			 $parseClient = new ParseClient;
			 $parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
			 $parseClient->setServerURL(MORALIS_SERVER_URL, 'server');

		   $user = ParseUser::logIn($results[0]->parse_user_name, $results[0]->parse_password);
			 $sessionToken = $user->getSessionToken();

			 update_user_meta( $userId, "wsc_parse_session_token",  $sessionToken);

			 alpn_log("PARSE USER LOGGER IN - " . $sessionToken);

		 } catch (ParseException $error) {
		   // The login failed. Check error to see why.
			 update_user_meta( $userId, "wsc_parse_session_token",  "");
			 alpn_log("PARSE USER LOGIN FAILED");
			 alpn_log($error);
		 }
	 }
}
add_action('wp_login', 'wsc_parse_login', 10, 2);

function wsc_parse_logout($userId) {
	 try {
		 $parseClient = new ParseClient;
		 $parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
		 $parseClient->setServerURL(MORALIS_SERVER_URL, 'server');
	   ParseUser::logOut();
		 update_user_meta( $userId, "wsc_parse_session_token",  "");

	 } catch (ParseException $error) {
		 alpn_log("PARSE USER LOGOUT EXCEPTION");
		 alpn_log($error);
	 }
}
add_action('wp_logout', 'wsc_parse_logout', 10, 1);


//add_action('profile_update', 'sync_alpn_user_info_on_register');
function test_plugin_setup_menu(){
}

function cleanup_pte_user_on_delete( $user_id ) {
	//TODO WHAT ABOUT ARCHIVING INTERATIONS, Files, ETC?

	// see jira for these. Not dangerous, just uses up space. Vault Ids deleted so floating and paying forever so delete.
	//cloud files
	//images
  //WP Forms definitions in Posts

	//DELETE PARSE USER USING USERNAME AND PWD AND THEIR APIS

	global $wpdb;

	alpn_log('cleanup_pte_user_on_delete_start - ' . $user_id);
	//channels unlimited, no cost. No need to delete unless privacy issue.
	//CC Groups delete user

	$data = array('owner_id' => $user_id);
	pte_manage_cc_groups("delete_user", $data);

	//Handle Fax Numbers
	pte_release_all_pstn_numbers($user_id);
	//Store vault keys to batch delete
	$wpdb->query(
		$wpdb->prepare("INSERT INTO alpn_object_keys_to_delete SELECT pdf_key AS object_key FROM alpn_vault WHERE owner_id = %d AND pdf_key <> ''", $user_id)
	);

	$wpdb->query(
		$wpdb->prepare("INSERT INTO alpn_object_keys_to_delete SELECT file_key AS object_key FROM alpn_vault WHERE owner_id = %d AND file_key <> ''", $user_id)
	);
	//Wp-Forms
	$wpdb->query(
		$wpdb->prepare("DELETE FROM wp_posts WHERE ID IN (SELECT form_id FROM alpn_topic_types where owner_id = %d)", $user_id)
	);

	$whereclause = array('owner_id' => $user_id);
	$wpdb->delete( "alpn_topics", $whereclause );
	$wpdb->delete( "alpn_topic_types", $whereclause );
	$wpdb->delete( "alpn_vault", $whereclause );
	$wpdb->delete( "alpn_templates", $whereclause );
	$wpdb->delete( "alpn_links", $whereclause );
	$wpdb->delete( "alpn_user_lists", $whereclause );
	$wpdb->delete( "alpn_proteams", $whereclause );

	//ProTeam participant
	$whereclause = array('wp_id' => $user_id);
	$wpdb->delete( "alpn_proteams", $whereclause );

	//ProTeam Owner
	$whereclause = array('owner_id' => $user_id);
	$wpdb->delete( "alpn_proteams", $whereclause );

	$whereclause = array('id' => $user_id);
	$wpdb->delete( "alpn_user_metadata", $whereclause );

	$whereclause = array('owner_id_1' => $user_id);
	$wpdb->delete( "alpn_topic_links", $whereclause );

	$whereclause = array('owner_id_2' => $user_id);
	$wpdb->delete( "alpn_topic_links", $whereclause );

	$ownerNetworkId = get_user_meta( $user_id, 'pte_user_network_id', true );
	$whereclause = array('owner_network_id' => $ownerNetworkId);
	$wpdb->delete( "alpn_interactions", $whereclause );

	delete_user_meta( $user_id, "pte_user_network_id");
	//reset records of my connections.
	$whereclause = array('connected_id' => $user_id);
	$topicData = array('connected_id' => NULL, 'connected_topic_id' => NULL, 'connected_network_id' => NULL, 'channel_id' => NULL);
	$wpdb->update( "alpn_topics", $topicData, $whereclause );

}
add_action( 'delete_user', 'cleanup_pte_user_on_delete', 10 );

//Database


function alpn_handle_topic_add_edit ($fields, $entry, $form_data, $entry_id ) { //Add record to topic with special handling for user and network

  global $wpdb;
  $returnDetails = array();
	$newMember = false;

  alpn_log("alpn_handle_topic_add_edit");
  //alpn_log($fields);

  $fieldsAll = $fields;
  $row_id = $userEmail = '';
	$existingPersonWithEmail = array();

	$formId = $entry['id'];
  $fields = $entry['fields'];

  //alpn_log($fields);
  //alpn_log('alpn_handle_topic_add_edit_TEXTAREAFIELDS');
  //alpn_log($fieldsAll);

	if (isset($entry['new_owner']) && $entry['new_owner']) {
		alpn_log('New Owner');
		update_user_meta( $entry['new_owner'], "wsc_new_member", true);
		$userInfo = get_user_by('id', $entry['new_owner']);
	} else if (isset($entry['owner_id']) && $entry['owner_id']) {
		alpn_log('New Topic Passing in owner_id');
		$userInfo = get_user_by('id', $entry['owner_id']);
	} else {
		alpn_log('Logged in User');

		$userInfo = wp_get_current_user();
	}

	$userId = $userInfo->data->ID;
	$userEmail = $userInfo->data->user_email;

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
						$newKey = $alpnNormalizeMapFlipped[$key];
						$mappedFields[$newKey] = $fields[$key];
					} else {
						$mappedFields[$alpnNormalizeMapFlipped[$key]] = '';
					}
				}

				if (isset($entry['new_owner'])) {
					$topicName = pte_make_string($nameSource, $fields, $alpnNormalizeMap);
					$topicAbout = $userEmail;

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

				if ($topicTypeSpecial == 'user') { //User
					$mappedFields['person_email'] = $userEmail;  //sets user profile email to WP email
				}

				if (isset($mappedFields['person_email']) && $mappedFields['person_email']) {  //person only
					//Check for contact only dupes and dissallow
					$altId = $mappedFields['person_email'];  //Email
						if ($topicTypeSpecial == 'contact') {     //
							$existingPersonWithEmail = $wpdb->get_results(
								$wpdb->prepare("SELECT id, name, owner_id, dom_id FROM alpn_topics WHERE alt_id = %s AND (special = 'contact' OR special = 'user') AND owner_id = %d", $altId, $userId)
							);
							if (isset($existingPersonWithEmail[0]) && $existingPersonWithEmail[0]->id != $row_id) { //pass data via user_meta. I can't figure out how to get data through wpforms.
								$existingUserLink = "<span class='pte_topic_type_check_title_link' onclick='event.stopPropagation(); alpn_mission_control(\"select_by_mode\", \"{$existingPersonWithEmail[0]->dom_id}\")'>{$existingPersonWithEmail[0]->name}</span>";
								$last_record_id['id'] = $userId;
								$errorData = array(
									"pte_error" => true,
									"pte_error_id" => 100,
									"pte_error_message" => "A contact with this unique email address already exists. Please follow this link: {$existingUserLink}"
								);
				        $last_record_id['last_return_to'] = json_encode($errorData);
								$wpdb->replace( 'alpn_user_metadata', $last_record_id );
								return;
							}
						}
				}

				//TODO Topic Meta. Network: user-editable status: prospect, active, trusted (need better name). Topic: type.
				$topicData = array("alt_id" => $altId, "modified_date" => $now, "owner_id" => $userId, "topic_type_id" => $topicTypeId, "special" => $topicTypeSpecial, "name" => $topicName, "about" => $topicAbout, "topic_content" => json_encode($mappedFields), "topic_meta" => json_encode($topicMeta));
				//TODO topics: lotsa other stats.

				if ($row_id) { //EDIT

					$pteMeta = array();  //Name and About are fields on the Topic. So if connected, we need to use the connected person's data. If connected, it won't blow away these. Prefer a more dynamic mechanism
					if (isset($fields[0])) {
						$pteMeta = json_decode($fields[0], true);
						$connectedId = $pteMeta["connected_id"];
						if ($connectedId) {
							unset($topicData['name']);
							unset($topicData['about']);
							}
					}
					//handle image handle and sync_id.
					$syncId = "";
					$topicRow = $wpdb->get_results(
						$wpdb->prepare("SELECT image_handle, sync_id FROM alpn_topics WHERE id = %d", $row_id)
					);

					if (isset($topicRow[0])) {
						$tRow = $topicRow[0];
						$currentImageHandle = $tRow->image_handle;
						$syncId = $tRow->sync_id;

						if ($topicSchemaKey == "Person") {
							if ($currentImageHandle == "" || substr($currentImageHandle, 0, 16) == "pte_icon_letter_") {
								$firstChar = strtolower(substr($mappedFields['person_givenname'], 0, 1));
								if ($firstChar >= 'a' && $firstChar <= 'z') {
									$currentImageHandle = $topicData['image_handle'] = "pte_icon_letter_" . $firstChar . ".png";
								} else {
									$currentImageHandle = $topicData['image_handle'] = "pte_icon_letter_n.png";
								}
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
						$data['sync_id'] = $syncId;
						$data['owner_id'] = $userId;
						$data['user_id'] = $userId;
            $data['image_handle'] = $currentImageHandle;
            $data['topic_id'] = $row_id;
						$firstChar = strtolower(substr($mappedFields['person_givenname'], 0, 1));
						if ($currentImageHandle == "" ) {
							if ($firstChar && $firstChar >= 'a' && $firstChar <= 'z') {
								$data['image_handle'] = "pte_icon_letter_" . $firstChar . ".png";
							} else {
								$data['image_handle'] = "pte_icon_letter_n.png";
							}
						}
						$data['topic_name'] = $mappedFields['person_givenname'];
            $data['full_name'] = $topicName;
            pte_manage_cc_groups("update_user", $data);
						wp_update_user([
						    'ID' => $userId, // this is the ID of the user you want to update.
						    'first_name' => $mappedFields['person_givenname'],
								'last_name' => $mappedFields['person_familyname'],
								'display_name' => $mappedFields['person_givenname'],
								'user_nicename' => $mappedFields['person_givenname'],
						    'nickname' => $mappedFields['person_givenname'],
						]);
						update_user_meta( $userId, "pte_user_icon",  $data['image_handle']);

						// update Topic Name and About for all connected
						$nameAboutData = array(
							"name" => $topicName,
							"about" => $topicAbout
						);
						$whereClause = array(
							"connected_id" => $userId
						);
						$wpdb->update( 'alpn_topics', $nameAboutData, $whereClause );
          }

					if ($topicTypeSpecial == 'topic') { //update topic
						$data['owner_id'] = $userId;
            $data['image_handle'] = $currentImageHandle;
            $data['topic_id'] = $row_id;
            $data['topic_name'] = $topicName;

            pte_manage_cc_groups("update_channel", $data);
					}
				} else { //   ADD

					if (isset($entry['icon_image']) && $entry['icon_image']) {
						$topicData['image_handle'] = $entry['icon_image'];

					} else if ($topicSchemaKey == "Person") {
						$firstChar = strtolower(substr($mappedFields['person_givenname'], 0, 1));
						if ($firstChar >= 'a' && $firstChar <= 'z') {
							$topicData['image_handle'] = "pte_icon_letter_" . $firstChar . ".png";
						} else {
							$topicData['image_handle'] = "pte_icon_letter_n.png";
						}
					}
					if (isset($entry['logo_image']) && $entry['logo_image']) {
						$topicData['logo_handle'] = $entry['logo_image'];
					}

					if (isset($entry['create_email_route']) && $entry['create_email_route']) {
							$topicData['email_route_id'] = $entry['create_email_route'];
					}

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





						$data = array(
              "sync_type" => "return_create_sync_id",
              "sync_user_id" => $userId,
              "sync_payload" => array()
            );
            $syncId = pte_manage_user_sync($data);

						$data['sync_id'] = $syncId;
            $data['owner_id'] = $userId;
						$data['user_id'] = $userId;
            $data['topic_id'] = $row_id;
						$data['topic_name'] = $mappedFields['person_givenname'];
            $data['full_name'] = $topicName;
						$data['image_handle'] = $topicData['image_handle'];
            pte_manage_cc_groups("add_user", $data);
						wp_update_user([
						    'ID' => $userId, // this is the ID of the user you want to update.
						    'first_name' => $mappedFields['person_givenname'],
								'last_name' => $mappedFields['person_familyname'],
								'display_name' => $mappedFields['person_givenname'],
								'user_nicename' => $mappedFields['person_givenname'],
						    'nickname' => $mappedFields['person_givenname'],
						]);
						update_user_meta( $userId, "pte_user_icon",  $topicData['image_handle']);
						update_user_meta( $userId, "pte_user_network_id",  $row_id);
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
