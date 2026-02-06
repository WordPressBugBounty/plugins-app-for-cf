<?php

namespace DigitalPoint\Cloudflare\Helper;

class Api
{
	protected static $transientKey = 'acf_int';
	public static $version = null;

	public static function check($force = false)
	{
		$cloudflareAppInternal = get_transient(static::$transientKey);

		if ($force || empty($cloudflareAppInternal['d']) || $cloudflareAppInternal['d'] + 21600 < time()) // 6 hours
		{
			set_transient(static::$transientKey, ['d' => time(), 'l' => null, 'v' => false]);
			return false;
		}
		return (!empty($cloudflareAppInternal['v']));
	}
}