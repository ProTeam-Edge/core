<?php

/**
 * The Theme My Login Avatars Extension
 *
 * @package Theme_My_Login_Avatars
 */

/*
Plugin Name: Theme My Login Avatars
Plugin URI: https://thememylogin.com/extensions/avatars
Description: Adds user avatar support to Theme My Login and WordPress.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.0.3
Text Domain: tml-avatars
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the Avatars extension.
 */
class Theme_My_Login_Avatars extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'tml-avatars';

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.0.3';

	/**
	 * The extension's documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url = 'https://docs.thememylogin.com/category/117-avatars';

	/**
	 * The extension's support URL.
	 *
	 * @var string
	 */
	protected $support_url = 'https://thememylogin.com/support';

	/**
	 * The extension's store URL.
	 *
	 * @var string
	 */
	protected $store_url = 'https://thememylogin.com';

	/**
	 * The extension's item ID.
	 *
	 * @var int
	 */
	protected $item_id = 726906;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_avatars_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_avatars_license_status';

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'Avatars', 'tml-avatars' );
	}

	/**
	 * Include extension files.
	 *
	 * @since 1.0
	 */
	protected function include_files() {
		require $this->path . 'functions.php';

		if ( is_admin() ) {
			require $this->path . 'admin.php';
		}
	}

	/**
	 * Add extension actions.
	 *
	 * @since 1.0
	 */
	protected function add_actions() {
		// Add enctype to TML profile form
		add_action( 'init', 'tml_avatars_add_enctype_to_tml_profile' );

		// Handle avatar removal on TML profiles
		add_action( 'tml_action_profile', 'tml_avatars_handle_avatar_removal' );

		if ( is_admin() ) {
			// Handle avatar removal on WP profiles
			add_action( 'admin_init', 'tml_avatars_handle_avatar_removal' );
		}
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		// Handle avatar uploads
		add_filter( 'user_profile_update_errors', 'tml_avatars_handle_avatar_upload', 10, 3 );

		// Override the user avatar
		add_filter( 'get_avatar_url', 'tml_avatars_filter_avatar_url', 10, 3 );

		// Change the profile picture description
		add_filter( 'user_profile_picture_description', 'tml_avatars_user_profile_picture_description', 10, 2 );

		if ( is_admin() ) {
			// Add enctype to WP profile form
			add_filter( 'user_edit_form_tag', 'tml_avatars_admin_add_enctype_to_wp_profile' );
		}
	}

	/**
	 * Get the extension settings page args.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings page args.
	 */
	public function get_settings_page_args() {
		return array(
			'page_title' => __( 'Theme My Login Avatar Settings', 'tml-avatars' ),
			'menu_title' => __( 'Avatars', 'tml-avatars' ),
			'menu_slug' => 'tml-avatars',
		);
	}

	/**
	 * Get the extension settings sections.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings sections.
	 */
	public function get_settings_sections() {
		return tml_avatars_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_avatars_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	protected function update() {
		$version = get_site_option( '_tml_avatars_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		update_site_option( '_tml_avatars_version', $this->version );
	}
}

tml_register_extension( new Theme_My_Login_Avatars( __FILE__ ) );
