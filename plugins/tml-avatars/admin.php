<?php

/**
 * Theme My Login Avatars Admin Functions
 *
 * @package Theme_My_Login_Avatars
 * @subpackage Administration
 */

/**
 * Add enctype to profile form.
 *
 * @since 1.0
 */
function tml_avatars_admin_add_enctype_to_wp_profile() {
	echo ' enctype="multipart/form-data"';
}

/**
 * Get the avatars settings sections.
 *
 * @since 1.0
 *
 * @return array The avatars settings sections.
 */
function tml_avatars_admin_get_settings_sections() {
	return array(
		// General
		'tml_avatars_settings_general' => array(
			'title' => '',
			'callback' => '__return_null',
			'page' => 'tml-avatars',
		),
	);
}

/**
 * Get the avatars settings fields.
 *
 * @since 1.0
 *
 * @return array The avatars settings fields.
 */
function tml_avatars_admin_get_settings_fields() {
	return array(
		// General
		'tml_avatars_settings_general' => array(
			// Disable Gravatars
			'tml_avatars_disable_gravatars' => array(
				'title' => __( 'Gravatars', 'theme-my-login' ),
				'callback' => 'tml_admin_setting_callback_checkbox_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'label_for' => 'tml_avatars_disable_gravatars',
					'label' => __( 'Disable Gravatars', 'tml-avatars' ),
					'value' => '1',
					'checked' => get_site_option( 'tml_avatars_disable_gravatars' ),
				),
			),
		),
	);
}
