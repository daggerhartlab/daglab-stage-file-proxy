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

foreach (glob(__DIR__ . '/includes/*.php') as $filename) {
	require_once $filename;
}

add_action( 'template_redirect', 'stage_file_proxy_404' );
/**
 * Handling 404 response.
 * @return void
 */
function stage_file_proxy_404(){
	// We only care about 404s.
	if (!is_404()) {
		return;
	}
	// Ignore if we've already processed the request.
	if (isset($_REQUEST['stage_file_proxy'])) {
		return;
	}

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
