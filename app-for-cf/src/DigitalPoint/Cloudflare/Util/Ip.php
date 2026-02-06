<?php
namespace DigitalPoint\Cloudflare\Util;

class Ip
{
	// From: https://www.cloudflare.com/ips/
	protected static $cloudFlareIps = [
		'v4' => [
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'104.16.0.0/13',
			'104.24.0.0/14',
			'108.162.192.0/18',
			'131.0.72.0/22',
			'141.101.64.0/18',
			'162.158.0.0/15',
			'172.64.0.0/13',
			'173.245.48.0/20',
			'188.114.96.0/20',
			'190.93.240.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17'
		],
		'v6' => [
			'2a06:98c0::/29',
			'2c0f:f248::/32',
			'2400:cb00::/32',
			'2405:8100::/32',
			'2405:b500::/32',
			'2606:4700::/32',
			'2803:f800::/32'
		]
	];


	public static function setTrueIp()
	{
		$cfIp = (empty($_SERVER['HTTP_CF_CONNECTING_IP']) || !filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) ? '' : $_SERVER['HTTP_CF_CONNECTING_IP']; /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		$ip = (empty($_SERVER['REMOTE_ADDR']) || !filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) ? '' : $_SERVER['REMOTE_ADDR']; /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

		if($cfIp && $cfIp != $ip)
		{
			// A little hacky... better to actually configure your server right, but less support issues I guess (for poorly-configured servers combined with Flexible SSL).
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
			{
				$_SERVER['HTTPS'] = 'on';
			}

			if (static::ipMatchesRanges($ip, self::$cloudFlareIps))
			{
				add_action('plugins_loaded', function() use ($cfIp) {
					$_SERVER['REMOTE_ADDR'] = $cfIp;
				}, 1);
			}
		}
	}

	public static function convertIpToBinary($ip)
	{
		if (strpos($ip, "\0") !== false)
		{
			return false;
		}
		return @inet_pton($ip);
	}

	public static function converBinaryToIp($ip, $shorten = true)
	{
		return @inet_ntop($ip);
	}

	public static function parseIp($ip)
	{
		$ip = trim($ip);
		$niceIp = $ip;

		if (preg_match('#/(\d+)$#', $ip, $match))
		{
			$ip = substr($ip, 0, -strlen($match[0]));
			$cidr = $match[1];
			if ($cidr && $cidr < 8)
			{
				$cidr = 8;
				$niceIp = $ip . "/$cidr";
			}
		}
		else
		{
			$cidr = 0;
		}

		if (strpos($ip, ':') !== false)
		{
			// IPv6 -- no partials, only CIDR
			$binary = static::convertIpToBinary($ip);
			if ($binary === false)
			{
				return false;
			}
		}
		else
		{
			$ip = preg_replace('/\.+$/', '', $ip);
			if (!preg_match('/^\d+(\.\d+){0,2}(\.\d+|\.\*)?$/', $ip))
			{
				return false;
			}

			if (substr($ip, -2) == '.*')
			{
				$ip = substr($ip, 0, -2);
			}

			$ipParts = explode('.', $ip);
			foreach ($ipParts AS $part)
			{
				if ($part < 0 || $part > 255)
				{
					return false;
				}
			}

			$localCidr = 32;
			while (count($ipParts) < 4)
			{
				$ipParts[] = 0;
				$localCidr -= 8;
			}

			if (!$cidr && $localCidr != 32)
			{
				$cidr = $localCidr;
			}

			$binary = static::convertIpToBinary(implode('.', $ipParts));
			if (!$binary)
			{
				return false;
			}
		}

		$range = static::getIpCidrRange($binary, $cidr);

		return [
			'ip' => static::converBinaryToIp(is_string($range) ? $range : $range[0]),
			'cidr' => $cidr,
		];
	}


	public static function getIpCidrRange($ip, $cidr)
	{
		if (!$cidr)
		{
			return $ip;
		}

		$bytes = strlen($ip);
		$bits = $bytes * 8;
		if ($cidr >= $bits)
		{
			return $ip; // exact match
		}

		$prefixBytes = (int)floor($cidr / 8);
		$remainingBits = ($cidr - $prefixBytes * 8);

		$prefix = substr($ip, 0, $prefixBytes);
		if ($remainingBits)
		{
			$partialByteOrd = ord($ip[$prefixBytes]); // first character after full prefix bytes
			$mask = (1 << 8 - $remainingBits) - 1;

			$upperBound = chr($partialByteOrd | $mask);
			$lowerBound = chr($partialByteOrd & ~$mask);
			$boundLength = 1;
		}
		else
		{
			$upperBound = '';
			$lowerBound = '';
			$boundLength = 0;
		}

		$suffixBytes = $bytes - $prefixBytes - $boundLength;
		if ($suffixBytes)
		{
			$lowerSuffix = str_repeat(chr(0), $suffixBytes);
			$upperSuffix = str_repeat(chr(255), $suffixBytes);
		}
		else
		{
			$lowerSuffix = '';
			$upperSuffix = '';
		}

		return [$prefix . $lowerBound . $lowerSuffix, $prefix . $upperBound . $upperSuffix];
	}


	public static function ipMatchesCidrRange($testIp, $rangeIp, $cidr)
	{
		$range = static::getIpCidrRange($rangeIp, $cidr);
		if (is_string($range))
		{
			return ($testIp === $range);
		}
		else
		{
			return static::ipMatchesRange($testIp, $range[0], $range[1]);
		}
	}

	public static function ipMatchesRange($testIp, $lowerBound, $upperBound)
	{
		return (
			strlen($testIp) === strlen($lowerBound) &&
			strcmp($testIp, $lowerBound) >= 0 &&
			strcmp($testIp, $upperBound) <= 0
		);
	}

	public static function ipMatchesRanges($ip, array $ranges)
	{
		$ip = static::convertIpToBinary($ip);
		if ($ip === false)
		{
			return false;
		}

		$type = strlen($ip) == 4 ? 'v4' : 'v6';

		if (empty($ranges[$type]))
		{
			return false;
		}

		foreach ($ranges[$type] AS $range)
		{
			if (is_string($range))
			{
				$range = explode('/', $range);
			}

			$rangeIp = static::convertIpToBinary($range[0]);
			$cidr = intval($range[1]);

			if (static::ipMatchesCidrRange($ip, $rangeIp, $cidr))
			{
				return true;
			}
		}

		return false;
	}
}