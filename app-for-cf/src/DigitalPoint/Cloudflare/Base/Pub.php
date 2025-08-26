<?php

namespace DigitalPoint\Cloudflare\Base;
class Pub
{
	protected static $instance;

	protected $preload = [];
	protected $purgeUrls = [];
	protected $cloudflareRepo = null;

	/**
	 * Protected constructor. Use {@link getInstance()} instead.
	 */
	protected function __construct()
	{
	}

	public static final function getInstance()
	{
		if (!static::$instance)
		{
			$class = self::class;
			static::$instance = new $class;

			// Set the real IP of the end user if a site isn't already doing this at the web server level and handle Flexible SSL redirection loop
			\DigitalPoint\Cloudflare\Util\Ip::setTrueIp();

			static::$instance->initHooks();
		}

		return static::$instance;
	}

	public static function autoload($class)
	{
		$filename = static::autoloaderClassToFile($class);
		if (!$filename)
		{
			return false;
		}

		$proLocation = substr_replace(APP_FOR_CLOUDFLARE_PLUGIN_DIR, '-pro', -1);

		if (file_exists($proLocation . $filename))
		{
			$cloudflareAppInternal = (array)get_transient('acf_int');

			if (
				!empty($cloudflareAppInternal['v']) ||
				strpos(@$_SERVER['SCRIPT_NAME'], '/plugins.php') !== false || /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
				in_array($class, [
					'DigitalPoint\Cloudflare\Helper\Api',

					'DigitalPoint\Cloudflare\Api\Advanced',
					'DigitalPoint\Cloudflare\Base\PubAdvanced',
					'DigitalPoint\Cloudflare\Repository\Advanced\Cloudflare'
				])
			)
			{
				include $proLocation . $filename;
				return (class_exists($class, false) || interface_exists($class, false));
			}
		}

		if (file_exists(APP_FOR_CLOUDFLARE_PLUGIN_DIR . $filename))
		{
			include APP_FOR_CLOUDFLARE_PLUGIN_DIR . $filename;
			return (class_exists($class, false) || interface_exists($class, false));
		}

		return false;
	}

	protected static function autoloaderClassToFile($class)
	{
		if (preg_match('#[^a-zA-Z0-9_\\\\]#', $class))
		{
			return false;
		}

		return '/src/' . str_replace(array('_', '\\'), '/', $class) . '.php';
	}

	/**
	 * Initializes WordPress hooks
	 */
	protected function initHooks()
	{
		$this->cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();
		if ($this->cloudflareRepo->option('cloudflarePreload'))
		{
			ob_start();

			add_filter('script_loader_tag', [$this, 'handleScriptLoaderTag'], 9999999, 3);
			add_filter('style_loader_tag', [$this, 'handleStyleLoaderTag'], 9999999, 3);

			add_action('wp_print_footer_scripts', [$this, 'handlePrintFooterScripts'], 9999999);

			add_filter('wp_preload_resources', [$this, 'filterPreloadResources'], 9999999);
		}

		add_filter('wp_headers', [$this, 'handleHeaders'], 9999999);
		add_filter('rest_post_dispatch', [$this, 'filterRestPostDispatch'], 1048576, 3);

		$purgeEverythingActions = [
			'autoptimize_action_cachepurged',	// Compat with https://wordpress.org/plugins/autoptimize
			'switch_theme',						// Switch theme
			'customize_save_after'				// Edit theme
		];
		// Going to use the same filter name as the main Cloudflare plugin since we are literally doing the exact same thing.
		// Don't force third-party devs to do it twice.
		$purgeEverythingActions = apply_filters('cloudflare_purge_everything_actions', $purgeEverythingActions);
		foreach ($purgeEverythingActions as $action)
		{
			add_action($action, [$this, 'purgeCacheEverything'], 1048576);
		}

		$cloudflarePurgeActions = [
			'delete_attachment',
			'deleted_post'
		];
		// Same as above... Going to use the same filter name as the main Cloudflare plugin since we are literally doing the exact same thing.
		// Don't force third-party devs to do it twice.
		$cloudflarePurgeActions = apply_filters('cloudflare_purge_url_actions', $cloudflarePurgeActions);
		foreach ($cloudflarePurgeActions as $action)
		{
			add_action($action, [$this, 'purgeCacheByPostIds'], 1048576);
		}

		add_action('post_updated', [$this, 'purgeCacheByPostIds'], 1048576, 3);

		// Pick up if a post changed to/from 'publish'
		add_action('transition_post_status', [$this, 'purgeCacheOnPostStatusChange'], 1048576, 3);

		// Handle comments
		add_action('transition_comment_status', [$this, 'purgeCacheOnCommentStatusChange'], 1048576, 3);
		add_action('comment_post', [$this, 'purgeCacheOnNewComment'], 1048576, 3);

		add_action('admin_bar_menu', [$this, 'adminMenuBar'], 100);

		add_action('cfPurgeCache', ['DigitalPoint\Cloudflare\Cron\Jobs', 'purgeCache']);

		add_filter('wp_get_attachment_url', [$this, 'filterWpGetAttachmentUrl'], 1048576, 2);
		add_filter('wp_calculate_image_srcset', [$this, 'filterWpCalculateImageSrcset'], 1048576, 5);

		add_action('shutdown', [$this, 'shutdown']);

		$turnstileOptions = $this->cloudflareRepo->option('cfTurnstile');
		if (!empty($turnstileOptions['siteKey']) && !empty($turnstileOptions['secretKey']))
		{
			\DigitalPoint\Cloudflare\Turnstile\WordPress::getInstance($turnstileOptions);
		}

		$class = static::autoload('DigitalPoint\Cloudflare\Base\PubAdvanced');
		if ($class)
		{
			\DigitalPoint\Cloudflare\Base\PubAdvanced::getInstance();
		}
	}

	public static function plugin_activation()
	{
		\DigitalPoint\Cloudflare\Setup::install();
		\DigitalPoint\Cloudflare\Helper\Api::check(true);
	}

	public static function plugin_deactivation()
	{
		\DigitalPoint\Cloudflare\Setup::uninstall();
	}


	/**
	 * Log debugging info to the error log.
	 *
	 * Enabled when WP_DEBUG_LOG is enabled, but can be disabled via the app_for_cf_debug_log filter.
	 *
	 * @param mixed $app_for_cf_debug The data to log.
	 */
	public static function log($app_for_cf_debug)
	{
		if (apply_filters( 'app_for_cf_debug_log', defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ))
		{
			error_log( print_r( compact( 'app_for_cf_debug' ), true ) ); /* @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r */
		}
	}

	/*
	 * wp_headers hook does not get fired for login (or admin) pages, so we don't need to check if it's the login page.
	 */
	public function handleHeaders($headers)
	{
		$noCache = is_user_logged_in();

		if (!$noCache && !empty($_COOKIE) && is_array($_COOKIE))
		{
			$noCache = (bool)preg_grep('#^wordpress_|comment_|wp-#si', array_keys($_COOKIE));
		}

		$cloudflareAppOptions = $this->cloudflareRepo->option(null);

		$cacheTime = (int)@$cloudflareAppOptions['cfPageCachingSeconds'];

		if (!$noCache &&
			$cacheTime > 0 &&
			!empty($headers['Content-Type']) && substr($headers['Content-Type'], 0, 9) === 'text/html' &&
			!is_404() &&
			(!defined('WP_DEBUG') || !WP_DEBUG)
		)
		{
			$cacheTime = (int)@$cloudflareAppOptions['cfPageCachingSeconds'];

			$headers['Cache-Control'] = 'max-age=0,s-maxage=' . $cacheTime;
		}

		return $headers;
	}

	public function handleScriptLoaderTag($tag, $handle, $src)
	{
		if (is_string($src) && strlen($src) > 3)
		{
			$this->preload['<' . sanitize_url($src) . '>;as=script;rel=preload'] = true;
		}
		return $tag;
	}

	public function handleStyleLoaderTag($tag, $handle, $href)
	{
		if (is_string($href) && strlen($href) > 3 && strpos($href, '/ie.css') === false)
		{
			$this->preload['<' . sanitize_url($href) . '>;as=style;rel=preload'] = true;
		}
		return $tag;
	}

	public function handlePrintFooterScripts()
	{
		if (!headers_sent() && $this->preload)
		{
			header('Link: ' . implode(',', array_slice(array_keys($this->preload), 0, 10)), false);
		}
	}

	public function filterPreloadResources($preloadResources)
	{
		if (is_array($preloadResources))
		{
			foreach ($preloadResources as $resource)
			{
				if(!empty($resource['href']) && !empty($resource['as']))
				{
					$preload = '<' . sanitize_url($resource['href']) . '>;as=' . esc_attr($resource['as']) . ';rel=preload';

					if (empty($this->preload[$preload]))
					{
						if (!empty($resource['fetchpriority']) && $resource['fetchpriority'] === 'high')
						{
							$this->preload = [$preload => true] + $this->preload;
						}
						else
						{
							$this->preload[$preload] = true;
						}
					}
				}
			}
		}
		return $preloadResources;
	}

	public function purgeCacheEverything()
	{
		$this->cloudflareRepo->purgeCache();
	}

	public function purgeCacheByPostIds($postIds, $postAfter = null, $postBefore = null)
	{
		$postIds = (array)$postIds;
		foreach ($postIds as $postId)
		{
			$post = get_post($postId);

			$postType = get_post_type($postId);

			if (wp_is_post_autosave($postId) ||
				wp_is_post_revision($postId) ||
				!($post instanceof \WP_Post) ||
				!is_post_type_viewable($postType)
			)
			{
				continue;
			}

			$savedPost = get_post($postId);
			if (!is_a($savedPost, 'WP_Post'))
			{
				continue;
			}

			// Home
			$this->purgeUrls[] = home_url() . '/';

			// Post
			$postLink = get_permalink($postId);
			$this->purgeUrls[] = $postLink;

			// Possible that date or status was changed
			if ($postBefore)
			{
				$this->purgeUrls[] = get_permalink($postBefore);
			}

			// Maybe trashed?
			if (get_post_status($postId) === 'trash')
			{
				$oldPost = str_replace('__trashed', '', $postLink);
				$this->purgeUrls[] = $oldPost;
				$this->purgeUrls[] = $oldPost . 'feed/';
			}

			// Author
			$this->purgeUrls[] = get_author_posts_url($post->post_author);
			$this->purgeUrls[] = get_author_feed_link($post->post_author);

			// Categories
			foreach (get_object_taxonomies($postType) as $taxonomy)
			{
				// Only if category is public
				$taxonomy_data = get_taxonomy($taxonomy);
				if ($taxonomy_data instanceof \WP_Taxonomy && !$taxonomy_data->public)
				{
					continue;
				}

				$terms = get_the_terms($postId, $taxonomy);

				if (empty($terms) || is_wp_error($terms))
				{
					continue;
				}

				foreach ($terms as $term)
				{
					$termLink = get_term_link($term);
					$termFeedLink = get_term_feed_link($term->term_id, $term->taxonomy);
					if (!is_wp_error($termLink) && !is_wp_error($termFeedLink))
					{
						$this->purgeUrls[] = $termLink;
						$this->purgeUrls[] = $termFeedLink;
					}
				}
			}

			// Archives
			if ($archive = get_post_type_archive_link($postType))
			{
				if ($archive != home_url())
				{
					$this->purgeUrls[] = $archive;
				}
				$this->purgeUrls[] = get_post_type_archive_feed_link($postType);
			}

			//Feeds
			$this->purgeUrls[] = get_bloginfo_rss('atom_url');
			$this->purgeUrls[] = get_bloginfo_rss('rdf_url');
			$this->purgeUrls[] = get_bloginfo_rss('rss_url');
			$this->purgeUrls[] = get_bloginfo_rss('rss2_url');
			$this->purgeUrls[] = get_bloginfo_rss('comments_atom_url');
			$this->purgeUrls[] = get_bloginfo_rss('comments_rss2_url');

			// Page for posts
			if ($pageForPosts = get_permalink(get_option('page_for_posts')))
			{
				if (is_string($pageForPosts))
				{
					$this->purgeUrls[] = $pageForPosts;
				}
			}

			// Pagination (first 5 and maybe last couple too, but not at this point).
			$totalPosts = wp_count_posts()->publish;
			$perPage = get_option('posts_per_page');

			// Limit to up to 5 pages... first one *probably* shouldn't be used, but let's be safe.
			foreach (range(1, min(5, ceil($totalPosts / $perPage))) as $page)
			{
				$this->purgeUrls[] = home_url(sprintf('/page/%s/', $page));
			}

			// Attachments
			if ($postType == 'attachment')
			{
				foreach (get_intermediate_image_sizes() as $size)
				{
					$imageSrc = wp_get_attachment_image_src($postId, $size);
					if (!empty($imageSrc) && is_array($imageSrc) && !empty($imageSrc[0]))
					{
						$this->purgeUrls[] = $imageSrc[0];
					}
				}
			}
		}

		$this->purgeUrls = apply_filters('cloudflare_purge_by_url', $this->purgeUrls, $postId);
	}

	public static function purgeCacheOnPostStatusChange($new, $old, $post)
	{
		if ($new === 'publish' || $old === 'publish')
		{
			static::$instance->purgeCacheByPostIds($post->ID);
		}
	}

	public static function purgeCacheOnCommentStatusChange($new, $old, $comment)
	{
		if(!empty($comment->comment_post_ID) && $new != $old && ($new == 'approved' || $old == 'approved'))
		{
			static::$instance->purgeCacheByPostIds($comment->comment_post_ID);
		}
	}

	public function shutdown()
	{
		if ($this->purgeUrls)
		{
			$this->purgeUrls = array_values(array_unique(array_filter($this->purgeUrls)));

			if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)
			{
				$chunks = array_chunk($this->purgeUrls, 30);

				foreach ($chunks as $chunk)
				{
					if (!$this->cloudflareRepo->purgeCache(['files' => $chunk]))
					{
						break;
					}
				}
			}
			elseif (!wp_next_scheduled('cfPurgeCache', [$this->purgeUrls]))
			{
				wp_schedule_single_event(time(), 'cfPurgeCache', [$this->purgeUrls]);
			}
		}


	}


	public static function purgeCacheOnNewComment($commentId, $status, $data)
	{
		if ($status == 1 && is_array($data) && !empty($data['comment_post_ID']))
		{
			static::$instance->purgeCacheByPostIds($data['comment_post_ID']);
		}
	}

	public function adminMenuBar($wp_admin_bar)
	{
		// need CSS
		if ($this->cloudflareRepo->option('cfPurgeCacheOnAdminBar'))
		{
			$wp_admin_bar->add_node([
				'id' => 'purge-cache',
				'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">' . esc_html__('Purge cache', 'app-for-cf') . '</span>',
				'href' => admin_url('admin.php?page=app-for-cf_cache'),
				'meta' => ['target' => '_blank']
			]);

			echo '<style>
			#wpadminbar #wp-admin-bar-purge-cache .ab-icon:before {
    			content: "\\f17c";
    			top: 3px;
    			transition: color 2s ease;
			}
			
			#wpadminbar #wp-admin-bar-purge-cache .ab-icon.active:before {
    			color: darkorange;
			}
			
			#wpadminbar #wp-admin-bar-purge-cache {
				transition: background-color 1s ease;
			}

			@media screen and (max-width: 782px) {
					#wpadminbar li#wp-admin-bar-purge-cache {
					display: block;
				}
			}
		</style>
		<script>
			window.addEventListener("DOMContentLoaded",()=>{
				document.querySelector("#wp-admin-bar-purge-cache a").addEventListener("click",async (e)=>{					
					e.preventDefault();
					const formData = new FormData();
					formData.append("_wpnonce","' . esc_attr(wp_create_nonce()) . '");	
					try {
						const response = await fetch(document.querySelector("#wp-admin-bar-purge-cache a").getAttribute("href"), {
							method: "POST",
							body: formData,
	                     });
						document.querySelector("#wp-admin-bar-purge-cache .ab-icon").classList.add("active");
						setTimeout(()=>{document.querySelector("#wp-admin-bar-purge-cache .ab-icon").classList.remove("active");},2000);
                        await response;
                    } catch (e) {
						console.error(e);
					}
				})
			});
		</script>';

		}
	}

	public function filterGetAttachedFile($file, $attachmentId)
	{
		$url = $this->filterWpGetAttachmentUrl('', $attachmentId, true);
		if ($url)
		{
			$file = $url;
		}
		return $file;
	}

	public function filterWpGetAttachmentUrl($url, $postId, $skipCacheBreaker = false, $optionEnabled = null)
	{
		if($optionEnabled === null)
		{
			$optionEnabled = $this->cloudflareRepo->option('cfImagesTransform');
		}

		if ($optionEnabled)
		{
			return home_url() . '/cdn-cgi/image/format=auto,slow-connection-quality=30,onerror=redirect/' . $url;
		}

		return $url;
	}

	public function filterWpCalculateImageSrcset($sources, $sizeArray, $imageSrc, $imageMeta, $attachmentId)
	{
		if ($sources && is_array($sources))
		{
			$optionEnabled = $this->cloudflareRepo->option('cfImagesTransform');

			if ($optionEnabled)
			{
				foreach ($sources as &$source)
				{
					if (!empty($source['url']))
					{
						$source['url'] = $this->filterWpGetAttachmentUrl($source['url'], null, false, $optionEnabled);
					}
				}
			}
		}

		return $sources;
	}

	public function filterRestPostDispatch($response, $server, $request)
	{
		$response->header('Cache-Control', 'private, no-cache, must-revalidate', true);
		return $response;
	}
}