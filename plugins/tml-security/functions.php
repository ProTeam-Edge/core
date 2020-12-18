<?php

/**
 * Theme My Login Security Functions
 *
 * @package Theme_My_Login_Security
 * @subpackage Functions
 */

/**
 * Get the Security plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Security The Security plugin instance.
 */
function tml_security() {
	return theme_my_login()->get_extension( 'tml-security' );
}

/**
 * Handle disabling of wp-login.php
 *
 * @since 1.1
 */
function tml_security_wp_login_disabled_check() {
	global $pagenow, $wp_query;

	if ( 'wp-login.php' != $pagenow ) {
		return;
	}

	$action = tml_get_request_value( 'action' );
	if ( empty( $action ) ) {
		$action = 'login';
	}

	if ( 'postpass' == $action && tml_is_post_request() ) {
		return;
	}

	if ( 'login' == $action && tml_get_request_value( 'interim-login' ) ) {
		return;
	}

	if ( get_site_option( 'tml_security_disable_wp_login' ) ) {
		$pagenow = 'index.php';

		$wp_query->set_404();

		status_header( 404 );
		nocache_headers();

		if ( ! $template = get_404_template() ) {
			$template = get_index_template();
		}
		include $template;
		exit;
	}
}

/**
 * Handle lockout on authentication.
 *
 * @since 1.0
 *
 * @param WP_User|WP_Error $user     The user object if successfully authenticated, WP_Error object if not.
 * @param string           $username The attempted username.
 * @param string           $password The attempted password.
 * @return WP_User|WP_Error The user object if successfully authenticated, WP_Error object if not.
 */
function tml_security_login_lockout_check( $user, $username, $password ) {

	if ( tml_security_is_locked_out() ) {
		if ( tml_security_is_lockout_expired() ) {
			tml_security_delete_lockout();
		} else {
			return new WP_Error(
				'locked_out',
				__( '<strong>ERROR</strong>: You have been temporarily banned from logging in.', 'tml-security' )
			);
		}
	} elseif ( is_wp_error( $user ) ) {
		switch ( $user->get_error_code() ) {
			case 'invalid_username' :
				tml_security_insert_attempt( array(
					'attempt_username' => $username,
				) );
				if ( get_option( 'tml_security_lockout_invalid_usernames' ) ) {
					tml_security_insert_lockout();
					return new WP_Error(
						'locked_out',
						__( '<strong>ERROR</strong>: You have been temporarily banned from logging in.', 'tml-security' )
					);
				}
				break;

			case 'incorrect_password' :
				tml_security_insert_attempt( array(
					'attempt_username' => $username,
				) );
				if ( tml_security_is_login_attempt_threshold_met() ) {
					tml_security_insert_lockout();
					return new WP_Error(
						'locked_out',
						__( '<strong>ERROR</strong>: You have been temporarily banned from logging in.', 'tml-security' )
					);
				}
				break;
		}
	} elseif ( $user instanceof WP_User ) {
		if ( tml_security_is_user_locked( $user->ID ) ) {
			return new WP_Error(
				'account_locked',
				__( '<strong>ERROR</strong>: You have been temporarily banned from logging in.', 'tml-security' )
			);
		}
	}
	return $user;
}

/**
 * Insert an attempt.
 *
 * @since 1.0
 *
 * @param array $data {
 *     An array of data for inserting an attempt.
 *
 *     @param string $attempt_ip       The IP address of the attempt.
 *     @param string $attempt_username The attempted username.
 *     @param string $attempt_date     The date of the attempt.
 *     @param string $attempt_type     The type of attempt.
 *     @param bool   $attempt_failed   Whether the attempt failed or not.
 * }
 * @return int|bool The attempt ID if the record is inserted, false if not.
 */
function tml_security_insert_attempt( $data = array() ) {
	global $wpdb;

	$data = wp_parse_args( $data, array(
		'attempt_ip' => tml_security_get_ip(),
		'attempt_username' => '',
		'attempt_date' => current_time( 'mysql' ),
		'attempt_type' => 'login',
	) );

	$data['attempt_ip'] = inet_pton( $data['attempt_ip'] );

	if ( empty( $data['attempt_ip'] ) || empty( $data['attempt_username'] ) ) {
		return false;
	}

	$attempt_id = $wpdb->insert( $wpdb->get_blog_prefix( 0 ) . 'tml_security_failed_attempts', $data );

	return $attempt_id;
}

/**
 * Determine if the login attempt threshold has been met or exceeded.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return bool True if the threshold has been met or exceeded, false if not.
 */
function tml_security_is_login_attempt_threshold_met( $ip = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );
	$limit = (int) get_site_option( 'tml_security_lockout_login_attempts', 5 );
	$attempts = (int) tml_security_get_attempt_count( $ip, 'login' );

	return $attempts >= $limit;
}

/**
 * Get the number of attempts for an IP address.
 *
 * @param string $ip    The IP address.
 * @param string $type  The attempt type.
 * @param string $since The date from which to count attempts.
 * @return int The number of attempts.
 */
function tml_security_get_attempt_count( $ip = null, $type = 'login', $since = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );
	$prefix = $wpdb->get_blog_prefix( 0 );

	$threshold = get_site_option( 'tml_security_lockout_threshold', 5 ) * MINUTE_IN_SECONDS;
	if ( null === $since ) {
		$since = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $threshold );
	}

	$query = "SELECT COUNT(*) FROM {$prefix}tml_security_failed_attempts WHERE HEX(attempt_ip) = %s AND attempt_type = %s";
	if ( ! empty( $since ) ) {
		$query .= " AND attempt_date > %s";
	}

	return (int) $wpdb->get_var( $wpdb->prepare( $query, bin2hex( inet_pton( $ip ) ), $type, $since ) );
}

/**
 * Get the last attempt ID for an IP address.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return int|false The attempt ID or false if it doesn't exist.
 */
function tml_security_get_last_attempt_id( $ip = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );
	$prefix = $wpdb->get_blog_prefix( 0 );

	return $wpdb->get_var( $wpdb->prepare( "
		SELECT attempt_id FROM {$prefix}tml_security_failed_attempts
		WHERE HEX(attempt_ip) = %s
		ORDER BY attempt_date DESC
	", bin2hex( inet_pton( $ip ) ) ) );
}

/**
 * Insert a lockout.
 *
 * @since 1.0
 *
 * @param string $ip         The IP address.
 * @param int    $attempt_id The ID of the attempt that led to the lockout.
 * @return bool True if lockout is inserted, false if not.
 */
function tml_security_insert_lockout( $ip = null, $attempt_id = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );

	if ( empty( $attempt_id ) ) {
		$attempt_id = tml_security_get_last_attempt_id( $ip );
	}

	if ( empty( $ip ) || empty( $attempt_id ) ) {
		return false;
	}

	return $wpdb->insert( $wpdb->get_blog_prefix( 0 ) . 'tml_security_lockouts', array(
		'lockout_ip' => inet_pton( $ip ),
		'attempt_id' => (int) $attempt_id,
	) );
}

/**
 * Delete a lockout.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return bool True if the lockout is deleted, false if not.
 */
function tml_security_delete_lockout( $ip = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );
	$prefix = $wpdb->get_blog_prefix( 0 );

	return $wpdb->query( $wpdb->prepare(
		"DELETE FROM {$prefix}tml_security_lockouts WHERE HEX(lockout_ip) = %s",
		bin2hex( inet_pton( $ip ) )
	) );
}

/**
 * Determine if an IP address is locked out.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return bool True if the IP address is locked out, false if not.
 */
function tml_security_is_locked_out( $ip = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );

	$prefix = $wpdb->get_blog_prefix( 0 );

	return $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$prefix}tml_security_lockouts WHERE HEX(lockout_ip) = %s",
		bin2hex( inet_pton( $ip ) )
	) );
}

/**
 * Get the expiration time for a lockout.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return string The lockout expiration.
 */
function tml_security_get_lockout_expiration( $ip = null ) {
	global $wpdb;

	$ip = tml_security_get_ip( $ip );
	$prefix = $wpdb->get_blog_prefix( 0 );
	$duration = get_site_option( 'tml_security_lockout_duration', 5 ) * MINUTE_IN_SECONDS;
	$expiration = current_time( 'timestamp' );

	$date = $wpdb->get_var( $wpdb->prepare( "
		SELECT a.attempt_date FROM {$prefix}tml_security_failed_attempts a
		LEFT JOIN {$prefix}tml_security_lockouts l ON a.attempt_id = l.attempt_id
		WHERE HEX(l.lockout_ip) = %s
	", bin2hex( inet_pton( $ip ) ) ) );

	if ( ! empty( $date ) ) {
		$expiration = mysql2date( 'U', $date ) + $duration;
	}

	return $expiration;
}

/**
 * Determine if a lockout has expired.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return bool True if the lockout has expired, false if not.
 */
function tml_security_is_lockout_expired( $ip = null ) {
	return current_time( 'timestamp' ) >= tml_security_get_lockout_expiration( $ip );
}

/**
 * Get the IP address, optionally filtered.
 *
 * @since 1.0
 *
 * @param string $ip The IP address.
 * @return string The IP address.
 */
function tml_security_get_ip( $ip = null ) {
	if ( empty( $ip ) ) {
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			if ( ! empty( $_SERVER['SERVER_ADDR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['SERVER_ADDR'] ) {
				$ips = array_map( 'trim', explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
				$ip = reset( $ips );
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	}
	return $ip;
}

/**
 * Lock a user.
 *
 * @since 1.1
 *
 * @param int $user_id The user ID.
 */
function tml_security_lock_user( $user_id ) {
	/**
	 * Fires when a user is locked.
	 *
	 * @since 1.1
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'tml_security_user_locked', $user_id );

	update_user_meta( $user_id, 'is_locked', 1 );
}

/**
 * Unlock a user.
 *
 * @since 1.1
 *
 * @param int $user_id The user ID.
 */
function tml_security_unlock_user( $user_id ) {
	/**
	 * Fires when a user is unlocked.
	 *
	 * @since 1.1
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'tml_security_user_unlocked', $user_id );

	delete_user_meta( $user_id, 'is_locked' );
}

/**
 * Determine if a user is locked.
 *
 * @since 1.1
 *
 * @param int $user_id The user ID.
 * @return bool True if the user is locked, false if not.
 */
function tml_security_is_user_locked( $user_id ) {
	$is_locked = get_user_meta( $user_id, 'is_locked', true );
	/**
	 * Filters whether a user is locked or not.
	 *
	 * @since 1.1
	 *
	 * @param bool $is_locked Whether the user is locked or not.
	 * @param int  $user_id   The user ID.
	 */
	return (bool) apply_filters( 'tml_security_is_user_locked', $is_locked, $user_id );
}

/**
 * Enforce the password requirements.
 *
 * @since 1.1
 *
 * @param WP_Error $errors The error object.
 * @return WP_Error The error object.
 */
function tml_security_validate_password( $errors ) {
	if ( ! tml_is_post_request() ) {
		return $errors;
	}

	$password = '';
	if ( 'registration_errors' == current_filter() && isset( $_POST['user_pass1'] ) ) {
		$password = $_POST['user_pass1'];
	} elseif ( 'validate_password_reset' == current_filter() && isset( $_POST['pass1'] ) ) {
		$password = $_POST['pass1'];
	} elseif ( 'user_profile_update_errors' == current_filter() && isset( $_POST['pass1'] ) ) {
		$password = $_POST['pass1'];
		if ( empty( $password ) ) {
			return;
		}
	}

	if ( $min_length = get_site_option( 'tml_security_password_minimum_length', 6 ) ) {
		if ( strlen( $password ) < $min_length ) {
			$errors->add( 'password_length', sprintf(
				__( '<strong>ERROR</strong>: Passwords must be at least %d characters long.', 'tml-security' ),
				$min_length
			) );
		}
	}
	if ( get_site_option( 'tml_security_passwords_require_letters' ) ) {
		if ( ! preg_match( '/[a-z]+/', $password ) ) {
			$errors->add( 'password_missing_letters',
				__( '<strong>ERROR</strong>: Passwords must contain at least 1 letter.', 'tml-security' )
			);
		}
	}
	if ( get_site_option( 'tml_security_passwords_require_capital_letters' ) ) {
		if ( ! preg_match( '/[A-Z]+/', $password ) ) {
			$errors->add( 'password_missing_capital_letters',
				__( '<strong>ERROR</strong>: Passwords must contain at least 1 capital letter.', 'tml-security' )
			);
		}
	}
	if ( get_site_option( 'tml_security_passwords_require_numbers' ) ) {
		if ( ! preg_match( '/[0-9]+/', $password ) ) {
			$errors->add( 'password_missing_numbers',
				__( '<strong>ERROR</strong>: Passwords must contain at least 1 number.', 'tml-security' )
			);
		}
	}
	if ( get_site_option( 'tml_security_passwords_require_special_chars' ) ) {
		/**
		 * Filter the list of required special characters.
		 *
		 * @since 1.1
		 *
		 * @param array $special_chars The required special character list.
		 */
		$special_chars = apply_filters( 'tml_security_password_special_chars', array( '!', '"', '?', '%', '^', '&' ) );

		if ( ! preg_match( '/[' . preg_quote( implode( '', $special_chars ), '/' ) . ']+/', $password ) ) {
			$errors->add( 'password_missing_special_chars', sprintf(
				__( '<strong>ERROR</strong>: Passwords must contain at least one of the following: %s.', 'tml-security' ),
				implode( ' ', $special_chars )
			) );
		}
	}
	return $errors;
}
