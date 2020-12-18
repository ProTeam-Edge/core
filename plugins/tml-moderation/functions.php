<?php

/**
 * Theme My Login Moderation Functions
 *
 * @package Theme_My_Login_Moderation
 * @subpackage Functions
 */

/**
 * Get the Moderation plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Moderation The Moderation plugin instance.
 */
function tml_moderation() {
	return theme_my_login()->get_extension( 'theme-my-login-moderation' );
}

/**
 * Register actions.
 *
 * @since 1.0
 */
function tml_moderation_register_actions() {
	if ( tml_moderation_require_activation() ) {
		tml_register_action( 'activate', array(
			'title' => __( 'Activate Your Account', 'theme-my-login-moderation' ),
			'slug' => 'activate',
			'callback' => 'tml_moderation_activation_handler',
			'show_on_forms' => false,
			'show_in_slug_settings' => false,
		) );
	}
}

/**
 * Register forms.
 *
 * @since 1.0
 */
function tml_moderation_register_forms() {
	if ( tml_moderation_require_activation() ) {
		tml_register_form( 'activate', array(
			'action' => tml_get_action_url( 'activate' ),
			'render_args' => array(
				'show_links' => false,
			),
		) );
	}
}

/**
 * Register notifications for the Notifications extension.
 *
 * @since 1.0
 */
function tml_moderation_register_notification_triggers() {
	if ( ! tml_extension_exists( 'theme-my-login-notifications' ) ) {
		return;
	}

	tml_notifications_register_trigger( 'moderate_user', array(
		'label' => __( 'Moderated User', 'theme-my-login-moderation' ),
		'hook' => 'tml_moderation_moderate_user',
		'group' => __( 'Moderation', 'theme-my-login-moderation' ),
	) );

	tml_notifications_register_trigger( 'new_user_activated', array(
		'label' => __( 'New User Activated', 'theme-my-login-moderation' ),
		'hook' => 'tml_moderation_user_activated',
		'group' => __( 'Moderation', 'theme-my-login-moderation' ),
	) );

	tml_notifications_register_trigger( 'new_user_approved', array(
		'label' => __( 'New User Approved', 'theme-my-login-moderation' ),
		'hook' => 'tml_moderation_user_approved',
		'group' => __( 'Moderation', 'theme-my-login-moderation' ),
	) );

	tml_notifications_register_trigger( 'user_activation_resend', array(
		'label' => __( 'Resend User Activation', 'theme-my-login-moderation' ),
		'hook' => 'tml_moderation_resend_activation_notification',
		'group' => __( 'Moderation', 'theme-my-login-moderation' ),
	) );
}

/**
 * Moderate new users.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_moderation_moderate_user( $user_id ) {
	if ( ! tml_moderation_is_active() ) {
		return;
	}

	if ( tml_moderation_require_activation() ) {
		update_user_meta( $user_id, 'tml_moderation_requires_activation', 1 );
	}

	if ( tml_moderation_require_approval() ) {
		update_user_meta( $user_id, 'tml_moderation_requires_approval', 1 );
	}

	/**
	 * Fires after moderating a user.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'tml_moderation_moderate_user', $user_id );
}

/**
 * Determine if a user requires activation.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 * @return bool True if the user requires activation, false if not.
 */
function tml_moderation_user_requires_activation( $user_id ) {
	$required = (bool) get_user_meta( $user_id, 'tml_moderation_requires_activation', true );

	/**
	 * Filters whether a user requires email activation or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $required Whether email activation is required for the user or not.
	 * @param int  $user_id  The user ID.
	 */
	return (bool) apply_filters( 'tml_moderation_user_requires_activation', $required, $user_id );
}

/**
 * Determine if a user requires approval.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 * @return bool True if the user requires approval, false if not.
 */
function tml_moderation_user_requires_approval( $user_id ) {
	$required = (bool) get_user_meta( $user_id, 'tml_moderation_requires_approval', true );

	/**
	 * Filters whether a user requires admin approval or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $required Whether admin approval is required for the user or not.
	 * @param int  $user_id  The user ID.
	 */
	return (bool) apply_filters( 'tml_moderation_user_requires_approval', $required, $user_id );
}

/**
 * Determine if a user is pending.
 *
 * @since 1.0
 *
 * @param int $user_id Optional. The user ID. Default is current user ID.
 * @return bool True if the user is pending, false if not.
 */
function tml_moderation_is_user_pending( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( tml_moderation_user_requires_activation( $user_id ) ) {
		return true;
	}

	if ( tml_moderation_user_requires_approval( $user_id ) ) {
		return true;
	}

	return false;
}

/**
 * Handle the "activate" action.
 *
 * @since 1.0
 */
function tml_moderation_activation_handler() {
	$resend = tml_get_request_value( 'resend', 'get' );
	$login = tml_get_request_value( 'login' );
	$key = tml_get_request_value( 'key' );

	if ( $login && ( $resend || $key ) ) {
		$user = get_user_by( is_email( $login ) ? 'email' : 'login', $login );
		if ( ! $user ) {
			if ( tml_is_username_login_type() ) {
				tml_add_error( 'invalid_login', __( '<strong>ERROR</strong>: Invalid username.', 'theme-my-login-moderation' ) );
			} elseif ( tml_is_email_login_type() ) {
				tml_add_error( 'invalid_login', __( '<strong>ERROR</strong>: Invalid email address.', 'theme-my-login-moderation' ) );
			} else {
				tml_add_error( 'invalid_login', __( '<strong>ERROR</strong>: Invalid username or email address.', 'theme-my-login-moderation' ) );
			}
			return;
		} elseif ( ! tml_moderation_user_requires_activation( $user->ID ) ) {
			if ( tml_moderation_user_requires_approval( $user->ID ) ) {
				$message = __( '<strong>ERROR</strong>: This account has already been activated. However, it must be approved by an administrator. You will receive an email when this process is complete.', 'theme-my-login-moderation' );
			} else {
				$message = sprintf(
					__( '<strong>ERROR</strong>: This account has already been activated. Click <a href="%s">here</a> to log in.', 'theme-my-login-moderation' ),
					tml_get_action_url( 'login' )
				);
			}
			/**
			 * Filters the message displayed when an account is already active.
			 *
			 * @since 1.0
			 *
			 * @param string $message The "account already active" message.
			 * @param int    $user_id The user ID.
			 */
			$message = apply_filters( 'tml_moderation_account_already_active_message', $message, $user->ID );

			tml_add_error( 'account_already_active', $message );
			return;
		}

		if ( $login && $resend ) {
			/**
			 * Fires before resending the user activation notification.
			 *
			 * @since 1.0
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'tml_moderation_resend_activation_notification', $user->ID );

			tml_add_error( 'notification_sent', __( 'Please check your email for the new activation link.', 'theme-my-login-moderation' ), 'message' );
			return;

		} elseif ( $login && $key ) {

			$key = preg_replace( '/[^a-z0-9]/i', '', $key );

			if ( empty( $key ) || ! wp_check_password( $key, $user->user_activation_key ) ) {
				tml_add_error( 'invalid_key', sprintf(
					__( '<strong>ERROR</strong>: Invalid activation key. Click <a href="%s">here</a> to get a new one.', 'theme-my-login-moderation' ),
					add_query_arg( array(
						'login' => $login,
						'resend' => 'activation',
					), tml_get_action_url( 'activate' ) )
				) );
				return;
			}

			if ( ! tml_has_errors() ) {
				tml_moderation_activate_user( $user->ID );

				if ( tml_moderation_user_requires_approval( $user->ID ) ) {
					$message = __( 'Your account has been activated. However, it must be approved by an administrator. You will receive an email when this process is complete.', 'theme-my-login-moderation' );
				} elseif ( tml_allow_user_passwords() ) {
					$message = sprintf(
						__( 'Your account has been activated. You may now <a href="%s">log in</a>.', 'theme-my-login-moderation' ),
						tml_get_action_url( 'login' )
					);
				} else {
					$message = sprintf( 'Your account has been activated. Please check your email.', 'theme-my-login-moderation' );
				}
				/**
				 * Filters the message displayed when an account is successfully activated.
				 *
				 * @since 1.0
				 *
				 * @param string $message The "account activated" message.
				 * @param int    $user_id The user ID.
				 */
				$message = apply_filters( 'tml_moderation_account_activated_message', $message, $user->ID );

				tml_add_error( 'account_activated', $message, 'message' );
				return;
			}
		}
	}
}

/**
 * Activate a user.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_moderation_activate_user( $user_id ) {
	global $wpdb;

	if ( ! tml_moderation_user_requires_activation( $user_id ) ) {
		return;
	}

	delete_user_meta( $user_id, 'tml_moderation_requires_activation' );

	$wpdb->update( $wpdb->users, array(
		'user_activation_key' => '',
	), array(
		'ID' => $user_id,
	) );

	/**
	 * Fires after a user has been activated.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'tml_moderation_user_activated', $user_id );
}

/**
 * Send the new user activation notification.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_moderation_new_user_activation_notification( $user_id ) {
	global $wpdb;

	$user = get_userdata( $user_id );

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	$key = wp_generate_password( 20, false );

	$wpdb->update( $wpdb->users, array(
		'user_activation_key' => wp_hash_password( $key ),
	), array(
		'user_login' => $user->user_login,
	) );

	$activation_url = add_query_arg( array(
		'key' => $key,
		'login' => rawurlencode( $user->user_login ),
	), tml_get_action_url( 'activate' ) );

	$switched_locale = switch_to_locale( get_locale() );

	$message  = sprintf( __( 'Thanks for registering at %s! To complete the activation of your account please click the following link: ', 'theme-my-login-moderation' ), $blogname ) . "\r\n\r\n";
	$message .= $activation_url . "\r\n";

	$email = array(
		'to' => $user->user_email,
		'subject' => __( '[%s] Activate Your Account', 'theme-my-login-moderation' ),
		'message' => $message,
		'headers' => '',
	);

	/**
	 * Filters the contents of the new user activation email.
	 *
	 * @since 1.0
	 *
	 * @param array   $email {
	 *     Used to build wp_mail().
	 *
	 *     @type string $to      The recipient of the email.
	 *     @type string $subject The subject of the email.
	 *     @type string $message The body of the email.
	 *     @type string $headers The headers of the email.
	 * }
	 * @param WP_User $user     User object for new user.
	 * @param string  $blogname The site title.
	 * @param string  $key      The activation key.
	 */
	$email = apply_filters( 'tml_moderation_user_activation_email', $email, $user, $blogname, $key );

	wp_mail(
		$email['to'],
		wp_specialchars_decode( sprintf( $email['subject'], $blogname ) ),
		$email['message'],
		$email['headers']
	);

	if ( $switched_locale ) {
		restore_previous_locale();
	}
}

/**
 * Send the new user approval notification to the administrator.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_moderation_new_user_approval_admin_notification( $user_id ) {

	$user = get_userdata( $user_id );

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	$switched_locale = switch_to_locale( get_locale() );

	$message  = sprintf( __( 'New user requires approval on your blog %s:', 'theme-my-login-moderation' ), $blogname ) . "\r\n\r\n";
	$message .= sprintf( __( 'Username: %s', 'theme-my-login-moderation' ), $user->user_login ) . "\r\n";
	$message .= sprintf( __( 'E-mail: %s', 'theme-my-login-moderation' ), $user->user_email ) . "\r\n\r\n";
	$message .= __( 'To approve or deny this user:', 'theme-my-login-moderation' ) . "\r\n";
	$message .= admin_url( 'users.php?tml_moderation_status=pending-approval' );

	$email = array(
		'to' => get_option( 'admin_email' ),
		'subject' => __( '[%s] New User Awaiting Approval', 'theme-my-login-moderation' ),
		'message' => $message,
		'headers' => '',
	);

	/**
	 * Filters the contents of the new user approval email.
	 *
	 * @since 1.0
	 *
	 * @param array   $email {
	 *     Used to build wp_mail().
	 *
	 *     @type string $to      The recipient of the email.
	 *     @type string $subject The subject of the email.
	 *     @type string $message The body of the email.
	 *     @type string $headers The headers of the email.
	 * }
	 * @param WP_User $user     User object for new user.
	 * @param string  $blogname The site title.
	 */
	$email = apply_filters( 'tml_moderation_user_approval_email', $email, $user, $blogname );

	wp_mail(
		$email['to'],
		wp_specialchars_decode( sprintf( $email['subject'], $blogname ) ),
		$email['message'],
		$email['headers']
	);

	if ( $switched_locale ) {
		restore_previous_locale();
	}
}

/**
 * Send the default WP new user activation.
 *
 * @since 1.0
 *
 * @param int    $user_id The user ID.
 * @param string $notify  Whether to notify the user, the site admin or both.
 */
function tml_moderation_new_user_notification( $user_id, $notify = 'both' ) {
	$action = current_action();

	if ( 'tml_moderation_user_activated' == $action ) {
		if ( tml_moderation_user_requires_approval( $user_id ) ) {
			return;
		} else {
			$notify = 'user';
		}
	} elseif ( 'tml_moderation_user_approved' == $action ) {
		if ( tml_moderation_user_requires_activation( $user_id ) ) {
			return;
		} else {
			$notify = 'user';
		}
	} elseif ( 'tml_moderation_moderate_user' == $action ) {
		if ( tml_moderation_user_requires_approval( $user_id ) ) {
			return;
		} else {
			$notify = 'admin';
		}
	}

	tml_send_new_user_notifications( $user_id, $notify );
}

/**
 * Block pending users from logging in.
 *
 * @since 1.0
 *
 * @param WP_User $user The user object.
 * @return WP_User|WP_Error The user object on success or error object on failure.
 */
function tml_moderation_authenticate_pending_check( $user ) {
	if ( $user instanceof WP_User && tml_moderation_is_user_pending( $user->ID ) ) {
		if ( tml_moderation_user_requires_activation( $user->ID ) ) {
			$message = sprintf(
				__( '<strong>ERROR</strong>: You have not yet confirmed your e-mail address. Click <a href="%s">here</a> to resend the activation email.', 'theme-my-login' ),
				add_query_arg( array(
					'resend' => 'activation',
					'login' => $user->user_login,
				), tml_get_action_url( 'activate' ) )
			);
		} elseif ( tml_moderation_user_requires_approval( $user->ID ) ) {
			$message = __( '<strong>ERROR</strong>: Your registration has not yet been approved.', 'theme-my-login-moderation' );
		} else {
			$message = __( '<strong>ERROR</strong>: Your registration is still pending.', 'theme-my-login-moderation' );
		}
		return new WP_Error( 'pending', $message );
	}
	return $user;
}

/**
 * Block pending users from resetting their password.
 *
 * @since 1.0
 *
 * @param bool $allow   Whether the user can reset their password or not.
 * @param int  $user_id The user ID.
 * @return bool Whether the user can reset their password or not.
 */
function tml_moderation_allow_password_reset( $allow = false, $user_id = 0 ) {
	if ( tml_moderation_is_user_pending( $user_id ) ) {
		return false;
	}
	return $allow;
}

/**
 * Filter the registration redirect.
 *
 * @since 1.0
 *
 * @param string $redirect_to The registration redirect.
 * @return string The registration redirect.
 */
function tml_moderation_registration_redirect( $redirect_to = '' ) {
	if ( tml_moderation_require_activation() ) {
		$redirect_to = add_query_arg( 'pending', 'activation', tml_get_action_url( 'login' ) );
	} elseif ( tml_moderation_require_approval() ) {
		$redirect_to = add_query_arg( 'pending', 'approval', tml_get_action_url( 'login' ) );
	}
	return $redirect_to;
}

/**
 * Add moderation action messages.
 *
 * @since 1.0
 */
function tml_moderation_action_messages() {
	if ( ! tml_is_action() ) {
		return;
	}

	switch ( tml_get_request_value( 'pending' ) ) {
		case 'activation' :
			$message = __( 'Your registration was successful but you must now confirm your email address before you can log in. Please check your email and click on the link provided.', 'theme-my-login-moderation' );
			/**
			 * Filters the message displayed after a successful registration when email activation is enabled.
			 *
			 * @since 1.0
			 *
			 * @param string $message The "pending activation" message.
			 */
			$message = apply_filters( 'tml_moderation_pending_activation_message', $message );

			tml_add_error( 'pending_activation', $message, 'message' );
			break;

		case 'approval' :
			$message = __( 'Your registration was successful but you must now be approved by an administrator before you can log in. You will be notified by email once your account has been approved.', 'theme-my-login-moderation' );
			/**
			 * Filters the message displayed after a successful registration when admin approval is enabled.
			 *
			 * @since 1.0
			 *
			 * @param string $message The "pending approval" message.
			 */
			$message = apply_filters( 'tml_moderation_pending_approval_message', $message );

			tml_add_error( 'pending_approval', $message, 'message' );
			break;
	}
}

/**
 * Determine if email activation is required.
 *
 * @since 1.0
 *
 * @return bool True if email activation is required, false if not.
 */
function tml_moderation_require_activation() {
	/**
	 * Filters whether email activation is required or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $required Whether email activation is required or not.
	 */
	return apply_filters( 'tml_moderation_require_activation',
		get_option( 'tml_moderation_require_activation' )
	);
}

/**
 * Determine if admin approval is required.
 *
 * @since 1.0
 *
 * @return bool True if admin approval is required, false if not.
 */
function tml_moderation_require_approval() {
	/**
	 * Filters whether admin approval is required or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $required Whether admin approval is required or not.
	 */
	return apply_filters( 'tml_moderation_require_approval',
		get_option( 'tml_moderation_require_approval' )
	);
}

/**
 * Determine if any form of moderation is active.
 *
 * @since 1.0
 *
 * @return bool True if any form of moderation if active, false if not.
 */
function tml_moderation_is_active() {
	if ( tml_moderation_require_activation() ) {
		return true;
	}
	if ( tml_moderation_require_approval() ) {
		return true;
	}
	return false;
}
