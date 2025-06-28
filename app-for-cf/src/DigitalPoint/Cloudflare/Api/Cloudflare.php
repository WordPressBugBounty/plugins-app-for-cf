<?php

namespace DigitalPoint\Cloudflare\Api;

abstract class CloudflareAbstract
{
	abstract public function option($optionKey);
	abstract protected function isAdmin();
	abstract protected function isDebug();
	abstract protected function time();
	abstract protected function request($method, $url, $params);
	abstract public function resolvePromises(array $promises);
	abstract protected function parseResponse($response);
	abstract protected function logError($message);
	abstract protected function phrase($phraseKey, array $params = []);
	abstract protected function printableException($message, $code = 0, \Exception $previous = null);

	protected $baseUrl = 'https://api.cloudflare.com/client/v4/';
	protected $zuluTimeFormat = 'Y-m-d\TH:i:s\Z';

	public function getGraphQLDmarcSources($zoneId, array $approvedSources = [], $days = 7)
	{
		$currentDate = new \DateTime();
		$currentDate->setTimestamp($this->time());

		$dateFrom = clone $currentDate;
		$dateFrom->sub(new \DateInterval('P' . $days . 'D'));

		$query = 'query {
		viewer {
			zones(filter: {zoneTag: $zoneTag}) {
				dmarcReportsSourcesAdaptiveGroups(limit: 10000, filter: $filter, orderBy: [sum_totalMatchingMessages_DESC]) {
					dimensions {
						sourceOrgName
						sourceOrgSlug
						__typename
					}
					avg {
						dmarc
						dkimPass
						spfPass
						__typename
					}
					sum {
						totalMatchingMessages
						__typename
					}
					uniq {
						ipCount
						__typename
					}
					__typename
				}
				__typename
			}
			__typename
		}
	}';
		$variables = [
			'zoneTag' => $zoneId,
			'filter' => [
				'date_gt' => $dateFrom->format('Y-m-d'),
				'date_leq' => $currentDate->format('Y-m-d'),
			],
		];

		if (count($approvedSources) > 0)
		{
			$variables['filter']['AND'] = [
				[
					'sourceOrgSlug_notin' => $approvedSources
				]
			];
		}

		return $this->queryGraphQL(
			null,
			$query,
			$variables
		);
	}

	public function getGraphQLZoneAnalyticsDmarc($zoneId, $days = 7)
	{
		$currentDate = new \DateTime();
		$currentDate->setTimestamp($this->time());

		$dateFrom = clone $currentDate;
		$dateFrom->sub(new \DateInterval('P' . $days . 'D'));

		$query = 'query {
			viewer {
				zones(filter: {zoneTag: $zoneTag}) {
					dmarcReportsSourcesAdaptiveGroups(limit: 10000, filter: $filter, orderBy: [datetimeDay_DESC, sum_totalMatchingMessages_DESC]) {
						dimensions {
							datetimeDay
							dkim
							spf
							__typename
						}
						sum {
							totalMatchingMessages
							__typename
						}
						__typename
					}
					__typename
				}
				__typename
			}
		}';
		$variables = [
			'zoneTag' => $zoneId,
			'filter' => [
				'AND' => [
					[
						'date_geq' => $dateFrom->format('Y-m-d'),
						'date_leq' => $currentDate->format('Y-m-d'),
					],
				],
			],
		];

		return $this->queryGraphQL(
			null,
			$query,
			$variables
		);
	}

	public function getGraphQLZoneAnalytics($zoneId, $days = 1, $exactDate = false)
	{
		$datetimeStart = new \DateTime();
		$datetimeStart->setTimestamp($this->time());
		$datetimeStart->sub(new \DateInterval('P' . $days . 'D'));

		$datetimeEnd = new \DateTime();
		$datetimeEnd->setTimestamp($this->time());
		$datetimeEnd->sub(new \DateInterval('PT1S'));

		if ($days == 1 && !$exactDate)
		{
			$datetimeStart->sub(new \DateInterval('PT1H'));
			$datetimeEnd->sub(new \DateInterval('PT1H'));

			$groupName = 'httpRequests1hGroups';
			$timeslot = 'datetime';
			$dateFormat = $this->zuluTimeFormat;
		}
		else
		{
			$groupName = 'httpRequests1dGroups';
			$timeslot = 'date';
			$dateFormat = 'Y-m-d';
		}

		// For rebuilding daily stats
		if ($exactDate)
		{
			$datetimeStart = new \DateTime($exactDate);
			$datetimeStart->sub(new \DateInterval('P' . $days . 'D'));

			$datetimeEnd = new \DateTime($exactDate);
			$datetimeEnd->sub(new \DateInterval('PT1S'));
		}


		$query = 'query GetZoneAnalytics($zoneTag: string, $since: string, $until: string) {
			viewer {
				zones(filter: {zoneTag: $zoneTag}) {
		            totals: ' . $groupName . '(limit: 10000, filter: {' . $timeslot . '_geq: $since, ' . $timeslot . '_lt: $until}) {
		                uniq {
		                    uniques
		                    __typename
		                }
		                __typename
		            }
		            zones: ' . $groupName . '(orderBy: [' . $timeslot . '_ASC], limit: 10000, filter: {' . $timeslot . '_geq: $since, ' . $timeslot . '_lt: $until}) {
		                dimensions {
		                    timeslot: ' . $timeslot . '
		                    __typename
		                }
		                uniq {
		                    uniques
		                    __typename
		                }
		                sum {
		                    browserMap {
		                        pageViews
		                        key: uaBrowserFamily
		                        __typename
		                    }
		                    bytes
		                    cachedBytes
		                    cachedRequests
		                    contentTypeMap {
		                        bytes
		                        requests
		                        key: edgeResponseContentTypeName
		                        __typename
		                    }
		                    clientSSLMap {
		                        requests
		                        key: clientSSLProtocol
		                        __typename
		                    }
		                    countryMap {
		                        bytes
		                        requests
		                        threats
		                        key: clientCountryName
		                        __typename
		                    }
		                    encryptedBytes
		                    encryptedRequests
		                    ipClassMap {
		                        requests
		                        key: ipType
		                        __typename
		                    }
		                    pageViews
		                    requests
		                    responseStatusMap {
		                        requests
		                        key: edgeResponseStatus
		                        __typename
		                    }
		                    threats
		                    threatPathingMap {
		                        requests
		                        key: threatPathingName
		                        __typename
		                    }
		                    __typename
		                }
		                __typename
		            }
		            __typename
		        }
		        __typename
		    }
		}';


		return $this->queryGraphQL('GetZoneAnalytics', $query, [
			'zoneTag' => $zoneId,
			'since' =>  $datetimeStart->format($dateFormat),
			'until' => $datetimeEnd->format($dateFormat)
		]);

	}
	public function getGraphQLCaptchaSolveRate($zoneId, $ruleId, $days = 1)
	{
		$datetimeStart = new \DateTime();
		$datetimeStart->setTimestamp($this->time());
		$datetimeStart->sub(new \DateInterval('P' . $days . 'D'));

		$datetimeEnd = new \DateTime();
		$datetimeEnd->setTimestamp($this->time());
		$datetimeEnd->sub(new \DateInterval('PT1S'));

		$query = 'query (
  $zoneTag: string
) {
  viewer {
    zones(filter: { zoneTag: $zoneTag }) {
      issued: firewallEventsAdaptiveByTimeGroups(
        limit: 1
        filter: $issued_filter
      ) {
        count
      }
      solved: firewallEventsAdaptiveByTimeGroups(
        limit: 1
        filter: $solved_filter
      ) {
        count
      }
    }
  }
}';

		return $this->queryGraphQL(
			'GetCaptchaSolvedRate',
			$query,
			[
				'issued_filter' => [
					'OR' => [
						['action' => 'jschallenge'],
						['action' => 'managed_challenge'],
						['action' => 'challenge'],
					],
					'datetime_geq' => $datetimeStart->format($this->zuluTimeFormat),
					'datetime_leq' => $datetimeEnd->format($this->zuluTimeFormat),
					'ruleId' => $ruleId
				],
				'solved_filter' => [
					'OR' => [
						['action' => 'jschallenge_solved'],
						['action' => 'challenge_solved'],
						['action' => 'managed_challenge_non_interactive_solved'],
						['action' => 'managed_challenge_interactive_solved'],
					],
					'datetime_geq' => $datetimeStart->format($this->zuluTimeFormat),
					'datetime_leq' => $datetimeEnd->format($this->zuluTimeFormat),
					'ruleId' => $ruleId
				],
				'zoneTag' => $zoneId
			]
		);
	}

	public function getGraphQLRuleActivityQuery($zoneId, $ruleId, $days = 1)
	{
		$datetimeStart = new \DateTime();
		$datetimeStart->setTimestamp($this->time());
		$datetimeStart->sub(new \DateInterval('P' . $days . 'D'));

		$datetimeEnd = new \DateTime();
		$datetimeEnd->setTimestamp($this->time());
		$datetimeEnd->sub(new \DateInterval('PT1S'));

		$query = 'query (
  $zoneTag: string
) {
  viewer {
    zones(filter: { zoneTag: $zoneTag }) {
      issued: firewallEventsAdaptiveByTimeGroups(
        limit: 1
        filter: $filter
      ) {
        count
      }
    }
  }
}';

		return $this->queryGraphQL(
			'RuleActivityQuery',
			$query,
			[
				'filter' => [
					'AND' => [
						['action_neq' => 'challenge_solved'],
						['action_neq' => 'challenge_failed'],
						['action_neq' => 'challenge_bypassed'],
						['action_neq' => 'jschallenge_solved'],
						['action_neq' => 'jschallenge_failed'],
						['action_neq' => 'jschallenge_bypassed'],
						['action_neq' => 'managed_challenge_skipped'],
						['action_neq' => 'managed_challenge_non_interactive_solved'],
						['action_neq' => 'managed_challenge_interactive_solved'],
						['action_neq' => 'managed_challenge_bypassed'],
					],
					'datetime_geq' => $datetimeStart->format($this->zuluTimeFormat),
					'datetime_leq' => $datetimeEnd->format($this->zuluTimeFormat),
					'ruleId' => $ruleId
				],
				'zoneTag' => $zoneId
			]
		);
	}


	/*
	 * Schema: https://pages.johnspurlock.com/graphql-schema-docs/cloudflare.html
	 */
	protected function queryGraphQL($operationName, $query, array $variables, $returnPromise = true)
	{
		$method = $returnPromise ? 'postAsync' : 'POST';

		$params = [
			'operationName' => $operationName,
			'query' => $query,
			'variables' => $variables
		];

		return $this->makeRequest($method, 'graphql', ['json' => $params]);
	}

	public function listZones($domain = null)
	{
		$params = ['per_page' => 1000];
		if ($domain)
		{
			$params['name'] = $domain;
		}
		return $this->makeRequest('GET', 'zones', ['query' => $params]);
	}

	public function getSettings($zoneId, $endpoint = 'settings', $returnPromise = false)
	{
		$method = $returnPromise ? 'getAsync' : 'GET';

		$params = [];
		return $this->makeRequest($method, sprintf('zones/%s/%s', $zoneId, $endpoint), ['query' => $params]);
	}

	public function setSettings($zoneId, $settings, $endpoint = 'settings', $method = 'PATCH')
	{
		return $this->makeRequest($method, sprintf('zones/%s/%s', $zoneId, $endpoint), ['json' => $settings]);
	}

	public function getFirewallRules($zoneId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('zones/%s/rulesets/phases/http_request_firewall_custom/entrypoint', $zoneId), ['query' => $params], 404);
	}

	public function getFirewallAccessRules($zoneId, $page = 1, $perPage = 1000)
	{
		$params = [
			'page' => $page,
			'per_page' => $perPage,
		];
		return $this->makeRequest('GET', sprintf('zones/%s/firewall/access_rules/rules', $zoneId), ['query' => $params]);
	}

	public function getFirewallUserAgentRules($zoneId, $page = 1, $perPage = 1000)
	{
		$params = [
			'page' => $page,
			'per_page' => $perPage
		];
		return $this->makeRequest('GET', sprintf('zones/%s/firewall/ua_rules', $zoneId), ['query' => $params]);
	}

	public function getPageRules($zoneId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('zones/%s/pagerules', $zoneId), ['query' => $params]);
	}

	public function getRulesetPhase($zoneId, $phase, $returnFalseOnExceptionCode = 0)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('zones/%s/rulesets/phases/%s', $zoneId, $phase), ['query' => $params], $returnFalseOnExceptionCode);
	}

	public function getCacheRules($zoneId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('zones/%s/rulesets/phases/http_request_cache_settings/entrypoint', $zoneId), ['query' => $params], 404);
	}

	public function createCacheRule($zoneId, $description, $expression, $actionParameters, $identifier, $enabled = true)
	{
		$params = [
			'action' => 'set_cache_settings',
			'description' => $description,
			'expression' => $expression,
			'action_parameters' => $actionParameters,
			'enabled' => $enabled
		];

		if ($identifier)
		{
			$return = $this->makeRequest('POST', sprintf('zones/%s/rulesets/%s/rules', $zoneId, $identifier), ['json' => $params]);
		}
		else
		{
			$params = ['rules' => [
				$params
			]];

			$return = $this->makeRequest('PUT', sprintf('zones/%s/rulesets/phases/http_request_cache_settings/entrypoint', $zoneId), ['json' => $params]);
		}

		return $return;
	}

	public function deleteCacheRule($zoneId, $rulesetId, $identifier)
	{
		return $this->makeRequest('DELETE', sprintf('zones/%s/rulesets/%s/rules/%s', $zoneId, $rulesetId, $identifier));
	}

	public function getAccessApps($accountId, $hostname = null)
	{
		$params = ['per_page' => 1000];
		if ($hostname)
		{
			$params['domain'] = $hostname;
		}
		return $this->makeRequest('GET', sprintf('accounts/%s/access/apps', $accountId), ['query' => $params]);
	}

	public function getAccessGroups($accountId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('accounts/%s/access/groups', $accountId), ['query' => $params]);
	}

	public function getTurnstileSites($accountId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('accounts/%s/challenges/widgets', $accountId), ['query' => $params]);
	}

	public function getTurnstileWidget($accountId, $siteKey)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('accounts/%s/challenges/widgets/%s', $accountId, $siteKey), ['query' => $params]);
	}

	public function postTurnstileSiteVerify($secretKey, $response, $ip)
	{
		$params = [
			'secret' => $secretKey,
			'response' => $response,
			'remoteip' => $ip,
		];

		return $this->makeRequest('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', ['json' => $params]);
	}

	public function requestTrace($accountId, $url, $method, $protocol = null, $botScore = null, $country = null, $skipChallenge = null, $threatScore = null)
	{
		$params = [
			'url' => $url,
			'method' => $method,
			'skip_response' => true
		];
		if ($protocol)
		{
			$params['protocol'] = $protocol;
		}
		if ($botScore)
		{
			$params['context']['bot_score'] = $botScore;
		}
		if ($country)
		{
			$params['context']['geoloc']['iso_code'] = $country;
		}
		if ($skipChallenge)
		{
			$params['context']['skip_challenge'] = $skipChallenge;
		}
		if ($threatScore !== null)
		{
			$params['context']['threat_score'] = $threatScore;
		}

		return $this->makeRequest('POST', sprintf('accounts/%s/request-tracer/trace', $accountId), ['json' => $params]);
	}

	public function ipDetails($accountId, $ip)
	{
		if (substr_count($ip, ':'))
		{
			$params = [
				'ipv6' => $ip
			];
		}
		else
		{
			$params = [
				'ipv4' => $ip
			];
		}

		return $this->makeRequest('GET', sprintf('accounts/%s/intel/ip', $accountId), ['query' => $params]);
	}

	public function domainDetails($accountId, $domain)
	{
		$params = [
			'domain' => $domain
		];
		return $this->makeRequest('GET', sprintf('accounts/%s/intel/domain', $accountId), ['query' => $params]);
	}

	public function whois($accountId, $domain)
	{
		$params = [
			'domain' => $domain
		];
		return $this->makeRequest('GET', sprintf('accounts/%s/intel/whois', $accountId), ['query' => $params]);
	}

	public function getDmarcReports($zoneId)
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('zones/%s/email/security/dmarc-reports', $zoneId), ['query' => $params]);
	}

	public function verifyToken()
	{
		$params = [];
		return $this->makeRequest('GET', sprintf('user/tokens/verify'), ['query' => $params]);
	}

	public function purgeCache($zoneId, array $params = ['purge_everything' => true])
	{
		return $this->makeRequest('POST', sprintf('zones/%s/purge_cache', $zoneId), ['json' => $params], [971, 1134]);
	}

	protected function makeRequest($method, $endpoint, array $params = [], $returnFalseOnExceptionCode = 0, $bucketName = false)
	{
		if (empty($this->option('cloudflareAuth.token')) && empty($this->option('cloudflareAuth.email')) && empty($this->option('cloudflareAuth.api_key')))
		{
			$this->printableException($this->phrase('missing_cloudflare_authentication_info'));
			return;
		}

		// R2
		if ($bucketName && method_exists($this, 'signS3Request'))
		{
			$baseUrl = $this->r2BaseUrlFull($bucketName);
			$this->signS3Request($method, $baseUrl . $endpoint, $params);
		}
		else
		{
			$baseUrl = $this->baseUrl;

			if (!isset($params['multipart']))
			{
				$baseParams = ['headers' => [
					'Content-Type' => 'application/json',
				]];
				$params = array_merge($baseParams, $params);
			}

			$token = $this->option('cloudflareAuth.token');

			if (!empty($token))
			{
				$params['headers']['Authorization'] = 'Bearer ' . $token;
			}
			else
			{
				$params['headers']['X-Auth-Email'] = $this->option('cloudflareAuth.email');
				$params['headers']['X-Auth-Key'] = $this->option('cloudflareAuth.api_key');
			}
		}

		try
		{
			if (substr($endpoint, 0, 8) !== 'https://')
			{
				$endpoint = sprintf($baseUrl . '%s', $endpoint);
			}

			$response = $this->request($method, $endpoint, $params);
		}
		catch(Exception\Client $e)
		{
			$response = $e->response;

			if ($bucketName)
			{
				$errorCode = $e->getCode();
				if ($errorCode == 404)
				{
					return ['exists' => false];
				}
				elseif($response['contentType'] == 'application/xml')
				{
					try {
						$xml = new \SimpleXMLElement($response['contents']);
						$message = json_encode($xml);
					}
					catch(\Exception $e)
					{
						$message = 'Invalid response, code: ' . $errorCode . ' / ' . $response['contents'];
					}

					if ($this->isAdmin())
					{
						$this->printableException($message, 0, $e);
					}

					$this->logError('Cloudflare: ' . $message);
				}
			}

			if (!is_array($returnFalseOnExceptionCode))
			{
				$returnFalseOnExceptionCode = [$returnFalseOnExceptionCode];
			}

			$error = \json_decode($response['contents'], true);

			if ($e->getCode() == 404 && in_array(404, $returnFalseOnExceptionCode))
			{
				return false;
			}

			if (!empty($error['errors'][0]['code']) && !empty($error['errors'][0]['message']))
			{
				if (in_array($error['errors'][0]['code'], $returnFalseOnExceptionCode))
				{
					return false;
				}

				$message = $error['errors'][0]['code'] . ': ' . $error['errors'][0]['message'];
			}
			else
			{
				$message = $e->getMessage() . ' / ' . json_encode($error);
			}

			if (!empty($error['messages'][0]['message']))
			{
				$message .= ' / ' . $error['messages'][0]['message'];
			}

			if ($this->isAdmin())
			{
				if ($this->isDebug())
				{
					$message .= '<br /> <br /><b>' . $method . '</b> ' . sprintf($baseUrl . '%s', $endpoint) . '<br /><br />' . json_encode($params);
				}

				$this->printableException($message, 0, $e);
			}
			$this->logError('Cloudflare: ' . $message);
		}
		catch(\Exception $e)
		{
			if ($this->isAdmin())
			{
				$this->printableException($e->getMessage(), 0, $e);
			}

			$this->logError('Cloudflare: ' . $e->getMessage());
		}

		if (substr($method, -5) === 'Async')
		{
			return $response;
		}

		if (!isset($response))
		{
			return null;
		}

		$response = $this->parseResponse($response);

		if ($bucketName)
		{
			return [
				'exists' => $response['statusCode'] == 200,
				'timestamp' => strtotime($response['lastModified']),
				'mimetype' => explode(';', $response['contentType'], 2)[0],
				'size' => $response['contentLength'],
				'content' => $response['contents']
			];
		}
		{
			return json_decode($response['contents'], true);
		}
	}
}

if (trait_exists('DigitalPoint\Cloudflare\Traits\XF'))
{
	class Cloudflare extends Advanced
	{
		use \DigitalPoint\Cloudflare\Traits\XF;
	}
}
elseif(trait_exists('DigitalPoint\Cloudflare\Traits\WP'))
{
	class Cloudflare extends Advanced
	{
		use \DigitalPoint\Cloudflare\Traits\WP;
	}
}