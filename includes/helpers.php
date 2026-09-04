<?php

/**
 * @file
 * Contains general helper functions.
 */

/**
 * Get a list of file extensions to be proxied.
 *
 * @return string[]
 */
function stage_file_proxy_get_proxied_file_extensions() {
	return [
		'jpg',
		'jpeg',
		'png',
		'gif',
		'webp',
		'svg',
		'mp4',
		'mp3',
		'wav',
		'pdf',
	];
}

/**
 * Get the file extension for the current request (e.g. 'png').
 *
 * @return string
 */
function stage_file_proxy_get_current_request_file_extension() {
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

/**
 * Whether we should act on the current request.
 *
 * @return bool
 */
function stage_file_proxy_should_act_on_current_request() {
	// We only care about 404s.
	if (!is_404()) {
		return false;
	}

	// Ignore if we've already processed the request.
	if (isset($_REQUEST['stage_file_proxy'])) {
		return false;
	}

	// We can't do anything if we don't have a request URI.
	if (!isset($_SERVER['REQUEST_URI'])) {
		return false;
	}

	// Do nothing if the file extension isn't one we care about.
	$extension = stage_file_proxy_get_current_request_file_extension();
	if (!in_array($extension, stage_file_proxy_get_proxied_file_extensions())) {
		return false;
	}

	return true;
}
