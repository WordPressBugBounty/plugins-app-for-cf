<?php
namespace DigitalPoint\Cloudflare\Cron;

class Jobs
{
	public static function purgeCache($urls)
	{
		$chunks = array_chunk($urls, 30);

		$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();

		foreach ($chunks as $chunk)
		{
			if (!$cloudflareRepo->purgeCache(['files' => $chunk]))
			{
				break;
			}
		}
	}
}