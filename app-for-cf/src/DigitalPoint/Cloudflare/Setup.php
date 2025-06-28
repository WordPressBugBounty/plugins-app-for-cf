<?php

namespace DigitalPoint\Cloudflare;
class Setup
{
	public static function defaults()
	{
		return [
			'cfWorkersSubdomain' => '',
			'cfProxy' => [
				'image' => 0,
				'url' => 0
			],
			'cfR2Bucket' => [
				'media' => ''
			],
			'cfAccountId' => '',
			'cfZoneId' => '',
			'cfTokenId' => '',
			'cfZone' => '',
			'cfPageCachingSeconds' => '',
			'cloudflareAuth' => [
				'type' => 'token',
				'email' => '',
				'api_key' => '',
				'token' => ''
			],
			'cloudflareFirewallExpireDays' => 7,
			'cloudflareBlockIpsSpamClean' => 1,
			'cfExternalDataUrl' => '',
			'cfWebpCompression' => 0,
			'cfImagesTransform' => 0,
			'cfLicenseKey' => '',
			'cfPurgeCacheOnAdminBar' => 0,
			'cfTurnstile' => [
				'siteKey' => '',
				'secretKey' => '',
				'onRegister' => 0,
				'onLogin' => 0,
				'onPassword' => 0,
				'onComment' => 0,
			]
		];
	}

	public static function defaultsMultisite()
	{
		return [
			'cfR2Bucket' => [
				'media' => ''
			],
			'cloudflareAuth' => [
				'type' => 'token',
				'email' => '',
				'api_key' => '',
				'token' => ''
			],
			'cfLicenseKey' => ''
		];
	}


	public static function install()
	{
		if (is_multisite()) // Multi-site install
		{
			$sites = get_sites();
			foreach ($sites as $site)
			{
				switch_to_blog($site->blog_id);

				static::installAction();

				restore_current_blog();
			}
		}
		else
		{ // Single install
			static::installAction();
		}

		\DigitalPoint\Cloudflare\Helper\Api::check(true);
	}


	public static function uninstall()
	{
		if (is_multisite()) // Multi-site install
		{
			$sites = get_sites();
			foreach ($sites as $site)
			{
				switch_to_blog($site->blog_id);

				static::uninstallAction();

				restore_current_blog();
			}
		}
		else
		{ // Cleanup Single install
			static::uninstallAction();
		}
	}

	protected static function installAction()
	{
		if (!get_option('app_for_cf'))
		{
			update_option('app_for_cf', static::defaults());
		}
	}

	protected static function uninstallAction()
	{
		// Intentionally not deleting the settings so user doesn't need to reconfig later if they reactivate
		// delete_option('app_for_cf');
	}
}
