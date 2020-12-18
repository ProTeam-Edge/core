<?php

/**
 * The Theme My Login Security Extension
 *
 * @package Theme_My_Login_Security
 */

/*
Plugin Name: Theme My Login Security
Plugin URI: https://thememylogin.com/extensions/security
Description: Adds security features to Theme My Login.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.1.3
Text Domain: tml-security
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the Security extension.
 */
class Theme_My_Login_Security extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'tml-security';

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.1.3';

	/**
	 * The extension's documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url = 'https://docs.thememylogin.com/category/29-security';

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
	protected $item_id = 55;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_security_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_security_license_status';

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'Security', 'tml-security' );
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
		// Check if wp-login.php needs to be disabled
		add_action( 'init', 'tml_security_wp_login_disabled_check' );

		// Check if authentication needs to be blocked
		add_action( 'authenticate', 'tml_security_login_lockout_check', 100, 3 );

		// Enforce password requirements
		add_action( 'validate_password_reset', 'tml_security_validate_password' );

		if ( is_admin() ) {
			// Render the admin notices
			add_action( 'admin_notices', 'tml_security_admin_notices' );
		}
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		// Enforce password requirements
		if ( tml_allow_user_passwords() ) {
			add_filter( 'registration_errors', 'tml_security_validate_password' );
		}
		add_filter( 'user_profile_update_errors', 'tml_security_validate_password' );

		if ( is_admin() ) {
			// Add the user status column
			add_filter( 'manage_users_columns', 'tml_security_admin_user_columns' );
			add_filter( 'manage_users_custom_column', 'tml_security_admin_user_custom_columns', 10, 3 );

			// Add actions to the users edit page
			add_filter( 'user_row_actions', 'tml_security_admin_user_row_actions', 10, 2 );

			// User bulk actions
			add_filter( 'bulk_actions-users', 'tml_security_admin_user_bulk_actions' );

			// Handle user row and bulk actions
			add_filter( 'handle_bulk_actions-users', 'tml_security_admin_handle_user_actions', 10, 3 );
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
			'page_title' => __( 'Theme My Login Security Settings', 'tml-security' ),
			'menu_title' => __( 'Security', 'tml-security' ),
			'menu_slug' => 'tml-security',
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
		return tml_security_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_security_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	protected function update() {
		$version = get_site_option( '_tml_security_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		if ( version_compare( $version, '1.0', '<' ) ) {
			// Initial migration
			$options = get_option( 'theme_my_login_security', array() );
			if ( ! empty( $options ) ) {
				if ( isset( $options['failed_login']['threshold'] ) ) {
					update_site_option( 'tml_security_lockout_login_attempts', $options['failed_login']['threshold'] );
				}
				if ( isset( $options['failed_login']['threshold_duration'] ) && isset( $options['failed_login']['threshold_duration_unit'] ) ) {
					$threshold = $options['failed_login']['threshold_duration'];
					switch ( $options['failed_login']['threshold_duration_unit'] ) {
						case 'day' :
							$threshold = ( $threshold * DAY_IN_SECONDS ) / MINUTE_IN_SECONDS;
							break;
						case 'hour' :
							$threshold = ( $threshold * HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS;
							break;
					}
					update_site_option( 'tml_security_lockout_threshold', $threshold );
				}
				if ( isset( $options['failed_login']['lockout_duration'] ) && isset( $options['failed_login']['lockout_duration_unit'] ) ) {
					$duration = $options['failed_login']['lockout_duration'];
					switch ( $options['failed_login']['lockout_duration_unit'] ) {
						case 'day' :
							$duration = ( $duration * DAY_IN_SECONDS ) / MINUTE_IN_SECONDS;
							break;
						case 'hour' :
							$duration = ( $duration * HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS;
							break;
					}
					update_site_option( 'tml_security_lockout_duration', $duration );
				}
				delete_option( 'theme_my_login_security' );
			}
		}

		if ( version_compare( $version, '1.1', '<' ) ) {
			$users = get_users( array(
				'meta_key' => 'theme_my_login_security'
			) );
			foreach ( $users as $user ) {
				if ( ! $meta = get_user_meta( $user->ID, 'theme_my_login_security', true ) ) {
					continue;
				}
				if ( ! empty( $meta['is_locked'] ) && empty( $meta['lock_expiration'] ) ) {
					tml_security_lock_user( $user->ID );
				}
				delete_user_meta( $user->ID, 'theme_my_login_security' );
			}
		}

		// DB schema
		tml_security_admin_install_db_schema();

		update_site_option( '_tml_security_version', $this->version );
	}
}

tml_register_extension( new Theme_My_Login_Security( __FILE__ ) );
