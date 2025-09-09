<?php

namespace DigitalPoint\Cloudflare\Traits;

trait WP
{
	protected $timeout = 15;

	protected function getClassName($className)
	{
		return $className;
	}

	public function option($optionKey)
	{
		$option = get_option('app_for_cf');

		if (is_multisite())
		{
			$optionNetwork = get_site_option('app_for_cf_network');
		}

		// All options
		if (!$optionKey)
		{
			if (is_multisite())
			{
				if (is_array($optionNetwork))
				{
					foreach($optionNetwork as $key => $item)
					{
						if (is_array($item))
						{
							foreach($item as $subKey => $subItem)
							{
								if (empty($option['network_exclude'][$key][$subKey]))
								{
									$option[$key][$subKey] = $subItem;
								}
							}
						}
						else
						{
							if (empty($option['network_exclude'][$key]))
							{
								$option[$key] = $item;
							}
						}
					}
				}

				return $option;
			}
			else
			{
				return $option;
			}
		}

		$split = explode('.', $optionKey, 10);

		foreach($split as $key => $segment)
		{
			// Using array_key_exists() because something could be null
			$option = is_array($option) && array_key_exists($segment, $option) ? $option[$segment] : (count($split) == $key + 1 ? null : []);
			if (is_multisite())
			{
				$optionNetwork = is_array($optionNetwork) && array_key_exists($segment, $optionNetwork) ? $optionNetwork[$segment] : (count($split) == $key + 1 ? null : []);
			}
		}

		if (is_multisite() && !$option)
		{
			return $optionNetwork;
		}
		return $option;
	}

	protected function updateOption($name, $value = '')
	{
		$option = get_option('app_for_cf');

		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$option[$key] = $value;
			}
		}
		else
		{
			$option[$name] = $value;
		}

		update_option('app_for_cf', $option);
	}

	protected function getSiteUrl()
	{
		if (defined('CLOUDFLARE_SITE_URL') && CLOUDFLARE_SITE_URL != '')
		{
			$siteUrl = idn_to_ascii(CLOUDFLARE_SITE_URL, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
		}
		else
		{
			$siteUrl = get_site_url();
		}

		return strtolower($siteUrl);
	}

	protected function isAdmin()
	{
		return current_user_can('manage_options');
	}

	protected function isDebug()
	{
		if (defined('WP_DEBUG'))
		{
			return WP_DEBUG;
		}
		return false;
	}

	protected function time()
	{
		return time();
	}

	protected function request($method, $url, $options)
	{
		if (!empty($options['query']))
		{
			if (is_array($options['query']))
			{
				$url .= '?' . http_build_query($options['query']);
			}
			elseif(is_string($options['query']))
			{
				$url .= '?' . $options['query'];
			}
		}

		if (substr($method, -5) === 'Async')
		{
			$class = $this->getClassName('DigitalPoint\Cloudflare\Http\Promise');
			return new $class(substr($method, 0, -5), $url, $options);
		}
		else
		{
			$args = [
				'method' => $method,
				'timeout' => $this->timeout
			];
			if (!empty($options['headers']))
			{
				$args['headers'] = $options['headers'];
			}
			if (!empty($options['json']))
			{
				$args['body'] = wp_json_encode($options['json']);
			}
			elseif (!empty($options['body']))
			{
				$args['body'] = $options['body'];
				$args['headers']['Content-Type'] = ''; // WordPress sets a default (and invalid Content-Type in the case of uploading R2 objects), setting it blank prevents this.
			}

			try
			{
				$response = wp_remote_request($url, $args);

				if ($response instanceof \WP_Error)
				{
					$error = $response->get_error_code() . ': ' . $response->get_error_message();
					$this->logError($error);
					throw new \Exception($error);
				}
				if ($response['response']['code'] >= 499 && $response['response']['code'] <= 599)
				{
					$response = wp_remote_request($url, $args); // HTTP 5xx error, retry once.  499 is a special case (also retrying that once).
				}

				if (is_array($response))
				{
					if ($response['response']['code'] >= 400 && $response['response']['code'] <= 499)
					{
						$exception = new \DigitalPoint\Cloudflare\Api\Exception\Client($response['response']['message'], $response['response']['code']);
						$exception->response = $this->parseResponse($response);
						throw $exception;
					}
					elseif($response['response']['code'] >= 500 && $response['response']['code'] <= 599)
					{
						$exception = new \DigitalPoint\Cloudflare\Api\Exception\Server($response['response']['message'], $response['response']['code']);
						$exception->response = $this->parseResponse($response);
						throw $exception;
					}
				}
			}
			catch(\Exception $e)
			{
				throw $e;
			}

			return $response;
		}
	}

	public function resolvePromises(array $promises)
	{
		$promises = array_filter($promises);

		$requests = [];

		foreach ($promises as $key => $promise)
		{
			$request = [
				'url' => $promise->url,
				'type' => $promise->method,
			];

			if (!empty($promise->options['headers']))
			{
				$request['headers'] = $promise->options['headers'];
			}
			if (!empty($promise->options['json']))
			{
				$request['data'] = wp_json_encode($promise->options['json']);
			}

			$requests[$key] = $request;
		}

		if (version_compare($GLOBALS['wp_version'], '6.2', '<' ))
		{
			// Deprecated in WordPress 6.2... need this for older versions.
			$responses = \Requests::request_multiple($requests, ['timeout' => $this->timeout]);
		}
		else
		{
			$responses = \WpOrg\Requests\Requests::request_multiple($requests, ['timeout' => $this->timeout]);
		}

		foreach($promises as $key => &$promise)
		{
			if (empty($responses[$key]->body))
			{
				$promise = null;
			}
			else
			{
				$promise = json_decode($responses[$key]->body, true);
			}
		}
		return $promises;
	}


	protected function parseResponse($response)
	{
		// Response already parsed if it failed > 1 times.  Won't be parsed normally (if it failed 0 or 1 time).
		if (isset($response['statusCode']))
		{
			return $response;
		}

		return [
			'statusCode' => !empty($response['response']['code']) ? $response['response']['code'] : '',
			'contents' => !empty($response['body']) ? $response['body'] : '',
			'contentType' => !empty($response['headers']['Content-Type']) ? $response['headers']['Content-Type'] : '',
			'contentLength' => !empty($response['headers']['Content-Length']) ? $response['headers']['Content-Length'] : '',
			'lastModified' => !empty($response['headers']['Last-Modified']) ? $response['headers']['Last-Modified'] : '',
		];
	}

	protected function logError($message)
	{
		set_transient('app_for_cf_last_error', str_replace([$this->option('cloudflareAuth.token')], '******', $message), 10);
	}

	protected function phrase($phraseKey, $params = [])
	{
		if ($params)
		{
			$phrases = [
				/* translators: %s = zone (domain) name coming from user's Cloudflare account */
				'cloudflare_zone_not_found_x' => vsprintf(__('Cloudflare zone not found: %s', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for seconds */
				'x_seconds' => vsprintf(__('%s seconds', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for minutes */
				'x_minutes' => vsprintf(__('%s minutes', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for hours */
				'x_hours' => vsprintf(__('%s hours', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for days */
				'x_days' => vsprintf(__('%s days', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for months */
				'x_months' => vsprintf(__('%s months', 'app-for-cf'), $params),

				/* translators: %s = Numeric value for megabytes */
				'x_mb' => vsprintf(__('%s MB', 'app-for-cf'), $params),

				/* translators: %s = TLS version */
				'tls_x' => vsprintf(__('TLS %s', 'app-for-cf'), $params),

				/* translators: %s = What is being enabled or disabled */
				'enable_disable_x' => vsprintf(__('Enable / disable \'%s\'', 'app-for-cf'), $params),
			];
		}
		else
		{
			$phrases = [
				'off' => __('Off', 'app-for-cf'),
				'on' => __('On', 'app-for-cf'),
				'essentially_off' => __('Essentially off', 'app-for-cf'),
				'low' => __('Low', 'app-for-cf'),
				'medium' => __('Medium', 'app-for-cf'),
				'high' => __('High', 'app-for-cf'),
				'under_attack' => __('I\'m under attack!', 'app-for-cf'),

				'add_header' => __('Add header', 'app-for-cf'),
				'overwrite_headers' => __('Overwrite header', 'app-for-cf'),
				'respect_existing_headers' => __('Respect existing headers', 'app-for-cf'),
				'off_not_secure' => __('Off (not secure)', 'app-for-cf'),
				'flexible' => __('Flexible', 'app-for-cf'),
				'full' => __('Full', 'app-for-cf'),
				'full_strict' => __('Full (strict)', 'app-for-cf'),
				'full_origin_pull' => __('Strict (SSL-only origin pull)', 'app-for-cf'),
				'lossless' => __('Lossless', 'app-for-cf'),
				'lossy' => __('Lossy', 'app-for-cf'),
				'no_query_string' => __('No query string', 'app-for-cf'),
				'ignore_query_string' => __('Ignore query string', 'app-for-cf'),
				'standard' => __('Standard', 'app-for-cf'),
				'zero_round_trip' => __('Zero round trip', 'app-for-cf'),
				'include_subdomains' => __('Include subdomains', 'app-for-cf'),
				'preload' => __('Preload', 'app-for-cf'),
				'nosniff' => __('No-Sniff', 'app-for-cf'),

				'cf_section_title.overview' => __('Overview', 'app-for-cf'),
				'cf_section_title.edge_certificates' => __('Edge Certificates', 'app-for-cf'),
				'cf_section_title.origin_server' => __('Origin Server', 'app-for-cf'),
				'cf_section_title.waf' => __('WAF', 'app-for-cf'),
				'cf_section_title.bots' => __('Bots', 'app-for-cf'),
				'cf_section_title.settings' => __('Settings', 'app-for-cf'),
				'cf_section_title.image_optimization' => __('Image Optimization', 'app-for-cf'),
				'cf_section_title.content_optimization' => __('Content Optimization', 'app-for-cf'),
				'cf_section_title.protocol_optimization' => __('Protocol Optimization', 'app-for-cf'),
				'cf_section_title.other' => __('Other', 'app-for-cf'),
				'cf_section_title.configuration' => __('Configuration', 'app-for-cf'),
				'cf_section_title.tiered_cache' => __('Tiered Cache', 'app-for-cf'),

				'cf_setting_title.security_level' => __('Security Level', 'app-for-cf'),
				'cf_setting_title.development_mode' => __('Development Mode', 'app-for-cf'),
				'cf_setting_title.0rtt' => __('0-RTT Connection Resumption', 'app-for-cf'),
				'cf_setting_title.http2' => __('HTTP/2', 'app-for-cf'),
				'cf_setting_title.origin_max_http_version' => __('HTTP/2 to Origin', 'app-for-cf'),
				'cf_setting_title.http3' => __('HTTP/3 (with QUIC)', 'app-for-cf'),
				'cf_setting_title.ip_geolocation' => __('IP Geolocation', 'app-for-cf'),
				'cf_setting_title.ipv6' => __('IPv6 Compatibility', 'app-for-cf'),
				'cf_setting_title.max_upload' => __('Maximum Upload Size', 'app-for-cf'),
				'cf_setting_title.nel' => __('Network Error Logging', 'app-for-cf'),
				'cf_setting_title.opportunistic_onion' => __('Onion Routing', 'app-for-cf'),
				'cf_setting_title.pseudo_ipv4' => __('Pseudo IPv4', 'app-for-cf'),
				'cf_setting_title.websockets' => __('WebSockets', 'app-for-cf'),

				'cf_setting_title.always_online' => __('Always Online™', 'app-for-cf'),
				'cf_setting_title.browser_cache_ttl' => __('Browser Cache TTL', 'app-for-cf'),
				'cf_setting_title.cache_level' => __('Caching Level', 'app-for-cf'),
				'cf_setting_title.crawlhints' => __('Crawler Hints', 'app-for-cf'),
				'cf_setting_title.tiered_caching' => __('Smart Tiered Caching Topology', 'app-for-cf'),
				'cf_setting_title.always_use_https' => __('Always Use HTTPS', 'app-for-cf'),
				'cf_setting_title.automatic_https_rewrites' => __('Automatic HTTPS Rewrites', 'app-for-cf'),
				'cf_setting_title.ech' => __('Encrypted Client Hello', 'app-for-cf'),
				'cf_setting_title.certificate_transparency' => __('Certificate Transparency Monitoring', 'app-for-cf'),
				'cf_setting_title.min_tls_version' => __('Minimum TLS Version', 'app-for-cf'),
				'cf_setting_title.opportunistic_encryption' => __('Opportunistic Encryption', 'app-for-cf'),
				'cf_setting_title.security_header' => __('HTTP Strict Transport Security (HSTS)', 'app-for-cf'),
				'cf_setting_title.ssl' => __('SSL/TLS Encryption Mode', 'app-for-cf'),
				'cf_setting_title.tls_1_3' => __('TLS 1.3', 'app-for-cf'),
				'cf_setting_title.tls_client_auth' => __('Authenticated Origin Pulls', 'app-for-cf'),
				'cf_setting_title.amp_real_url' => __('AMP Real URL', 'app-for-cf'),
				'cf_setting_title.sxg' => __('Automatic Signed Exchanges (SXGs)', 'app-for-cf'),
				'cf_setting_title.speed_brain' => __('Speed Brain', 'app-for-cf'),
				'cf_setting_title.fonts' => __('Cloudflare Fonts', 'app-for-cf'),
				'cf_setting_title.early_hints' => __('Early Hints', 'app-for-cf'),
				'cf_setting_title.h2_prioritization' => __('Enhanced HTTP/2 Prioritization', 'app-for-cf'),
				'cf_setting_title.minify' => __('Auto Minify', 'app-for-cf'),
				'cf_setting_title.polish' => __('Polish', 'app-for-cf'),
				'cf_setting_title.webp' => __('Polish WebP', 'app-for-cf'),
				'cf_setting_title.rocket_loader' => __('Rocket Loader™', 'app-for-cf'),

				'cf_setting_title.bot_fight_mode' => __('Bot Fight Mode', 'app-for-cf'),
				'cf_setting_title.ai_bots_protection' => __('AI Bots', 'app-for-cf'),
				'cf_setting_title.crawler_protection' => __('AI Labyrinth', 'app-for-cf'),
				'cf_setting_title.bot_likely_automated' => __('Likely Automated', 'app-for-cf'),
				'cf_setting_title.bot_definitely_automated' => __('Definitely Automated', 'app-for-cf'),
				'cf_setting_title.bot_verified_bots' => __('Verified Bots', 'app-for-cf'),
				'cf_setting_title.bot_static_resource_protection' => __('Static Resource Protection', 'app-for-cf'),
				'cf_setting_title.bot_optimize_wordpress' => __('Optimize For WordPress', 'app-for-cf'),
				'cf_setting_title.bot_enable_js' => __('JavaScript Detections', 'app-for-cf'),

				'cf_setting_title.leaked_credential_checks' => __('Leaked credentials', 'app-for-cf'),
				'cf_setting_title.challenge_ttl' => __('Challenge Passage', 'app-for-cf'),
				'cf_setting_title.browser_check' => __('Browser Integrity Check', 'app-for-cf'),
				'cf_setting_title.replace_insecure_js' => __('Replace insecure JavaScript libraries', 'app-for-cf'),

				'cf_setting_title.waf' => __('Managed Rules (previous version)', 'app-for-cf'),
				'cf_setting_title.email_obfuscation' => __('Email Address Obfuscation', 'app-for-cf'),
				'cf_setting_title.hotlink_protection' => __('Hotlink Protection', 'app-for-cf'),

				'cf_setting_explain.0rtt' => __('Improves performance for clients who have previously connected to your website.', 'app-for-cf'),
				'cf_setting_explain.http2' => __('Accelerates your website with HTTP/2.', 'app-for-cf'),
				'cf_setting_explain.origin_max_http_version' => __('Allow HTTP/2 requests between Cloudflare\'s edge and your origin.', 'app-for-cf'),
				'cf_setting_explain.http3' => __('Accelerates HTTP requests by using QUIC, which provides encryption and performance improvements compared to TCP and TLS.', 'app-for-cf'),
				'cf_setting_explain.ip_geolocation' => __('Include the country code of the visitor location with all requests to your website.', 'app-for-cf'),
				'cf_setting_explain.ipv6' => __('Enable IPv6 support and gateway.', 'app-for-cf'),
				'cf_setting_explain.max_upload' => __('The amount of data visitors can upload to your website in a single request.', 'app-for-cf'),
				'cf_setting_explain.nel' => __('Browser reports that show end user connectivity to your sites.', 'app-for-cf'),
				'cf_setting_explain.opportunistic_onion' => __('Onion Routing allows routing traffic from legitimate users on the Tor network through Cloudflare\'s onion services rather than exit nodes, thereby improving privacy of the users and enabling more fine-grained protection.', 'app-for-cf'),
				'cf_setting_explain.pseudo_ipv4' => __('Adds an IPv4 header to requests when a client is using IPv6, but the server only supports IPv4.', 'app-for-cf'),
				'cf_setting_explain.websockets' => __('Allow WebSockets connections to your origin server.', 'app-for-cf'),
				'cf_setting_explain.always_online' => __('Keep your website online for visitors when your origin server is unavailable. Cloudflare serves limited copies of web pages available from the Internet Archive\'s Wayback Machine.', 'app-for-cf'),
				'cf_setting_explain.browser_cache_ttl' => __('Determine the length of time Cloudflare instructs a visitor\'s browser to cache files. During this period, the browser loads the files from its local cache, speeding up page loads.', 'app-for-cf'),
				'cf_setting_explain.cache_level' => __('Determine how much of your website\'s static content you want Cloudflare to cache. Increased caching can speed up page load time.', 'app-for-cf'),
				'cf_setting_explain.crawlhints' => __('Crawler Hints provide high quality data to search engines and other crawlers when sites using Cloudflare change their content. This allows crawlers to precisely time crawling, avoid wasteful crawls, and generally reduce resource consumption on origins and other Internet infrastructure.', 'app-for-cf'),
				'cf_setting_explain.tiered_caching' => __('Tiered caching is a practice where Cloudflare\'s network of global data centers are divided into a hierarchy of upper-tiers and lower-tiers. In order to control bandwidth and number of connections between an origin and Cloudflare, only upper-tiers are permitted to request content from an origin and are responsible for distributing information to the lower-tiers. By enabling Tiered Cache, Cloudflare will dynamically find the single best upper tier for an origin using Argo performance and routing data. This practice improves bandwidth efficiency by limiting the number of data centers that can ask the origin for content, reduces origin load, and makes websites more cost-effective to operate.', 'app-for-cf'),
				'cf_setting_explain.always_use_https' => __('Redirect all requests with scheme "http" to "https". This applies to all http requests to the zone.', 'app-for-cf'),
				'cf_setting_explain.automatic_https_rewrites' => __('Automatic HTTPS Rewrites helps fix mixed content by changing "http" to "https" for all resources or links on your web site that can be served with HTTPS.', 'app-for-cf'),
				'cf_setting_explain.ech' => __('Encrypted Client Hello (ECH) enhances the privacy of visitors to your website by encrypting the entire ClientHello message during the TLS handshake, including the Server Name Indication (SNI).', 'app-for-cf'),
				'cf_setting_explain.certificate_transparency' => __('Receive an email when a Certificate Authority issues a certificate for your domain.', 'app-for-cf'),
				'cf_setting_explain.min_tls_version' => __('Only allow HTTPS connections from visitors that support the selected TLS protocol version or newer.', 'app-for-cf'),
				'cf_setting_explain.opportunistic_encryption' => __('Opportunistic Encryption allows browsers to benefit from the improved performance of HTTP/2 by letting them know that your site is available over an encrypted connection. Browsers will continue to show "http" in the address bar, not "https".', 'app-for-cf'),
				'cf_setting_explain.security_header' => __('Enforce web security policy for your website.', 'app-for-cf'),
				'cf_setting_explain.ssl' => __('This controls if your site runs under the secure HTTPS protocol for clients as well as how Cloudflare communicates with your origin server.', 'app-for-cf'),
				'cf_setting_explain.tls_1_3' => __('Enable the latest version of the TLS protocol for improved security and performance.', 'app-for-cf'),
				'cf_setting_explain.tls_client_auth' => __('TLS client certificate presented for authentication on origin pull.', 'app-for-cf'),
				'cf_setting_explain.amp_real_url' => __('Display your site\'s actual URL on your AMP pages, instead of the traditional Google AMP cache URL.  Even if you don\'t use AMP, this setting will add CCA DNS records to your zone that is a best practice if your site is using HTTPS.', 'app-for-cf'),
				'cf_setting_explain.sxg' => __('Improve your website\'s performance by making cacheable resources available on Google\'s Signed Exchanges. Enable Chromium based browsers to prefetch your website on Google\'s search results page and make your website faster. Improve the Largest Contentful Paint (LCP) which is part of the Core Web Vitals and increase your SEO ranking.', 'app-for-cf'),
				'cf_setting_explain.brotli' => __('Speed up page load times for your visitor\'s HTTPS traffic by applying Brotli compression.', 'app-for-cf'),
				'cf_setting_explain.speed_brain' => __('Speed Brain speeds up page load times by leveraging the Speculation Rules API. This instructs browsers to make speculative prefetch requests as a way to speed up next page navigation loading time.', 'app-for-cf'),
				'cf_setting_explain.fonts' => __('Optimize font loading. Cloudflare Fonts reduces external requests for third-party fonts, resulting in improved privacy and performance for faster page loads.', 'app-for-cf'),
				'cf_setting_explain.early_hints' => __('Cloudflare\'s edge will cache and send 103 Early Hints responses with Link headers from your HTML pages. Early Hints allows browsers to preload linked assets before they see a 200 OK or other final response from the origin.', 'app-for-cf'),
				'cf_setting_explain.h2_prioritization' => __('Optimizes the order of resource delivery, independent of the browser. Greatest improvements will be experienced by visitors using Safari and Edge browsers.', 'app-for-cf'),
				'cf_setting_explain.polish' => __('Improve image load time by optimizing images hosted on your domain.', 'app-for-cf'),
				'cf_setting_explain.webp' => __('Optionally, the WebP image codec can be used with supported clients for additional performance benefits.', 'app-for-cf'),
				'cf_setting_explain.rocket_loader' => __('Improve the paint time for pages which include JavaScript.', 'app-for-cf'),
				'cf_setting_explain.bot_fight_mode' => __('Challenge requests that match patterns of known bots, before they access your site.', 'app-for-cf'),
				'cf_setting_explain.ai_bots_protection' => __('Block bots from scraping your content for AI applications like model training. Note: Blocking AI Bots will also block verified AI bots.', 'app-for-cf'),
				'cf_setting_explain.crawler_protection' => __('Prevent AI bots that ignore your website\'s robots.txt file from accessing your website\'s content. Unauthorized AI bots will be trapped in a maze of generated nofollow links.', 'app-for-cf'),

				'cf_setting_explain.bot_likely_automated' => __('Likely automated traffic that is a high probability of being from a bot.', 'app-for-cf'),
				'cf_setting_explain.bot_definitely_automated' => __('Definitely automated traffic typically consists of bad bots.', 'app-for-cf'),
				'cf_setting_explain.bot_verified_bots' => __('Verified bots are unique good bot identities validated by Cloudflare.', 'app-for-cf'),
				'cf_setting_explain.bot_static_resource_protection' => __('Enable if static resources on your application need bot protection. Note: Static resource protection can also result in legitimate traffic being blocked.', 'app-for-cf'),
				'cf_setting_explain.bot_optimize_wordpress' => __('Enable if your website relies on features of WordPress that conflict with Super Bot Fight Mode. Note: Requires that Verified Bots are allowed.', 'app-for-cf'),
				'cf_setting_explain.bot_enable_js' => __('Use lightweight, invisible JavaScript detections to improve Bot Management.', 'app-for-cf'),

				'cf_setting_explain.leaked_credential_checks' => __('Checks databases of stolen credentials for popular CMS applications and generic authentication patterns.', 'app-for-cf'),
				'cf_setting_explain.challenge_ttl' => __('Specify the length of time that a visitor, who has successfully completed a Captcha or JavaScript Challenge, can access your website. When the configured timeout expires, the visitor will be issued a new challenge. Challenge Passage does not apply to Rate Limiting.', 'app-for-cf'),
				'cf_setting_explain.browser_check' => __('Evaluate HTTP headers from your visitors browser for threats. If a threat is found a block page will be delivered.', 'app-for-cf'),
				'cf_setting_explain.replace_insecure_js' => __('Automatically replace insecure JavaScript libraries with safer and faster alternatives provided under cdnjs and powered by Cloudflare. Currently supports the following libraries: Polyfill.', 'app-for-cf'),

				'cf_setting_explain.waf' => __('The Cloudflare WAF provides both automatic protection from vulnerabilities.', 'app-for-cf'),
				'cf_setting_explain.email_obfuscation' => __('Display obfuscated email addresses on your website to prevent harvesting by bots and spammers, without visible changes to the address for human visitors.', 'app-for-cf'),
				'cf_setting_explain.hotlink_protection' => __('Protect your images from off-site linking.', 'app-for-cf'),

				'allow' => __('Allow', 'app-for-cf'),
				'block' => __('Block', 'app-for-cf'),
				'managed_challenge' => __('Managed challenge', 'app-for-cf'),
				'no_admins_with_email' => __('No admins with email', 'app-for-cf'),
				'xenforo_country_blocking' => __('WordPress country blocking', 'app-for-cf'),
				'ai_scrapers_and_crawlers_rule' => __('AI Scrapers and Crawlers rule', 'app-for-cf'),
				'xenforo_internal_directory_blocking' => __('WordPress internal directory blocking', 'app-for-cf'),
				'xenforo_registration_contact_challenge' => __('WordPress registration challenge', 'app-for-cf'),
				'cache_xenforo_guest_pages' => __('WordPress guest page caching', 'app-for-cf'),
				'cache_xenforo_media_attachments' => __('WordPress media attachment caching', 'app-for-cf'),
				'cache_static_content' => __('Cache static content', 'app-for-cf'),
				'override_cache_r2' => __('Override cache for R2 bucket', 'app-for-cf'),

				'wordpress_admin' => __('WordPress admin', 'app-for-cf'),
				'wordpress_xml_rpc' => __('WordPress XML-RPC', 'app-for-cf'),

				'pagerules.always_use_https' => __('Always use HTTPS', 'app-for-cf'),
				'pagerules.automatic_https_rewrites' => __('Automatic HTTP rewrites', 'app-for-cf'),
				'pagerules.browser_cache_ttl' => __('Browser cache TTL', 'app-for-cf'),
				'pagerules.browser_check' => __('Browser integrity check', 'app-for-cf'),
				'pagerules.cache_deception_armor' => __('Cache deception armor', 'app-for-cf'),
				'pagerules.cache_level' => __('Cache level', 'app-for-cf'),
				'pagerules.disable_apps' => __('Disable apps', 'app-for-cf'),
				'pagerules.disable_performance' => __('Disable apps', 'app-for-cf'),
				'pagerules.disable_security' => __('Disable security', 'app-for-cf'),
				'pagerules.disable_zaraz' => __('Disable Zaraz', 'app-for-cf'),
				'pagerules.edge_cache_ttl' => __('Edge cache TTL', 'app-for-cf'),
				'pagerules.email_obfuscation' => __('Email obfuscation', 'app-for-cf'),
				'pagerules.forwarding_url' => __('Forwarding URL', 'app-for-cf'),

				'pagerules.ip_geolocation' => __('IP geolocation', 'app-for-cf'),
				'pagerules.opportunistic_encryption' => __('Opportunistic encryption', 'app-for-cf'),
				'pagerules.explicit_cache_control' => __('Origin cache control', 'app-for-cf'),
				'pagerules.security_level' => __('Security level', 'app-for-cf'),
				'pagerules.rocket_loader' => __('Rocket Loader', 'app-for-cf'),
				'pagerules.server_side_exclude' => __('Server side excludes', 'app-for-cf'),
				'pagerules.ssl' => __('SSL', 'app-for-cf'),

				'pagerules.bypass' => __('Bypass', 'app-for-cf'),
				'pagerules.basic' => __('No query string', 'app-for-cf'),
				'pagerules.simplified' => __('Ignore query string', 'app-for-cf'),
				'pagerules.aggressive' => __('Standard', 'app-for-cf'),
				'pagerules.cache_everything' => __('Cache everything', 'app-for-cf'),

				'pagerules.flexible' => __('Flexible', 'app-for-cf'),
				'pagerules.full' => __('Full', 'app-for-cf'),
				'pagerules.strict' => __('Strict', 'app-for-cf'),

				'pagerules.essentially_off' => __('Essentially off', 'app-for-cf'),
				'pagerules.low' => __('Low', 'app-for-cf'),
				'pagerules.medium' => __('Medium', 'app-for-cf'),
				'pagerules.high' => __('High', 'app-for-cf'),
				'pagerules.under_attack' => __('I\'m under attack', 'app-for-cf'),

				'pagerules.on' => __('On', 'app-for-cf'),
				'pagerules.off' => __('Off', 'app-for-cf'),

				'pagerules.html' => __('HTML', 'app-for-cf'),
				'pagerules.css' => __('CSS', 'app-for-cf'),
				'pagerules.js' => __('JS', 'app-for-cf'),

				'cacherules.cache' => __('Cache', 'app-for-cf'),
				'cacherules.edge_ttl' => __('Edge cache TTL', 'app-for-cf'),
				'cacherules.browser_ttl' => __('Browser cache TTL', 'app-for-cf'),
				'cacherules.serve_stale' => __('Serve stale content', 'app-for-cf'),
				'cacherules.respect_strong_etags' => __('Respect strong eTags', 'app-for-cf'),
				'cacherules.cache_key' => __('Cache key', 'app-for-cf'),
				'cacherules.origin_error_page_passthru' => __('Origin error page pass-thru', 'app-for-cf'),

				'cacherules.cache_by_device_type' => __('Cache by device type', 'app-for-cf'),
				'cacherules.cache_deception_armor' => __('Cache deception armor', 'app-for-cf'),
				'cacherules.ignore_query_strings_order' => __('Ignore query string order', 'app-for-cf'),
				'cacherules.custom_key' => __('Ignore query string', 'app-for-cf'),

				'enabled' => __('Enabled', 'app-for-cf'),
				'disabled' => __('Disabled', 'app-for-cf'),
				'respect_origin' => __('Respect origin', 'app-for-cf'),

				'missing_authentication_info' => __('Missing authentication info.', 'app-for-cf'),

				'cloudflare_cache_purged' => __('Cloudflare cache purged.', 'app-for-cf'),

				'cloudflare_turnstile_site_exists' => __('Your Cloudflare account already has a Turnstile site for this domain.', 'app-for-cf'),
				'cloudflare_turnstile_site_exists_view_existing' => __('View existing Site Key and Secret Key', 'app-for-cf'),
			];
		}

		if (!empty($phrases[$phraseKey]))
		{
			return $phrases[$phraseKey];
		}

		return $phraseKey;
	}
	protected function printableException($message, $code = 0, \Exception $previous = null)
	{
		$this->logError($message);
	}

	protected function dateFormat($timestamp, $withTime = false)
	{
		if ($withTime)
		{
			return sprintf(
				/* translators: %s = Date / time phrase from WordPress core */
				__('%1$s at %2$s'), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				wp_date( __('Y/m/d'), $timestamp), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				wp_date( __('g:i a'), $timestamp) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
			);
		}
		return wp_date( __('Y/m/d'), $timestamp); /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
	}

	protected function getTimeZone()
	{
		return wp_timezone_string();
	}
}