<?php

/**
 * @file
 * Registers the options page and manages plugin settings form and validation.
 */

add_action( 'admin_init', 'stage_file_proxy_init' );
/**
 * Register our plugin settings with Settings API.
 *
 * @return void
 */
function stage_file_proxy_init() {
	register_setting(
		'stage-file-proxy-group',
		'stage-file-proxy-settings',
		'stage_file_proxy_settings_validate_and_sanitize'
	);
	add_settings_section(
		'section-1',
		__( 'Source Domain', 'stage-file-proxy' ),
		'stage_file_proxy_section_1_callback',
		'stage-file-proxy'
	);
	add_settings_field(
		'field-1-1',
		__( 'Source Domain', 'stage-file-proxy' ),
		'stage_file_proxy_source_domain_callback',
		'stage-file-proxy',
		'section-1'
	);
	add_settings_field(
		'field-1-2',
		__( 'Method', 'select' ),
		'stage_file_proxy_method_callback',
		'stage-file-proxy',
		'section-1'
	);
}

/**
 * Render the options page.
 */
function stage_file_proxy_options_page() {
	?>
	<div class="wrap">
		<h2><?php _e('Stage File Proxy Options', 'stage-file-proxy'); ?></h2>
		<form action="options.php" method="POST">
			<?php settings_fields('stage-file-proxy-group'); ?>
			<?php do_settings_sections('stage-file-proxy'); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Render the settings section description.
 *
 * @return void
 */
function stage_file_proxy_section_1_callback() {
	_e(
		'Please enter the domain where the source images are location (for example, http://www.daggerhartlab.com).',
		'stage-file-proxy'
	);
}

/**
 * Render the `source_domain` settings field.
 *
 * @return void
 */
function stage_file_proxy_source_domain_callback() {
	$settings = (array) get_option('stage-file-proxy-settings');
	$field = "source_domain";
	$value = '';
	if (!empty($settings[$field])) {
		$value = esc_attr($settings[$field]);
	}

	echo "<input type='text' name='stage-file-proxy-settings[$field]' value='$value' />";
}

/**
 * Render the `method` settings field.
 *
 * @return void
 */
function stage_file_proxy_method_callback() {
	$settings = (array) get_option('stage-file-proxy-settings');
	$field = "method";
	$value = '';
	if (!empty($settings[$field])) {
		$value = esc_attr($settings[$field]);
	}
	?>
	<select name="stage-file-proxy-settings[method]">
		<option value="download" <?php selected($value, 'download') ?>>Download</option>
		<option value="redirect" <?php selected($value, 'redirect') ?>>Redirect</option>
	</select>
	<?php
}

/**
 * Validate submitted settings.
 *
 * @param array $input The user-submitted form inputs.
 *
 * @return array
 */
function stage_file_proxy_settings_validate_and_sanitize( $input ) {
	if (filter_var($input['source_domain'], FILTER_VALIDATE_URL) !== FALSE) {
		$output['source_domain'] = trim($input['source_domain']);
	}
	else {
		add_settings_error(
			'stage-file-proxy-settings',
			'invalid-source_domain',
			'Please enter a valid domain.'
		);
	}
	$output['method'] = trim($input['method']);

	return $output;
}
