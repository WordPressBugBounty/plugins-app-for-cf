<?php

namespace DigitalPoint\Cloudflare\Traits\Expression;

trait WP
{
	protected function getCookiePrefix()
	{
		return '';
	}

	public function getGuestCache()
	{
		return '(' . implode(' and ', \DigitalPoint\Cloudflare\Base\Pub::getInstance()->pageCachingCriteria()) . ')';
	}

	public function getBlockInternal()
	{
		//Can't do this because WordPress doesn't separate server-side code with client-side code.
		return;
	}

	public function countriesToExpression(array $countries, $applyTo = '')
	{
		$expression = [];
		foreach ($countries as $country)
		{
			$expression[] = '(ip.geoip.country eq "' . strtoupper($country) . '")';
		}

		$expression = implode(' or ', $expression);

		if ($applyTo === 'registration')
		{
			$expression = "($expression) and (http.request.full_uri contains \"" . site_url('wp-login.php?action=register') . "\")";
		}

		return $expression;
	}
}