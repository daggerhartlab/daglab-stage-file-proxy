<?php
/**
 * Plugin Name:     DagLab - Stage File Proxy
 * Plugin URI:      https://github.com/daggerhartlab/daglab-stage-file-proxy
 * Description:     Proxies files from the production site uploads folder on demand. *NOT meant for use on production websites.*
 * Author:          daggerhart
 * Author URI:      https://www.daggerhartlab.com
 * Text Domain:     stage-file-proxy
 * Version:         0.1.0
 *
 * @package         Stage_File_Proxy
 */

add_action( 'admin_menu', 'stage_file_proxy_menu' );
/**
 * Add options link to admin sidebar menu.
 *
 * @return void
 */
function stage_file_proxy_menu() {
	add_options_page(
		__('Stage File Proxy Options', 'stage-file-proxy'),
		__('Stage File Proxy Options', 'stage-file-proxy'),
		'manage_options',
		'stage-file-proxy',
		'stage_file_proxy_options_page'
	);
}

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
	$value = esc_attr($settings[$field]);

	echo "<input type='text' name='stage-file-proxy-settings[$field]' value='$value' />";
}

/**
 * Render the `method` settings field.
 *
 * @return void
 */
function stage_file_proxy_method_callback() {
	$settings = (array) get_option('stage-file-proxy-settings');
	$field    = "method";
	$value    = esc_attr($settings[$field]);
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

add_action( 'template_redirect', 'stage_file_proxy_404' );
/**
 * Handling 404 response.
 * @return void
 */
function stage_file_proxy_404(){
	if (is_404() && !isset($_REQUEST['stage_file_proxy'])) {
		$settings = (array) get_option('stage-file-proxy-settings');
		if (isset($settings['source_domain']) && $settings['source_domain'] <> '') {
			if (substr($settings['source_domain'], - 1) == '/') {
				$settings['source_domain'] = substr($settings['source_domain'], 0, - 1);
			}
			$source = $settings['source_domain'] . $_SERVER['REQUEST_URI'];

			if ($settings['method'] == 'redirect') {
				header("Location: " . $source);
				exit;
			}
			$parts = explode('/', $_SERVER['REQUEST_URI']);
			$post_date = "{$parts[3]}-{$parts[4]}-01";

			if ($file = file_get_contents($source)) {
				// Daglab - we added this for svgs, but maybe there's a better way since
				//  we have the safe_svg plugin :shrug:.
				add_filter('upload_mimes', function($t, $user) {
					$t['svg'] = "image/svg+xml";
					return $t;
				}, 10, 2);
				// End DagLab

				$upload = wp_upload_bits(basename($_SERVER['REQUEST_URI']), NULL, $file, $post_date);
				if (wp_redirect($upload['url'] . '?stage_file_proxy=true')) {
					exit;
				}
			}
		}
	}
}
