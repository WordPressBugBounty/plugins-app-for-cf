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
		$originalIp = $ip;

		if (strlen($ip) == 4)
		{
			// already encoded IPv4
			return $ip;
		}

		if (strlen($ip) == 16 && preg_match('/[^0-9a-f.:]/i', $ip))
		{
			// already encoded IPv6
			return $ip;
		}

		$ip = trim($ip, " \t");

		if (strpos($ip, ':') !== false)
		{
			// IPv6
			if (preg_match('#:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$#i', $ip, $match))
			{
				// embedded IPv4, just treat as IPv4
				$long = ip2long($match[1]);
				if (!$long)
				{
					return false;
				}

				return hex2bin( /* @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found */
					str_pad(dechex($long), 8, '0', STR_PAD_LEFT)
				);
			}

			if (strpos($ip, '::') !== false)
			{
				if (substr_count($ip, '::') > 1)
				{
					// ambiguous
					return false;
				}

				$delims = substr_count($ip, ':');
				if ($delims > 7)
				{
					return false;
				}

				$ip = str_replace('::', str_repeat(':0', 8 - $delims) . ':', $ip);
				if ($ip[0] == ':')
				{
					$ip = '0' . $ip;
				}
			}

			$ip = strtolower($ip);

			$parts = explode(':', $ip);
			if (count($parts) != 8)
			{
				return false;
			}

			foreach ($parts AS &$part)
			{
				$len = strlen($part);
				if ($len > 4 || preg_match('/[^0-9a-f]/', $part))
				{
					return false;
				}

				if ($len < 4)
				{
					$part = str_repeat('0', 4 - $len) . $part;
				}
			}

			$hex = implode('', $parts);
			if (strlen($hex) != 32)
			{
				return false;
			}

			if (preg_match('/^00000000000000000000ffff([0-9a-f]{8})$/', $hex, $match))
			{
				// ::ffff:IPv4 address that was written in pure IPv6 form, treat as an IPv4 address
				return hex2bin($match[1]); /* @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found */
			}

			return hex2bin($hex); /* @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found */
		}

		if (strpos($ip, '.'))
		{
			// IPv4
			if (!preg_match('#(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})#', $ip, $match))
			{
				return false;
			}

			$long = ip2long($match[1]);
			if ($long === false)
			{
				return false;
			}

			return hex2bin( /* @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found */
				str_pad(dechex($long), 8, '0', STR_PAD_LEFT)
			);
		}

		if (strlen($ip) == 4 || strlen($ip) == 16)
		{
			// already binary encoded
			return $ip;
		}

		if (is_numeric($originalIp) && $originalIp < pow(2, 32))
		{
			// IPv4 as integer
			return hex2bin( /* @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found */
				str_pad(dechex($originalIp), 8, '0', STR_PAD_LEFT)
			);
		}

		return false;
	}

	public static function converBinaryToIp($ip, $shorten = true)
	{
		if (strlen($ip) == 4)
		{
			// IPv4
			$parts = [];
			foreach (str_split($ip) AS $char)
			{
				$parts[] = ord($char);
			}

			return implode('.', $parts);
		}

		if (strlen($ip) == 16)
		{
			// IPv6
			$parts = [];
			$chunks = str_split($ip);
			for ($i = 0; $i < 16; $i += 2)
			{
				$char1 = $chunks[$i];
				$char2 = $chunks[$i + 1];

				$part = sprintf('%02x%02x', ord($char1), ord($char2));
				if ($shorten)
				{
					// reduce this to the shortest length possible, but keep 1 zero if needed
					$part = ltrim($part, '0');
					if (!strlen($part))
					{
						$part = '0';
					}
				}
				$parts[] = $part;
			}

			$output = implode(':', $parts);
			if ($shorten)
			{
				$output = preg_replace_callback(
					'/((^0|:0){2,})(.*)$/',
					function($matches)
					{
						return ':' . (strlen($matches[3]) ? $matches[3] : ':');
					},
					$output
				);

				if ($output == ':')
				{
					// correct way of writing an IPv6 address of all zeroes
					$output = '::';
				}

				if (preg_match('/^::ffff:([0-9a-f]{2})([0-9a-f]{2}):([0-9a-f]{2})([0-9a-f]{2})$/i', $output, $match))
				{
					// IPv4-mapped IPv6
					$output = '::ffff:' . hexdec($match[1]) . '.' . hexdec($match[2]) . '.'
						. hexdec($match[3]) . '.' . hexdec($match[4]);
				}
			}

			return strtolower($output);
		}

		if (preg_match('/^[0-9]+$/', $ip))
		{
			return long2ip($ip + 0);
		}

		return false;
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