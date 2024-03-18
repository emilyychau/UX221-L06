<?php
/*
Plugin Name: Tiny gtag.js Analytics
Description: Simple, customisable gtag.js for Analytics and/or AdWords.
Version: 3.0.1
Author: Roy Orbison
Author URI: https://profiles.wordpress.org/lev0/
Licence: GPLv2 or later
*/

define('TINY_GTAG_BASE', basename(__FILE__, '.php'));

if (is_admin()) {

add_action(
	'admin_menu'
	, function() {
		/* translators: gtag.js */
		$title = sprintf(__('Tiny %s Analytics', 'tiny-gtag-js-analytics'), 'gtag.js');
		$slug_admin = TINY_GTAG_BASE . '-admin';
		$slug_settings = TINY_GTAG_BASE . '-settings';

		add_options_page(
			esc_html($title)
			, 'gtag.js'
			, 'administrator'
			, $slug_admin
			, function() use ($title, $slug_admin, $slug_settings) {
				?>
				<div class=wrap>
					<h1><?php echo esc_html($title); ?></h1>
					<form action=options.php method=post>
						<?php
						settings_fields($slug_settings);
						do_settings_sections($slug_admin);
						submit_button();
						?>
					</form>
				</div>
				<?php
			}
		);

		add_action(
			'admin_init'
			, function() use ($slug_admin, $slug_settings) {
				$sanitised = false; # https://core.trac.wordpress.org/ticket/21989
				register_setting(
					$slug_settings
					, TINY_GTAG_BASE
					, [
						'sanitize_callback' => function($inputs) use (&$sanitised) {
							if ($sanitised) {
								return $inputs;
							}
							$sanitised = true;
							foreach ($inputs as $setting => $val) {
								switch ($setting) {
									case 'enabled':
									case 'body':
										$inputs[$setting] = (bool) $val;
										break;
									default:
										$inputs[$setting] = trim($val);
								}
							}
							$id = $inputs['ga4'] ?: $inputs['ua'] ?: $inputs['aw'];
							$inputs['script_param'] = urlencode($id);
							$inputs['configs'] = array_map('json_encode', array_filter(
								[
									$inputs['ga4'],
									$inputs['ua'],
									$inputs['aw'],
								]
								, 'strlen'
							));
							return $inputs;
						},
					]
				);

				$options = get_option(TINY_GTAG_BASE);

				$slug_sect = TINY_GTAG_BASE . '-general';
				add_settings_section(
					$slug_sect
					, _x('Settings', 'Settings page title', 'tiny-gtag-js-analytics')
					, function() {
						echo '<p>'
							, sprintf(
								/* translators: 1: G-XXXXXXXXXX, 2: UA-XXXXXXXX-X, 3: AW-XXXXXXXXX */
								esc_html__('Provide one or more of %1$s, %2$s and %3$s.', 'tiny-gtag-js-analytics')
								, '<code>G-XXXXXXXXXX</code>'
								, '<code>UA-XXXXXXXX-X</code>'
								, '<code>AW-XXXXXXXXX</code>'
							)
							, '</p>';
					}
					, $slug_admin
				);
				$name = 'enabled';
				add_settings_field(
					$name
					, __('Output enabled', 'tiny-gtag-js-analytics')
					, function() use (&$options, $name) {
						printf(
							'<input type=hidden name="%1$s[%2$s]" value="0">'
								. '<input type=checkbox name="%1$s[%2$s]" id=%2$s value="1"%3$s>'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) && !$options[$name] ? '' : ' checked'
						);
					}
					, $slug_admin
					, $slug_sect
				);
				$name = 'body';
				add_settings_field(
					$name
					, __('Place output after opening body tag', 'tiny-gtag-js-analytics')
					, function() use (&$options, $name) {
						printf(
							'<input type=hidden name="%1$s[%2$s]" value="0">'
								. '<input type=checkbox name="%1$s[%2$s]" id=%2$s value="1"%3$s>'
								. "\n" . '<p class="description">'
								. sprintf(
									/* translators: wp_body_open */
									esc_html__('Recommended, but your theme must support the %s action. Try it out.', 'tiny-gtag-js-analytics')
									, '<code>wp_body_open</code>'
								)
								. '</p>'
							, TINY_GTAG_BASE
							, $name
							, empty($options[$name]) ? '' : ' checked'
						);
					}
					, $slug_admin
					, $slug_sect
				);
				$name = 'ga4';
				add_settings_field(
					$name
					, 'G-XXXXXXXXXX'
					, function() use (&$options, $name) {
						printf(
							'<input type=text pattern="\\s*G-[A-Z\\d]+\\s*" title="'
								. esc_attr(sprintf(
									/* translators: G-XXXXXXXXXX */
									_x('%s (X\'s are uppercase alphanumeric characters)', 'GA4 ID validation', 'tiny-gtag-js-analytics')
									, 'G-XXXXXXXXXX'
								))
								. '" class=regular-text id=%1$s-%2$s name="%1$s[%2$s]" value="%3$s">'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) ? esc_attr($options[$name]) : ''
						);
					}
					, $slug_admin
					, $slug_sect
					, [
						'label_for' => TINY_GTAG_BASE . "-$name",
					]
				);
				$name = 'ua';
				add_settings_field(
					$name
					, 'UA-XXXXXXXX-X'
					, function() use (&$options, $name) {
						printf(
							'<input type=text pattern="\\s*UA-\\d+-\\d+\\s*" title="'
								. esc_attr(sprintf(
									/* translators: UA-XXXXXXXX-X */
									_x('%s (X\'s are digits)', 'Analytics ID validation', 'tiny-gtag-js-analytics')
									, 'UA-XXXXXXXX-X'
								))
								. '" class=regular-text id=%1$s-%2$s name="%1$s[%2$s]" value="%3$s">'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) ? esc_attr($options[$name]) : ''
						);
					}
					, $slug_admin
					, $slug_sect
					, [
						'label_for' => TINY_GTAG_BASE . "-$name",
					]
				);
				$name = 'aw';
				add_settings_field(
					$name
					, 'AW-XXXXXXXXX'
					, function() use (&$options, $name) {
						printf(
							'<input type=text pattern="\\s*AW-\\d+\\s*" title="'
								. esc_attr(sprintf(
									/* translators: AW-XXXXXXXXX */
									_x('%s (X\'s are digits)', 'AdWords ID validation', 'tiny-gtag-js-analytics')
									, 'AW-XXXXXXXXX'
								))
								. '" class=regular-text id=%1$s-%2$s name="%1$s[%2$s]" value="%3$s">'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) ? esc_attr($options[$name]) : ''
						);
					}
					, $slug_admin
					, $slug_sect
					, [
						'label_for' => TINY_GTAG_BASE . "-$name",
					]
				);
				$name = 'extra';
				add_settings_field(
					$name
					, __('Additional Tracking JavaScript', 'tiny-gtag-js-analytics')
					, function() use (&$options, $name) {
						printf(
							'<textarea class="regular-text code" id=%1$s-%2$s name="%1$s[%2$s]">%3$s</textarea>'
								. '<p class="description">'
								. esc_html__('Optional. Be careful, syntax errors here could break your site.', 'tiny-gtag-js-analytics')
								. '</p>'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) ? esc_html($options[$name]) : ''
						);
					}
					, $slug_admin
					, $slug_sect
					, [
						'label_for' => TINY_GTAG_BASE . "-$name",
					]
				);
				$name = 'pre_extra';
				add_settings_field(
					$name
					, __('Preliminary JavaScript', 'tiny-gtag-js-analytics')
					, function() use (&$options, $name) {
						printf(
							'<textarea class="regular-text code" id=%1$s-%2$s name="%1$s[%2$s]">%3$s</textarea>'
								. '<p class="description">'
								. sprintf(
									/* translators: gtag.js */
									esc_html__('Normally not required. Further %s set-up script output before the standard config and Additional Tracking JavaScript.', 'tiny-gtag-js-analytics')
									, 'gtag.js'
								)
								. '</p>'
							, TINY_GTAG_BASE
							, $name
							, isset($options[$name]) ? esc_html($options[$name]) : ''
						);
					}
					, $slug_admin
					, $slug_sect
					, [
						'label_for' => TINY_GTAG_BASE . "-$name",
					]
				);
			}
		);
	}
	, 9999
);
add_filter(
	'plugin_action_links_' . plugin_basename(__FILE__)
	, function($links) {
		array_unshift(
			$links
			, '<a href="' . admin_url('options-general.php?page=' . TINY_GTAG_BASE . '-admin') . '">'
				. esc_html_x('Settings', 'Plugin page link text', 'tiny-gtag-js-analytics') . '</a>'
		);
		return $links;
	}
);

}

add_action(
	'wp_head'
	, function() {
		$options = get_option(TINY_GTAG_BASE);
		if (!$options) { # assume incomplete install
			return;
		}

		# upgrade existing
		$configs = [];
		if (isset($options['config'])) {
			if ($options['config']) {
				$configs[] = $options['config'];
			}
			if (!empty($options['config2'])) {
				$configs[] = $options['config2'];
			}
			unset($options['config'], $options['config2']);
		}
		$options += [
			'enabled' => true,
			'body' => false,
			'ga4' => '',
			'aw' => '',
			'pre_extra' => '',
			'configs' => $configs,
		];

		/**
		 * Modify the script tags that are output by Tiny gtag.js Analytics
		 *
		 * @since 1.0.0
		 *
		 * @param array $options  {
		 *     Options + raw output variables of script tags.
		 *
		 *     @type boolean $enabled Whether plugin output is enabled, default: admin setting.
		 *     @type boolean $body Whether plugin output should be after the opening <body>, default: admin setting.
		 *     @type string $ga4 The G-XXXXXXXXXX ID for your reference, changing this only affects subsequent filters as it's not output directly.
		 *     @type string $ua The UA-XXXXXXXX-X ID for your reference, changing this only affects subsequent filters as it's not output directly.
		 *     @type string $aw The AW-XXXXXXXXX ID for your reference, changing this only affects subsequent filters as it's not output directly.
		 *     @type string $script_param Library script parameter, default: URL-encoded $ga4, $ua or $aw.
		 *     @type array $configs any supplied gtag config parameter(s), default: JSON-encoded $ga, $uai, and/or $aw, or empty array.
		 *     @type string $pre_extra Extra JavaScript to place on the current page before configs, default: code entered in admin settings.
		 *     @type string $extra Extra JavaScript to place on the current page after configs, default: code entered in admin settings.
		 * }
		 */
		$options = array_intersect_key((array) apply_filters('tiny_gtag_js_analytics_output', $options), $options)
			+ $options;
		if (
			!$options['enabled']
			|| !$options['script_param']
			|| !$options['configs']
		) {
			return;
		}

		$options['js_options'] = (object) array_filter(array_intersect_key(
			$options
			, array_flip([
				'ga4',
				'ua',
				'aw',
			])
		));
		$output = function() use ($options) {
			extract($options);
			if ($pre_extra) {
				$pre_extra = rtrim("\n$pre_extra");
			}
			foreach ($configs as $k => $config) {
				$configs[$k] = "\ngtag('config', $config);";
			}
			$configs = implode('', $configs);
			if ($extra) {
				$extra = rtrim("\n$extra");
			}
			$js_options = json_encode($js_options);

			echo <<<EOHTML
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=$script_param"></script>
<script>
var tinyGtagJsOptions = $js_options;
window.dataLayer || (dataLayer = []);
function gtag(){dataLayer.push(arguments);}$pre_extra
gtag('js', new Date());$configs$extra
</script>

EOHTML;
		};

		if (!$options['body'] || !function_exists('wp_body_open')) {
			$output();
		}
		else {
			add_action('wp_body_open', $output, 5);
		}
	}
	, 5
);
