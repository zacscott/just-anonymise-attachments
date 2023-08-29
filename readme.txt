=== Just Fast Images ===
Contributors: zacscottau
Tags: performance
Requires at least: 6.0
Tested up to: 6.2.2
Stable tag: 2.0.1
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically optimise and improve image performance.

== Description ==

Automatically optimise and improve image performance.

Currently supports the following features:

- Automatic conversion to WEBP format.
- Dynamic resizing of images, replacing WordPress standard image sizes to save disk space.
- Limiting maximum image size.
- Anonymisation of attachment paths.

== Screenshots ==

1. Just Fast Images settings page.

== Changelog ==

= 2.0.6 =
* Add srcset support.

= 2.0.5 =
* Fix bug with `wp_get_attachment_metadata` filter.

= 2.0.4 =
* Add fallback if image webp encoding failed.
* Skip optimising GIFs to prevent some encoding issues.

= 2.0.3 =
* Handle query strings on asset routes gracefully.

= 2.0.2 =
* Add image resizing fallback to full size.

= 2.0.1 =
* Add image resizing fallback to thumbnail size.

= 2.0.0 =
* Update to dynamically resize images, replacing WordPress standard image sizes to save disk space.

= 1.0.1 =
* Update to enforce max image size on full images.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release.
