<?php

/**
 * Theme My Login Redirection Admin Fucntions
 *
 * @package Theme_My_Login_Redirection
 * @subpackage Administration
 */

/**
 * Print admin styles.
 *
 * @since 1.0
 */
function tml_redirection_admin_print_styles() {
	global $plugin_page;

	if ( 'tml-redirection' != $plugin_page ) {
		return;
	}
?>

	<style type="text/css">
		.postbox {
			margin-bottom: 0.5em !important;
		}

		.postbox fieldset input[type="radio"] + input[type="text"] {
			display: none;
			margin-top: 1em;
		}

		.postbox fieldset input[type="radio"]:checked + input[type="text"] {
			display: block;
		}

		.postbox .rule-actions {
			text-align: right;
		}
	</style>

<?php
}

/**
 * Render the custom redirection section.
 *
 * @since 1.0
 */
function tml_redirection_admin_meta_boxes() {
	$current_screen = get_current_screen();

	$rules = tml_redirection_get_rules();
	foreach ( $rules as $id => $rule ) {
		if ( ! empty( $rule['title'] ) ) {
			$rule_title = $rule['title'];
		} else {
			$rule_title = __( 'Untitled Rule', 'tml-redirection' );
		}

		add_meta_box(
			'tml_redirection_rule-' . $id,
			$rule_title,
			'tml_redirection_admin_meta_box',
			$current_screen->id,
			'normal',
			'default',
			compact( 'id', 'rule' )
		);
	}
	?>

	<input type="submit" style="display: none;" />

	<?php submit_button( __( 'Add New', 'tml-redirection' ), 'secondary', 'add_rule', false ); ?>

	<div class="metabox-holder">
		<?php do_meta_boxes( $current_screen->id, 'normal', null ); ?>
	</div>

	<?php
}

/**
 * Render a redirection rule meta box.
 *
 * @since 1.0
 *
 * @param null  $object Not used.
 * @param array $box    The meta box arguments.
 */
function tml_redirection_admin_meta_box( $object, $box ) {
	$id = $box['args']['id'];

	$rule = wp_parse_args( $box['args']['rule'], tml_redirection_get_default_rule() );
	?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="tml_redirection_rules_<?php echo $id; ?>_title"><?php esc_html_e( 'Title', 'tml-redirection' ); ?></label></th>
			<td><input name="tml_redirection_rules[<?php echo $id; ?>][title]" type="text" id="tml_redirection_rules_<?php echo $id; ?>_title" value="<?php echo esc_attr( $rule['title'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Log In', 'tml-redirection' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Log In', 'tml-redirection' ); ?></span></legend>
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][login_type]" value="default"<?php checked( $rule['login_type'], 'default' ); ?> /> <?php esc_html_e( 'Default', 'tml-redirection' ); ?></label>
					<br />
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][login_type]" value="referer"<?php checked( $rule['login_type'], 'referer' ); ?> /> <?php esc_html_e( 'Referer', 'tml-redirection' ); ?></label>
					<br />
					<label>
						<input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][login_type]" value="custom"<?php checked( $rule['login_type'], 'custom' ); ?> /> <?php esc_html_e( 'Custom', 'tml-redirection' ); ?>
						<input type="text" name="tml_redirection_rules[<?php echo $id; ?>][login_url]" value="<?php echo esc_attr( $rule['login_url'] ); ?>" class="regular-text" />
					</label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Log Out', 'tml-redirection' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Log Out', 'tml-redirection' ); ?></span></legend>
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][logout_type]" value="default"<?php checked( $rule['logout_type'], 'default' ); ?> /> <?php esc_html_e( 'Default', 'tml-redirection' ); ?></label>
					<br />
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][logout_type]" value="referer"<?php checked( $rule['logout_type'], 'referer' ); ?> /> <?php esc_html_e( 'Referer', 'tml-redirection' ); ?></label>
					<br />
					<label>
						<input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][logout_type]" value="custom"<?php checked( $rule['logout_type'], 'custom' ); ?> /> <?php esc_html_e( 'Custom', 'tml-redirection' ); ?>
						<input type="text" name="tml_redirection_rules[<?php echo $id; ?>][logout_url]" value="<?php echo esc_attr( $rule['logout_url'] ); ?>" class="regular-text" />
					</label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Register', 'tml-redirection' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Register', 'tml-redirection' ); ?></span></legend>
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][register_type]" value="default"<?php checked( $rule['register_type'], 'default' ); ?> /> <?php esc_html_e( 'Default', 'tml-redirection' ); ?></label>
					<br />
					<label><input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][register_type]" value="referer"<?php checked( $rule['register_type'], 'referer' ); ?> /> <?php esc_html_e( 'Referer', 'tml-redirection' ); ?></label>
					<br />
					<label>
						<input type="radio" name="tml_redirection_rules[<?php echo $id; ?>][register_type]" value="custom"<?php checked( $rule['register_type'], 'custom' ); ?> /> <?php esc_html_e( 'Custom', 'tml-redirection' ); ?>
						<input type="text" name="tml_redirection_rules[<?php echo $id; ?>][register_url]" value="<?php echo esc_attr( $rule['register_url'] ); ?>" class="regular-text" />
					</label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Roles', 'tml-redirection' ); ?></th>
			<td>
				<?php foreach ( wp_roles()->get_names() as $role => $role_name ) : ?>
					<label><input type="checkbox" name="tml_redirection_rules[<?php echo $id; ?>][roles][]" value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $rule['roles'] ) ); ?> /> <?php echo translate_user_role( $role_name ); ?></label><br />
				<?php endforeach; ?>
			</td>
		</tr>
	</table>

	<div class="rule-actions">
		<button type="submit" name="delete_rule" value="<?php echo $id; ?>" class="button button-secondary"><?php esc_html_e( 'Delete', 'tml-redirection' ); ?></button>
	</div>

	<?php
}

/**
 * Sanitize the custom redirection rules.
 *
 * @since 1.0
 *
 * @param array $redirection The redirection rules.
 * @return array The redirection rules.
 */
function tml_redirection_admin_sanitize_rules( $rules = array() ) {
	// Don't double sanitize
	if ( is_multisite() ) {
		remove_filter(
			'sanitize_option_tml_redirection_rules',
			'tml_redirection_admin_sanitize_rules'
		);
	}

	if ( ! is_array( $rules ) ) {
		$rules = array();
	}

	$default_rule = tml_redirection_get_default_rule();

	foreach ( (array) $rules as $id => $rule ) {
		$rules[ $id ] = wp_parse_args( $rule, $default_rule );
		if ( ! is_array( $rules[ $id ]['roles'] ) ) {
			$rules[ $id ]['roles'] = array();
		}
	}

	if ( isset( $_POST['delete_rule'] ) ) {
		$rule_id = absint( $_POST['delete_rule'] );
		unset( $rules[ $rule_id ] );
		add_settings_error( 'tml_redirection_settings_rules',
			'rule_deleted',
			__( 'Rule deleted.', 'tml-redirection' ),
			'updated'
		);
	} elseif ( isset( $_POST['add_rule'] ) ) {
		$rules[] = $default_rule;
		add_settings_error( 'tml_redirection_settings_rules',
			'rule_added',
			__( 'Rule added.', 'tml-redirection' ),
			'updated'
		);
	}

	$rules = array_values( array_filter( $rules ) );

	return $rules;
}

/**
 * Get the redirection settings sections.
 *
 * @since 1.0
 *
 * @return array The redirection settings sections.
 */
function tml_redirection_admin_get_settings_sections() {
	return array(
		// Rules
		'tml_redirection_settings_rules' => array(
			'title' => __( 'Redirection Rules', 'tml-redirection' ),
			'callback' => 'tml_redirection_admin_meta_boxes',
			'page' => 'tml-redirection',
		),
	);
}

/**
 * Get the redirection settings fields.
 *
 * @since 1.0
 *
 * @return array The redirection settings fields.
 */
function tml_redirection_admin_get_settings_fields() {
	return array(
		// Rules
		'tml_redirection_settings_rules' => array(
			'tml_redirection_rules' => array(
				'sanitize_callback' => 'tml_redirection_admin_sanitize_rules',
			),
		),
	);
}

/**
 * Migrate legacy options.
 *
 * @since 1.0.1
 */
function tml_redirection_migrate_options() {
	$options = get_option( 'theme_my_login_redirection', array() );
	if ( ! empty( $options ) ) {
		$rules = get_site_option( 'tml_redirection_rules', array() );
		foreach ( wp_roles()->get_names() as $role => $role_name ) {
			if ( ! isset( $options[ $role ] ) ) {
				continue;
			}

			$rule = tml_redirection_get_default_rule();

			$rule['title'] = "Migrated {$role_name} Rule";

			if ( isset( $options[ $role ]['login_type'] ) ) {
				$rule['login_type'] = $options[ $role ]['login_type'];
			}
			if ( isset( $options[ $role ]['login_url'] ) ) {
				$rule['login_url'] = $options[ $role ]['login_url'];
			}
			if ( isset( $options[ $role ]['logout_type'] ) ) {
				$rule['logout_type'] = $options[ $role ]['logout_type'];
			}
			if ( isset( $options[ $role ]['logout_url'] ) ) {
				$rule['logout_url'] = $options[ $role ]['logout_url'];
			}

			$rule['roles'] = array( $role );

			$rules[] = $rule;
		}
		update_site_option( 'tml_redirection_rules', $rules );

		delete_option( 'theme_my_login_redirection' );
	}
}
