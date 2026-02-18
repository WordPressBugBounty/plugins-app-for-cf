<?php

namespace DigitalPoint\Cloudflare\Helper;

class WordPress
{
	public static function isLocaleSupported(&$locales = [])
	{
		$locales = array('en_US');
		return in_array(get_locale(), $locales);
	}

	public static function addAsset($type = 'css')
	{
		if ($type === 'css')
		{
			wp_enqueue_style('app-for-cf_admin_css', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/cf/css/admin.min.css', [], APP_FOR_CLOUDFLARE_VERSION);
		}
		elseif($type === 'js')
		{
			wp_enqueue_script('app-for-cf_admin_js', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/cf/js/admin.min.js', [], APP_FOR_CLOUDFLARE_VERSION, ['in_footer' => true]);
		}
		elseif($type === 'chart')
		{
			wp_enqueue_script('app-for-cf_chartjs_js', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/chartjs/chart.umd.js', [], '4.5.1', ['in_footer' => true]);
			wp_enqueue_script('app-for-cf_chart_js', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/cf/js/chart.min.js', [], APP_FOR_CLOUDFLARE_VERSION, ['in_footer' => true]);
		}
		elseif($type === 'notice')
		{
			wp_enqueue_script('app-for-cf_notice_js', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/cf/js/notice.min.js', [], APP_FOR_CLOUDFLARE_VERSION, ['in_footer' => true]);
		}
		elseif($type === 'css_admin_plugin')
		{
			wp_enqueue_style('app-for-cf_admin_plugin_css', APP_FOR_CLOUDFLARE_PLUGIN_URL . 'assets/cf/css/admin_plugin.min.css', [], APP_FOR_CLOUDFLARE_VERSION);
		}
	}

	public static function getApi()
	{
		return new \DigitalPoint\Cloudflare\Api\Cloudflare();
	}

	public static function sanitizeSettings($input, $addTo = true, $isMultisite = false)
	{
		if (!$isMultisite)
		{
			if ($addTo)
			{
				$defaults = get_option('app_for_cf');
			}
			else
			{
				$defaults = \DigitalPoint\Cloudflare\Setup::defaults();
			}

			$checkLicense = !empty($input['cfLicenseKey']) && (empty($defaults['cfLicenseKey']) || $input['cfLicenseKey'] !== $defaults['cfLicenseKey']);
		}
		else
		{
			if ($addTo)
			{
				$defaults = get_site_option('app_for_cf');
			}
			else
			{
				$defaults = \DigitalPoint\Cloudflare\Setup::defaultsMultisite();
			}
		}

		$input = array_merge((array)$defaults, $input);

		if (is_array($input))
		{
			// in case $defaults weren't an array to start with.
			unset($input[0]);

			foreach($input as $name => $item)
			{
				if (is_array($item))
				{
					unset($input[$name][0]);

					foreach($item as $subItemName => $subItem)
					{
						if (!is_array($subItem))
						{
							$input[$name][$subItemName] = wp_strip_all_tags($subItem);
						}
					}
				}
				else
				{
					if ($name !== 'extra_js' || !current_user_can('unfiltered_html'))
					{
						$input[$name] = wp_strip_all_tags($item);
					}
				}
			}
		}
		else
		{
			$input = wp_strip_all_tags($input);
		}

		if (!$isMultisite)
		{
			// System to allow network settings to be used as defaults for a site (if they are null, they will take on the value from network-wide settings)
			if (!empty($_POST['fromMultisite']) && is_array($_POST['fromMultisite'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
			{
				foreach ($_POST['fromMultisite'] as $key => $multisiteItem) /* @phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
				{
					$key = sanitize_text_field($key);

					if (is_array($multisiteItem))
					{
						foreach ($multisiteItem as $subKey => $multisiteSubItem)
						{
							$subKey = sanitize_text_field($subKey);

							if ($multisiteSubItem)
							{
								$input[$key][$subKey] = '';
								unset($input['network_exclude'][$key][$subKey]);
							}
							else
							{
								$input['network_exclude'][$key][$subKey] = true;
							}
						}
					}
					else
					{
						if ($multisiteItem)
						{
							$input[$key] = '';
							unset($input['network_exclude'][$key]);
						}
						else
						{
							$input['network_exclude'][$key] = true;
						}
					}
				}
			}

			if (!array_key_exists('cfLicenseKey', $input))
			{
				$input['cfLicenseKey'] = '';
			}

			if ($checkLicense || empty($input['cfLicenseKey']))
			{
				\DigitalPoint\Cloudflare\Helper\Api::check(true, $input['cfLicenseKey']);
			}
		}

		// Clean up/remove unused keys
		if (!empty($input['network_exclude']) && is_array($input['network_exclude']))
		{
			$input['network_exclude'] = array_map('array_filter', $input['network_exclude']);
			$input['network_exclude'] = array_filter($input['network_exclude']);
			if(!count($input['network_exclude']))
			{
				unset($input['network_exclude']);
			}
		}

		if ($isMultisite)
		{
			if (!empty($input['cloudflareAuth']) && is_array($input['cloudflareAuth']))
			{
				$input['cloudflareAuth'] = array_filter($input['cloudflareAuth']);
				if (!count($input['cloudflareAuth']))
				{
					unset($input['cloudflareAuth']);
				}
			}
		}

		if (!empty($input['cfImagesTransform']) && !empty($input['cfZoneId']))
		{
			static::getApi()->setSettings($input['cfZoneId'], ['value' => 'on'], 'settings/transformations');
		}

		return $input;
	}

	public static function hasOwnDomain()
	{
		// Mostly for Turnstile testing when the site isn't on its own domain.
		if(defined('WP_DEBUG') && WP_DEBUG)
		{
			return true;
		}

		if (defined('DOMAIN_CURRENT_SITE') && is_multisite() && !is_main_site())
		{
			$siteHostname = strtolower(wp_parse_url(site_url(), PHP_URL_HOST));
			$networkDomain = '.' . strtolower(DOMAIN_CURRENT_SITE);

			if (substr($siteHostname, 0 - strlen($networkDomain)) === $networkDomain)
			{
				return false;
			}
		}
		return true;
	}

	public static function hasOwnApiToken()
	{
		if (is_multisite() && !is_main_site() && empty(get_option('app_for_cf')['cloudflareAuth']['token']))
		{
			return false;
		}

		return true;
	}

	public static function isPluginActive($plugin)
	{
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active($plugin);
	}
}