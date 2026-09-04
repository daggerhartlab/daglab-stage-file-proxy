<?php
/**
 * Plugin Name:     DagLab - Stage File Proxy
 * Plugin URI:      https://github.com/daggerhartlab/daglab-stage-file-proxy
 * Description:     Proxies files from the production site uploads folder on demand. *NOT meant for use on production websites.*
 * Author:          daggerhart
 * Author URI:      https://www.daggerhartlab.com
 * Text Domain:     stage-file-proxy
 * Version:         0.2.0
 *
 * @package         Stage_File_Proxy
 */

foreach (glob(__DIR__ . '/includes/*.php') as $filename) {
	require_once $filename;
}

add_action( 'template_redirect', 'stage_file_proxy_404' );
/**
 * Handling 404 response.
 * @return void
 */
function stage_file_proxy_404(){
	if (!stage_file_proxy_should_act_on_current_request()) {
		return;
	}

	$settings = (array) get_option('stage-file-proxy-settings');
	if (empty($settings['source_domain'])) {
		return;
	}

	$domain = untrailingslashit($settings['source_domain']);
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$source = $domain . $path;

	// Handling redirect case.
	if ($settings['method'] == 'redirect') {
		header("Location: " . $source);
		exit;
	}

	// Handling download case.
	$parts = explode('/', $_SERVER['REQUEST_URI']);

	// The post date is used in `wp_upload_bits` to determine which directory
	// the file should go in. If the site doesn't use year/month directories
	// for uploads, then it should be null.
	$post_date = null;
	if (isset($parts[3], $parts[4])) {
		$post_date = "{$parts[3]}/{$parts[4]}";
	}

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
