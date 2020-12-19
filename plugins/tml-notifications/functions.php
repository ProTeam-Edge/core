<?php

/**
 * Theme My Login Notifications Functions
 *
 * @package Theme_My_Login_Notifications
 * @subpackage Functions
 */

/**
 * Get the Notifications plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Notifications The Notifications plugin instance.
 */
function tml_notifications() {
	return theme_my_login()->get_extension( 'theme-my-login-notifications' );
}

/**
 * Register the default notification triggers.
 *
 * @since 1.0
 */
function tml_notifications_register_default_triggers() {
	tml_notifications_register_trigger( 'new_user_registered', array(
		'label' => __( 'New User Registered', 'theme-my-login-notifications' ),
		'hook' => 'register_new_user',
		'group' => __( 'Core', 'theme-my-login-notifications' ),
	) );

	tml_notifications_register_trigger( 'new_user_created', array(
		'label' => __( 'New User Created', 'theme-my-login-notficiations' ),
		'hook' => 'edit_user_created_user',
		'group' => __( 'Core', 'theme-my-login-notifications' ),
	) );

	tml_notifications_register_trigger( 'lost_password_request', array(
		'label' => __( 'Lost Password Request', 'theme-my-login-notifications' ),
		'hook' => 'retrieved_password_key',
		'args' => array( 'user', 'key' ),
		'group' => __( 'Core', 'theme-my-login-notifications' ),
	) );

	tml_notifications_register_trigger( 'password_changed', array(
		'label' => __( 'Password Changed', 'theme-my-login-notifications' ),
		'hook' => 'after_password_reset',
		'group' => __( 'Core', 'theme-my-login-notifications' ),
	) );
}

/**
 * Register a notification trigger.
 *
 * @since 1.0
 *
 * @param string $trigger The trigger name.
 * @param array  $args {
 *     Optional. An array of arguments for registering a notification trigger.
 * }
 * @return array The notification trigger data.
 */
function tml_notifications_register_trigger( $trigger, $args = array() ) {
	return tml_notifications()->register_trigger( $trigger, $args );
}

/**
 * Get a notification trigger.
 *
 * @since 1.0
 *
 * @param string $trigger The trigger name.
 * @return array|bool The trigger data if it exists or false otherwise.
 */
function tml_notifications_get_trigger( $trigger ) {
	return tml_notifications()->get_trigger( $trigger );
}

/**
 * Get the registered notification triggers.
 *
 * @since 1.0
 *
 * @return array The registered notification triggers.
 */
function tml_notifications_get_triggers() {
	return tml_notifications()->get_triggers();
}

/**
 * Get the registered notification trigger hooks.
 *
 * @since 1.0
 *
 * @return array The registered notification trigger hooks.
 */
function tml_notifications_get_trigger_hooks() {
	$hooks = array();
	foreach ( tml_notifications_get_triggers() as $trigger ) {
		$hooks[ $trigger['hook'] ] = $trigger['name'];
	}
	return $hooks;
}

/**
 * Get the registered notification trigger groups.
 *
 * @since 1.0
 *
 * @return array The registered notification trigger groups.
 */
function tml_notifications_get_trigger_groups() {
	$groups = array();
	foreach ( tml_notifications_get_triggers() as $trigger ) {
		$groups[ $trigger['group'] ][] = $trigger;
	}
	return $groups;
}

/**
 * Get the default notifications.
 *
 * @since 1.1
 *
 * @return array The default notifications.
 */
function tml_notifications_get_default_notifications() {
	$saved_notifications = (array) get_site_option( 'tml_notifications_default_notifications', array() );

	$default_notifications = array(
		'wp_new_user_notification_email' => array(
			'title' => __( 'New User Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient' ),
		),
		'wp_new_user_notification_email_admin' => array(
			'title' => __( 'New User Admin Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array(),
		),
		'tml_retrieve_password_email' => array(
			'title' => __( 'Retrieve Password Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient', 'disable' ),
		),
		'password_change_email' => array(
			'title' => __( 'Password Change Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient' ),
		),
		'wp_password_change_notification_email' => array(
			'title' => __( 'Password Change Admin Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array(),
		),
		'email_change_confirmation_email' => array(
			'title' => __( 'Email Change Confirmation Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient', 'from_name', 'from_address', 'format', 'subject', 'disable' ),
		),
		'email_change_email' => array(
			'title' => __( 'Email Change Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient' ),
		),
	);

	if ( tml_extension_exists( 'theme-my-login-moderation' ) ) {
		$default_notifications['tml_moderation_user_activation_email'] = array(
			'title' => __( 'User Activation Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array( 'recipient', 'disable' ),
		);
		$default_notifications['tml_moderation_user_approval_email'] = array(
			'title' => __( 'User Approval Admin Notification', 'theme-my-login-notifications' ),
			'hidden_fields' => array(),
		);
	}


	$notifications = array_merge_recursive( $saved_notifications, $default_notifications );

	/**
	 * Filter the default notifications.
	 *
	 * @since 1.0
	 *
	 * @param array $notifications The default notifications.
	 */
	return (array) apply_filters( 'tml_notifications_get_default_notifications', $notifications );
}

/**
 * Get a default notification.
 *
 * @since 1.1
 *
 * @return array The notfication or false if it doesn't exist.
 */
function tml_notifications_get_default_notification( $notification ) {
	$notifications = tml_notifications_get_default_notifications();
	if ( isset( $notifications[ $notification ] ) ) {
		return $notifications[ $notification ];
	}
	return false;
}

/**
 * Determine if a default notification is disabled.
 *
 * @since 1.1
 *
 * @param string $notification The notification name.
 * @return bool Whether the notification is disabled or not.
 */
function tml_notifications_is_default_notification_disabled( $notification ) {
	if ( ! $notification = tml_notifications_get_default_notification( $notification ) ) {
		return false;
	}
	return ! empty( $notification['disable'] );
}

/**
 * Get the custom notifications.
 *
 * @since 1.0
 *
 * @return array The custom notifications.
 */
function tml_notifications_get_custom_notifications() {
	$notifications = get_site_option( 'tml_notifications_custom_notifications', array() );

	/**
	 * Filter the custom notifications.
	 *
	 * @since 1.0
	 *
	 * @param array $notifications The custom notifications.
	 */
	return (array) apply_filters( 'tml_notifications_get_custom_notifications', $notifications );
}

/**
 * Handle user notifications.
 *
 * @since 1.0
 */
function tml_notifications_user_notification_handler() {
	$current_action = current_action();
	$trigger_hooks = tml_notifications_get_trigger_hooks();

	if ( ! isset( $trigger_hooks[ $current_action ] ) ) {
		return;
	}

	$trigger = tml_notifications_get_trigger( $trigger_hooks[ $current_action ] );

	$args = array_combine( $trigger['args'], array_slice( func_get_args(), 0, count( $trigger['args'] ) ) );
	if ( ! isset( $args['user'] ) ) {
		return;
	}

	if ( is_array( $args['user'] ) ) {
		$args['user'] = (object) $args['user'];
	}
	$args['user'] = new WP_User( $args['user'] );

	$variables = array();
	switch ( $current_action ) {
		case 'retrieved_password_key' :
			$variables['%reset_url%'] = network_site_url(
				sprintf( 'wp-login.php?action=rp&key=%s&login=%s', $args['key'], rawurlencode( $args['user']->user_login ) ),
				'login'
			);
			break;
	}

	foreach ( tml_notifications_get_custom_notifications() as $notification ) {
		if ( ! isset( $notification['triggers'] ) ) {
			continue;
		}

		if ( ! in_array( $trigger['name'], $notification['triggers'] ) ) {
			continue;
		}

		$recipient = $notification['recipient'];
		if ( $args['user'] instanceof WP_User ) {
			if ( empty( $recipient ) ) {
				$recipient = $args['user']->user_email;
			}
		}

		if ( empty( $recipient ) ) {
			continue;
		}

		$subject = tml_notifications_replace_variables( $notification['subject'], $args['user'], $variables );
		$message = tml_notifications_replace_variables( $notification['message'], $args['user'], $variables );

		$headers = array();
		if ( ! empty( $notification['from_name'] ) && ! empty( $notification['from_address'] ) ) {
			$headers[] = 'From: "' . $notification['from_name'] . '" <' . $notification['from_address'] . '>';
		} elseif ( ! empty( $notification['from_address'] ) ) {
			$headers[] = 'From: ' . $notification['from_address'];
		}
		if ( 'html' == $notification['format'] ) {
			$headers[] = 'Content-Type: text/html';

			$message = wpautop( $message );
		} else {
			$message = preg_replace( "/(\r\n|\r|\n)/", "\r\n", $message );
		}

		wp_mail( $recipient, $subject, $message, $headers );
	}
}

/**
 * Store the current password reset key, since WordPress doesn't pass it to all filters.
 *
 * @since 1.1
 *
 * @param string $user_login The user login.
 * @param string $key        The password reset key.
 */
function tml_notifications_retrieve_password_key( $user_login, $key ) {
	tml_set_data( 'password_reset_key', $key );
}


/**
 * Apply filters to most of the standard WP email filters.
 *
 * @since 1.1
 *
 * @param array $email             The email arguments.
 * @param array|int|string|WP_User The user data, ID, login, or object.
 * @return array The email arguments.
 */
function tml_notifications_filter_default_notification( $email, $user ) {
	$current_filter = current_filter();
	$replacements = array();

	if ( ! $user instanceof WP_User ) {
		if ( is_array( $user ) ) {
			$user = new WP_User( (object) $user );
		} elseif ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		} else {
			$user = get_user_by( 'login', $user );
		}
	}

	switch ( $current_filter ) {
		case 'wp_new_user_notification_email' :
		case 'tml_retrieve_password_email' :
			$replacements['%reset_url%'] = add_query_arg( array(
				'key' => tml_get_data( 'password_reset_key' ),
				'login' => rawurlencode( $user->user_login ),
			), network_site_url( 'wp-login.php?action=rp', 'login') );
			break;

		case 'email_change_email' :
			$user_data = func_get_arg( 2 );
			$replacements['%new_email%'] = $user_data['user_email'];
			break;

		case 'tml_moderation_user_activation_email' :
			$replacements['%activation_url%'] = add_query_arg( array(
				'key' => func_get_arg( 3 ),
				'login' => rawurlencode( $user->user_login ),
			), tml_get_action_url( 'activate' ) );
			break;
	}

	if ( ! $notification = tml_notifications_get_default_notification( $current_filter ) ) {
		return $email;
	}

	if ( ! empty( $notification['recipient'] ) ) {
		$email['to'] = $notification['recipient'];
	}

	if ( ! empty( $notification['subject'] ) ) {
		$email['subject'] = tml_notifications_replace_variables( $notification['subject'], $user, $replacements );
	}

	if ( ! empty( $notification['message'] ) ) {
		if ( 'html' == $notification['format'] ) {
			$message = wpautop( $notification['message'] );
		} else {
			$message = preg_replace( "/(\r\n|\r|\n)/", "\r\n", $notification['message'] );
		}
		$email['message'] = tml_notifications_replace_variables( $message, $user, $replacements );
	}

	if ( ! is_array( $email['headers'] ) ) {
		$email['headers'] = array();
	}

	if ( ! empty( $notification['from_name'] ) && ! empty( $notification['from_address'] ) ) {
		$email['headers'][] = "From: {$notification['from_name']} <{$notification['from_address']}>";
	} elseif ( ! empty( $notification['from_address'] ) ) {
		$email['headers'][] = "From: {$notification['from_address']}";
	}

	if ( ! empty( $notification['format'] ) ) {
		$email['headers'][] = "Content-Type: text/{$notification['format']}";
	}

	return $email;
}

/**
 * Filter the new user email content.
 *
 * @since 1.1
 *
 * @param string $content The new user email content.
 * @param array  $args    The new user email arguments.
 * @return string The new user email content.
 */
function tml_notifications_filter_new_user_email_content( $content, $args ) {
	if ( ! $notification = tml_notifications_get_default_notification( 'email_change_confirmation_email' ) ) {
		return $content;
	}

	if ( empty( $notification['message'] ) ) {
		return $content;
	}

	return tml_notifications_replace_variables( $notification['message'], wp_get_current_user(), array(
		'%confirm_url%' => esc_url( admin_url( 'profile.php?newuseremail=' . $args['hash'] ) ),
		'%new_email%' => $args['newemail'],
	) );
}

/**
 * Replace variables matching a pattern in a string.
 *
 * @since 1.0
 *
 * @param string      $input The input string.
 * @param int|WP_User $user  The user ID or object.
 * @param array       $replacements The additional replacement variables.
 * @return string The input string with known variables replaced.
 */
function tml_notifications_replace_variables( $input, $user = null, $replacements = array() ) {
	$defaults = array(
		'%site_name%' => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
		'%site_description%' => wp_specialchars_decode( get_option( 'blogdescription' ), ENT_QUOTES ),
		'%site_url%' => site_url(),
		'%home_url%' => home_url(),
		'%login_url%' => wp_login_url(),
		'%user_ip%' => $_SERVER['REMOTE_ADDR'] ,
	);

	if ( is_multisite() ) {
		$defaults = array_merge( $defaults, array(
			'%site_name%' => get_network()->site_name,
			'%site_url%' => network_site_url(),
			'%home_url%' => network_home_url(),
		) );
	}

	$replacements = wp_parse_args( $replacements, $defaults );

	if ( ! empty( $user ) && ! $user instanceof WP_User ) {
		$user = new WP_User( $user );
	}

	if ( $user instanceof WP_User ) {
		preg_match_all( '/%([a-zA-Z0-9-_]*)%/', $input, $matches );

		foreach ( $matches[0] as $key => $match ) {
			if ( ! isset( $replacements[ $match ] ) && isset( $user->{ $matches[1][ $key ] } ) ) {
				$replacements[ $match ] = $user->{ $matches[1][ $key ] };
			}
		}
	}

	/**
	 * Filters the notification replacement variables.
	 *
	 * @since 1.0
	 *
	 * @param array   $replacements The replacement variables.
	 * @param WP_User $user         The user object.
	 */
	$replacements = apply_filters( 'tml_notifications_replace_variables', $replacements, $user );

	if ( empty( $replacements ) ) {
		return $input;
	}

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $input );
}
