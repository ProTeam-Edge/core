<?php

/**
 * Theme My Login Profiles Functions
 *
 * @package Theme_My_Login_Profiles
 * @subpackage Functions
 */

/**
 * Get the Profiles plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Profiles The Profiles plugin instance.
 */
function tml_profiles() {
	return theme_my_login()->get_extension( 'theme-my-login-profiles' );
}

/**
 * Register the profile action.
 *
 * @since 1.0
 */
function tml_profiles_register_action() {
	tml_register_action( 'profile', array(
		'title' => __( 'Your Profile', 'theme-my-login-profiles' ),
		'slug' => 'profile',
		'callback' => 'tml_profiles_action_handler',
		'show_on_forms' => false,
		'show_nav_menu_item' => is_user_logged_in(),
	) );
}

/**
 * Register the profile form.
 *
 * @since 1.0
 */
function tml_profiles_register_form() {
	if ( is_admin() ) {
		return;
	}

	$user = wp_get_current_user();
	$user->filter = 'edit';

	$sessions = WP_Session_Tokens::get_instance( $user->ID );

	tml_register_form( 'profile', array(
		'action' => tml_get_action_url( 'profile' ),
		'attributes' => array(
			'novalidate' => 'novalidate',
		),
		'render_args' => array(
			'show_links' => false,
		),
	) );

	$languages = get_available_languages();
	$show_admin_bar = ! tml_profiles_user_has_restricted_admin();

	if ( $languages || $show_admin_bar ) {
		/**
		 * Personal Options
		 */
		tml_add_form_field( 'profile', 'personal_options_section_header', array(
			'type' => 'custom',
			'content' => '<h3>' . esc_html_x( 'Personal Options', 'profile section', 'theme-my-login-profiles' ) . '</h3>',
			'wrap' => false,
			'priority' => 10,
		) );

		if ( $show_admin_bar ) {
			tml_add_form_field( 'profile', 'admin_bar_front', array(
				'type' => 'checkbox',
				'label' => __( 'Show Toolbar when viewing site', 'theme-my-login-profiles' ),
				'value' => 1,
				'id' => 'admin_bar_front',
				'attributes' => array_filter( array(
					'checked' => _get_admin_bar_pref( 'front', $user->ID ) ? 'checked' : '',
				) ),
				'priority' => 15,
			) );
		}

		if ( $languages ) {
			$user_locale = $user->locale;
			if ( 'en_US' === $user_locale ) {
				$user_locale = '';
			} elseif ( '' === $user_locale || ! in_array( $user_locale, $languages, true ) ) {
				$user_locale = 'site-default';
			}

			tml_add_form_field( 'profile', 'locale', array(
				'type' => 'custom',
				'content' => wp_dropdown_languages( array(
					'name' => 'locale',
					'id' => 'locale',
					'selected' => $user_locale,
					'languages' => $languages,
					'show_available_translations' => false,
					'show_option_site_default' => true,
					'echo' => false,
				) ),
				'priority' => 20,
			) );
		}
	}

	/**
	 * Name
	 */
	tml_add_form_field( 'profile', 'name_section_header', array(
		'type' => 'custom',
		'content' => '<h3>' . esc_html_x( 'Name', 'profile section', 'theme-my-login-profiles' ) . '</h3>',
		'wrap' => false,
		'priority' => 25,
	) );

	tml_add_form_field( 'profile', 'user_login', array(
		'type' => 'text',
		'label' => __( 'Username', 'theme-my-login-profiles' ),
		'description' => __( 'Usernames cannot be changed.', 'theme-my-login-profiles' ),
		'value' => $user->user_login,
		'id' => 'user_login',
		'attributes' => array(
			'disabled' => 'disabled',
		),
		'priority' => 30,
	) );

	tml_add_form_field( 'profile', 'first_name', array(
		'type' => 'text',
		'label' => __( 'First Name', 'theme-my-login-profiles' ),
		'value' => $user->first_name,
		'id' => 'first_name',
		'priority' => 35,
	) );

	tml_add_form_field( 'profile', 'last_name', array(
		'type' => 'text',
		'label' => __( 'Last Name', 'theme-my-login-profiles' ),
		'value' => $user->last_name,
		'id' => 'last_name',
		'priority' => 40,
	) );

	tml_add_form_field( 'profile', 'nickname', array(
		'type' => 'text',
		'label' => __( 'Nickname', 'theme-my-login-profiles' ),
		'value' => $user->nickname,
		'id' => 'nickname',
		'priority' => 45,
	) );

	tml_add_form_field( 'profile', 'display_name', array(
		'type' => 'dropdown',
		'label' => __( 'Display name publicly as', 'theme-my-login-profiles' ),
		'options' => tml_profiles_get_user_display_name_options( $user ),
		'value' => $user->display_name,
		'id' => 'display_name',
		'priority' => 50,
	) );

	/**
	 * Contact Info
	 */
	tml_add_form_field( 'profile', 'contact_info_section_header', array(
		'type' => 'custom',
		'content' => '<h3>' . esc_html_x( 'Contact Info', 'profile section', 'theme-my-login-profiles' ) . '</h3>',
		'wrap' => false,
		'priority' => 55,
	) );

	$new_email = get_user_meta( $user->ID, '_new_email', true );

	tml_add_form_field( 'profile', 'email', array(
		'type' => 'email',
		'label' => __( 'Email', 'theme-my-login-profiles' ),
		'description' => $new_email && $new_email['newemail'] != $user->user_email ? sprintf(
			__( 'There is a pending change of your email to %s.', 'theme-my-login-profiles' ),
			'<code>' . esc_html( $new_email['newemail'] ) . '</code>'
		) . ' ' . sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url(
				self_admin_url( 'profile.php?dismiss=' . $user->ID . '_new_email'),
				'dismiss-' . $user->ID . '_new_email'
			) ),
			__( 'Cancel', 'theme-my-login-profiles' )
		) : '',
		'value' => $user->user_email,
		'id' => 'email',
		'priority' => 60,
	) );

	tml_add_form_field( 'profile', 'url', array(
		'type' => 'url',
		'label' => __( 'Website', 'theme-my-login-profiles' ),
		'value' => $user->user_url,
		'id' => 'url',
		'priority' => 65,
	) );

	foreach ( wp_get_user_contact_methods( $user ) as $name => $label ) {
		tml_add_form_field( 'profile', $name, array(
			'type' => 'text',
			'label' => apply_filters( 'user_' . $name . '_label', $label ),
			'value' => $user->$name,
			'id' => $name,
			'priority' => 70,
		) );
	}

	/**
	 * About Yourself
	 */
	tml_add_form_field( 'profile', 'about_yourself_section_header', array(
		'type' => 'custom',
		'content' => '<h3>' . esc_html_x( 'About Yourself', 'profile section', 'theme-my-login-profiles' ) . '</h3>',
		'wrap' => false,
		'priority' => 75,
	) );

	tml_add_form_field( 'profile', 'description', array(
		'type' => 'textarea',
		'label' => __( 'Biographical Info', 'theme-my-login-profiles' ),
		'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.', 'theme-my-login-profiles' ),
		'value' => $user->description,
		'id' => 'description',
		'priority' => 80,
	) );

	if ( get_option( 'show_avatars' ) ) {
		tml_add_form_field( 'profile', 'avatar', array(
			'type' => 'custom',
			'content' => 'tml_profiles_get_avatar_field_content',
			'priority' => 85,
		) );
	}

	if ( $show_password_fields = apply_filters( 'show_password_fields', true, $user ) ) {
		/**
		 * Account Management
		 */
		tml_add_form_field( 'profile', 'account_management_section_header', array(
			'type' => 'custom',
			'content' => '<h3>' . esc_html_x( 'Account Management', 'profile section', 'theme-my-login-profiles' ) . '</h3>',
			'wrap' => false,
			'priority' => 90,
		) );

		tml_add_form_field( 'profile', 'pass1', array(
			'type' => 'password',
			'label' => __( 'New Password', 'theme-my-login-profiles' ),
			'id' => 'pass1',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority' => 95,
		) );

		tml_add_form_field( 'profile', 'pass2', array(
			'type' => 'password',
			'label' => __( 'Repeat New Password', 'theme-my-login-profiles' ),
			'description' => __( 'Type your new password again.', 'theme-my-login-profiles' ),
			'id' => 'pass2',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority' => 95,
		) );
	}

	tml_add_form_field( 'profile', 'show_user_profile', array(
		'type'        => 'action',
		'render_args' => array( $user ),
		'priority'    => 100,
	) );

	tml_add_form_field( 'profile', 'submit', array(
		'type' => 'submit',
		'value' => __( 'Update Profile', 'theme-my-login-profiles' ),
		'priority' => 105,
	) );

	tml_add_form_field( 'profile', '_wpnonce', array(
		'type' => 'hidden',
		'value' => wp_create_nonce( 'update-user_' . $user->ID ),
		'priority' => 105,
	) );

	tml_add_form_field( 'profile', 'user_id', array(
		'type' => 'hidden',
		'value' => $user->ID,
		'priority' => 105,
	) );
}

/**
 * Handle the profile action.
 *
 * @since 1.0
 */
function tml_profiles_action_handler() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/user.php';

	if ( ! is_user_logged_in() ) {
		$redirect_to = wp_login_url();
		wp_redirect( $redirect_to );
		exit;
	}

	$current_user = wp_get_current_user();

	if ( tml_get_request_value( 'updated' ) ) {
		tml_add_error( 'profile_updated', __( 'Profile updated.', 'theme-my-login-profiles' ), 'message' );

		if ( $email = get_user_meta( $current_user->ID, '_new_email', true ) ) {
			tml_add_error( 'email_not_changed', sprintf(
				__( 'Your email address has not been updated yet. Please check your inbox at %s for a confirmation email.', 'theme-my-login-profles' ),
				'<code>' . esc_html( $email['newemail'] ) . '</code>'
			), 'message' );
		}
	}

	// Execute confirmed email change. See send_confirmation_on_profile_email().
	if ( isset( $_GET[ 'newuseremail' ] ) && $current_user->ID ) {
		$new_email = get_user_meta( $current_user->ID, '_new_email', true );
		if ( $new_email && hash_equals( $new_email[ 'hash' ], $_GET[ 'newuseremail' ] ) ) {
			$user = new stdClass;
			$user->ID = $current_user->ID;
			$user->user_email = esc_html( trim( $new_email[ 'newemail' ] ) );
			if ( is_multisite() && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $current_user->user_login ) ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $user->user_email, $current_user->user_login ) );
			}
			wp_update_user( $user );
			delete_user_meta( $current_user->ID, '_new_email' );
			wp_redirect( add_query_arg( array( 'updated' => 'true' ), self_admin_url( 'profile.php' ) ) );
			die();
		} else {
			wp_redirect( add_query_arg( array( 'error' => 'new-email' ), self_admin_url( 'profile.php' ) ) );
		}
	} elseif ( ! empty( $_GET['dismiss'] ) && $current_user->ID . '_new_email' === $_GET['dismiss'] ) {
		check_admin_referer( 'dismiss-' . $current_user->ID . '_new_email' );
		delete_user_meta( $current_user->ID, '_new_email' );
		wp_redirect( add_query_arg( array('updated' => 'true'), self_admin_url( 'profile.php' ) ) );
		die();
	}

	if ( ! tml_is_post_request() ) {
		return;
	}

	if ( ! wp_verify_nonce( tml_get_request_value( '_wpnonce', 'post' ), 'update-user_' . $current_user->ID ) ) {
		wp_nonce_ays( 'update-user_' . $current_user->ID );
		exit;
	}

	if ( ! current_user_can( 'edit_user', $current_user->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to edit this user.', 'theme-my-login-profiles' ) );
	}

	do_action( 'personal_options_update', $current_user->ID );

	$errors = edit_user( $current_user->ID );
	if ( ! is_wp_error( $errors ) ) {
		$redirect_to = add_query_arg( 'updated', true, tml_get_action_url( 'profile' ) );
		wp_redirect( $redirect_to );
		exit;
	}

	tml_set_errors( $errors );
}

/**
 * Get the possible display name values for a user.
 *
 * @since 1.0
 *
 * @param int|WP_User The user ID or object.
 * @return array The possible display name for the user.
 */
function tml_profiles_get_user_display_name_options( $user ) {
	if ( ! $user instanceof WP_User ) {
		$user = get_userdata( $user );
	}

	$names = array(
		'display_nickname' => $user->nickname,
		'display_username' => $user->user_login,
	);

	if ( ! empty( $user->first_name ) ) {
		$names['display_firstname'] = $user->first_name;
	}

	if ( ! empty( $user->last_name ) ) {
		$names['display_lastname'] = $user->last_name;
	}

	if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
		$names['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
		$names['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
	}

	if ( ! in_array( $user->display_name, $names ) ) {
		$names = array( 'display_displayname' => $user->display_name ) + $names;
	}

	$names = array_unique( array_map( 'trim', $names ) );

	/**
	 * Filter the list of possible display names for a user.
	 *
	 * @since 1.0
	 *
	 * @param array $names   The possible display names for a user.
	 * @param int   $user_id The user ID.
	 */
	$names = apply_filters( 'tml_profiles_get_user_display_name_options', $names, $user->ID );

	return array_combine( $names, $names );
}

/**
 * Determine whether a user's profile should be themed or not.
 *
 * @since 1.0
 *
 * @param int|WP_User $user The user ID or object.
 * @return bool True if the user's profile should be themed, false if not.
 */
function tml_profiles_user_has_themed_profile( $user = null ) {
	if ( empty( $user ) ) {
		$user = wp_get_current_user();
	} elseif ( is_int( $user ) ) {
		$user = get_userdata( $user );
	}

	$themed = array_intersect( $user->roles, get_site_option( 'tml_profiles_themed_profile_roles', array() ) );

	/**
	 * Filter whether a user's profile should be themed or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $themed  Whether the user's profile should be themed or not.
	 * @param int  $user_id The user ID.
	 */
	return apply_filters( 'tml_profiles_user_has_themed_profile', $themed, $user->ID );
}

/**
 * Determine whether a user's admin access is restricted or not.
 *
 * @since 1.0
 *
 * @param int|WP_User $user The user ID or object.
 * @return bool True if the user's admin access is restricted, false if not.
 */
function tml_profiles_user_has_restricted_admin( $user = null ) {
	if ( empty( $user ) ) {
		$user = wp_get_current_user();
	} elseif ( is_int( $user ) ) {
		$user = get_userdata( $user );
	}

	$restricted = array_intersect( $user->roles, get_site_option( 'tml_profiles_restricted_admin_roles', array() ) );
	if ( is_super_admin( $user->ID ) ) {
		$restricted = false;
	}

	/**
	 * Filter whether a user's admin access is restricted or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $restricted Whether the user's admin access is restricted or not.
	 * @param int  $user_id    The user ID.
	 */
	return apply_filters( 'tml_profiles_user_has_restricted_admin', $restricted, $user->ID );
}

/**
 * Filter the result of get_edit_profile_url().
 *
 * @since 1.0
 *
 * @param string $url     The URL.
 * @param int    $user_id The user ID.
 * @param string $scheme  The URL scheme.
 * @return string The filtered URL.
 */
function tml_profiles_filter_edit_profile_url( $url = '', $user_id = 0, $scheme = 'admin' ) {
	if ( tml_profiles_user_has_themed_profile( $user_id ) ) {
		$url = tml_get_action_url( 'profile', $scheme, 'network_site_url' == current_filter() );
	}
	return $url;
}

/**
 * Filter the result of show_admin_bar().
 *
 * @since 1.0
 *
 * @param bool $show Whether to show the admin bar or not.
 * @return bool Whether to show the admin bar or not.
 */
function tml_profiles_filter_show_admin_bar( $show = true ) {
	if ( tml_profiles_user_has_restricted_admin() ) {
		return false;
	}
	return $show;
}

/**
 * Filter the result of wp_register().
 *
 * @since 1.0
 *
 * @param string $link The link.
 * @return string The link.
 */
function tml_profiles_filter_register( $link = '' ) {
	if ( is_user_logged_in() && tml_profiles_user_has_restricted_admin() ) {
		$link = str_replace(
			sprintf( '<a href="%s">%s</a>',
				admin_url(),
				__( 'Site Admin' )
			),
			sprintf( '<a href="%s">%s</a>',
				get_edit_profile_url(),
				__( 'Your Profile', 'theme-my-login-profiles' )
			),
			$link
		);
	}
	return $link;
}

/**
 * Filter the result of admin_url().
 *
 * @since 1.0.6
 *
 * @param string $action [description]
 * @param string $path   [description]
 * @return string The site_url action.
 */
function tml_profiles_filter_admin_url( $url, $path ) {

	if ( $position = strpos( $path, '?' ) ) {
		$path = substr( $path, 0, $position );
	}

	if ( 'profile.php' == $path ) {
		// Bail if themed profiles not enabled for this user
		if ( ! tml_profiles_user_has_themed_profile() ) {
			return $url;
		}

		// Parse the URL
		$parsed_url = parse_url( $url );

		// Parse the query
		$query = array();
		if ( ! empty( $parsed_url['query'] ) ) {
			parse_str( htmlspecialchars_decode( $parsed_url['query'] ), $query );
		}

		$url = tml_get_action_url( 'profile', 'admin' );
		if ( ! empty( $query ) ) {
			$url = add_query_arg( $query, $url );
		}
	}
	return $url;
}

/**
 * Get the avatar field content.
 *
 * @since 1.0.7
 *
 * @return string The avatar field content.
 */
function tml_profiles_get_avatar_field_content() {

	$user = wp_get_current_user();

	$content = sprintf(
		'<span class="tml-label">%s</span>',
		esc_html__( 'Profile Picture', 'theme-my-login-profiles' )
	);
	$content .= "\n" . get_avatar( $user->ID );

	$description = sprintf(
		__( 'You can change your profile picture on <a href="%s">Gravatar</a>.', 'theme-my-login-profiles' ),
		__( 'https://en.gravatar.com/', 'theme-my-login-profiles' )
	);
	$description = apply_filters( 'user_profile_picture_description', $description, $user );

	$content .= "\n" . sprintf( '<span class="tml-description">%s</span>', $description );

	return $content;
}
