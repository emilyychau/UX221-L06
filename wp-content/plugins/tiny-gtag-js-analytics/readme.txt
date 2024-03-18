=== Tiny gtag.js Analytics ===
Contributors: lev0
Tags: Google, analytics
Requires at least: 4.6.0
Tested up to: 6.2
Stable tag: 3.0.1
Requires PHP: 5.4.0
License: GPLv2 or later

Adds the required script tags for gtag.js to your site's HTML.

== Description ==

A simple, light, ad-free plugin to enable Google's global site tag. This enables analytics and conversion tracking via **gtag.js** on your WordPress site.  You can customise it by adding extra JavaScript, and further extend the plugin's functionality using the provided `tiny_gtag_js_analytics_output` hook.

== Installation ==

1. Upload the entire, extracted plugin directory to the `wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen directly.
1. Activate the plugin through the Plugins screen.
1. Go to *Settings > gtag.js*, add your Analyics and/or AdWords IDs, and save.
1. Clear caches of any optimisation plugins, or CDNs you use on your site, if any.

== Changelog ==
 
= 3.0.1 =

* Fixed crash on first settings save.
 
= 3.0.0 =

* Support for GA4 IDs.
* Changed inputs to `tiny_gtag_js_analytics_output` filter hook, update any integration accordingly. (Does not affect typical plugin usage.)

= 2.0.0 =

* Support for AdWords IDs.
* Made tracking ID(s) available to other scripts via `tinyGtagJsOptions` global variable.
* Link to settings from Plugins page.
* Support output after `<body>` as recommended by Google.
* Fixed filter hook.
* Fixed i18n.

= 1.0.1 =

* Better form validation.
