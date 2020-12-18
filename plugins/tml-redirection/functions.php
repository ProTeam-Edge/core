<?php

/**
 * Theme My Login Redirection Functions
 *
 * @package Theme_My_Login_Redirection
 * @subpackage Functions
 */

/**
 * Get the Redirection plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Redirection The Redirection plugin instance.
 */
function tml_redirection() {
	return theme_my_login()->get_extension( 'tml-redirection' );
}

/**
 * Get the redirection rules.
 *
 * @since 1.0
 *
 * @return array The redirection rules.
 */
function tml_redirection_get_rules() {
	$rules = get_site_option( 'tml_redirection_rules', array() );

	/**
	 * Filter the redirection rules.
	 *
	 * @since 7.0
	 *
	 * @param array $rules The redirection rules.
	 */
	return (array) apply_filters( 'tml_redirection_get_rules', $rules );
}

/**
 * Get the default rule structure.
 *
 * @since 1.0
 *
 * @return array The default rule structure.
 */
function tml_redirection_get_default_rule() {
	return array(
		'title' => '',
		'login_type' => 'default',
		'login_url' => '',
		'logout_type' => 'default',
		'logout_url' => '',
		'register_type' => 'default',
		'register_url' => '',
		'roles' => array(),
	);
}

/**
 * Handle log in redirection.
 *
 * @since 1.0
 *
 * @param string $redirect_to The redirect URL.
 * @param string $requested_redirect_to The requested redirect URL.
 * @param WP_User $user The user object.
 */
function tml_redirection_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	return tml_redirection_get_redirect_url( $user, 'login', $redirect_to );
}

/**
 * Handle log out redirection.
 *
 * @since 1.0
 *
 * @param string $redirect_to The redirect URL.
 * @param string $requested_redirect_to The requested redirect URL.
 * @param WP_User $user The user object.
 */
function tml_redirection_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
	return tml_redirection_get_redirect_url( $user, 'logout', $redirect_to );
}

/**
 * Handle registration redirection.
 *
 * @since 1.1
 *
 * @param string  $redirect_to The redirect URL.
 * @param WP_User $user        The user object.
 */
function tml_redirection_registration_redirect( $redirect_to, $user ) {
	return tml_redirection_get_redirect_url( $user, 'register', $redirect_to );
}

/**
 * Get the proper redirect URL based on the user and the type.
 *
 * @since 1.0
 *
 * @param WP_User $user The user object.
 * @param string $action The action being redirected.
 * @param string $default The default redirect URL.
 * @return string The redirect URL.
 */
function tml_redirection_get_redirect_url( $user, $action = 'login', $default = '' ) {
	if ( ! $user instanceof WP_User ) {
		return $default;
	}

	if ( is_super_admin() ) {
		$roles = array( 'administrator' );
	} else {
		$roles = (array) $user->roles;
	}

	$type = $url = '';
	foreach ( tml_redirection_get_rules() as $rule ) {
		if ( array_intersect( $roles, $rule['roles'] ) ) {
			$type = $rule["{$action}_type"];
			$url = $rule["{$action}_url"];
		}
	}

	switch ( $type ) {
		case 'referer' :
			if ( ! empty( $_COOKIE['login_referer'] ) ) {
				$redirect_to = wp_unslash( $_COOKIE['login_referer'] );
				setcookie( 'login_referer', ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
				if ( SITECOOKIEPATH != COOKIEPATH ) {
					setcookie( 'login_referer', ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH, COOKIE_DOMAIN );
				}
			} else {
				$redirect_to = wp_get_referer();
			}
			break;

		case 'custom' :
			/**
			 * Filters the replacement variables available for custom redirection URLs.
			 *
			 * @since 1.0
			 *
			 * @param array $variables The replacement variables.
			 * @param WP_User $user The user object.
			 */
			$variables = apply_filters( 'tml_redirection_custom_url_variables', array(
				'%user_id%' => $user->ID,
				'%user_nicename%' => $user->user_nicename,
			), $user );

			$redirect_to = str_replace( array_keys( $variables ), array_values( $variables ), $url );
			break;

		default :
			$redirect_to = $default;
	}

	if ( empty( $redirect_to ) ) {
		$redirect_to = $default;
	}

	return $redirect_to;
}

/**
 * Set a referer cookie when neccessary.
 *
 * @since 1.0.2
 */
function tml_redirection_set_referer_cookie() {
	if ( ! $action = tml_get_action() ) {
		return;
	}

	if ( ! in_array( $action->get_name(), array( 'login', 'register', 'lostpassword' ) ) ) {
		return;
	}

	if ( tml_is_post_request() ) {
		return;
	}

	if ( tml_get_request_value( 'loggedout' ) ) {
		return;
	}

	if ( ! $referer = tml_get_request_value( 'redirect_to', 'GET' ) ) {
		if ( ! $referer = wp_get_referer() ) {
			return;
		}
	}

	foreach ( tml_get_actions() as $action ) {
		if ( strpos( $referer, $action->get_url() ) === 0 ) {
			return;
		}
	}

	setcookie( 'login_referer', $referer, 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	if ( SITECOOKIEPATH != COOKIEPATH ) {
		setcookie( 'login_referer', $referer, 0, SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	}
}
