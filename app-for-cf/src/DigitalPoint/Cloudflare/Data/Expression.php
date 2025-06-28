<?php

namespace DigitalPoint\Cloudflare\Data;
abstract class ExpressionAbstract
{
	abstract public function getGuestCache();
	abstract public function getBlockInternal();
	abstract public function getRegistrationChallenge($includeContact);
	abstract public function countriesToExpression(array $countries, $applyTo = '');
	public function getMediaAttachmentCache() {}
	public function getR2TokenAuth($hostname, $secretKey) {}
	public function getBlockAiScrapers()
	{
		return '(cf.verified_bot_category eq "AI Crawler")';
	}
	public function getStaticContent()
	{
		$extensions = [
			'7z',
			'avi',
			'bz2',
			'csv',
			'css',
			'dmg',
			'doc',
			'docx',
			'eps',
			'exe',
			'gif',
			'gz',
			'ico',
			'iso',
			'jar',
			'jpeg',
			'jpg',
			'js',
			'mid',
			'midi',
			'mp3',
			'mp4',
			'mpeg',
			'ogg',
			'pdf',
			'ppt',
			'pptx',
			'rar',
			'tar',
			'svg',
			'svgz',
			'ttf',
			'webm',
			'webp',
			'woff',
			'woff2',
			'xls',
			'xlsx',
			'zip',
		];

		foreach ($extensions as &$extension)
		{
			$extension = '(ends_with(http.request.uri.path, ".' . $extension . '"))';
		}

		return implode(' or ', $extensions);
	}
	public function expressionToCountries($expression)
	{
		preg_match_all('#ip.geoip.country eq "(..)"#si', strtoupper($expression), $matches);
		return $matches[1];
	}
	public function getSpecialCacheRuleR2($hostname)
	{
		return '(http.host eq "' . $hostname . '")';
	}

	public function getSpecialCacheRuleCss() {}
	public function getSpecialCacheRuleImageProxy() {}
}

if (trait_exists('DigitalPoint\Cloudflare\Traits\XF'))
{
	class Expression extends ExpressionAbstract
	{
		use \DigitalPoint\Cloudflare\Traits\Expression\XF;
	}
}
elseif(trait_exists('DigitalPoint\Cloudflare\Traits\WP'))
{
	class Expression extends ExpressionAbstract
	{
		use \DigitalPoint\Cloudflare\Traits\Expression\WP;
	}
}