<?php

/**
 * The Theme My Login Moderation Extension
 *
 * @package Theme_My_Login_Moderation
 */

/*
Plugin Name: Theme My Login Moderation
Plugin URI: https://thememylogin.com/extensions/moderation
Description: Adds user moderation support to Theme My Login.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.1
Text Domain: theme-my-login-moderation
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the Moderation extension.
 */
class Theme_My_Login_Moderation extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'theme-my-login-moderation';

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
	protected $documentation_url = 'https://docs.thememylogin.com/category/24-moderation';

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
	protected $item_id = 38;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_moderation_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_moderation_license_status';

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'Moderation', 'theme-my-login-moderation' );
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
		// Flush permalinks on activation/deactivation
		add_action( 'tml_activate_moderation', 'tml_flush_rewrite_rules' );
		add_action( 'tml_deactivate_moderation', 'tml_flush_rewrite_rules' );

		// Register actions
		add_action( 'init', 'tml_moderation_register_actions', 0 );

		// Register forms
		add_action( 'init', 'tml_moderation_register_forms', 0 );

		// Register notification triggers
		add_action( 'init', 'tml_moderation_register_notification_triggers' );

		// Display moderation messages
		add_action( 'template_redirect', 'tml_moderation_action_messages' );

		// Apply the selected moderation
		add_action( 'register_new_user', 'tml_moderation_moderate_user' );

		// Send the admin new user registration notification
		add_action( 'tml_moderation_moderate_user', 'tml_moderation_new_user_notification' );

		if ( tml_moderation_is_active() ) {
			// Don't allow auto-login
			remove_action( 'register_new_user', 'tml_handle_auto_login');

			// Disable the new user notification
			remove_action( 'register_new_user', 'tml_send_new_user_notifications' );
		}

		if ( tml_moderation_require_activation() ) {
			// Send the new user activation notification
			add_action( 'tml_moderation_moderate_user', 'tml_moderation_new_user_activation_notification' );
			add_action( 'tml_moderation_resend_activation_notification', 'tml_moderation_new_user_activation_notification' );
			add_action( 'tml_moderation_user_activated', 'tml_moderation_new_user_notification' );
		}

		if ( tml_moderation_require_approval() ) {
			// Send the new user approval notification
			add_action( 'tml_moderation_moderate_user', 'tml_moderation_new_user_approval_admin_notification' );
			add_action( 'tml_moderation_user_approved', 'tml_moderation_new_user_notification' );
		}

		// Don't allow pending users to log in
		add_action( 'authenticate', 'tml_moderation_authenticate_pending_check', 99 );

		if ( is_admin() ) {
			// Render the admin notices
			add_action( 'admin_notices', 'tml_moderation_admin_notices' );
		}
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		// Filter the registration redirect based on moderation type
		add_filter( 'registration_redirect', 'tml_moderation_registration_redirect' );

		// Don't allow pending users to reset their password
		add_filter( 'allow_password_reset', 'tml_moderation_allow_password_reset', 10, 2 );

		if ( is_admin() ) {
			// Add args to the users query
			add_filter( 'users_list_table_query_args', 'tml_moderation_admin_users_list_table_query_args' );

			// Add the pending view
			add_filter( 'views_users', 'tml_moderation_admin_user_views' );

			// Add the user status column
			add_filter( 'manage_users_columns', 'tml_moderation_admin_user_columns' );
			add_filter( 'manage_users_custom_column', 'tml_moderation_admin_user_custom_columns', 10, 3 );

			// Add actions to the users edit page
			add_filter( 'user_row_actions', 'tml_moderation_admin_user_row_actions', 10, 2 );

			// User bulk actions
			add_filter( 'bulk_actions-users', 'tml_moderation_admin_user_bulk_actions' );

			// Handle user row and bulk actions
			add_filter( 'handle_bulk_actions-users', 'tml_moderation_admin_handle_user_actions', 10, 3 );
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
			'page_title' => __( 'Theme My Login Moderation Settings', 'theme-my-login-moderation' ),
			'menu_title' => __( 'Moderation', 'theme-my-login-moderation' ),
			'menu_slug' => 'theme-my-login-moderation',
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
		return tml_moderation_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_moderation_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	public function update() {
		$version = get_site_option( '_tml_moderation_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		// Initial migration
		$options = get_option( 'theme_my_login_moderation', array() );
		if ( ! empty( $options ) ) {
			if ( ! empty( $options['type'] ) ) {
				if ( 'email' == $options['type'] ) {
					update_site_option( 'tml_moderation_require_activation', 1 );
				} elseif ( 'admin' == $options['type'] ) {
					update_site_option( 'tml_moderation_require_approval', 1 );
				}
			}
			delete_option( 'theme_my_login_moderation' );
		}

		update_site_option( '_tml_moderation_version', $this->version );

		tml_flush_rewrite_rules();
	}
}

tml_register_extension( new Theme_My_Login_Moderation( __FILE__ ) );
