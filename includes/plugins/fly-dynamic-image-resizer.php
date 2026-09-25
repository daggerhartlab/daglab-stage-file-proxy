<?php

/**
 * @file
 * Contains compatibility fixes for Fly Dynamic Image Resizer plugin.
 */

add_filter( 'fly_attached_file', 'stage_file_proxy_fly_attached_file', 10, 2 );
/**
 * Downloads an image from the production source before Fly Image plugin
 * attempts to do anything with it.
 *
 * Downloading is necessary regardless of whether the Stage File Proxy "Method"
 * setting is "Redirect" or "Download," because the Fly plugin needs the file
 * to be on disk for it to work properly.
 *
 * @param string $path
 *    The filepath that Fly is looking for.
 * @param $attachment_id
 *    The attachment ID of the media item that Fly is looking for.
 *
 * @return string
 *    The Fly plugin needs the same path back from the filter that it passed in.
 *
 */
function stage_file_proxy_fly_attached_file($path, $attachment_id) {
	// Do nothing if the file already exists or for some reason the path is
	// empty.
	if (!$path || file_exists($path)) {
		return $path;
	}

	// Get the source domain for Stage File Proxy.
	$settings = (array) get_option('stage-file-proxy-settings');
	if (empty($settings['source_domain'])) {
		return $path;
	}

	// Form the Source URL that we'll download the image from.
	$uploads = wp_get_upload_dir();
	$relative = ltrim(str_replace($uploads['basedir'], '', $path), '/');
	$source = untrailingslashit($settings['source_domain']) . '/wp-content/uploads/' . $relative;

	// Request the image from production source.
	$response = wp_remote_get($source, ['timeout' => 15]);
	if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
		return $path;
	}

	// Ensure the right directories exist and write the file locally.
	wp_mkdir_p(dirname($path));
	file_put_contents($path, wp_remote_retrieve_body($response));

	return $path;
}
