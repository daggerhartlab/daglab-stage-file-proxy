=== Stage File Proxy ===
Contributors: daggerhart
Tags: development, uploads
Requires at least: 3.7
Tested up to: 7.1
Stable tag: 0.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stage File Proxy Plugin for Wordpress Development. This plugin IS NOT meant for use on production websites.

== Description ==

This plugin is used on development sites ONLY to automatically download missing files from the uploads folder when requested.  This plugin IS NOT meant for use on production websites.

== Installation ==

1. Download and enable the plugin.
2. Set the "Source Domain" on the Stage File Proxy Options settings page.
3. Make sure that the box for "Organize my uploads into month- and year-based folders" on the "Media Settings" page matches the production website. This controls the location that the file will be downloaded to within the uploads folders.

== Changelog ==

= 0.0.3 =
* Corrected plugin version numbers
* Tested up to 7.1
* Added .editorconfig and cleaned up spacing
* Added more inline documentation
* Corrected text domain usage
* Added function prefixes
* Fixed PHP warnings on settings page
* Small refactor into `includes` directory
* Improved logic around which requests we should act on and how we handle request data
* Fixed issues with handling of year/month folders
