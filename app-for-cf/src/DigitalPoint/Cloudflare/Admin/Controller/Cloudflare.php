<?php
namespace DigitalPoint\Cloudflare\Admin\Controller;

class Cloudflare extends Advanced\Cloudflare
{
	use \DigitalPoint\Cloudflare\Traits\WP;

	protected $rules = null;
	protected $userAgentRules = null;

	public function actionRequesttrace()
	{
		$this->assertHasOwnDomain();
		$this->assertHasOwnApiToken();

		$viewParams = [];

		if ($this->isPost())
		{
			$this->assertNonce();

			$url = $this->filter('url', 'str');
			$method = $this->filter('method', 'str');
			$protocol = $this->filter('protocol', 'str');
			$botScore = $this->filter('bot_score', 'uint');
			$country = $this->filter('country', 'str');
			$skipChallenge = $this->filter('skip_challenge', 'bool');

			// check if it exists first because 0 and empty are different here
			if (isset($_POST['threat_score'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
			{
				$threatScore = $this->filter('threat_score', 'uint');
			}
			else
			{
				$threatScore = null;
			}

			$viewParams['result'] = $this->getCloudflareRepo()->requestTrace($url, $method, $protocol, $botScore, $country, $skipChallenge, $threatScore);
		}

		$countryClass = $this->getClassName('DigitalPoint\Cloudflare\Data\Country');
		$countryClass = new $countryClass();
		/** @var \DigitalPoint\Cloudflare\Data\Country $countryClass */

		$viewParams['countries'] = $countryClass->getCountries();
		$viewParams['url'] = $this->getSiteUrl();

		return $this->view('requestTrace', $viewParams);
	}

	public function actionIpdetails()
	{
		$this->assertHasOwnDomain();
		$this->assertHasOwnApiToken();

		$viewParams = [];

		if ($this->isPost())
		{
			$this->assertNonce();

			$viewParams['ip'] = $this->filter('ip', 'str');

			$viewParams['result'] = $this->getCloudflareRepo()->ipDetails($viewParams['ip']);

			$countryClass = $this->getClassName('DigitalPoint\Cloudflare\Data\Country');
			$countryClass = new $countryClass();
			/** @var \DigitalPoint\Cloudflare\Data\Country $countryClass */

			$countries = $countryClass->getCountries();
			if (!empty($viewParams['result']['result'][0]['belongs_to_ref']['country']) && !empty($countries[$viewParams['result']['result'][0]['belongs_to_ref']['country']]))
			{
				$viewParams['country'] = $countries[$viewParams['result']['result'][0]['belongs_to_ref']['country']];
			}
		}
		else
		{
			$viewParams['ip'] = (empty($_SERVER['REMOTE_ADDR']) || !filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) ? '' : $_SERVER['REMOTE_ADDR']; /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		}

		return $this->view('ipDetails', $viewParams);
	}

	public function actionDomaindetails()
	{
		$this->assertHasOwnDomain();
		$this->assertHasOwnApiToken();

		$viewParams = [];

		if ($this->isPost())
		{
			$this->assertNonce();
			$viewParams['result'] = $this->getCloudflareRepo()->domainDetails($this->filter('domain', 'str'));
		}

		$viewParams['hostname'] = wp_parse_url($this->getSiteUrl(), PHP_URL_HOST);

		return $this->view('domainDetails', $viewParams);
	}

	public function actionWhois()
	{
		$this->assertHasOwnDomain();
		$this->assertHasOwnApiToken();

		$viewParams = [];

		if ($this->isPost())
		{
			$this->assertNonce();
			$viewParams['result'] = $this->getCloudflareRepo()->whois($this->filter('domain', 'str'));
		}

		$viewParams['hostname'] = wp_parse_url($this->getSiteUrl(), PHP_URL_HOST);

		return $this->view('whois', $viewParams);
	}



	public function actionStats()
	{
		$this->assertHasOwnDomain();

		if ($this->isPost())
		{
			$range = $this->filter('range', 'str');

			switch ($range) {
				case 'week':
					$days = 7;
					break;
				case 'month':
					$days = 30;
					break;
				case 'year':
					$days = 364;
					break;
				default:
					$days = 1;
			}

			/** @var \DigitalPoint\Cloudflare\Repository\Cloudflare $cloudflareRepo */
			$cloudflareRepo = $this->getCloudflareRepo();

			$promises = ['analytics' => $cloudflareRepo->getGraphQLZoneAnalytics($days)];
			$promises = $cloudflareRepo->resolvePromises($promises);

			wp_send_json (['stats' => $cloudflareRepo->prepareGraphQLZoneAnalytics($promises['analytics'])]);
		}
	}

	public function actionNoticedismiss()
	{
		if ($this->isPost())
		{
			$key = $this->filter('key', 'key');
			set_transient($key . wp_get_current_user()->ID, 1, 30 * 86400); // can't exceed 30 days because of how memcache works (for people using memcache for transients)
			wp_send_json([]);
		}
	}

	public function actionStatsDmarc()
	{
		$this->assertHasOwnDomain();

		if ($this->isPost())
		{
			$range = $this->filter('range', 'str');

			switch ($range) {
				case 'week':
					$days = 7;
					break;
				case 'month':
					$days = 30;
					break;
				case 'year':
					$days = 364;
					break;
				default:
					$days = 1;
			}

			/** @var \DigitalPoint\Cloudflare\Repository\Cloudflare $cloudflareRepo */
			$cloudflareRepo = $this->getCloudflareRepo();

			$promises = ['stats' => $cloudflareRepo->getGraphQLZoneAnalyticsDmarc($days)];
			$promises = $cloudflareRepo->resolvePromises($promises);

			wp_send_json (['stats' => $cloudflareRepo->prepareGraphQLZoneAnalyticsDmarc($promises['stats'])]);
		}
	}

	public function actionSettings()
	{
		$this->assertHasOwnDomain();

		// A bit of a special case for settings action because of how the AJAX system works (need to whitelist actions here otherwise you will get stuck in an infinite loop with this method repeating)
		if(!empty($_REQUEST['action']) && ($_REQUEST['action'] === 'copy_settings' || $_REQUEST['action'] === 'easy')) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			if ($this->handleAction() === false)
			{
				return;
			}
		}

		if ($this->isPost())
		{
			$this->actionToggle();

			$return = [
				'message' => __('Saved to Cloudflare.', 'app-for-cf')
			];

			wp_send_json($return);

			/*
			 * 		if (!empty($appForCloudflareOptions['cfImagesTransform']))
		{

		}
			 */


		}

		$viewParams = [];

		$appForCloudflareOptions = $this->option(null);

		$tokenId = '';
		$tokenPermissions = [];

		$error = esc_html__('API token does not appear to be valid.', 'app-for-cf');
		if (!empty($appForCloudflareOptions['cloudflareAuth']['token']))
		{
			try {

				$cloudflareRepo = $this->getCloudflareRepo();

				$tokenId = $cloudflareRepo->verifyToken();
				if (is_string($tokenId))
				{
					$error = '';

					try
					{
						$tokenPermissions = $cloudflareRepo->getTokenPermissions($tokenId);
					}
					catch(\Exception $e)
					{
						$tokenPermissions = null;
					}
				}
				else
				{
					$tokenId = null;
				}

				if ($tokenId)
				{
					$viewParams = $this->getCloudflareRepo()->organizeSettings();
				}
			}
			catch(\Exception $e)
			{
			}

			if (empty($appForCloudflareOptions['cfTokenId']) || $appForCloudflareOptions['cfTokenId'] !== $tokenId)
			{
				$this->updateOption('cfTokenId', $tokenId);
			}
		}

		$viewParams['tokenId'] = $tokenId;
		$viewParams['error'] = $error;
		$viewParams['tokenPermissions'] = $tokenPermissions;

		return $this->view('settings', $viewParams);
	}


	protected function actionToggle()
	{
		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();

		$cloudflareSettings = $cloudflareRepo->organizeSettings(null, false)['settings'];
		$settingsToChange = [];

		foreach ($cloudflareSettings as $setting)
		{
			if (!empty($setting['options']['editable']))
			{
				if (!empty($setting['defaults']['type']) && $setting['defaults']['type'] === 'checkbox')
				{
					$newArray = $this->filter($setting['id'], 'array-str');
					$newValue = [];

					foreach ($setting['options']['value'] as $key => $value)
					{
						$newValue[$key] = empty($newArray[$key]) ? 'off' : $newArray[$key];
						$this->convertBoolean($newValue[$key]);
					}
					if ($newValue != $setting['options']['value'])
					{
						$settingsToChange[$setting['id']] = ['id' => $setting['id'], 'value' => $newValue];
					}
				}
				// For bot fight mode
				elseif(!empty($setting['defaults']['type']) && $setting['defaults']['type'] === 'array' && !is_array($setting['options']['value']))
				{
					$newValue = $this->filter($setting['id'], $setting['defaults']['data_type'][$setting['defaults']['value_key']]);

					$result = $cloudflareRepo->getEndpointResultsByKey($setting['defaults']['override_endpoint'])['result'];

					if ($newValue != $result[$setting['defaults']['value_key']])
					{
						$result[$setting['defaults']['value_key']] = $newValue;
						$settingsToChange[$setting['id']] = $result;
					}
				}
				elseif(!empty($setting['defaults']['type']) && $setting['defaults']['type'] === 'array')
				{
					$newArray = $this->filter($setting['id'], 'array-array-str');
					$newValue = [];

					foreach ($setting['options']['value'] as $key => $value)
					{
						foreach ($value as $subKey => $subValue)
						{
							$newValue[$key][$subKey] = empty($newArray[$key][$subKey]) ? '' : $newArray[$key][$subKey];

							if (!empty($setting['defaults']['data_type'][$key][$subKey]))
							{
								if ($setting['defaults']['data_type'][$key][$subKey] === 'bool')
								{
									$this->convertBoolean($newValue[$key][$subKey], true);
								}
								elseif($setting['defaults']['data_type'][$key][$subKey] === 'int')
								{
									$newValue[$key][$subKey] = (int)$newValue[$key][$subKey];
								}
							}

						}
					}

					if ($newValue != $setting['options']['value'])
					{
						$settingsToChange[$setting['id']] = ['id' => $setting['id'], 'value' => $newValue];
					}

				}
				else
				{
					$newValue = $this->filter($setting['id'], 'str');

					// Need to watch this in case there's a Cloudflare setting that actually can be set to numeric "1" or "0"
					if (!empty($setting['defaults']['data_type']))
					{
						if ($setting['defaults']['data_type'] === 'bool')
						{
							$this->convertBoolean($newValue, is_bool($setting['defaults']['good']));
						}
						elseif($setting['defaults']['data_type'] === 'int')
						{
							$newValue = (int)$newValue;

							// This is for origin_max_http_version (a toggle with possible values of 1 or 2)
							if (!empty($setting['defaults']['good']) && $setting['defaults']['good'] == 2)
							{
								$newValue++;
								if (is_string($setting['defaults']['good']))
								{
									$newValue = (string)$newValue;
								}
							}
						}
					}

					if (!is_array($setting['options']['value']) && (strlen($newValue) > 0 || is_bool($newValue)) && $newValue != $setting['options']['value'])
					{
						$settingsToChange[$setting['id']] = ['id' => $setting['id'], 'value' => $newValue];
					}
				}
			}
		}
		if ($settingsToChange)
		{
			$cloudflareRepo->updateSettings($settingsToChange);
		}
	}

	protected function actionCopySettings()
	{
		$this->assertHasOwnApiToken();

		$cloudflareRepo = $this->getCloudflareRepo();

		$zones = $cloudflareRepo->getZones();

		if ($this->isPost())
		{
			if (!$zoneCopyFrom = $this->filter('zone', 'str'))
			{
				return $this->error($this->phrase('please_select_a_zone'));
			}

			$validZone = false;
			foreach ($zones as $zone)
			{
				if ($zone['name'] === $zoneCopyFrom)
				{
					$validZone = true;
					break;
				}
			}

			if (!$validZone)
			{
				return $this->error($this->phrase('please_select_a_zone'));
			}

			$copyFromSettings = $cloudflareRepo->stripExtraSettingData($cloudflareRepo->organizeSettings($zoneCopyFrom, false)['settings']);

			// Edge case since we are dealing with multiple zones, uncache source.
			$cloudflareRepo->clearEndpointResults();

			$existingSettings = $cloudflareRepo->stripExtraSettingData($cloudflareRepo->organizeSettings(null, false)['settings']);

			if ($settingChanges = $cloudflareRepo->getSettingsDiff($copyFromSettings, $existingSettings))
			{
				$cloudflareRepo->updateSettings($settingChanges);
			}

			wp_safe_redirect(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php')));
			return false;
		}

		$viewParams = [
			'zones' => $zones
		];

		return $this->view('copySettings', $viewParams);
	}

	protected function actionEasy()
	{
		$this->assertHasOwnDomain();

		$this->assertNonce();

		if ($this->isPost())
		{
			$this->getCloudflareRepo()->setEasyMode();
			wp_safe_redirect(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php')));
			return false;
		}
		$viewParams = [];

		return $this->view('easyConfig', $viewParams);
	}


	public function actionFirewall()
	{
		if ($this->handleAction() === false)
		{
			return;
		};

		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();

		$viewParams = [
			'rules' => $cloudflareRepo->getFirewallRules(),
			'rules_user_agent' => $cloudflareRepo->getFirewallUserAgentRules(),
			'rules_ip' => $cloudflareRepo->getZoneFirewallAccessRules(),
			'dash_base' => $cloudflareRepo->getDashBase(),
		];

		if (!empty($viewParams['rules']))
		{
			$promises = [];

			foreach ($viewParams['rules'] as $key => $rule)
			{
				if (in_array($rule['action'], ['challenge', 'js_challenge', 'managed_challenge']))
				{
					$promises[$key] = $cloudflareRepo->getGraphQLCaptchaSolveRate($rule['id']);
				}
				elseif (in_array($rule['action'], ['block', 'allow', 'log', 'bypass']))
				{
					$promises[$key] = $cloudflareRepo->getGraphQLRuleActivityQuery($rule['id']);
				}
			}

			$promises = $cloudflareRepo->resolvePromises($promises);

			foreach ($viewParams['rules'] as $key => &$rule)
			{
				if (in_array($rule['action'], ['challenge', 'js_challenge', 'managed_challenge']))
				{
					$rule['captcha_solve_rate'] = $cloudflareRepo->prepareGraphQLCaptchaSolveRate($promises[$key]);
				}
				elseif (in_array($rule['action'], ['block', 'allow', 'log', 'bypass']))
				{
					$rule['captcha_solve_rate'] = $cloudflareRepo->prepareGraphQLRuleActivityQuery($promises[$key]);
				}
			}
		}

		return $this->view('firewall', $viewParams);
	}

	public function actionAccess()
	{
		if ($this->handleAction() === false)
		{
			return;
		};

		$this->assertHasOwnDomain();
		$this->assertHasOwnApiToken();

		$cloudflareRepo = $this->getCloudflareRepo();

		$viewParams = [
			'apps' => $cloudflareRepo->getAccessApps(),
			'groups' => $cloudflareRepo->getAccessGroups(),
			'dash_base' => $cloudflareRepo->getTeamsDashBase()
		];

		return $this->view('access', $viewParams);
	}

	public function actionRules()
	{
		if ($this->handleAction() === false)
		{
			return;
		};

		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();

		$viewParams = [
			'page_rules' => $cloudflareRepo->getPageRules(),
			'cache_rules' => $cloudflareRepo->getCacheRules(),
			'dash_base' => $cloudflareRepo->getDashBase()
		];

		return $this->view('rules', $viewParams);
	}




	public function actionAnalytics()
	{
		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();

		$viewParams = $cloudflareRepo->getRumSiteStatus();
		$viewParams['sub_action'] = $this->filter('sub_action', 'str');

		if ($viewParams['sub_action'] === 'enable')
		{
			$this->assertNonce();
			if ($this->isPost())
			{
				$cloudflareRepo->updateRumSite(null, true, $this->filter('exclude_europe', 'bool'));
				wp_safe_redirect(menu_page_url('app-for-cf_analytics', false));
			}
			else
			{
				return $this->view('analytics_enable', $viewParams);
			}
		}
		elseif ($viewParams['sub_action'] === 'disable')
		{
			$this->assertNonce();
			$cloudflareRepo->updateRumSite(null, false);
			wp_safe_redirect(menu_page_url('app-for-cf_analytics', false));
		}

		$viewParams['accountId'] = $cloudflareRepo->getAccountId();
		return $this->view('analytics', $viewParams);
	}




	public function actionCaching()
	{
		if ($this->handleAction() === false)
		{
			return;
		};

		$this->assertHasOwnDomain();

		$viewParams = [];
		return $this->view('caching', $viewParams);
	}

	protected function actionGuestPageCache()
	{
		$this->assertNonce();
		$this->assertHasOwnDomain();

		if ($this->isPost())
		{
			$seconds = $this->filter('seconds', 'uint');
			$this->updateOption('cfPageCachingSeconds', $seconds);
			if ($seconds)
			{
				$cloudflareRepo = $this->getCloudflareRepo();
				$cacheRules = $cloudflareRepo->getCacheRules();

				$expressionClass = $this->getClassName('DigitalPoint\Cloudflare\Data\Expression');
				$expressionClass = new $expressionClass();
				/** @var \DigitalPoint\Cloudflare\Data\Expression $expressionClass */

				$expression = $expressionClass->getGuestCache();

				$hasCacheRule = false;

				foreach ($cacheRules as $cacheRule)
				{
					if ($cacheRule['expression'] == $expression)
					{
						if (!$cacheRule['enabled'])
						{
							$this->getCloudflareRepo()->deleteCacheRule($cacheRule['ruleset_id'], $cacheRule['id']);
						}
						else
						{
							$hasCacheRule = true;
							break;
						}
					}
				}

				if (!$hasCacheRule)
				{
					$this->getCloudflareRepo()->addSpecialCacheRule('guest_cache');
				}

			}
			else
			{
				$this->getCloudflareRepo()->deleteSpecialCacheRule('guest_cache');
			}
			return;
		}

		if ($this->filter('sub_action','str') === 'disable')
		{
			$this->updateOption('cfPageCachingSeconds', 0);
			$this->getCloudflareRepo()->deleteSpecialCacheRule('guest_cache');
			return;
		}

		$viewParams = [];

		return $this->view('cachingGuestPage', $viewParams);
	}

	public function actionDmarc()
	{
		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();
		$dmarcReport = $cloudflareRepo->getDmarcReports();
		$cloudflareRepo->getZoneId($hostname, true, true);

		if (!empty($dmarcReport['result']) && !empty($dmarcReport['result']['status']) && $dmarcReport['result']['status'] === 'missing-dmarc-report')
		{
			/* translators: %1$s = <a href...>, %2$s = </a> */
			$this->error(sprintf(__('There’s no RUA found in your DMARC record. Add RUA to start receiving DMARC reports. %1$sFix here%2$s.', 'app-for-cf'), sprintf('<a href="%s/%s/%s" target="_blank">', $cloudflareRepo->getDashBaseAccount(), $hostname, 'email/dmarc-management/dmarc-reports/dmarc/wizard'), '</a>'));
		}

		$approvedSources = [];

		if (!empty($dmarcReport['result']['approved_sources']))
		{
			foreach($dmarcReport['result']['approved_sources'] as $source)
			{
				$approvedSources[] = $source['slug'];
			}
		}

		$dmarcSources = $cloudflareRepo->resolvePromises([$cloudflareRepo->getDmarcSources($approvedSources)]);

		$viewParams = [
			'sources' => $cloudflareRepo->prepareGraphQLDmarcSources($dmarcSources[0]),
			'management_url' => sprintf($cloudflareRepo->getDashBaseAccount() . '/%s/email/dmarc-management', $hostname)
		];

		return $this->view('dmarc', $viewParams);
	}

	public function actionCache()
	{
		$this->assertHasOwnDomain();

		if ($this->isPost())
		{
			if (\DigitalPoint\Cloudflare\Base\Pub::getInstance()->applyFilters('app_for_cf_purge_cache', true))
			{
				$this->getCloudflareRepo()->purgeCache();
			}
			echo wp_kses('<div class="updated notice is-dismissible"><p>' . esc_html($this->phrase('cloudflare_cache_purged')) . '</p></div>',
				[
					'div' => [
						'class' => [],
					],
					'p' => [
					]
				]
			);
		}

		$viewParams = [];
		return $this->view('cache', $viewParams);
	}



	public function actionTurnstileWidgetAdd()
	{
		$this->assertHasOwnDomain();

		$cloudflareRepo = $this->getCloudflareRepo();

		$site = $cloudflareRepo->getTurnstileWidgetByDomain();
		if ($site)
		{
			return $this->error($this->phrase('cloudflare_turnstile_site_exists') . '<br /><br /><a href="' . esc_url($cloudflareRepo->getTurnstileSiteUrlEdit($site['sitekey'])) . '" target="_blank">' . $this->phrase('cloudflare_turnstile_site_exists_view_existing') . '</a>.');
		}

		if ($this->isPost())
		{
			$result = $cloudflareRepo->addTurnstileSite(
				get_bloginfo('name'),
				wp_parse_url($this->getSiteUrl(), PHP_URL_HOST)
			);

			if (!empty($result['result']))
			{
				$this->updateOption(
					'cfTurnstile',
					[
						'siteKey' => $result['result']['sitekey'],
						'secretKey' => $result['result']['secret']
					]
				);
			}

			wp_safe_redirect(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php')));
		}

		return $this->view('TurnstileSiteAdd', []);
	}


	public function actionMultisitesettings()
	{
		$tokenPermissions = [];

		try {

			$cloudflareRepo = $this->getCloudflareRepo();

			$tokenId = $cloudflareRepo->verifyToken();
			if (is_string($tokenId))
			{
				try
				{
					$tokenPermissions = $cloudflareRepo->getTokenPermissions($tokenId);
				}
				catch(\Exception $e)
				{
					$tokenPermissions = null;
				}
			}
			else
			{
				$tokenId = null;
			}
		}
		catch(\Exception $e)
		{
		}

		$viewParams = [
			'tokenPermissions' => $tokenPermissions,
		];
		return $this->view('MultisiteSettings', $viewParams);
	}

	protected function convertBoolean(&$bool, $trueBoolean = false)
	{
		if ($bool === '1')
		{
			$bool = ($trueBoolean ? true : 'on');
		}
		elseif(!$bool)
		{
			$bool = ($trueBoolean ? false : 'off');
		}
	}

	protected function assertHasOwnDomain()
	{
		if (!\DigitalPoint\Cloudflare\Helper\WordPress::hasOwnDomain())
		{
			die ('<div id="message" class="error notice"><p>' . esc_html__('Feature requires your your site to have its own domain.', 'app-for-cf') . '</p></div></body></html>');
		}
	}

	protected function assertHasOwnApiToken()
	{
		if (!\DigitalPoint\Cloudflare\Helper\WordPress::hasOwnApiToken())
		{
			/* translators: %1$s = <a href...>, %2$s = </a> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Feature requires your your site to have its own %1$sCloudflare API token%2$s.', 'app-for-cf'), sprintf('<a href="%1$s">', esc_url(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php')))), '</a>') . '</p></div></body></html>');
		}
	}

	protected function assertHasKey($key)
	{
		if (empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Missing: %1$s%3$s%2$s', 'app-for-cf'), '<strong>', '</strong>', esc_html($key)) . '</p></div></body></html>');
		}
	}

	protected function assertHasChecked()
	{
		if(empty($_REQUEST['checked'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			die ('<div id="message" class="error notice"><p>' . esc_html__('No items selected.', 'app-for-cf') . '</p></div></body></html>');
		}
	}

	protected function assertCanZip()
	{
		if (!class_exists('ZipArchive'))
		{
			/* translators: %1$s = <code>, %2$s = </code> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Cloudflare backup function is only supported if you have %1$sZipArchive%2$s support. You may need to ask your host to enable this.', 'app-for-cf'), '<code>', '</code>') . '</p></div></body></html>');
		}
	}

	protected function getCloudflareRepo()
	{
		return new \DigitalPoint\Cloudflare\Repository\Cloudflare();
	}

}