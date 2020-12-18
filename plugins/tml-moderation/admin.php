<?php

/**
 * Theme My Login Moderation Admin Functions
 *
 * @package Theme_My_Login_Moderation
 * @subpackage Administration
 */

/**
 * Approves a user.
 *
 * @since 1.0
 *
 * @param int $user_id The user ID.
 */
function tml_moderation_admin_approve_user( $user_id ) {

	if ( ! tml_moderation_user_requires_approval( $user_id ) ) {
		return;
	}

	delete_user_meta( $user_id, 'tml_moderation_requires_approval' );

	/**
	 * Fires after a user has been approved.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'tml_moderation_user_approved', $user_id );
}

/**
 * Add args yo yhr users list table query.
 *
 * @since 1.0
 *
 * @param array $args The users list table query args.
 * @return array The users list table query args.
 */
function tml_moderation_admin_users_list_table_query_args( $args ) {
	if ( isset( $_REQUEST['tml_moderation_status'] ) ) {
		switch ( $_REQUEST['tml_moderation_status'] ) {
			case 'pending-activation' :
				$args['meta_key'] = 'tml_moderation_requires_activation';
				$args['meta_compare'] = 'EXISTS';
				break;

			case 'pending-approval' :
				$args['meta_key'] = 'tml_moderation_requires_approval';
				$args['meta_compare'] = 'EXISTS';
				break;

			case 'active' :
				$args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key' => 'tml_moderation_requires_activation',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key' => 'tml_moderation_requires_approval',
						'compare' => 'NOT EXISTS',
					),
				);
				break;
		}
	}
	return $args;
}

/**
 * Add pending to the user views.
 *
 * @since 1.0.3
 *
 * @param array $views The user views.
 * @return array The user views.
 */
function tml_moderation_admin_user_views( $views ) {
	$pending_activation = new WP_User_Query( array(
		'meta_key' => 'tml_moderation_requires_activation',
		'meta_compare' => 'EXISTS',
		'number' => -1,
		'fields' => 'ID',
	) );

	if ( $pending_activation->total_users ) {
		$name = sprintf(
			_x( 'Pending Activation <span class="count">(%s)</span>', $pending_activation->total_users, 'users', 'theme-my-login-moderation' ),
			number_format_i18n( $pending_activation->total_users )
		);
		$views['pending_activation'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			esc_url( add_query_arg( 'tml_moderation_status', 'pending-activation', 'users.php' ) ),
			'pending-activation' == tml_get_request_value( 'tml_moderation_status' ) ? ' class="current" aria-current="page"' : '',
			$name
		);
	}

	$pending_approval = new WP_User_Query( array(
		'meta_key' => 'tml_moderation_requires_approval',
		'meta_compare' => 'EXISTS',
		'number' => -1,
		'fields' => 'ID',
	) );

	if ( $pending_approval->total_users ) {
		$name = sprintf(
			_x( 'Pending Approval <span class="count">(%s)</span>', $pending_approval->total_users, 'users', 'theme-my-login-moderation' ),
			number_format_i18n( $pending_approval->total_users )
		);
		$views['pending_approval'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			esc_url( add_query_arg( 'tml_moderation_status', 'pending-approval', 'users.php' ) ),
			'pending-approval' == tml_get_request_value( 'tml_moderation_status' ) ? ' class="current" aria-current="page"' : '',
			$name
		);
	}

	if ( tml_get_request_value( 'tml_moderation_status' ) ) {
		$views['all'] = str_replace( array( ' class="current"', ' aria-current="page"' ), '', $views['all'] );
	}

	return $views;
}

/**
 * Add custom columns to the user list table.
 *
 * @since 1.0
 *
 * @param array $columns The user list table columns.
 * @return The user list table columns.
 */
function tml_moderation_admin_user_columns( $columns ) {
	// Find the role column
	$index = array_search( 'role', array_keys( $columns ) );

	// Use the position after the role column if found, or after the last column if not
	$index = ( false === $index ) ? count( $columns ) : $index + 1;

	// Insert the column
	return array_merge( array_slice( $columns, 0, $index ), array(
		'tml_moderation_status' => __( 'Status', 'theme-my-login-moderation' ),
	), array_slice( $columns, $index ) );
}

/**
 * Handle custom columns in the user list table.
 *
 * @since 1.0
 *
 * @param string $output      The column output.
 * @param string $column_name The column name.
 * @param int    $user_id     The user ID.
 * @return string The column output.
 */
function tml_moderation_admin_user_custom_columns( $output, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'tml_moderation_status' :
			$statuses = array();
			if ( ! tml_moderation_is_user_pending( $user_id ) ) {
				$statuses[] = sprintf( '<a href="%s">%s</a>',
					add_query_arg( 'tml_moderation_status', 'active', 'users.php' ),
					esc_html__( 'Active', 'theme-my-login-moderation' )
				);
			} else {
				if ( tml_moderation_user_requires_activation( $user_id ) ) {
					$statuses[] = sprintf( '<a href="%s">%s</a>',
						add_query_arg( 'tml_moderation_status', 'pending-activation', 'users.php' ),
						esc_html__( 'Pending Activation', 'theme-my-login-moderation' )
					);
				}
				if ( tml_moderation_user_requires_approval( $user_id ) ) {
					$statuses[] = sprintf( '<a href="%s">%s</a>',
						add_query_arg( 'tml_moderation_status', 'pending-approval', 'users.php' ),
						esc_html__( 'Pending Approval', 'theme-my-login-moderation' )
					);
				}
			}
			$output .= implode( '<br />', $statuses );
			break;
	}
	return $output;
}

/**
 * Add actions to each user row on the users edit page.
 *
 * @since 1.0
 *
 * @param array $actions The user row actions.
 * @param WP_User $user  The user object.
 * @return array The user row actions.
 */
function tml_moderation_admin_user_row_actions( $actions, $user ) {

	if ( get_current_user_id() == $user->ID ) {
		return $actions;
	}

	if ( ! tml_moderation_is_user_pending( $user->ID ) ) {
		return $actions;
	}

	$is_site_users = 'site-users-network' === get_current_screen()->id;
	if ( $is_site_users ) {
		$site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	}

	$url = $is_site_users ? "site-users.php?id={$site_id}&" : 'users.php?';

	if ( tml_moderation_user_requires_activation( $user->ID ) ) {
		$actions['resend-activation'] = sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( 'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
				wp_nonce_url( $url . 'action=resend-activation&users=' . $user->ID, 'resend-activation' )
			) ),
			__( 'Resend Activation', 'theme-my-login-moderation' )
		);
		$actions['activate-user'] = sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( 'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
				wp_nonce_url( $url . 'action=activate-user&users=' . $user->ID, 'activate-user' )
			) ),
			__( 'Activate', 'theme-my-login-moderation' )
		);
	}

	if ( tml_moderation_user_requires_approval( $user->ID ) ) {
		$actions['approve-user'] = sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( 'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
				wp_nonce_url( $url . 'action=approve-user&users=' . $user->ID, 'approve-user' )
			) ),
			__( 'Approve', 'theme-my-login-moderation' )
		);
	}

	return $actions;
}

/**
 * Add bulk actions to the user list table.
 *
 * @since 1.0
 *
 * @param array $bulk_actions The bulk actions.
 * @return array The bulk actions.
 */
function tml_moderation_admin_user_bulk_actions( $bulk_actions ) {
	if ( tml_moderation_require_activation() ) {
		$bulk_actions['resend-activation'] = __( 'Resend Activation', 'theme-my-login-moderation');
		$bulk_actions['activate-user'] = __( 'Activate', 'theme-my-login-moderation' );
	}
	if ( tml_moderation_require_approval() ) {
		$bulk_actions['approve-user'] = __( 'Approve', 'theme-my-login-moderation' );
	}
	return $bulk_actions;
}

/**
 * Handle users edit screen actions.
 *
 * @since 1.0
 *
 * @param string    $sendback The URL to redirect to after the action has been handled.
 * @param string    $action   The action being requested.
 * @param int|array $user_ids The requested user ID or IDs.
 * @return string The URL to redirect to after the action has been handled.
 */
function tml_moderation_admin_handle_user_actions( $sendback, $action, $user_ids ) {
	switch ( $action ) {
		case 'resend-activation' :
		case 'activate-user' :
		case 'approve-user' :
			$count = 0;
			foreach ( (array) $user_ids as $user_id ) {
				if ( get_current_user_id() == $user_id ) {
					continue;
				}

				if ( 'resend-activation' == $action && tml_moderation_user_requires_activation( $user_id ) ) {
					tml_moderation_new_user_activation_notification( $user_id );
					++$count;
				} elseif ( 'activate-user' == $action && tml_moderation_user_requires_activation( $user_id ) ) {
					tml_moderation_activate_user( $user_id );
					++$count;
				} elseif ( 'approve-user' == $action && tml_moderation_user_requires_approval( $user_id ) ) {
					tml_moderation_admin_approve_user( $user_id );
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
 * @since 1.0
 */
function tml_moderation_admin_notices() {
	$messages = array();

	if ( empty( $_GET['update'] ) ) {
		return;
	}

	switch ( $_GET['update'] ) {
		case 'resend-activation' :
			$count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
			if ( 1 == $count ) {
				$message = __( 'Activation email sent.', 'theme-my-login-moderation' );
			} else {
				$message = _n( 'Activation email sent to %s user.', 'Activation email sent to %s users.', $count );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $count ) ) . '</p></div>';
			break;

		case 'activate-user' :
			$count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
			if ( 1 == $count ) {
				$message = __( 'User activated.', 'theme-my-login-moderation' );
			} else {
				$message = _n( '%s user activated.', '%s users activated.', $count );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $count ) ) . '</p></div>';
			break;

		case 'approve-user' :
			$count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
			if ( 1 == $count ) {
				$message = __( 'User approved.', 'theme-my-login-moderation' );
			} else {
				$message = _n( '%s user approved.', '%s users approved.', $count );
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
* Get the moderation settings sections.
*
* @since 1.0
*
* @return array The moderation settings sections.
*/
function tml_moderation_admin_get_settings_sections() {
	return array(
		// General
		'tml_moderation_settings_general' => array(
			'title' => '',
			'callback' => '__return_null',
			'page' => 'theme-my-login-moderation',
		),
	);
}

/**
* Get the moderation settings fields.
*
* @since 1.0
*
* @return array The moderation settings fields.
*/
function tml_moderation_admin_get_settings_fields() {
	return array(
		// General
		'tml_moderation_settings_general' => array(
			// Require activation
			'tml_moderation_require_activation' => array(
				'title' => __( 'Moderation Type', 'theme-my-login-moderation' ),
				'callback' => 'tml_admin_setting_callback_checkbox_group_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'legend' => __( 'Moderation Type', 'theme-my-login-moderation' ),
					'options' => array(
						'tml_moderation_require_activation' => array(
							'label' => __( 'Require email activation', 'theme-my-login-moderation' ),
							'value' => '1',
							'checked' => get_site_option( 'tml_moderation_require_activation' ),
						),
						'tml_moderation_require_approval' => array(
							'label' => __( 'Require admin approval', 'theme-my-login-moderation' ),
							'value' => '1',
							'checked' => get_site_option( 'tml_moderation_require_approval' ),
						),
					),
				),
			),
			// Require approval
			'tml_moderation_require_approval' => array(
				'sanitize_callback' => 'intval',
			),
		),
	);
}
