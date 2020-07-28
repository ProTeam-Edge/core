<?php

/**
 * Theme My Login Notifications Admin Functions
 *
 * @package Theme_My_Login_Notifications
 * @subpackage Administration
 */

/**
 * Check if a user's profile should be themed and redirect accordingly.
 *
 * @since 1.0
 */
function tml_profiles_admin_themed_profile_check() {
	global $pagenow;

	if ( 'profile.php' != $pagenow ) {
		return;
	}

	if ( ! tml_profiles_user_has_themed_profile() ) {
		return;
	}

	wp_redirect( tml_get_action_url( 'profile' ) );
	exit;
}

/**
 * Check if a user's admin access is restricted and redirect accordingly.
 *
 * @since 1.0
 */
function tml_profiles_admin_restricted_admin_check() {
	if ( defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( ! tml_profiles_user_has_restricted_admin() ) {
		return;
	}

	wp_redirect( tml_get_action_url( 'profile' ) );
	exit;
}

/**
 * Get the profiles settings sections.
 *
 * @since 1.0
 *
 * @return array The profiles settings fields.
 */
function tml_profiles_admin_get_settings_sections() {
	return array(
		// General
		'tml_profiles_settings_general' => array(
			'title' => '',
			'callback' => '__return_null',
			'page' => 'theme-my-login-profiles',
		),
	);
}

/**
 * Get the profiles settings fields.
 *
 * @since 1.0
 *
 * @return array The profiles settings fields.
 */
function tml_profiles_admin_get_settings_fields() {
	$themed_profile_roles = get_site_option( 'tml_profiles_themed_profile_roles', array() );
	$themed_profile_roles_options = array();

	$restricted_admin_roles = get_site_option( 'tml_profiles_restricted_admin_roles', array() );
	$restricted_admin_roles_options = array();

	foreach ( wp_roles()->get_names() as $role => $role_name ) {

		$themed_profile_roles_options['tml_profiles_themed_profile_roles[' . $role . ']'] = array(
			'label' => translate_user_role( $role_name ),
			'value' => $role,
			'checked' => in_array( $role, $themed_profile_roles ),
		);

		$restricted_admin_roles_options['tml_profiles_restricted_admin_roles[' . $role . ']'] = array(
			'label' => translate_user_role( $role_name ),
			'value' => $role,
			'checked' => in_array( $role, $restricted_admin_roles ),
		);
	}

	return array(
		// General
		'tml_profiles_settings_general' => array(
			// Themed Profile Roles
			'tml_profiles_themed_profile_roles' => array(
				'title' => __( 'Themed Profile Roles', 'theme-my-login-profiles' ),
				'callback' => 'tml_admin_setting_callback_checkbox_group_field',
				'sanitize_callback' => 'tml_profiles_admin_sanitize_roles',
				'args' => array(
					'legend' => __( 'Themed Profile Roles', 'theme-my-login-profiles' ),
					'options' => $themed_profile_roles_options,
				),
			),
			// Restricted Admin Roles
			'tml_profiles_restricted_admin_roles' => array(
				'title' => __( 'Restricted Admin Roles', 'theme-my-login-profiles' ),
				'callback' => 'tml_admin_setting_callback_checkbox_group_field',
				'sanitize_callback' => 'tml_profiles_admin_sanitize_roles',
				'args' => array(
					'legend' => __( 'Restricted Admin Roles', 'theme-my-login-profiles' ),
					'options' => $restricted_admin_roles_options,
				),
			),
		),
	);
}

/**
 * Sanitize an array of roles.
 *
 * @since 1.0
 *
 * @param array $roles The roles array.
 * @return array The roles array.
 */
function tml_profiles_admin_sanitize_roles( $roles = array() ) {
	if ( ! is_array( $roles ) ) {
		$roles = array();
	}
	return array_values( array_map( 'sanitize_text_field', $roles ) );
}
