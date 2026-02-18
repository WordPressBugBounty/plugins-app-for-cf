<?php

namespace DigitalPoint\Cloudflare\Data;
abstract class ExpressionAbstract
{
	abstract public function getGuestCache();
	abstract public function getBlockInternal();
	abstract public function countriesToExpression(array $countries, $applyTo = '');
	public function getMediaAttachmentCache() {}
	public function getR2TokenAuth($hostname, $secretKey) {}
	public function getBlockAiScrapers()
	{
		return '(cf.verified_bot_category eq "AI Crawler")';
	}
	public function getStaticContent()
	{
		return '(http.request.uri.path.extension in {"7z" "avi" "avif" "apk" "bin" "bmp" "bz2" "class" "css" "csv" "doc" "docx" "dmg" "ejs" "eot" "eps" "exe" "flac" "gif" "gz" "ico" "iso" "jar" "jpg" "jpeg" "js" "mid" "midi" "mkv" "mp3" "mp4" "ogg" "otf" "pdf" "pict" "pls" "png" "ppt" "pptx" "ps" "rar" "svg" "svgz" "swf" "tar" "tif" "tiff" "ttf" "webm" "webp" "woff" "woff2" "xls" "xlsx" "zip" "zst"})';
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