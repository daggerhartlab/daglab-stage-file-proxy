<?php

/**
 * @file
 * Handles admin menu integration.
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
