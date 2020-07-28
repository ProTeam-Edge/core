<?php

/**
 * The Theme My Login Redirection Extension
 *
 * @package Theme_My_Login_Redirection
 */

/*
Plugin Name: Theme My Login Redirection
Plugin URI: https://thememylogin.com/extensions/redirection
Description: Adds redirection support to Theme My Login.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.1
Text Domain: tml-redirection
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the Redirection extension.
 */
class Theme_My_Login_Redirection extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'tml-redirection';

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.1';

	/**
	 * The extension's documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url = 'https://docs.thememylogin.com/category/28-redirection';

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
	protected $item_id = 52;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_redirection_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_redirection_license_status';

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'Redirection', 'tml-redirection' );
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
		// Set a referrer cookie if applicable
		add_action( 'wp', 'tml_redirection_set_referer_cookie' );

		if ( is_admin() ) {
			// Add admin styles
			add_action( 'admin_print_styles', 'tml_redirection_admin_print_styles' );
		}
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		// Handle login redirect
		add_filter( 'login_redirect', 'tml_redirection_login_redirect', 100, 3 );

		// Handle logout redirect
		add_filter( 'logout_redirect', 'tml_redirection_logout_redirect', 100, 3 );

		// Handle registration redirect
		add_filter( 'tml_registration_redirect', 'tml_redirection_registration_redirect', 100, 2 );
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
			'page_title' => __( 'Theme My Login Redirection Settings', 'tml-redirection' ),
			'menu_title' => __( 'Redirection', 'tml-redirection' ),
			'menu_slug' => 'tml-redirection',
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
		return tml_redirection_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_redirection_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	protected function update() {
		$version = get_site_option( '_tml_redirection_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		tml_redirection_migrate_options();

		update_site_option( '_tml_redirection_version', $this->version );
	}
}

tml_register_extension( new Theme_My_Login_Redirection( __FILE__ ) );
