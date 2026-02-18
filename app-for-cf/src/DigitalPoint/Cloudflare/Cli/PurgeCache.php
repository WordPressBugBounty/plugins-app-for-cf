<?php

namespace DigitalPoint\Cloudflare\Cli;

use WP_CLI;

/**
 * Command specific to App for Cloudflare®.
 *
 * ## EXAMPLES
 *
 *     wp app-for-cf purge-cache
 *
 * @when after_wp_load
 */

class PurgeCache
{
	/**
	 * Purge Cloudflare edge cache.
	 *
	 * @subcommand purge-cache
	 */
	public function purgeCache($args, $assocArgs)
	{
		$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();
		$cloudflareRepo->purgeCache();

		WP_CLI::success(__('Cloudflare cache purged.', 'app-for-cf'));
	}
}