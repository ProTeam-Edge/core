<?php

/**
 * The Theme My Login Notifications Extension
 *
 * @package Theme_My_Login_Notifications
 */

/*
Plugin Name: Theme My Login Notifications
Plugin URI: https://thememylogin.com/extensions/notifications
Description: Adds custom notification support to Theme My Login.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.1.1
Text Domain: theme-my-login-notifications
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the Notifications extension.
 */
class Theme_My_Login_Notifications extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'theme-my-login-notifications';

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.1.1';

	/**
	 * The extension's documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url = 'https://docs.thememylogin.com/category/25-notifications';

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
	protected $item_id = 43;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_notifications_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_notifications_license_status';

	/**
	 * The available notification triggers.
	 *
	 * @var array
	 */
	protected $triggers = array();

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'Notifications', 'theme-my-login-notifications' );
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
		// Register the default notification triggers
		add_action( 'init', 'tml_notifications_register_default_triggers', 0 );

		// Passwords
		add_action( 'retrieve_password_key', 'tml_notifications_retrieve_password_key', 10, 2 );

		if ( is_admin() ) {
			// Print admin styles
			add_action( 'admin_print_styles', 'tml_notifications_admin_print_styles' );
		}
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		// Registration
		add_filter( 'wp_new_user_notification_email_admin', 'tml_notifications_filter_default_notification', 10, 3 );
		add_filter( 'wp_new_user_notification_email', 'tml_notifications_filter_default_notification', 10, 3 );

		if ( tml_notifications_is_default_notification_disabled( 'wp_new_user_notification_email_admin' ) ) {
			add_filter( 'tml_send_new_user_admin_notification', '__return_false' );
		}
		if ( tml_notifications_is_default_notification_disabled( 'wp_new_user_notification_email' ) ) {
			add_filter( 'tml_send_new_user_notification', '__return_false' );
		}

		// Passwords
		add_filter( 'tml_retrieve_password_email', 'tml_notifications_filter_default_notification', 10, 3 );
		add_filter( 'wp_password_change_notification_email', 'tml_notifications_filter_default_notification', 10, 3 );

		if ( tml_notifications_is_default_notification_disabled( 'wp_password_change_notification_email' ) ) {
			remove_action( 'after_password_reset', 'wp_password_change_notification' );
		}

		// Profile
		add_filter( 'password_change_email', 'tml_notifications_filter_default_notification', 10, 3 );
		add_filter( 'email_change_email', 'tml_notifications_filter_default_notification', 10, 3 );
		add_filter( 'new_user_email_content', 'tml_notifications_filter_new_user_email_content', 10, 2 );

		if ( tml_notifications_is_default_notification_disabled( 'password_change_email' ) ) {
			add_filter( 'send_password_change_email', '__return_false' );
		}
		if ( tml_notifications_is_default_notification_disabled( 'email_change_email' ) ) {
			add_filter( 'send_email_change_email', '__return_false' );
		}

		// Moderation
		if ( tml_extension_exists( 'theme-my-login-moderation' ) ) {
			add_filter( 'tml_moderation_user_activation_email', 'tml_notifications_filter_default_notification', 10, 4 );
			add_filter( 'tml_moderation_user_approval_email', 'tml_notifications_filter_default_notification', 10, 3 );

			if ( tml_notifications_is_default_notification_disabled( 'tml_moderation_user_approval_email' ) ) {
				remove_action( 'tml_moderation_moderate_user', 'tml_moderation_new_user_approval_admin_notification' );
			}
		}

		if ( is_multisite() ) {
			// Sanitize custom notifications before saving
			add_filter( 'pre_update_site_option_tml_notifications_custom_notifications', 'tml_notifications_admin_sanitize_custom_notifications' );
		}
	}

	/**
	 * Register a notification trigger.
	 *
	 * @since 1.0
	 *
	 * @param string $trigger The trigger name.
	 * @param array  $args {
	 *     Optional. An array of arguments for defining a notification trigger.
	 *
	 *     @param string   $label    The label for the trigger.
	 *     @param string   $hook     The hook for the trigger.
	 *     @param callable $function The function to be called on the hook.
	 *     @param int      $priority The priority to attach the function to the
	 *                               hook.
	 * }
	 * @return array The trigger data.
	 */
	public function register_trigger( $trigger, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'label' => '',
			'hook' => '',
			'function' => '',
			'priority' => 5,
			'args' => array( 'user' ),
			'group' => __( 'Custom', 'theme-my-login-notifications' ),
		) );

		$trigger = sanitize_key( $trigger );

		$args['name'] = $trigger;
		if ( empty( $args['label'] ) ) {
			$args['label'] = $trigger;
		}
		if ( empty( $args['function'] ) ) {
			$args['function'] = 'tml_notifications_user_notification_handler';
		}

		$this->triggers[ $trigger ] = $args;

		if ( ! empty( $args['hook'] ) && ! empty( $args['function'] ) ) {
			add_action( $args['hook'], $args['function'], $args['priority'], count( $args['args'] ) );
		}

		/**
		 * Fires after registering a notification trigger.
		 *
		 * @since 7.0
		 *
		 * @param string $name The trigger name.
		 * @param array  $trigger The trigger object.
		 */
		do_action( 'tml_notifications_registered_trigger', $trigger, $args );

		return $trigger;
	}

	/**
	 * Unregister a notification trigger.
	 *
	 * @since 1.0
	 *
	 * @param string $trigger The trigger name.
	 */
	public function unregister_trigger( $trigger ) {
		unset( $this->triggers[ $trigger ] );
	}

	/**
	 * Get a notification trigger.
	 *
	 * @since 1.0
	 *
	 * @param string $trigger The trigger name.
	 * @return array|bool The trigger data if it exists or false otherwise.
	 */
	public function get_trigger( $trigger ) {
		if ( isset( $this->triggers[ $trigger ] ) ) {
			return $this->triggers[ $trigger ];
		}
		return false;
	}

	/**
	 * Get all notification triggers.
	 *
	 * @since 1.0
	 *
	 * @return array The triggers.
	 */
	public function get_triggers() {
		return $this->triggers;
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
			'page_title' => __( 'Theme My Login Notifications', 'theme-my-login' ),
			'menu_title' => __( 'Notifications', 'theme-my-login' ),
			'menu_slug' => 'theme-my-login-notifications',
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
		return tml_notifications_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_notifications_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	protected function update() {
		$version = get_site_option( '_tml_notifications_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		tml_notifications_migrate_options();

		if ( version_compare( $version, '1.1', '<' ) ) {
			$notifications = array();

			if ( ! get_site_option( 'tml_notifications_send_new_user_notification', true ) ) {
				$notifications['wp_new_user_notification_email'] = array(
					'disable' => 1,
				);
			}
			if ( ! get_site_option( 'tml_notifications_send_new_user_admin_notification', true ) ) {
				$notifications['wp_new_user_notification_email_admin'] = array(
					'disable' => 1,
				);
			}
			if ( ! get_site_option( 'tml_notifications_send_password_change_notification', true ) ) {
				$notifications['password_change_email'] = array(
					'disable' => 1,
				);
			}
			if ( ! get_site_option( 'tml_notifications_send_password_change_admin_notification', true ) ) {
				$notifications['wp_password_change_notification_email'] = array(
					'disable' => 1,
				);
			}
			if ( ! get_site_option( 'tml_notifications_send_email_change_notification', true ) ) {
				$notifications['email_change_email'] = array(
					'disable' => 1,
				);
			}
			delete_site_option( 'tml_notifications_send_new_user_notification' );
			delete_site_option( 'tml_notifications_send_new_user_admin_notification' );
			delete_site_option( 'tml_notifications_send_password_change_notification' );
			delete_site_option( 'tml_notifications_send_password_change_admin_notification' );
			delete_site_option( 'tml_notifications_send_email_change_notification' );
		}

		update_site_option( '_tml_notifications_version', $this->version );
	}
}

tml_register_extension( new Theme_My_Login_Notifications( __FILE__ ) );
