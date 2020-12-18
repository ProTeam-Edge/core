<?php

/**
 * Theme My Login Security Admin Functions
 *
 * @package Theme_My_Login_Security
 * @subpackage Administration
 */

/**
 * Add custom columns to the user list table.
 *
 * @since 1.1
 *
 * @param array $columns The user list table columns.
 * @return The user list table columns.
 */
function tml_security_admin_user_columns( $columns ) {
	// Find the role column
	$index = array_search( 'role', array_keys( $columns ) );

	// Use the position after the role column if found, or after the last column if not
	$index = ( false === $index ) ? count( $columns ) : $index + 1;

	// Insert the column
	return array_merge( array_slice( $columns, 0, $index ), array(
		'tml_security_locked' => __( 'Locked', 'tml-security' ),
	), array_slice( $columns, $index ) );
}

/**
 * Handle custom columns in the user list table.
 *
 * @since 1.1
 *
 * @param string $output      The column output.
 * @param string $column_name The column name.
 * @param int    $user_id     The user ID.
 * @return string The column output.
 */
function tml_security_admin_user_custom_columns( $output, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'tml_security_locked' :
			$output = tml_security_is_user_locked( $user_id ) ? esc_html__( 'Yes' ) : esc_html__( 'No' );
			break;
	}
	return $output;
}

/**
 * Add actions to each user row on the users edit page.
 *
 * @since 1.1
 *
 * @param array $actions The user row actions.
 * @param WP_User $user  The user object.
 * @return array The user row actions.
 */
function tml_security_admin_user_row_actions( $actions, $user ) {

	if ( get_current_user_id() == $user->ID ) {
		return $actions;
	}

	$is_site_users = 'site-users-network' === get_current_screen()->id;
	if ( $is_site_users ) {
		$site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	}

	$url = $is_site_users ? "site-users.php?id={$site_id}&" : 'users.php?';

	if ( tml_security_is_user_locked( $user->ID ) ) {
		$actions['unlock'] = sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( 'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
				wp_nonce_url( $url . 'action=unlock&users=' . $user->ID, 'unlock-user' )
			) ),
			__( 'Unlock', 'tml-security' )
		);
	} else {
		$actions['lock'] = sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( 'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
				wp_nonce_url( $url . 'action=lock&users=' . $user->ID, 'lock-user' )
			) ),
			__( 'Lock', 'tml-security' )
		);
	}

	return $actions;
}

/**
 * Add bulk actions to the user list table.
 *
 * @since 1.1
 *
 * @param array $bulk_actions The bulk actions.
 * @return array The bulk actions.
 */
function tml_security_admin_user_bulk_actions( $bulk_actions ) {
	$bulk_actions['lock'] = __( 'Lock', 'tml-security');
	$bulk_actions['unlock'] = __( 'Unlock', 'tml-security' );
	return $bulk_actions;
}

/**
 * Handle users edit screen actions.
 *
 * @since 1.1
 *
 * @param string    $sendback The URL to redirect to after the action has been handled.
 * @param string    $action   The action being requested.
 * @param int|array $user_ids The requested user ID or IDs.
 * @return string The URL to redirect to after the action has been handled.
 */
function tml_security_admin_handle_user_actions( $sendback, $action, $user_ids ) {
	switch ( $action ) {
		case 'lock' :
		case 'unlock' :
			$count = 0;
			foreach ( (array) $user_ids as $user_id ) {
				if ( get_current_user_id() == $user_id ) {
					continue;
				}

				if ( 'lock' == $action && ! tml_security_is_user_locked( $user_id ) ) {
					tml_security_lock_user( $user_id );
					++$count;
				} elseif ( 'unlock' == $action && tml_security_is_user_locked( $user_id ) ) {
					tml_security_unlock_user( $user_id );
					++$count;
				}
			}

			$sendback = add_query_arg( array(
				'count' => $count,
				'update' => $action,
			), wp_get_referer() );
			break;
	}
	return $sendback;
}

/**
 * Render the admin notices.
 *
 * @since 1.1
 */
function tml_security_admin_notices() {
	$messages = array();

	if ( empty( $_GET['update'] ) ) {
		return;
	}

	switch ( $_GET['update'] ) {
		case 'lock' :
			$count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
			if ( 1 == $count ) {
				$message = __( 'User locked.', 'tml-security' );
			} else {
				$message = _n( '%s user locked.', '%s users locked.', $count, 'tml-security' );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $count ) ) . '</p></div>';
			break;

		case 'unlock' :
			$count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
			if ( 1 == $count ) {
				$message = __( 'User unlocked.', 'tml-security' );
			} else {
				$message = _n( '%s user unlocked.', '%s users unlocked.', $count, 'tml-security' );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $count ) ) . '</p></div>';
			break;
	}

	if ( ! empty( $messages ) ) {
		foreach ( $messages as $message ) {
			echo $message;
		}
	}
}

/**
 * Get the security settings sections.
 *
 * @since 1.0
 *
 * @return array The security settings sections.
 */
function tml_security_admin_get_settings_sections() {
	return array(
		// General
		'tml_security_settings_general' => array(
			'title' => '',
			'callback' => '__return_null',
			'page' => 'tml-security',
		),
		// Lockout
		'tml_security_settings_lockout' => array(
			'title' => __( 'Throttling & Lockout', 'tml-security' ),
			'callback' => '__return_null',
			'page' => 'tml-security',
		),
		// Passwords
		'tml_security_settings_passwords' => array(
			'title' => __( 'Passwords', 'tml-security' ),
			'callback' => '__return_null',
			'page' => 'tml-security',
		),
	);
}

/**
 * Get the security settings fields.
 *
 * @since 1.0
 *
 * @return array The security settings fields.
 */
function tml_security_admin_get_settings_fields() {
	return array(
		// General
		'tml_security_settings_general' => array(
			// Disable wp-login.php
			'tml_security_disable_wp_login' => array(
				'title' => __( 'Disabe wp-login.php', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_checkbox_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_disable_wp_login',
					'label' => __( 'Disable traffic to wp-login.php', 'tml-security' ),
					'value' => 1,
					'checked' => get_site_option( 'tml_security_disable_wp_login' ),
				),
			),
		),
		// Lockout
		'tml_security_settings_lockout' => array(
			// Login attempts
			'tml_security_lockout_login_attempts' => array(
				'title' => __( 'Login Attempts', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_input_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_lockout_login_attempts',
					'value' => get_site_option( 'tml_security_lockout_login_attempts', 5 ),
					'input_type' => 'number',
					'input_class' => 'small-text',
				),
			),
			// Lockout threshold
			'tml_security_lockout_threshold' => array(
				'title' => __( 'Lockout Threshold', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_dropdown_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_lockout_threshold',
					'options' => tml_security_admin_get_durations(),
					'selected' => get_site_option( 'tml_security_lockout_threshold', 5 ),
				),
			),
			// Lockout duration
			'tml_security_lockout_duration' => array(
				'title' => __( 'Lockout Duration', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_dropdown_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_lockout_duration',
					'options' => tml_security_admin_get_durations(),
					'selected' => get_site_option( 'tml_security_lockout_duration', 5 ),
				),
			),
			// Lockout invalid usernames
			'tml_security_lockout_invalid_usernames' => array(
				'title' => __( 'Invalid Usernames', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_checkbox_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_lockout_invalid_usernames',
					'label' => __( 'Immediately lockout invalid usernames', 'tml-security' ),
					'value' => 1,
					'checked' => get_site_option( 'tml_security_lockout_invalid_usernames' ),
				),
			),
		),
		// Passwords
		'tml_security_settings_passwords' => array(
			// Minimum length
			'tml_security_password_minimum_length' => array(
				'title' => __( 'Minimum Length', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_input_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_security_password_minimum_length',
					'value' => get_site_option( 'tml_security_password_minimum_length', 6 ),
					'input_type' => 'number',
					'input_class' => 'small-text',
				),
			),
			// Require letters
			'tml_security_passwords_require_letters' => array(
				'title' => __( 'Requirements', 'tml-security' ),
				'callback' => 'tml_admin_setting_callback_checkbox_group_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'legend' => __( 'Requirements', 'tml-security' ),
					'options' => array(
						'tml_security_passwords_require_letters' => array(
							'label' => __( 'Require letters', 'tml-security' ),
							'value' => 1,
							'checked' => get_site_option( 'tml_security_passwords_require_letters' ),
						),
						'tml_security_passwords_require_capital_letters' => array(
							'label' => __( 'Require capital letters', 'tml-security' ),
							'value' => 1,
							'checked' => get_site_option( 'tml_security_passwords_require_capital_letters' ),
						),
						'tml_security_passwords_require_numbers' => array(
							'label' => __( 'Require numbers', 'tml-security' ),
							'value' => 1,
							'checked' => get_site_option( 'tml_security_passwords_require_numbers' ),
						),
						'tml_security_passwords_require_special_chars' => array(
							'label' => __( 'Require special characters', 'tml-security' ),
							'value' => 1,
							'checked' => get_site_option( 'tml_security_passwords_require_special_chars' ),
						),
					),
				),
			),
			// Require capital letters
			'tml_security_passwords_require_capital_letters' => array(
				'sanitize_callback' => 'intval',
			),
			// Require numbers
			'tml_security_passwords_require_numbers' => array(
				'sanitize_callback' => 'intval',
			),
			// Require special characters
			'tml_security_passwords_require_special_chars' => array(
				'sanitize_callback' => 'intval',
			),
		),
	);
}

/**
 * Get the available durations.
 *
 * @since 1.0
 *
 * @return array The available durations.
 */
function tml_security_admin_get_durations() {
	return apply_filters( 'tml_security_admin_get_durations', array(
		'5' => sprintf( __( '%d Minutes', 'tml-security' ), 5 ),
		'10' => sprintf( __( '%d Minutes', 'tml-security' ), 10 ),
		'30' => sprintf( __( '%d Minutes', 'tml-security' ), 30 ),
		'60' => __( '1 Hour', 'tml-security' ),
		'120' => sprintf( __( '%d Hours', 'tml-security' ), 2 ),
		'240' => sprintf( __( '%d Hours', 'tml-security' ), 4 ),
		'360' => sprintf( __( '%d Hours', 'tml-security' ), 6 ),
		'720' => sprintf( __( '%d Hours', 'tml-security' ), 12 ),
		'1440' => __( '1 Day', 'tml-security' ),
	) );
}

/**
 * Install the DB schema.
 *
 * @since 1.0
 */
function tml_security_admin_install_db_schema() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$prefix = $wpdb->get_blog_prefix( 0 );
	$charset_collate = $wpdb->get_charset_collate();

	$queries = array();

	$queries[] = "CREATE TABLE {$prefix}tml_security_failed_attempts (
		attempt_id bigint(20) unsigned NOT NULL auto_increment,
		attempt_ip varbinary(16) default NULL,
		attempt_username varchar(255) NOT NULL,
		attempt_date datetime NOT NULL default '0000-00-00 00:00:00',
		attempt_type varchar(20) NOT NULL default 'login',
		PRIMARY KEY  (attempt_id)
	) $charset_collate;";

	$queries[] = "CREATE TABLE {$prefix}tml_security_lockouts (
		lockout_ip varbinary(16) NOT NULL,
		attempt_id bigint(20) unsigned NOT NULL,
		PRIMARY KEY  (lockout_ip)
	) $charset_collate;";

	dbDelta( $queries );
}
