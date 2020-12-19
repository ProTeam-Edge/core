<?php

/**
 * Theme My Login Avatars Functions
 *
 * @package Theme_My_Login_Avatars
 * @subpackage Functions
 */

/**
 * Get the Avatars plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Avatars The Avatars plugin instance.
 */
function tml_avatars() {
	return theme_my_login()->get_extension( 'tml-avatars' );
}

/**
 * Determine if a user has a custom avatar.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 * @return bool True if the user has a custom avatar, false if not.
 */
function tml_avatars_user_has_avatar( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$avatar = get_user_meta( $user_id, 'tml_avatar', true );

	/**
	 * Filter whether a user has a custom avatar or not.
	 *
	 * @since 1.0
	 *
	 * @param bool $avatar  Whether the user has a custom avatar or not.
	 * @param int  $user_id The user ID.
	 */
	return (bool) apply_filters( 'tml_avatars_user_has_avatar', (bool) $avatar, $user_id );
}

/**
 * Remove a user's custom avatar.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_avatars_remove_avatar( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( ! tml_avatars_user_has_avatar( $user_id ) ) {
		return;
	}

	$uploads = wp_get_upload_dir();

	$file = trailingslashit( $uploads['basedir'] ) . get_user_meta( $user_id, 'tml_avatar', true );

	wp_delete_file( $file );

	delete_user_meta( $user_id, 'tml_avatar' );
}

/**
 * Get a user's avatar URL.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 * @return string The user's avatar URL.
 */
function tml_avatars_get_avatar_url( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( ! tml_avatars_user_has_avatar( $user_id ) ) {
		return;
	}

	$uploads = wp_get_upload_dir();

	return trailingslashit( $uploads['baseurl'] ) . get_user_meta( $user_id, 'tml_avatar', true );
}

/**
 * Set the upload directory for avatars.
 *
 * @since 1.0
 *
 * @param array $uploads The upload directory data.
 * @return array The upload directory data.
 */
function tml_avatars_upload_dir( $uploads ) {
	$uploads['subdir'] = '/tml-avatars';
	$uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
	$uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];
	return $uploads;
}

/**
 * Handle the avatar upload.
 *
 * @since 1.0
 *
 * @param WP_Error $errors The errors.
 * @param bool     $update Whether this is an update or not.
 * @param WP_User  $user   The user object.
 */
function tml_avatars_handle_avatar_upload( $errors, $update, $user ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! is_uploaded_file( $_FILES['avatar']['tmp_name'] ) ) {
		return;
	}

	if ( $errors->get_error_code() ) {
		return;
	}

	add_filter( 'upload_dir', 'tml_avatars_upload_dir' );

	$result = wp_handle_upload( $_FILES['avatar'], array(
		'mimes' => array(
			'jpg|jpeg|jpe' =>'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
		),
		'test_form' => false,
	) );

	remove_filter( 'upload_dir', 'tml_avatars_upload_dir' );

	if ( isset( $result['error'] ) ) {
		$errors->add( 'upload_failed', $result['error'] );
		return $errors;
	}

	$editor = wp_get_image_editor( $result['file'] );
	if ( is_wp_error( $editor ) ) {
		wp_delete_file( $result['file'] );
		$errors->add( $editor->get_error_code(), $editor->get_error_message() );
		return $errors;
	}

	$width = get_option( 'thumbnail_size_w', 150 );
	$height = get_option( 'thumbnail_size_h', 150 );
	$crop = get_option( 'thumbnail_crop' );

	$size = $editor->get_size();
	if ( image_resize_dimensions( $size['width'], $size['height'], $width, $height, $crop ) ) {
		$resized = $editor->resize( $width, $height, $crop );
		if ( is_wp_error( $resized ) ) {
			wp_delete_file( $result['file'] );
			$errors->add( $resized->get_error_code(), $resized->get_error_message() );
			return $errors;
		}

		$saved = $editor->save( $result['file'] );
		if ( is_wp_error( $saved ) ) {
			wp_delete_file( $result['file'] );
			$errors->add( $saved->get_error_code(), $saved->get_error_message() );
			return $errors;
		}
	}

	tml_avatars_remove_avatar( $user->ID );

	update_user_meta( $user->ID, 'tml_avatar', _wp_relative_upload_path( $result['file'] ) );

	return $errors;
}

/**
 * Handle the avatar removal.
 *
 * @since 1.0
 */
function tml_avatars_handle_avatar_removal() {
	global $pagenow;

	if ( ! ( isset( $_REQUEST['remove'] ) && 'avatar' == $_REQUEST['remove'] ) ) {
		return;
	}

	if ( wp_verify_nonce( tml_get_request_value( '_wpnonce' ), 'remove-avatar' ) ) {
		$user_id = 'user-edit.php' == $pagenow ? tml_get_request_value( 'user_id' ) : get_current_user_id();
		tml_avatars_remove_avatar( $user_id );
	}

	$redirect_to = remove_query_arg( array( 'remove', '_wpnonce' ) );
	wp_redirect( $redirect_to );
	exit;
}

/**
 * Filter the avatar URL.
 *
 * @since 1.0
 *
 * @param string $url         The avatar URL.
 * @param mixed  $id_or_email The user's ID or email.
 * @param array  $args        An array of arguments for displaying an avatar.
 * @return string The avatar URL.
 */
function tml_avatars_filter_avatar_url( $url, $id_or_email, $args ) {
	if ( ! empty( $args['force_default'] ) ) {
		return $url;
	}

	$url = get_site_option( 'tml_avatars_disable_gravatars' ) ? false : $url;

	if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
		$id_or_email = get_comment( $id_or_email );
	}

	if ( is_numeric( $id_or_email ) ) {
		$user_id = $id_or_email;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		if ( $user = get_user_by( 'email', $id_or_email ) ) {
			$user_id = $user->ID;
		}
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = $id_or_email->post_author;
	} elseif ($id_or_email instanceof WP_Comment ) {
		if ( ! is_avatar_comment_type( get_comment_type( $id_or_email ) ) ) {
			return $url;
		}
		if ( ! empty( $id_or_email->user_id ) ) {
			$user_id = $id_or_email->user_id;
		}
		if ( empty( $user_id ) && ! empty( $id_or_email->comment_author_email ) ) {
			if ( $user = get_user_by( 'email', $id_or_email->comment_author_email ) ) {
				$user_id = $user->ID;
			}
		}
	}

	if ( empty( $user_id ) || ! tml_avatars_user_has_avatar( $user_id ) ) {
		return $url;
	}

	return tml_avatars_get_avatar_url( $user_id );
}

/**
 * Filter the user profile picture description.
 *
 * @since 1.0
 *
 * @param string  $description The user profile picutre description.
 * @param WP_User $user        The user object.
 * @return string The user profile picture description.
 */
function tml_avatars_user_profile_picture_description( $description, $user ) {
	$description = sprintf(
		'<input type="file" name="avatar" value="%s" />',
		__( 'Choose File', 'tml-avatars' )
	);

	if ( tml_avatars_user_has_avatar( $user->ID ) ) {
		$description = sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_nonce_url( add_query_arg( 'remove', 'avatar' ), 'remove-avatar' ),
			__( 'Remove', 'tml-avatars' )
		) . ' ' . __( 'or', 'tml-avatars' ) . ' ' . $description;
	}

	return $description;
}

/**
 * Add enctype to TML profile form.
 *
 * @since 1.0
 */
function tml_avatars_add_enctype_to_tml_profile() {
	if ( $form = tml_get_form( 'profile' ) ) {
		$form->add_attribute( 'enctype', 'multipart/form-data' );
	}
}
