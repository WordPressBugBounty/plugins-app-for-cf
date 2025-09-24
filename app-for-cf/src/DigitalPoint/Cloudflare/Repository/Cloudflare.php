<?php

namespace DigitalPoint\Cloudflare\Repository;

use XF\Mvc\Entity\Repository;

abstract class CloudflareAbstract extends Repository
{
	abstract protected function getClassName($className);
	abstract public function option($optionKey);
	abstract protected function updateOption($name, $value = '');
	abstract protected function getSiteUrl();
	abstract public function resolvePromises(array $promises);
	abstract protected function phrase($phraseKey, array $params = []);
	abstract protected function printableException($message, $code = 0, \Exception $previous = null);
	abstract protected function dateFormat($timestamp, $withTime = false);
	abstract protected function getTimeZone();
	protected $apiClass = null;
	protected $zoneIds = [];
	protected $endpointResults = [];

	protected $dashBase = 'https://dash.cloudflare.com';
	protected $teamsDashBase = 'https://dash.teams.cloudflare.com';
	protected $zeroTrustDashBase = 'https://one.dash.cloudflare.com';

	protected $zone = null;
	protected $accountId = null;

	public function getDashBase()
	{
		return sprintf('%s/%s/%s', $this->dashBase, $this->getAccountId(), $this->getZone());
	}

	public function getDashBaseAccount()
	{
		return sprintf('%s/%s', $this->dashBase, $this->getAccountId());
	}

	public function getTeamsDashBase()
	{
		return sprintf('%s/%s', $this->teamsDashBase, $this->getAccountId());
	}
	public function getZeroTrustDashBase()
	{
		return sprintf('%s/%s', $this->zeroTrustDashBase, $this->getAccountId());
	}
	public function getTurnstileSiteUrl($siteKey)
	{
		return sprintf('%s/%s/turnstile/widget/%s', $this->dashBase, $this->getAccountId(), $siteKey);
	}
	public function getTurnstileSiteUrlEdit($siteKey)
	{
		return sprintf('%s/edit', $this->getTurnstileSiteUrl($siteKey));
	}

	public function getEndpointResultsByKey($key)
	{
		if (!empty($this->endpointResults[$key]))
		{
			return $this->endpointResults[$key];
		}

		return false;
	}

	/**
	 * @return \DigitalPoint\Cloudflare\Api\Cloudflare
	 */
	protected function getApiClass()
	{
		if (!$this->apiClass)
		{
			$className = 'DigitalPoint\Cloudflare\Api\Cloudflare';
			if (method_exists('XF', 'extendClass'))
			{
				$className = \XF::extendClass($className);
			}
			$this->apiClass = new $className();
		}
		return $this->apiClass;
	}

	protected function getSettingsToManage()
	{
		$settings = [
			'development_mode' => [
				'section' => 'top',
				'good' => 'off',
				'data_type' => 'bool',
			],
			'security_level' => [
				'section' => 'top', //security
				'good' => 'essentially_off',
				'type' => 'select',
				'values' => [
					'off' => $this->phrase('off'),
					'essentially_off' => $this->phrase('essentially_off'),
					'low' => $this->phrase('low'),
					'medium' => $this->phrase('medium'),
					'high' => $this->phrase('high'),
					'under_attack' => $this->phrase('under_attack'),
				]
			],

			'ssl' => [
				'section' => 'ssl_tls',
				'subsection_label' => $this->phrase('cf_section_title.overview'),
				'good' => 'full',
				'type' => 'select',
				'values' => [
					'off' => $this->phrase('off_not_secure'),
					'flexible' => $this->phrase('flexible'),
					'full' => $this->phrase('full'),
					'strict' => $this->phrase('full_strict'),
					'origin_pull' => $this->phrase('full_origin_pull'),
				]
			],
			'always_use_https' => [
				'section' => 'ssl_tls',
				'subsection_label' => $this->phrase('cf_section_title.edge_certificates'),
				'good' => 'on',
				'data_type' => 'bool',
			],
			'security_header' => [
				'section' => 'ssl_tls',
				'macro' => 'hsts',
				'good' => [
					'strict_transport_security' => [
						'enabled' => 1,
						'max_age' => 31536000,
						'include_subdomains' => 1,
						'preload' => 1,
						'nosniff' => ''
					]
				],
				'type' => 'array',
				'data_type' => [
					'strict_transport_security' => [
						'enabled' => 'bool',
						'max_age' => 'int',
						'include_subdomains' => 'bool',
						'preload' => 'bool',
						'nosniff' => 'bool',
					]
				],
				'values' => [
					'strict_transport_security' => [
						'enabled' => $this->phrase('enabled'),
						'max_age' => $this->phrase('max_age'),
						'include_subdomains' => $this->phrase('include_subdomains'),
						'preload' => $this->phrase('preload'),
						'nosniff' => $this->phrase('nosniff'),
					]
				],
				'max_age_options' => [
					0 => $this->phrase('disabled'),
					2592000 => $this->phrase('x_days', ['days' => 30]),
					5184000 => $this->phrase('x_months', ['months' => 2]),
					7776000 => $this->phrase('x_months', ['months' => 3]),
					10368000 => $this->phrase('x_months', ['months' => 4]),
					12960000 => $this->phrase('x_months', ['months' => 5]),
					15552000 => $this->phrase('x_months', ['months' => 6]),
					31536000 => $this->phrase('x_months', ['months' => 12]),
				]
			],
			'min_tls_version' => [
				'section' => 'ssl_tls',
				'good' => '1.0',
				'type' => 'select',
				'values' => [
					'1.0' => $this->phrase('tls_x', ['version' => '1.0']),
					'1.1' => $this->phrase('tls_x', ['version' => '1.1']),
					'1.2' => $this->phrase('tls_x', ['version' => '1.2']),
					'1.3' => $this->phrase('tls_x', ['version' => '1.3']),
				]
			],
			'opportunistic_encryption' => [
				'section' => 'ssl_tls',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'tls_1_3' => [
				'section' => 'ssl_tls',
				'good' => 'zrt',
				'type' => 'radio',
				'values' => [
					'off' => $this->phrase('off'),
					'on' => $this->phrase('on'),
					'zrt' => $this->phrase('zero_round_trip'),
				]
			],
			'automatic_https_rewrites' => [
				'section' => 'ssl_tls',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'ech' => [
				'section' => 'ssl_tls',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'certificate_transparency' => [
				'section' => 'ssl_tls',
				'good' => true,
				'beta' => true,
				'data_type' => 'bool',
				'override_endpoint' => 'ct/alerting',
				'override_result' => [
					'id' => 'certificate_transparency',
					'editable' => 1
				],
				'value_key' => 'enabled',
			],
			'tls_client_auth' => [
				'section' => 'ssl_tls',
				'subsection_label' => $this->phrase('cf_section_title.origin_server'),
				'good' => 'off',
				'data_type' => 'bool',
			],

			'waf' => [
				'section' => 'security',
				'subsection_label' => $this->phrase('cf_section_title.waf'),
				'good' => 'on',
				'data_type' => 'bool',
			],

			'bot_fight_mode' => [
				'section' => 'security',
				'subsection_label' => $this->phrase('cf_section_title.bots'),
				'good' => [
					'fight_mode' => false,
				],
				'type' => 'array',
				'data_type' => [
					'fight_mode' => 'bool',
				],
				'override_result' => [
					'id' => 'bot_fight_mode',
				],
				'override_result_editable_if_has' => 'fight_mode',
				'value_key' => 'fight_mode',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'ai_bots_protection' => [
				'section' => 'security',
				'good' => [
					'ai_bots_protection' => 'block',
				],
				'type' => 'select',
				'values' => [
					'disabled' => $this->phrase('allow'),
					'block' => $this->phrase('block'),
				],
				'data_type' => [
					'ai_bots_protection' => 'str',
				],
				'override_result' => [
					'id' => 'ai_bots_protection',
				],
				'override_result_editable_if_has' => 'ai_bots_protection',
				'value_key' => 'ai_bots_protection',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'crawler_protection' => [
				'section' => 'security',
				'good' => [
					'crawler_protection' => 'enabled',
				],
				'beta' => true,
				'type' => 'select',
				'values' => [
					'disabled' => $this->phrase('disabled'),
					'enabled' => $this->phrase('enabled'),
				],
				'data_type' => [
					'crawler_protection' => 'str',
				],
				'override_result' => [
					'id' => 'crawler_protection',
				],
				'override_result_editable_if_has' => 'crawler_protection',
				'value_key' => 'crawler_protection',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_likely_automated' => [
				'section' => 'security',
				'good' => [
					'sbfm_likely_automated' => 'allow',
				],
				'type' => 'select',
				'values' => [
					'allow' => $this->phrase('allow'),
					'block' => $this->phrase('block'),
					'managed_challenge' => $this->phrase('managed_challenge'),
				],
				'data_type' => [
					'sbfm_likely_automated' => 'str',
				],
				'override_result' => [
					'id' => 'bot_likely_automated',
				],
				'override_result_editable_if_has' => 'sbfm_likely_automated',
				'value_key' => 'sbfm_likely_automated',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_definitely_automated' => [
				'section' => 'security',
				'good' => [
					'sbfm_definitely_automated' => 'allow',
				],
				'type' => 'select',
				'values' => [
					'allow' => $this->phrase('allow'),
					'block' => $this->phrase('block'),
					'managed_challenge' => $this->phrase('managed_challenge'),
				],
				'data_type' => [
					'sbfm_definitely_automated' => 'str',
				],
				'override_result' => [
					'id' => 'bot_definitely_automated',
				],
				'override_result_editable_if_has' => 'sbfm_definitely_automated',
				'value_key' => 'sbfm_definitely_automated',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_verified_bots' => [
				'section' => 'security',
				'good' => [
					'sbfm_verified_bots' => 'allow',
				],
				'type' => 'select',
				'values' => [
					'allow' => $this->phrase('allow'),
					'block' => $this->phrase('block'),
				],
				'data_type' => [
					'sbfm_verified_bots' => 'str',
				],
				'override_result' => [
					'id' => 'bot_verified_bots',
				],
				'override_result_editable_if_has' => 'sbfm_verified_bots',
				'value_key' => 'sbfm_verified_bots',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_static_resource_protection' => [
				'section' => 'security',
				'good' => [
					'sbfm_static_resource_protection' => false,
				],
				'type' => 'array',
				'data_type' => [
					'sbfm_static_resource_protection' => 'bool',
				],
				'override_result' => [
					'id' => 'bot_static_resource_protection',
				],
				'override_result_editable_if_has' => 'sbfm_static_resource_protection',
				'value_key' => 'sbfm_static_resource_protection',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_optimize_wordpress' => [
				'section' => 'security',
				'good' => [
					'optimize_wordpress' => false,
				],
				'type' => 'array',
				'data_type' => [
					'optimize_wordpress' => 'bool',
				],
				'override_result' => [
					'id' => 'bot_optimize_wordpress',
				],
				'override_result_editable_if_has' => 'optimize_wordpress',
				'value_key' => 'optimize_wordpress',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],
			'bot_enable_js' => [
				'section' => 'security',
				'good' => [
					'enable_js' => false,
				],
				'type' => 'array',
				'data_type' => [
					'enable_js' => 'bool',
				],
				'override_result' => [
					'id' => 'bot_enable_js',
				],
				'override_result_editable_if_has' => 'sbfm_definitely_automated', // Weird situation where the API says it's editable, but it's actually not unless it's a Pro plan or higher
				'value_key' => 'enable_js',
				'override_endpoint' => 'bot_management',
				'overwrite_write_method' => 'PUT',
				'unset_on_write' => [
					'using_latest_model'
				],
			],

			'leaked_credential_checks' => [
				'section' => 'security',
				'subsection_label' => $this->phrase('cf_section_title.settings'),
				'good' => true,
				'data_type' => 'bool',
				'override_result' => [
					'id' => 'leaked_credential_checks',
					'editable' => 1
				],
				'override_endpoint' => 'leaked-credential-checks',
				'value_key' => 'enabled',
				'overwrite_write_method' => 'POST',
			],
			'challenge_ttl' => [
				'section' => 'security',
				'good' => 1800,
				'data_type' => 'int',
				'type' => 'select',
				'values' => [
					300 => $this->phrase('x_minutes', ['count' => 5]),
					900 => $this->phrase('x_minutes', ['count' => 15]),
					1800 => $this->phrase('x_minutes', ['count' => 30]),
					2700 => $this->phrase('x_minutes', ['count' => 45]),
					3600 => $this->phrase('x_minutes', ['count' => 60]),
					7200 => $this->phrase('x_hours', ['count' => 2]),
					10800 => $this->phrase('x_hours', ['count' => 3]),
					14400 => $this->phrase('x_hours', ['count' => 4]),
					28800 => $this->phrase('x_hours', ['count' => 8]),
					57600 => $this->phrase('x_hours', ['count' => 16]),
					86400 => $this->phrase('x_hours', ['count' => 24]),
					604800 => $this->phrase('x_days', ['days' => 7]),
					2592000 => $this->phrase('x_days', ['days' => 30]),
					31536000 => $this->phrase('x_months', ['months' => 12]),
				]
			],
			'browser_check' => [
				'section' => 'security',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'replace_insecure_js' => [
				'section' => 'security',
				'good' => 'on',
				'data_type' => 'bool',
			],

			'polish' => [
				'section' => 'speed',
				'subsection_label' => $this->phrase('cf_section_title.image_optimization'),
				'good' => 'off',
				'type' => 'select',
				'values' => [
					'off' => $this->phrase('off'),
					'lossless' => $this->phrase('lossless'),
					'lossy' => $this->phrase('lossy'),
				]
//				'sub' => ['webp']
			],
			'webp' => [
				'section' => 'speed',
				'good' => 'off',
				'data_type' => 'bool',
			],
			'speed_brain' => [
				'section' => 'speed',
				'subsection_label' => $this->phrase('cf_section_title.content_optimization'),
				'good' => 'on',
				'beta' => true,
				'data_type' => 'bool',
				'override_endpoint' => 'settings/speed_brain',
			],
			'fonts' => [
				'section' => 'speed',
				'good' => 'on',
				'beta' => true,
				'data_type' => 'bool',
				'override_endpoint' => 'settings/fonts',
			],
			'early_hints' => [
				'section' => 'speed',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'rocket_loader' => [
				'section' => 'speed',
				'good' => 'off',
				'not_good_alert' => true,
				'data_type' => 'bool',
			],

			'http2' => [
				'section' => 'speed',
				'subsection_label' => $this->phrase('cf_section_title.protocol_optimization'),
				'good' => 'on',
				'data_type' => 'bool',
			],
			'origin_max_http_version' => [
				'section' => 'speed',
				'good' => '2',
				'data_type' => 'int',
				'override_endpoint' => 'settings/origin_max_http_version',
				'override_result' => [
					'editable' => 1
				],
			],
			'http3' => [
				'section' => 'speed',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'h2_prioritization' => [
				'section' => 'speed',
				'good' => 'on',
				'data_type' => 'bool',
				'override_endpoint' => 'settings/h2_prioritization',
			],
			'0rtt' => [
				'section' => 'speed',
				'good' => 'on',
				'data_type' => 'bool',
			],


			'cache_level' => [
				'section' => 'caching',
				'subsection_label' => $this->phrase('cf_section_title.configuration'),
				'good' => 'aggressive',
				'type' => 'radio',
				'values' => [
					'basic' => $this->phrase('no_query_string'),
					'simplified' => $this->phrase('ignore_query_string'),
					'aggressive' => $this->phrase('standard'),
				]
			],
			'browser_cache_ttl' => [
				'section' => 'caching',
				'good' => 0,
				'data_type' => 'int',
				'type' => 'select',
				'values' => [
					0 => $this->phrase('respect_existing_headers'),
					30 => $this->phrase('x_seconds', ['count' => 30]),
					60 => $this->phrase('x_seconds', ['count' => 60]),
					120 => $this->phrase('x_minutes', ['count' => 2]),
					300 => $this->phrase('x_minutes', ['count' => 5]),
					1200 => $this->phrase('x_minutes', ['count' => 20]),
					1800 => $this->phrase('x_minutes', ['count' => 30]),
					3600 => $this->phrase('x_minutes', ['count' => 60]),
					7200 => $this->phrase('x_hours', ['count' => 2]),
					10800 => $this->phrase('x_hours', ['count' => 3]),
					14400 => $this->phrase('x_hours', ['count' => 4]),
					18000 => $this->phrase('x_hours', ['count' => 5]),
					28800 => $this->phrase('x_hours', ['count' => 8]),
					43200 => $this->phrase('x_hours', ['count' => 12]),
					57600 => $this->phrase('x_hours', ['count' => 16]),
					72000 => $this->phrase('x_hours', ['count' => 20]),
					86400 => $this->phrase('x_hours', ['count' => 24]),
					172800 => $this->phrase('x_days', ['days' => 2]),
					259200 => $this->phrase('x_days', ['days' => 3]),
					345600 => $this->phrase('x_days', ['days' => 4]),
					432000 => $this->phrase('x_days', ['days' => 5]),
					691200 => $this->phrase('x_days', ['days' => 8]),
					1382400 => $this->phrase('x_days', ['days' => 16]),
					2073600 => $this->phrase('x_days', ['days' => 24]),
					2678400 => $this->phrase('x_days', ['days' => 31]),
					5356800 => $this->phrase('x_months', ['months' => 2]),
					16070400 => $this->phrase('x_months', ['months' => 6]),
					31536000 => $this->phrase('x_months', ['months' => 12]),
				]
			],
			'crawlhints' => [ // Need Zone.Zone:Edit permissions to write, but not to read?  That's weird.  What does Zone.Zone even mean?
				'section' => 'caching',
				'good' => true,
				'beta' => true,
				'data_type' => 'bool',
				'override_endpoint' => 'flags/products/cache/changes',
				'override_endpoint_read' => 'flags',
				'override_result' => [
					'id' => 'crawlhints',
					'feature' => 'crawlhints_enabled',
					'editable' => 1
				],
				'value_key' => ['cache', 'crawlhints_enabled'],
				'overwrite_write_method' => 'POST',
			],
			'always_online' => [
				'section' => 'caching',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'tiered_caching' => [
				'section' => 'caching',
				'subsection_label' => $this->phrase('cf_section_title.tiered_cache'),
				'good' => 'on',
				'data_type' => 'bool',
				'override_endpoint' => 'argo/tiered_caching',
				'override_result' => ['editable' => 1]
			],

			'ipv6' => [
				'section' => 'network',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'websockets' => [
				'section' => 'network',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'pseudo_ipv4' => [
				'section' => 'network',
				'good' => 'off',
				'type' => 'select',
				'values' => [
					'off' => $this->phrase('off'),
					'add_header' => $this->phrase('add_header'),
					'overwrite_header' => $this->phrase('overwrite_headers'),
				]
			],
			'ip_geolocation' => [
				'section' => 'network',
				'good' => 'on',
				'data_type' => 'bool',
			],

			'max_upload' => [
				'section' => 'network',
				'good' => 100,
				'data_type' => 'int',
				'type' => 'select',
				'values' => [
					100 => $this->phrase('x_mb', ['size' => 100]),
					125 => $this->phrase('x_mb', ['size' => 125]),
					150 => $this->phrase('x_mb', ['size' => 150]),
					175 => $this->phrase('x_mb', ['size' => 175]),
					200 => $this->phrase('x_mb', ['size' => 200]),
					225 => $this->phrase('x_mb', ['size' => 225]),
					250 => $this->phrase('x_mb', ['size' => 250]),
					275 => $this->phrase('x_mb', ['size' => 275]),
					300 => $this->phrase('x_mb', ['size' => 300]),
					325 => $this->phrase('x_mb', ['size' => 325]),
					350 => $this->phrase('x_mb', ['size' => 350]),
					375 => $this->phrase('x_mb', ['size' => 375]),
					400 => $this->phrase('x_mb', ['size' => 400]),
					425 => $this->phrase('x_mb', ['size' => 425]),
					450 => $this->phrase('x_mb', ['size' => 450]),
					475 => $this->phrase('x_mb', ['size' => 475]),
					500 => $this->phrase('x_mb', ['size' => 500]),
				]
			],
			'nel' => [
				'section' => 'network',
				'good' => true,
				'data_type' => 'bool',
				'override_endpoint' => 'settings/nel',
				'value_key' => 'value.enabled',
			],
			'opportunistic_onion' => [
				'section' => 'network',
				'good' => 'on',
				'data_type' => 'bool',
			],

			'email_obfuscation' => [
				'section' => 'scrape_shield',
				'good' => 'on',
				'data_type' => 'bool',
			],
			'hotlink_protection' => [
				'section' => 'scrape_shield',
				'good' => 'off',
				'data_type' => 'bool',
			],
		];

		return $settings;
	}

	public function setEasyMode()
	{
		$zoneId = $this->getZoneId();
		$api = $this->getApiClass();

		$settings = [
			'items' => [
				[
					'id' => '0rtt',
					'value' => 'on'
				],
				[
					'id' => 'browser_cache_ttl',
					'value' => 0
				],
				[
					'id' => 'cache_level',
					'value' => 'aggressive'
				],
				[
					'id' => 'early_hints',
					'value' => 'on'
				],
				[
					'id' => 'http3',
					'value' => 'on'
				],
				[
					'id' => 'ip_geolocation',
					'value' => 'on'
				],
				[
					'id' => 'ipv6',
					'value' => 'on'
				],
				[
					'id' => 'min_tls_version',
					'value' => '1.2'
				],
				[
					'id' => 'opportunistic_encryption',
					'value' => 'on'
				],
				[
					'id' => 'opportunistic_onion',
					'value' => 'on'
				],
				[
					'id' => 'pseudo_ipv4',
					'value' => 'off'
				],
				[
					'id' => 'rocket_loader',
					'value' => 'off'
				],
				[
					'id' => 'tls_1_3',
					'value' => 'zrt'
				],
				[
					'id' => 'websockets',
					'value' => 'on'
				]
			]
		];

		if (class_exists('XF'))
		{
			$settings['items'][] = [
				'id' => 'security_level',
				'value' => 'essentially_off'
			];
		}
		else
		{
			$settings['items'][] = [
				'id' => 'security_level',
				'value' => 'medium'
			];
		}

		$api->setSettings($zoneId, $settings);

		$api->setSettings($zoneId, ['value' => ['enabled' => false]], 'settings/nel');
		$api->setSettings($zoneId, ['value' => '2'], 'settings/origin_max_http_version');
		$api->setSettings($zoneId, ['value' => 'on'], 'settings/speed_brain');
		$api->setSettings($zoneId, ['value' => 'on'], 'settings/fonts');
		$api->setSettings($zoneId, ['value' => 'on'], 'argo/tiered_caching');
	}

	public function organizeSettings($hostname = null, $hierarchy = true)
	{
		$managedSettings = $this->getSettingsToManage();
		$settings = $this->getZoneSettings($hostname, $managedSettings);

		$return = [];
		foreach ($managedSettings as $setting => $values)
		{
			$record = [
				'id' => $setting,
				'title' => $hierarchy ? $this->phrase('cf_setting_title.' . $setting) : '',
				'explain' => $hierarchy ? $this->phrase('cf_setting_explain.' . $setting) : '',
				'is_good' => $values['good'] == empty($settings[$setting]['value']) ? null : $settings[$setting]['value'],
				'options' => empty($settings[$setting]) ? null : $settings[$setting],
				'defaults' => $values
			];

			if ($hierarchy)
			{
				$return[$values['section']][] = $record;
			}
			else
			{
				$return[$setting] = $record;
			}
		}

		$sectionTitles = [];
		foreach ($return as $section => $setting)
		{
			$sectionTitles[$section] = $this->phrase('cf_section_title.' . $section);
		}

		return [
			'settings' => $return,
			'section_titles' => $sectionTitles
		];
	}

	public function getGraphQLZoneAnalytics($days = 1, $exactDate = false, $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$apiClass = $this->getApiClass();
		return $apiClass->getGraphQLZoneAnalytics($zoneId, $days, $exactDate);
	}

	public function getGraphQLCaptchaSolveRate($ruleId, $days = 1, $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$apiClass = $this->getApiClass();
		return $apiClass->getGraphQLCaptchaSolveRate($zoneId, $ruleId, $days);
	}

	public function prepareGraphQLZoneAnalytics($results)
	{
		$return = [
			'totals' => [],
			'detail' => [],
		];

		if (!empty($results['data']['viewer']['zones'][0]['zones']) && is_array($results['data']['viewer']['zones'][0]['zones']))
		{
			$withTime = $results['data']['viewer']['zones'][0]['zones'][0]['__typename'] == 'ZoneHttpRequests1hGroups';

			foreach ($results['data']['viewer']['zones'][0]['zones'] as $record)
			{
				if (strlen($record['dimensions']['timeslot']) == 10)
				{
					// Date only
					$timestamp = strtotime($record['dimensions']['timeslot'] . ' ' . $this->getTimeZone());
				}
				else
				{
					// Date with time
					$timestamp = strtotime($record['dimensions']['timeslot']);
				}

				$time = $this->dateFormat($timestamp, $withTime);

				$return['detail']['unique'][$time] = $record['uniq']['uniques'];
				$return['detail']['bytes'][$time] = $record['sum']['bytes'];
				$return['detail']['cachedBytes'][$time] = $record['sum']['cachedBytes'];
				$return['detail']['encryptedBytes'][$time] = $record['sum']['encryptedBytes'];
				$return['detail']['requests'][$time] = $record['sum']['requests'];
				$return['detail']['cachedRequests'][$time] = $record['sum']['cachedRequests'];
				$return['detail']['encryptedRequests'][$time] = $record['sum']['encryptedRequests'];
				$return['detail']['pageViews'][$time] = $record['sum']['pageViews'];
				$return['detail']['threats'][$time] = $record['sum']['threats'];
				$return['detail']['percentCached'][$time] = !empty($return['detail']['bytes'][$time]) ? ($return['detail']['cachedBytes'][$time] / $return['detail']['bytes'][$time]) * 100 : 0;
			}

			foreach($return['detail'] as $key => $values)
			{
				$return['totals'][$key] = array_sum($values);
			}

			// Different because they can span multiple timeslots
			$return['totals']['unique'] = $results['data']['viewer']['zones'][0]['totals'][0]['uniq']['uniques'];
			$return['totals']['percentCached'] = !empty($return['totals']['bytes']) ? ($return['totals']['cachedBytes'] / $return['totals']['bytes']) * 100 : 0;

		}

		return $return;
	}

	public function prepareGraphQLCaptchaSolveRate($results)
	{
		return [
			'issued' => (int)(!empty($results['data']['viewer']['zones'][0]['issued'][0]['count']) ? $results['data']['viewer']['zones'][0]['issued'][0]['count'] : 0),
			'solved' => (int)(!empty($results['data']['viewer']['zones'][0]['solved'][0]['count']) ? $results['data']['viewer']['zones'][0]['solved'][0]['count'] : 0)
		];
	}

	public function getGraphQLRuleActivityQuery($ruleId, $days = 1, $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$apiClass = $this->getApiClass();
		return $apiClass->getGraphQLRuleActivityQuery($zoneId, $ruleId, $days);
	}

	public function prepareGraphQLRuleActivityQuery($results)
	{
		return [
			'issued' => (int)@$results['data']['viewer']['zones'][0]['issued'][0]['count'],
		];
	}

	public function getZone($bypassCache = false)
	{
		$zone = $this->option('cfZone');
		if ($zone && !$bypassCache)
		{
			return $zone;
		}
		$this->getZoneId($hostname, true);
	}

	/*
	 * Setting the hostname to the zone is for when this method is used to find the matching Cloudflare zone
	 * (only happens if $bypassCache = true.  For example when generating the DMARC management deep link).
	 */
	public function getZoneId(&$hostname = null, $bypassCache = false, $setHostnameToZone = false)
	{
		if (!$hostname && !$bypassCache)
		{
			$zoneId = $this->option('cfZoneId');
			if ($zoneId)
			{
				return $zoneId;
			}
		}

		$canCache = false;
		if (!$hostname)
		{
			$hostname = parse_url($this->getSiteUrl(), PHP_URL_HOST);
			$canCache = true;
		}

		if (!empty($this->zoneIds[$hostname]))
		{
			return $this->zoneIds[$hostname];
		}

		$zoneId = null;

		$apiClass = $this->getApiClass();
		$dotSplit = explode('.', $hostname);

		if (count($dotSplit) < 2)
		{
			$this->printableException($this->phrase('cloudflare_zone_not_found_x', ['hostname' => $hostname]));
		}

		for ($i = 0; $i < count($dotSplit) - 1; $i++)
		{
			$hostnameTry = implode('.', array_slice($dotSplit, $i));

			$results = $apiClass->listZones($hostnameTry);

			if (!empty($results['success']) && $results['success'] == 1 && $results['result_info']['count'] == 1)
			{
				$zoneId = $results['result'][0]['id'];
				$this->zoneIds[$hostname] = $zoneId;

				$this->zone = $results['result'][0]['name'];

				$accountId = $results['result'][0]['account']['id'];

				if ($canCache && ($accountId != $this->option('cfAccountId') || $zoneId != $this->option('cfZoneId') || $this->zone != $this->option('cfZone')))
				{
					$this->updateOption([
						'cfAccountId' => $accountId,
						'cfZoneId' => $zoneId,
						'cfZone' => $this->zone
					]);
				}

				$this->accountId = $accountId;
				break;
			}
		}

		if (!$zoneId && !empty($results))
		{
			$this->printableException($this->phrase('cloudflare_zone_not_found_x', ['hostname' => $hostname]));
		}

		if ($setHostnameToZone)
		{
			$hostname = $hostnameTry;
		}

		return $zoneId;
	}

	public function getAccountId(&$hostname = null, $bypassCache = false)
	{
		if (!$hostname && !$bypassCache)
		{
			$accountId = $this->option('cfAccountId');
			if ($accountId)
			{
				return $accountId;
			}
		}

		$this->getZoneId($hostname, true, true);
		return $this->accountId;
	}

	public function getZones($domain = null)
	{
		$apiClass = $this->getApiClass();
		$results = $apiClass->listZones($domain);

		if (isset($results['result']))
		{
			return $results['result'];
		}
		return [];
	}

	public function getZoneSettings($hostname = null, $managedSettings = null)
	{
		$zoneId = $this->getZoneId($hostname);

		$api = $this->getApiClass();

		$promises = ['settings' => true];

		if ($managedSettings)
		{
			foreach ($managedSettings as $options)
			{
				if (!empty($options['override_endpoint']))
				{
					$endpoint = !empty($options['override_endpoint_read']) ? $options['override_endpoint_read'] : $options['override_endpoint'];
					$promises[$endpoint] = true;
				}
			}
		}

		if (count($this->endpointResults))
		{
			foreach ($this->endpointResults as $endpoint => $results)
			{
				unset($promises[$endpoint]);
			}
		}

		foreach($promises as $endpoint => $null)
		{
			$promises[$endpoint] = $api->getSettings($zoneId, $endpoint, true);
		}

		$promises = $this->resolvePromises($promises);

		foreach ($promises as $endpoint => $results)
		{
			$this->endpointResults[$endpoint] = $results;
		}

		$settings = $this->endpointResults['settings'];

		if ($managedSettings)
		{
			foreach ($managedSettings as $id => $options)
			{
				if (!empty($options['override_endpoint']))
				{
					$endpoint = !empty($options['override_endpoint_read']) ? $options['override_endpoint_read'] : $options['override_endpoint'];

					$results = $this->endpointResults[$endpoint];

					if (!empty($options['override_result']) && isset($results['result']) && is_array($results['result']))
					{
						$results['result'] = array_merge($results['result'], $options['override_result']);
					}

					if (!empty($options['override_result_editable_if_has']))
					{
						$results['result']['editable'] = isset($results['result'][$options['override_result_editable_if_has']]);
					}

					if (!empty($options['value_key']))
					{
						if (is_array($options['value_key']))
						{
							$item = $results['result'];
							foreach ($options['value_key'] as $key)
							{
								$item = $item[$key];
							}

							// Some hacky stuff to flatten multi-dimensional array
							$output = $options['override_result'];
							$output['value'] = $item;
							$results['result'] = $output;
						}
						else
						{
							if (substr_count($options['value_key'], '.'))
							{
								$split = explode('.', $options['value_key'], 2);
								$results['result']['value'] = $results['result'][$split[0]][$split[1]];
							}
							elseif (isset($results['result'][$options['value_key']]))
							{
								$results['result']['value'] = $results['result'][$options['value_key']];
							}
							else
							{
								// Shouldn't happen, but just in case.
								$results['result']['value'] = null;
							}
						}
					}
					$settings['result'][] = is_array($results) && key_exists('result', $results) ? $results['result'] : null;
				}
			}
		}

		// Some things are different API calls, but we are adding them into the main
//		$argo = $api->getArgoTieredCache($zoneId);
//		$argo['result']['editable'] = 1;
//		$settings['result'][] = $argo['result'];

		$return = [];
		foreach ($settings['result'] as $setting)
		{
			if (!empty($setting['id']))
			{
				$return[$setting['id']] = $setting;
			}
		}

		return $return;
	}

	public function updateSettings(array $settings, $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();

		$managedSettings = $this->getSettingsToManage();
		$settingsToUpdate = [];
		foreach ($managedSettings as $id => $options)
		{
			if (!empty($settings[$id]))
			{
				if (empty($options['override_endpoint']))
				{
					$settingsToUpdate['settings']['items'][] = $settings[$id];
				}
				else
				{
					if (!empty($options['value_key']) && !is_array($options['value_key']) && substr_count($options['value_key'], '.'))
					{
						// Example:  nel
						$split = explode('.', $options['value_key'], 2);
						$settingsToUpdate[$options['override_endpoint']][$split[0]][$split[1]] = $settings[$id]['value'];
					}
					elseif (empty($options['value_key']) || is_array($options['value_key']))
					{
						// Example:  crawlhints
						if (!empty($options['value_key']))
						{
							$settingsToUpdate[$options['override_endpoint']]['feature'] = end($options['value_key']);
						}

						// Example:  tiered_caching
						$settingsToUpdate[$options['override_endpoint']]['value'] = $settings[$id]['value'];
					}
					elseif(!isset($settings[$id]['value']))
					{
						// Example: bot_fight_mode
						$settingsToUpdate[$options['override_endpoint']] = $settings[$id];
					}
					else
					{
						// Example:  certificate_transparency
						$settingsToUpdate[$options['override_endpoint']][$options['value_key']] = $settings[$id]['value'];
					}

					if (!empty($options['overwrite_write_method']))
					{
						$settingsToUpdate[$options['override_endpoint']]['override_method'] = $options['overwrite_write_method'];
					}
				}

				if (isset($options['unset_on_write']) && is_array($options['unset_on_write']))
				{
					foreach($options['unset_on_write'] as $key)
					{
						unset($settingsToUpdate[$options['override_endpoint']][$key]);
					}
				}
			}
		}

		foreach ($settingsToUpdate as $endpoint => $settings)
		{
			if (!empty($settings['override_method']))
			{
				$method = $settings['override_method'];
				unset($settings['override_method']);

				$api->setSettings($zoneId, $settings, $endpoint, $method);
			}
			else
			{
				$api->setSettings($zoneId, $settings, $endpoint);
			}
		}
	}

	public function purgeCache(array $params = ['purge_everything' => true], $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();
		return $api->purgeCache($zoneId, $params);
	}

	public function getFirewallRules($hostname = null)
	{
		$zoneId = $this->getZoneId();
		$api = $this->getApiClass();

		$apiResults = $api->getFirewallRules($zoneId);

		if (!empty($apiResults['result']['rules']))
		{
			foreach ($apiResults['result']['rules'] as &$result)
			{
				$result['ruleset_id'] = $apiResults['result']['id'];

				$result['mode_phrase'] = $this->phrase('firewall.' . $result['action']);

				$result['using'] = [];
				if (substr_count($result['expression'], 'ip.geoip.asnum '))
				{
					$result['using'][] = $this->phrase('as_num');
				}
				if (substr_count($result['expression'], 'http.cookie '))
				{
					$result['using'][] = $this->phrase('cookie');
				}
				if (substr_count($result['expression'], 'ip.geoip.country '))
				{
					$result['using'][] = $this->phrase('country');
				}
				if (substr_count($result['expression'], 'ip.geoip.continent '))
				{
					$result['using'][] = $this->phrase('continent');
				}
				if (substr_count($result['expression'], 'http.host '))
				{
					$result['using'][] = $this->phrase('hostname');
				}
				if (substr_count($result['expression'], 'ip.src '))
				{
					$result['using'][] = $this->phrase('ip_source_address');
				}
				if (substr_count($result['expression'], 'http.referer '))
				{
					$result['using'][] = $this->phrase('referer');
				}
				if (substr_count($result['expression'], 'http.request.method '))
				{
					$result['using'][] = $this->phrase('request_method');
				}
				if (substr_count($result['expression'], 'ssl'))
				{
					$result['using'][] = $this->phrase('ssl_https');
				}
				if (substr_count($result['expression'], 'http.request.full_uri '))
				{
					$result['using'][] = $this->phrase('uri_full');
				}
				if (substr_count($result['expression'], 'http.request.uri '))
				{
					$result['using'][] = $this->phrase('uri');
				}
				if (substr_count($result['expression'], 'http.request.uri.path '))
				{
					$result['using'][] = $this->phrase('uri_path');
				}
				if (substr_count($result['expression'], 'http.request.uri.query '))
				{
					$result['using'][] = $this->phrase('uri_query_string');
				}
				if (substr_count($result['expression'], 'http.request.version '))
				{
					$result['using'][] = $this->phrase('http_version');
				}
				if (substr_count($result['expression'], 'http.user_agent '))
				{
					$result['using'][] = $this->phrase('user_agent');
				}
				if (substr_count($result['expression'], 'http.x_forwarded_for '))
				{
					$result['using'][] = $this->phrase('x_forwarded_for');
				}
				if (substr_count($result['expression'], 'cf.tls_client_auth.cert_verified'))
				{
					$result['using'][] = $this->phrase('client_certificate_verified');
				}
				if (substr_count($result['expression'], 'cf.client.bot'))
				{
					$result['using'][] = $this->phrase('known_bots');
				}
				if (substr_count($result['expression'], 'cf.threat_score '))
				{
					$result['using'][] = $this->phrase('threat_score');
				}
				if (substr_count($result['expression'], 'cf.verified_bot_category'))
				{
					$result['using'][] = $this->phrase('verified_bot_category');
				}
			}

			return $apiResults['result']['rules'];
		}

		return [];
	}

	public function getZoneFirewallAccessRules($hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();

		$splitHostname = explode('.', parse_url($this->getSiteUrl(), PHP_URL_HOST));

		$possibleHostnames = [];
		foreach ($splitHostname as $key => $part)
		{
			$possibleHostname = strtolower(implode('.', array_slice($splitHostname, -1 - $key)));
			$possibleHostnames[$possibleHostname] = true;
		}

		$results = [];

		$page = 1;
		while($page == 1 || (!empty($apiResults['result_info']['total_pages']) && $apiResults['result_info']['total_pages'] >= $page))
		{
			$apiResults = $api->getFirewallAccessRules($zoneId, $page, 1000);

			if (!empty($apiResults['result']))
			{
				foreach ($apiResults['result'] as $result)
				{
					if ($result['scope']['type'] === 'zone' && !empty($possibleHostnames[strtolower($result['scope']['name'])]))
					{
						$results[] = array_merge(
							$result,
							[
								'date_created' => strtotime(preg_replace('/(.*)(\..*?)(Z)/', '$1$3', $result['created_on'])),
								'mode_phrase' => $this->phrase('firewall.' . $result['mode'])
							]
						);
					}
				}
			}
			$page++;
		}

		return $results;
	}


	public function getFirewallUserAgentRules($hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();

		$results = [];

		$page = 1;
		while($page == 1 || (!empty($apiResults['result_info']['total_pages']) && $apiResults['result_info']['total_pages'] >= $page))
		{
			$apiResults = $api->getFirewallUserAgentRules($zoneId, $page, 1000);

			if (!empty($apiResults['result']))
			{
				foreach ($apiResults['result'] as $result)
				{
					$results[] = $result;
				}
			}
			$page++;
		}

		foreach ($results as &$result)
		{
			$result['mode_phrase'] = $this->phrase('firewall.' . $result['mode']);
		}

		return $results;
	}


	public function getPageRules($hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();

		$results = $api->getPageRules($zoneId);
		if (!empty($results) && is_array($results))
		{
			$results = $results['result'];
			foreach ($results as &$result)
			{
				//	$result['status_phrase'] = $this->phrase('pagerules.' . $result['status']);
				foreach ($result['actions'] as &$action)
				{
					$action['id_phrase'] = $this->phrase('pagerules.' . $action['id']);

					if (!empty($action['value']))
					{
						if (is_array($action['value']))
						{
							$action['value_phrase'] = [];
							$somethingOn = false;

							foreach ($action['value'] as $key => $value)
							{
								// For Minify (which doesn't exist anymore since it was deprecated)
								if ($value == 'on')
								{
									$action['value_phrase'][] = [
										'key' => $this->phrase('pagerules.' . $key),
										'value' => $this->phrase('pagerules.' . $value),
									];
									$somethingOn = true;
								}

								// Forwarding URL
								elseif($key == 'url') //  || $key == 'status_code'
								{
									$action['value_phrase'][] = [
										'key' => $value,
										//		'value' => $value,
									];
									$somethingOn = true;
								}
							}

							if (!$somethingOn)
							{
								$action['value_phrase'][] = [
									'key' => $this->phrase('pagerules.off')
								];
							}

						}
						else
						{
							$action['value_phrase'] = is_numeric($action['value']) ? $this->timeToHumanReadable($action['value']) : $this->phrase('pagerules.' . $action['value']);
						}
					}
					else
					{
						$action['value_phrase'] = $this->phrase('pagerules.on');
					}
				}
			}
		}

		return $results;
	}


	public function getCacheRules($hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();
		$results = $api->getCacheRules($zoneId);

		if (!empty($results['result']['rules']))
		{
			foreach ($results['result']['rules'] as &$rule)
			{
				$rule['ruleset_id'] = $results['result']['id'];

				foreach ($rule['action_parameters'] as $actionName => &$action)
				{
					$actionsOutput = [];

					$actionsOutput['id_phrase'] = $this->phrase('cacherules.' . $actionName);

					if (is_array($action))
					{
						if (!empty($action['mode']))
						{
							if($action['mode'] == 'override_origin')
							{
								$actionsOutput['value'] = is_numeric($action['default']) ? $this->timeToHumanReadable($action['default']) : $this->phrase('cacherules.' . $action['default']);
							}
							elseif($action['mode'] == 'respect_origin')
							{
								$actionsOutput['value'] = $this->phrase('respect_origin');
							}
							elseif($action['mode'] == 'bypass')
							{
								$actionsOutput['value'] = $this->phrase('pagerules.bypass');
							}

						}
						elseif($actionName === 'cache_key')
						{
							$actionsOutput['value'] = [];

							foreach($action as $parameterName => $parameterValue)
							{
								if ($parameterValue)
								{
									$actionsOutput['value'][] = $this->phrase('cacherules.' . $parameterName);
								}
							}
						}
						elseif($actionName === 'serve_stale')
						{
							$actionsOutput['value'] = empty($action['disable_stale_while_updating']) ? $this->phrase('enabled') : $this->phrase('disabled');
						}
						elseif($actionName === 'respect_strong_etags')
						{
							$actionsOutput['value'] = empty($action['respect_strong_etags']) ? $this->phrase('enabled') : $this->phrase('disabled');
						}
					}
					elseif(is_bool($action))
					{
						$actionsOutput['value'] = $action ? $this->phrase('enabled') : $this->phrase('disabled');
					}

					$rule['action_parameters_output'][$actionName] = $actionsOutput;
				}
			}

			return $results['result']['rules'];
		}
		return [];
	}
	public function addSpecialCacheRule($type, $ruleHostname = '', $enabled = true, $hostname = '')
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();

		$dataClass = $this->getClassName('DigitalPoint\Cloudflare\Data\Expression');
		$dataClass = new $dataClass();

		if ($type == 'r2-bucket')
		{
			$description = $this->phrase('override_cache_r2');
			$expression = $dataClass->getSpecialCacheRuleR2($ruleHostname);

			$actionParameters = [
				'browser_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'edge_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'cache' => true,
			];
		}
		elseif ($type == 'css')
		{
			$description = $this->phrase('cache_xenforo_css');
			$expression = $dataClass->getSpecialCacheRuleCss();
			$actionParameters = [
				'cache' => true,
			];
		}
		elseif ($type == 'image_proxy')
		{
			$description = $this->phrase('cache_xenforo_image_proxy');
			$expression = $dataClass->getSpecialCacheRuleImageProxy();
			$actionParameters = [
				'browser_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'edge_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'cache' => true,
			];
		}
		elseif($type == 'guest_cache')
		{
			$description = $this->phrase('cache_xenforo_guest_pages');
			$expression = $dataClass->getGuestCache();
			$actionParameters = [
				'cache' => true,
			];
		}
		elseif($type == 'media_cache')
		{
			$description = $this->phrase('cache_xenforo_media_attachments');
			$expression = $dataClass->getMediaAttachmentCache();
			$actionParameters = [
				'cache' => true,
			];
		}
		elseif($type == 'static_content')
		{
			$description = $this->phrase('cache_static_content');
			$expression = $dataClass->getStaticContent();
			$actionParameters = [
				'browser_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'edge_ttl' => [
					'default' => 31536000,
					'mode' => 'override_origin'
				],
				'cache' => true,
			];
		}

		$phase = $api->getRulesetPhase($zoneId, 'http_request_cache_settings/entrypoint', 404);
		if ($phase && !empty($phase['result']['id']))
		{
			$api->createCacheRule($zoneId, $description, $expression, $actionParameters, $phase['result']['id'], $enabled);
		}
		else
		{
			$api->createCacheRule($zoneId, $description, $expression, $actionParameters, '', $enabled);
		}
	}

	public function deleteSpecialCacheRule($type, $hostname = '')
	{
		$expression = '';

		$dataClass = $this->getClassName('DigitalPoint\Cloudflare\Data\Expression');
		$dataClass = new $dataClass();

		if ($type == 'guest_cache')
		{
			$expression = $dataClass->getGuestCache();
		}
		elseif ($type == 'media_cache')
		{
			$expression = $dataClass->getMediaAttachmentCache();
		}

		$cacheRules = $this->getCacheRules($hostname);

		if ($cacheRules)
		{
			foreach($cacheRules as $rule)
			{
				if ($rule['expression'] === $expression)
				{
					$zoneId = $this->getZoneId($hostname);
					$api = $this->getApiClass();
					$api->deleteCacheRule($zoneId, $rule['ruleset_id'], $rule['id']);
					return;
				}
			}
		}
	}

	public function getAccessApps($hostname = null, $reduce = true)
	{
		if (!$hostname)
		{
			$hostname = parse_url($this->getSiteUrl(), PHP_URL_HOST);
		}

		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();
		$results = $api->getAccessApps($accountId, $hostname);
		if ($reduce && !empty($results) && is_array($results))
		{
			$results = $results['result'];
		}

		return $results;
	}

	public function getAccessGroups($hostname = null)
	{
		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();

		$results = $api->getAccessGroups($accountId);
		if (!empty($results) && is_array($results))
		{
			$results = $results['result'];
		}

		$return = [];
		if (is_array($results))
		{
			foreach ($results as $group)
			{
				$return[$group['id']] = $group;
			}
		}

		return $return;
	}

	protected function getRumSites(&$hostname = null)
	{
		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();
		$result = $api->getRumSites($accountId);

		if ($result === false)
		{
			return false;
		}

		if (empty($result['result']))
		{
			return false;
		}

		return $result['result'];
	}

	public function getRumSiteStatus(&$hostname = null)
	{
		$sites = $this->getRumSites($hostname);
		if (is_array($sites))
		{
			$zoneId = $this->getZoneId($hostname);
			foreach($sites as $site)
			{
				if (!empty($site['ruleset']['zone_tag']) && $site['ruleset']['zone_tag'] === $zoneId)
				{
					if (!empty($site['auto_install']))
					{
						if (!empty($site['ruleset']['enabled']))
						{
							if (!empty($site['ruleset']['lite']))
							{
								return [
									'status' => 'enabled_no_eu',
									'site' => $site
								];
							}
							return [
								'status' => 'enabled',
								'site' => $site
							];
						}
					}
					return [
						'status' => 'not_enabled',
						'site' => $site
					];
				}
			}
		}
		return false;
	}

	public function updateRumSite($hostname = null, $enabled = true, $excludeEurope = false, $autoInstall = true)
	{
		$status = $this->getRumSiteStatus($hostname);

		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();

		if (!empty($status) && !empty($status['site']) && !empty($status['site']['site_tag']))
		{
			return $api->updateRumSite(
				$accountId,
				$status['site']['site_tag'],
				!empty($status['site']['ruleset']['zone_tag']) ? $status['site']['ruleset']['zone_tag'] : '',
				$enabled,
				$excludeEurope,
				$autoInstall
			);
		}

		return $api->createRumSite(
			$accountId,
			$this->getZoneId($hostname),
			$enabled,
			$excludeEurope,
			$autoInstall
		);
	}

	public function getTurnstileSites(&$hostname = null)
	{
		$accountId = $this->getAccountId($hostname, true);
		$api = $this->getApiClass();
		$result = $api->getTurnstileSites($accountId);

		if ($result === false)
		{
			return false;
		}

		if (empty($result['result']))
		{
			return false;
		}

		return $result['result'];
	}

	public function getTurnstileWidgetByDomain($hostname = null)
	{
		$sites = $this->getTurnstileSites($hostname);
		if ($sites)
		{
			foreach ($sites as $site)
			{
				if (!empty($site['domains']) && in_array($hostname, $site['domains']))
				{
					$result = $this->getTurnstileWidgetBySitekey($site['sitekey'], $hostname);

					if ($result['result'])
					{
						return $result['result'];
					}
				}
			}
		}
		return false;
	}

	public function getTurnstileWidgetBySitekey($sitekey, $hostname = null)
	{
		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();
		return $api->getTurnstileWidget($accountId, $sitekey);
	}

	public function addTurnstileSite($siteName, $domain, $mode = 'managed')
	{
		$accountId = $this->getAccountId($hostname);
		$api = $this->getApiClass();
		return $api->createTurnstileWidget($accountId, $siteName, $domain, $mode);
	}

	public function requestTrace($url, $method, $protocol = null, $botScore = null, $country = null, $skipChallenge = null, $threatScore = null)
	{
		$api = $this->getApiClass();
		return $api->requestTrace($this->getAccountId(), $url, $method, $protocol, $botScore, $country, $skipChallenge, $threatScore);
	}

	public function ipDetails($ip)
	{
		$api = $this->getApiClass();
		return $api->ipDetails($this->getAccountId(), $ip);
	}

	public function domainDetails($domain)
	{
		$api = $this->getApiClass();
		return $api->domainDetails($this->getAccountId(), $domain);
	}

	public function whois($domain)
	{
		$api = $this->getApiClass();
		return $api->whois($this->getAccountId(), $domain);
	}

	public function getDmarcReports(&$hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();
		return $api->getDmarcReports($zoneId);
	}

	public function getDmarcSources(array $approvedSources = [], &$hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$api = $this->getApiClass();
		return $api->getGraphQLDmarcSources($zoneId, $approvedSources);
	}

	public function prepareGraphQLDmarcSources($results)
	{
		$return = [];

		if (!empty($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups']) && is_array($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups']))
		{
			foreach ($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups'] as $record)
			{
				$return[] = [
					'org_name' => $record['dimensions']['sourceOrgName'],
					'org_slug' => $record['dimensions']['sourceOrgSlug'],
					'average' => [
						'dkim' => $record['avg']['dkimPass'],
						'dmarc' => $record['avg']['dmarc'],
						'spf' => $record['avg']['spfPass']
					],
					'total' => $record['sum']['totalMatchingMessages'],
					'ips' => $record['uniq']['ipCount'],
				];
			}
		}

		return $return;
	}

	public function getGraphQLZoneAnalyticsDmarc($days = 1, $hostname = null)
	{
		$zoneId = $this->getZoneId($hostname);
		$apiClass = $this->getApiClass();
		return $apiClass->getGraphQLZoneAnalyticsDmarc($zoneId, $days);
	}

	public function prepareGraphQLZoneAnalyticsDmarc($results)
	{
		if (!empty($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups']) && is_array($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups']))
		{
			$return = [
				'totals' => [],
				'detail' => []
			];

			foreach ($results['data']['viewer']['zones'][0]['dmarcReportsSourcesAdaptiveGroups'] as $record)
			{
				$timestamp = strtotime($record['dimensions']['datetimeDay'] . ' ' . $this->getTimeZone());

				$time = $this->dateFormat($timestamp);

				if (empty($return['detail']['pass'][$time]))
				{
					$return['detail']['pass'][$time] = 0;
				}
				if (empty($return['detail']['fail'][$time]))
				{
					$return['detail']['fail'][$time] = 0;
				}

				if ($record['dimensions']['dkim'] == 'fail' && $record['dimensions']['spf'] == 'fail')
				{
					$return['detail']['fail'][$time] += $record['sum']['totalMatchingMessages'];
				}
				else
				{
					$return['detail']['pass'][$time] += $record['sum']['totalMatchingMessages'];
				}
			}

			foreach($return['detail'] as $key => $values)
			{
				$return['totals'][$key] = array_sum($values);
				ksort($return['detail'][$key]);
			}

			// A little hacky... but we want a singular total value for label, and the first series is the one that gets the label
			$return['totals']['fail'] += $return['totals']['pass'];
		}
		else
		{
			$return = [
				'totals' => [
					'fail' => 0
				],
				'detail' => [
					'pass' => [
						$this->dateFormat(time()) => 0
					],
					'fail' => [
						$this->dateFormat(time()) => 0
					]
				]
			];
		}

		return $return;
	}


	public function verifyToken()
	{
		$api = $this->getApiClass();
		$results = $api->verifyToken();
		if (!empty($results['success']) && $results['result']['status'] == 'active')
		{
			if ($results['result']['id'] != $this->option('cfTokenId'))
			{
				$this->updateOption('cfTokenId', $results['result']['id']);
				$this->updateOption('cfZone', '');
			}
			return $results['result']['id'];
		}
		return $results;
	}

	public function getTokenPermissions($tokenId)
	{
		$apiClass = $this->getApiClass();

		$tokenDetails = $apiClass->getTokenDetails($tokenId);
//		$permissionGroups = $apiClass->getTokenPermissionGroups();

		$tokenPolicies = [];
		if (!empty($tokenDetails['success']) && $tokenDetails['result']['status'] === 'active' && !empty($tokenDetails['result']['policies']))
		{
			foreach($tokenDetails['result']['policies'] as $policy)
			{
				if (!empty($policy['effect']) && $policy['effect'] === 'allow')
				{
					foreach ($policy['permission_groups'] as $permissionGroup)
					{
						$tokenPolicies[$permissionGroup['id']] = $permissionGroup['name'];
					}
				}
			}
		}
		return $tokenPolicies;

		/*
				$neededPermissions = [
					// Account
					'1e13c5124ca64b72b1969a67e8829049' => 'Access: Apps and Policies Write',
					'26bc23f853634eb4bff59983b9064fde' => 'Access: Organizations, Identity Providers, and Groups Read',
					'b89a480218d04ceb98b4fe57ca29dc1f' => 'Account Analytics Read',
					'c1fde68c7bcc44588cbb6ddbc16d6480' => 'Account Settings Read',
					'1af1fa2adc104452b74a9a3364202f20' => 'Account Settings Write',
					'f3604047d46144d2a3e9cf4ac99d7f16' => 'Allow Request Tracer Read',
					'7cf72faf220841aabcfdfab81c43c4f6' => 'Billing Read',
					'df1577df30ee46268f9470952d7b0cdf' => 'Intel Read',
					'755c05aa014b4f9ab263aa80b8167bd8' => 'Turnstile Sites Write',
					'bf7481a1826f439697cb59a20b22293e' => 'Workers R2 Storage Write',
					'e086da7e2179491d91ee5f35b3ca210a' => 'Workers Scripts Write',

					// User
					'0cc3a61731504c89b99ec1be78b77aa0' => 'API Tokens Read',

					// Zone
					'9c88f9c5bce24ce7af9a958ba9c504db' => 'Analytics Read',
					'3b94c49258ec4573b06d51d99b6416c0' => 'Bot Management Write',
					'e17beae8b8cb423a99b1730f21238bed' => 'Cache Purge',
					'9ff81cbbe65c400b97d92c3c1033cab6' => 'Cache Settings Write',
					'43137f8d07884d3198dc0ee77ca6e79b' => 'Firewall Services Write',
					'ed07f6c337da4195b4e72a1fb2c6bcae' => 'Page Rules Write',
					'c03055bc037c4ea9afb9a9f104b7b721' => 'SSL and Certificates Write',
					'e6d2666161e84845a636613608cee8d5' => 'Zone Write',
					'3030687196b94b638145a3953da2b699' => 'Zone Settings Write',
					'fb6778dc191143babbfaa57993f1d275' => 'Zone WAF Write',
				];
		*/
	}

	public function timeToHumanReadable($seconds)
	{
		if ($seconds < 120)
		{
			return $this->phrase('x_seconds', ['count' => $seconds]);
		}
		elseif ($seconds < 7200)
		{
			return $this->phrase('x_minutes', ['count' => round($seconds / 60)]);
		}
		elseif ($seconds < 172800)
		{
			return $this->phrase('x_hours', ['count' => round($seconds / 3600)]);
		}
		elseif ($seconds < 5356800)
		{
			return $this->phrase('x_days', ['days' => round($seconds / 86400)]);
		}
		elseif ($seconds <= 31536000)
		{
			return $this->phrase('x_months', ['months' => round($seconds / 2678400)]);
		}
		return '';
	}
}

if (trait_exists('DigitalPoint\Cloudflare\Traits\XF'))
{
	class Cloudflare extends \DigitalPoint\Cloudflare\Repository\Advanced\Cloudflare
	{
		use \DigitalPoint\Cloudflare\Traits\XF;
		use \DigitalPoint\Cloudflare\Traits\Repository\XF;
	}
}
elseif(trait_exists('DigitalPoint\Cloudflare\Traits\WP'))
{
	class Cloudflare extends \DigitalPoint\Cloudflare\Repository\Advanced\Cloudflare
	{
		use \DigitalPoint\Cloudflare\Traits\WP;
		use \DigitalPoint\Cloudflare\Traits\Repository\WP;
	}
}