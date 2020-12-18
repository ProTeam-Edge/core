<?php

/**
 * Theme My Login Notifications Admin Functions
 *
 * @package Theme_My_Login_Notifications
 * @subpackage Administration
 */

/**
 * Print admin styles.
 *
 * @since 1.0
 */
function tml_notifications_admin_print_styles() {
	if ( ! tml_admin_is_plugin_page( 'notifications' ) ) {
		return;
	}
	?>

	<style type="text/css">
		.postbox {
			margin-bottom: 0.5em !important;
		}

		.postbox fieldset + fieldset {
			margin-top: 1em;
		}

		.postbox fieldset legend {
			font-weight: bold;
			margin: 0 0 0.5em;
		}

		.postbox .notification-actions {
			text-align: right;
		}

		@media (min-width: 576px) {

		}

		@media (min-width: 783px) {
			.postbox fieldset {
				float: left;
			}

			.postbox fieldset + fieldset {
				margin-left: 2em;
				margin-top: 0;
			}
		}
	</style>

	<?php
}

/**
 * Render the default notifications section.
 *
 * @since 1.1
 */
function tml_notifications_admin_setting_callback_default_notifications_section() {
	$current_screen = get_current_screen();

	$notifications = tml_notifications_get_default_notifications();
	foreach ( $notifications as $id => $notification ) {
		add_meta_box(
			'tml_notifications_custom_notification-' . $id,
			$notification['title'],
			'tml_notifications_admin_default_notification_meta_box',
			$current_screen->id,
			'default',
			'default',
			compact( 'id', 'notification' )
		);
	}
	?>

	<input type="submit" style="display: none;" />

	<div class="metabox-holder" data-sortable="off">
		<?php do_meta_boxes( $current_screen->id, 'default', null ); ?>
	</div>

	<?php
}

/**
 * Render a default notification meta box.
 *
 * @since 1.1
 *
 * @param null  $object Not used.
 * @param array $box    The meta box arguments.
 */
function tml_notifications_admin_default_notification_meta_box( $object, $box ) {
	$id = $box['args']['id'];

	$notification = $box['args']['notification'];

	if ( isset( $notification['hidden_fields'] ) ) {
		$hidden_fields = (array) $notification['hidden_fields'];
	} else {
		$hidden_fields = array();
	}
	?>

	<table class="form-table">
		<?php if ( ! in_array( 'recipient', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_recipient"><?php esc_html_e( 'Recipient', 'theme-my-login-notifications' ); ?></label></th>
				<td><input name="tml_notifications_default_notifications[<?php echo $id; ?>][recipient]" type="text" id="tml_notifications_default_notifications_<?php echo $id; ?>_recipient" value="<?php echo isset( $notification['recipient'] ) ? esc_attr( $notification['recipient'] ) : ''; ?>" class="regular-text" /></td>
			</tr>
		<?php endif; ?>

		<?php if ( ! in_array( 'from_name', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_from_name"><?php esc_html_e( 'From Name', 'theme-my-login-notifications' ); ?></label></th>
				<td><input name="tml_notifications_default_notifications[<?php echo $id; ?>][from_name]" type="text" id="tml_notifications_default_notifications_<?php echo $id; ?>_from_name" value="<?php echo isset( $notification['from_name'] ) ? esc_attr( $notification['from_name'] ) : ''; ?>" class="regular-text" /></td>
			</tr>
		<?php endif; ?>

		<?php if ( ! in_array( 'from_address', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_from_address"><?php esc_html_e( 'From Address', 'theme-my-login-notifications' ); ?></label></th>
				<td><input name="tml_notifications_default_notifications[<?php echo $id; ?>][from_address]" type="text" id="tml_notifications_default_notifications_<?php echo $id; ?>_from_address" value="<?php echo isset( $notification['from_address'] ) ? esc_attr( $notification['from_address'] ) : ''; ?>" class="regular-text" /></td>
			</tr>
		<?php endif; ?>

		<?php if ( ! in_array( 'format', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_format"><?php esc_html_e( 'Format', 'theme-my-login-notifications' ); ?></label></th>
				<td>
					<select name="tml_notifications_default_notifications[<?php echo $id; ?>][format]" id="tml_notifications_default_notifications_<?php echo $id; ?>_format">
						<option value="plain" <?php selected( isset( $notification['format'] ) && 'text' == $notification['format'] ); ?>><?php esc_html_e( 'Plain Text', 'theme-my-login-notifications' ); ?></option>
						<option value="html" <?php selected( isset( $notification['format'] ) && 'html' == $notification['format'] ); ?>><?php esc_html_e( 'HTML', 'theme-my-login-notifications' ); ?></option>
					</select>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! in_array( 'subject', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_subject"><?php esc_html_e( 'Subject', 'theme-my-login-notifications' ); ?></label></th>
				<td><input name="tml_notifications_default_notifications[<?php echo $id; ?>][subject]" type="text" id="tml_notifications_default_notifications_<?php echo $id; ?>_subject" value="<?php echo isset( $notification['subject'] ) ? esc_attr( $notification['subject'] ) : ''; ?>" class="large-text" /></td>
			</tr>
		<?php endif; ?>

		<tr valign="top">
			<th scope="row"><label for="tml_notifications_default_notifications_<?php echo $id; ?>_message"><?php esc_html_e( 'Message', 'theme-my-login-notifications' ); ?></label></th>
			<td>
				<?php wp_editor( isset( $notification['message'] ) ? $notification['message'] : '', 'tml_notifications_default_notifications_' . $id . '_message', array(
					'textarea_name' => 'tml_notifications_default_notifications[' . $id . '][message]',
				) ); ?>
			</td>
		</tr>

		<?php if ( ! in_array( 'disable', $hidden_fields ) ) : ?>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<input type="checkbox" name="tml_notifications_default_notifications[<?php echo $id; ?>][disable]" id="tml_notifications_default_notifications_<?php echo $id; ?>_disable" value="1" <?php checked( ! empty( $notification['disable'] ) ); ?> />
					<label for="tml_notifications_default_notifications_<?php echo $id; ?>_disable"><?php esc_html_e( 'Disable this notification', 'theme-my-login-notifications' ); ?></label>
				</td>
			</tr>
		<?php endif; ?>
	</table>

	<?php
}


/**
 * Sanitize the default notifications.
 *
 * @since 1.1
 *
 * @param array $notifications The default notifications.
 * @return array The default notifications.
 */
function tml_notifications_admin_sanitize_default_notifications( $notifications = array() ) {
	return $notifications;
}

/**
 * Render the custom notifications section.
 *
 * @since 1.0
 */
function tml_notifications_admin_setting_callback_custom_notifications_section() {
	$current_screen = get_current_screen();

	$notifications = tml_notifications_get_custom_notifications();
	foreach ( $notifications as $id => $notification ) {
		if ( ! empty( $notification['title'] ) ) {
			$notification_title = $notification['title'];
		} else {
			$notification_title = __( 'Untitled Notification', 'theme-my-login-notifications' );
		}

		add_meta_box(
			'tml_notifications_custom_notification-' . $id,
			$notification_title,
			'tml_notifications_admin_custom_notification_meta_box',
			$current_screen->id,
			'custom',
			'default',
			compact( 'id', 'notification' )
		);
	}
	?>

	<input type="submit" style="display: none;" />

	<?php submit_button( __( 'Add New', 'theme-my-login-notifications' ), 'secondary', 'add_notification', false ); ?>

	<div class="metabox-holder" data-sortable="off">
		<?php do_meta_boxes( $current_screen->id, 'custom', null ); ?>
	</div>

	<?php
}

/**
 * Render a custom notification meta box.
 *
 * @since 1.0
 *
 * @param null  $object Not used.
 * @param array $box    The meta box arguments.
 */
function tml_notifications_admin_custom_notification_meta_box( $object, $box ) {
	$id = $box['args']['id'];

	$notification = $box['args']['notification'];
	?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_title"><?php esc_html_e( 'Title', 'theme-my-login-notifications' ); ?></label></th>
			<td><input name="tml_notifications_custom_notifications[<?php echo $id; ?>][title]" type="text" id="tml_notifications_custom_notifications_<?php echo $id; ?>_title" value="<?php echo isset( $notification['title'] ) ? esc_attr( $notification['title'] ) : ''; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_recipient"><?php esc_html_e( 'Recipient', 'theme-my-login-notifications' ); ?></label></th>
			<td><input name="tml_notifications_custom_notifications[<?php echo $id; ?>][recipient]" type="text" id="tml_notifications_custom_notifications_<?php echo $id; ?>_recipient" value="<?php echo isset( $notification['recipient'] ) ? esc_attr( $notification['recipient'] ) : ''; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_from_name"><?php esc_html_e( 'From Name', 'theme-my-login-notifications' ); ?></label></th>
			<td><input name="tml_notifications_custom_notifications[<?php echo $id; ?>][from_name]" type="text" id="tml_notifications_custom_notifications_<?php echo $id; ?>_from_name" value="<?php echo isset( $notification['from_name'] ) ? esc_attr( $notification['from_name'] ) : ''; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_from_address"><?php esc_html_e( 'From Address', 'theme-my-login-notifications' ); ?></label></th>
			<td><input name="tml_notifications_custom_notifications[<?php echo $id; ?>][from_address]" type="text" id="tml_notifications_custom_notifications_<?php echo $id; ?>_from_address" value="<?php echo isset( $notification['from_address'] ) ? esc_attr( $notification['from_address'] ) : ''; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_format"><?php esc_html_e( 'Format', 'theme-my-login-notifications' ); ?></label></th>
			<td>
				<select name="tml_notifications_custom_notifications[<?php echo $id; ?>][format]" id="tml_notifications_custom_notifications_<?php echo $id; ?>_format">
					<option value="plain" <?php selected( isset( $notification['format'] ) && 'text' == $notification['format'] ); ?>><?php esc_html_e( 'Plain Text', 'theme-my-login-notifications' ); ?></option>
					<option value="html" <?php selected( isset( $notification['format']) && 'html' == $notification['format'] ); ?>><?php esc_html_e( 'HTML', 'theme-my-login-notifications' ); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_subject"><?php esc_html_e( 'Subject', 'theme-my-login-notifications' ); ?></label></th>
			<td><input name="tml_notifications_custom_notifications[<?php echo $id; ?>][subject]" type="text" id="tml_notifications_custom_notifications_<?php echo $id; ?>_subject" value="<?php echo isset( $notification['subject'] ) ? esc_attr( $notification['subject'] ) : ''; ?>" class="large-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tml_notifications_custom_notifications_<?php echo $id; ?>_message"><?php esc_html_e( 'Message', 'theme-my-login-notifications' ); ?></label></th>
			<td>
				<?php wp_editor( isset( $notification['message'] ) ? $notification['message'] : '', 'tml_notifications_custom_notifications_' . $id . '_message', array(
					'textarea_name' => 'tml_notifications_custom_notifications[' . $id . '][message]',
				) ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Triggers', 'theme-my-login-notifications' ); ?></th>
			<td>
				<?php foreach ( tml_notifications_get_trigger_groups() as $group => $triggers ) : ?>
					<fieldset>
						<legend><?php echo esc_html( $group ); ?></legend>
						<?php foreach ( $triggers as $trigger ) : ?>
							<label><input type="checkbox" name="tml_notifications_custom_notifications[<?php echo $id; ?>][triggers][]" value="<?php echo esc_attr( $trigger['name'] ); ?>" <?php checked( isset( $notification['triggers'] ) && in_array( $trigger['name'], $notification['triggers'] ) ); ?> /> <?php echo esc_html( $trigger['label'] ); ?></label><br />
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>
			</td>
		</tr>
	</table>

	<div class="notification-actions">
		<button type="submit" name="delete_notification" value="<?php echo $id; ?>" class="button button-secondary"><?php esc_html_e( 'Delete', 'theme-my-login-notifications' ); ?></button>
	</div>

	<?php
}

/**
 * Sanitize the custom notifications.
 *
 * @since 1.0
 *
 * @param array $notifications The custom notifications.
 * @return array The custom notifications.
 */
function tml_notifications_admin_sanitize_custom_notifications( $notifications = array() ) {
	// Don't double sanitize
	if ( is_multisite() ) {
		remove_filter(
			'sanitize_option_tml_notifications_custom_notifications',
			'tml_notifications_admin_sanitize_custom_notifications'
		);
	}

	if ( ! is_array( $notifications ) ) {
		$notifications = array();
	}

	if ( isset( $_POST['delete_notification'] ) ) {
		$notification_id = absint( $_POST['delete_notification'] );
		unset( $notifications[ $notification_id ] );
		add_settings_error( 'tml_notifications_settings_custom_notifications',
			'notification_deleted',
			__( 'Notification deleted.', 'theme-my-login-notifications' ),
			'updated'
		);
	} elseif ( isset( $_POST['add_notification'] ) ) {
		$notifications[] = array();
		add_settings_error( 'tml_notifications_settings_custom_notifications',
			'notification_added',
			__( 'Notification added.', 'theme-my-login-notifications' ),
			'updated'
		);
	}

	$notifications = array_values( $notifications );

	return $notifications;
}

/**
 * Get the notifications settings sections.
 *
 * @since 1.0
 *
 * @return array The notifications settings sections.
 */
function tml_notifications_admin_get_settings_sections() {
	return array(
		'tml_notifications_settings_default' => array(
			'title' => __( 'Default Notifications', 'theme-my-login-notifications' ),
			'callback' => 'tml_notifications_admin_setting_callback_default_notifications_section',
			'page' => 'theme-my-login-notifications',
		),
		'tml_notifications_settings_custom' => array(
			'title' => __( 'Custom Notifications', 'theme-my-login-notifications' ),
			'callback' => 'tml_notifications_admin_setting_callback_custom_notifications_section',
			'page' => 'theme-my-login-notifications',
		),
	);
}

/**
 * Get the notifications settings fields.
 *
 * @since 1.0
 *
 * @return array The notifications settings fields.
 */
function tml_notifications_admin_get_settings_fields() {
	return array(
		// Default notifications
		'tml_notifications_settings_default' => array(
			'tml_notifications_default_notifications' => array(
				'sanitize_callback' => 'tml_notifications_admin_sanitize_default_notifications',
			),
		),

		// Custom notifications
		'tml_notifications_settings_custom' => array(
			'tml_notifications_custom_notifications' => array(
				'sanitize_callback' => 'tml_notifications_admin_sanitize_custom_notifications',
			),
		),
	);
}

/**
 * Migrate legacy options.
 *
 * @since 1.0
 */
function tml_notifications_migrate_options() {

	// Initial migration
	$options = get_option( 'theme_my_login_email', array() );
	if ( empty( $options ) ) {
		return;
	}

	$email_maps = array(
		'new_user' => array(
			'user' => 'wp_new_user_notification_email',
			'admin' => 'wp_new_user_notification_email_admin',
		),
		'retrieve_pass' => array(
			'user' => 'tml_retrieve_password_email',
		),
		'reset_pass' => array(
			'admin' => 'wp_password_change_notification_email',
		),
		'user_activation' => array(
			'user' => 'tml_moderation_user_activation_email',
		),
		'user_approval' => array(
			'admin' => 'tml_moderation_user_approval_email',
		),
	);

	$option_maps = array(
		'user' => array(
			'mail_from_name' => 'from_name',
			'mail_from' => 'from_address',
			'mail_content_type' => 'format',
			'title' => 'subject',
			'message' => 'message',
		),
		'admin' => array(
			'admin_mail_to' => 'recipient',
			'admin_mail_from_name' => 'from_name',
			'admin_mail_from' => 'from_address',
			'admin_mail_content_type' => 'format',
			'admin_title' => 'subject',
			'admin_message' => 'message',
		),
	);

	$variable_map = array(
		'%blogname%' => '%site_name%',
		'$siteurl%' => '%site_url%',
		'%loginurl%' => '%login_url%',
		'%reseturl%' => '%reset_url%',
		'%activateurl%' => '%activation_url%',
		'%pendingurl%' => '%pending_url%',
	);

	$notifications = array();

	foreach ( $email_maps as $email => $email_map ) {
		if ( ! empty( $options[ $email ] ) ) {
			foreach ( $email_map as $type => $new_type ) {

				$notification = array();

				foreach ( $option_maps[ $type ] as $old_option => $new_option ) {
					$notification[ $new_option ] = $options[ $email ][ $old_option ];
					if ( 'subject' == $new_option || 'message' == $new_option ) {
						$notification[ $new_option ] = str_replace(
							array_keys( $variable_map ),
							array_values( $variable_map ),
							$notification[ $new_option ]
						);
					}
				}

				$notifications[ $new_type ] = $notification;
			}
		}
	}

	update_site_option( 'tml_notifications_default_notifications', $notifications );

	delete_option( 'theme_my_login_email' );
}
