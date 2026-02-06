<?php

namespace DigitalPoint\Cloudflare\Base;
use DigitalPoint\Cloudflare\Admin\View\AbstractView;

class Admin
{
	use \DigitalPoint\Cloudflare\Traits\WP;

	protected static $instance;

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

			static::$instance->initHooks();
		}

		return static::$instance;
	}

	/**
	 * Initializes WordPress hooks
	 */
	protected function initHooks()
	{
		add_action('admin_init', [$this, 'adminInit'], 20);
		add_action('admin_menu', [$this, 'adminMenu'], 10);

		add_action('wp_ajax_app-for-cf_settings', [$this, 'displayPage']);
		add_action('wp_ajax_app-for-cf_stats', [$this, 'displayPage']);
		add_action('wp_ajax_app-for-cf_stats-dmarc', [$this, 'displayPage']);
		add_action('wp_ajax_app-for-cf_notice_dismiss', [$this, 'displayPage']);

		add_filter('plugin_action_links_app-for-cf/app-for-cf.php', [$this, 'filterPluginActionLinks'], 10, 2);
		add_filter('all_plugins', [$this, 'allPlugins']);
		add_filter('install_plugin_complete_actions', [$this, 'installPluginCompleteActions'], 10, 3);
		add_filter('plugin_row_meta', [$this, 'pluginRowMeta'], 10, 2);

		add_filter('debug_information', [$this, 'debugInformation']);

		add_filter('admin_footer_text', [$this, 'adminFooterText']);
		add_filter('removable_query_args', [$this, 'removableQueryArgs']);

		$cloudflareAppOptions = $this->option(null);

		if (empty($cloudflareAppOptions['cloudflareAuth']['token']))
		{
			add_action('admin_notices', [$this, 'noticeNotConfigured']);
		}
		else
		{
			add_action('wp_dashboard_setup', [$this, 'dashboardSetup']);
		}

		if (get_transient('app_for_cf_last_error'))
		{
			add_action('admin_notices', [$this, 'noticeLastError']);
		}

		add_filter('site_status_test_result', [$this, 'filterSiteStatusTestResult']);

		add_action('network_admin_menu', [$this, 'networkAdminMenu'], 10);
		add_filter('network_admin_plugin_action_links_app-for-cf/app-for-cf.php', [$this, 'filterPluginActionLinks'], 10, 2);
	}

	public function adminInit()
	{
		register_setting('app-for-cf-group', 'app_for_cf', ['sanitize_callback' => ['DigitalPoint\Cloudflare\Helper\WordPress', 'sanitizeSettings']]); /* @phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic */
		add_filter( "option_page_capability_app-for-cf-group", [$this, 'filterOptionPageCapabilityCloudflare']);
		ob_start(); // Needed because WordPress starts outputting HTML for plugin pages, so we can't do a redirect within plugin page if we want (like after a POST request).
	}

	public function adminMenu()
	{
		$cloudflareAppOptions = $this->option(null);

		$currentUser = wp_get_current_user();
		$canViewSettings = (empty($cloudflareAppOptions['LockSettingsUserId']) || $cloudflareAppOptions['LockSettingsUserId'] == $currentUser->ID || defined('CLOUDFLARE_BYPASS_USER_LOCK'));

		if ($canViewSettings && \DigitalPoint\Cloudflare\Helper\WordPress::hasOwnDomain())
		{
			add_menu_page(esc_html__('Cloudflare', 'app-for-cf'), esc_html__('Cloudflare', 'app-for-cf') . (empty($cloudflareAppOptions['cloudflareAuth']['token']) ? ' <span class="menu-counter"><span class="count">!</span></span>' : ''), 'manage_options', 'app-for-cf_caching', null, 'dashicons-cloud', 76.19751234 );

			// Doesn't show in menu (parent_slug = '') - why?
			add_submenu_page('', esc_html__('Cloudflare Settings', 'app-for-cf'), esc_html__('Cloudflare Settings', 'app-for-cf'), 'manage_options', 'app-for-cf_settings', [$this, 'displayPage']);

			add_submenu_page('app-for-cf_caching', esc_html__('Public page caching', 'app-for-cf'), esc_html__('Public page caching', 'app-for-cf'), 'manage_options', 'app-for-cf_caching', [$this, 'displayPage'], 20);

			add_submenu_page('app-for-cf_caching', esc_html__('Firewall', 'app-for-cf'), esc_html__('Firewall', 'app-for-cf'), 'manage_options', 'app-for-cf_firewall', [$this, 'displayPage'], 30);
			add_submenu_page('app-for-cf_caching', esc_html__('Access', 'app-for-cf'), esc_html__('Access', 'app-for-cf'), 'manage_options', 'app-for-cf_access', [$this, 'displayPage'], 40);
			add_submenu_page('app-for-cf_caching', esc_html__('Rules', 'app-for-cf'), esc_html__('Rules', 'app-for-cf'), 'manage_options', 'app-for-cf_rules', [$this, 'displayPage'], 50);
			add_submenu_page('app-for-cf_caching', esc_html__('R2 (media storage)', 'app-for-cf'), esc_html__('R2 (media storage)', 'app-for-cf'), 'manage_options', 'app-for-cf_r2', [$this, 'displayPage'], 60); // Why does this not order properly with an integer?!
			add_submenu_page('app-for-cf_caching', esc_html__('Purge cache', 'app-for-cf'), esc_html__('Purge cache', 'app-for-cf'), 'manage_options', 'app-for-cf_cache', [$this, 'displayPage'], 70);
			add_submenu_page('app-for-cf_caching', esc_html__('Settings', 'app-for-cf'), esc_html__('Settings', 'app-for-cf') . (empty($cloudflareAppOptions['cloudflareAuth']['token']) ? ' <span class="menu-counter"><span class="count">' . esc_html__('Missing API token', 'app-for-cf') . '</span></span>' : ''), 'manage_options', 'options-general.php' . '?page=app-for-cf', null, 10);

			add_submenu_page('app-for-cf_caching', esc_html__('Analytics', 'app-for-cf'), esc_html__('Analytics', 'app-for-cf'), 'manage_options', 'app-for-cf_menu_heading', '', 100);
			add_submenu_page('app-for-cf_caching', esc_html__('Web analytics', 'app-for-cf'), esc_html__('Web analytics', 'app-for-cf'), 'manage_options', 'app-for-cf_analytics', [$this, 'displayPage'], 110);
			add_submenu_page('app-for-cf_caching', esc_html__('DMARC management', 'app-for-cf'), esc_html__('DMARC management', 'app-for-cf'), 'manage_options', 'app-for-cf_dmarc', [$this, 'displayPage'], 120);

			add_submenu_page('app-for-cf_caching', esc_html__('Tools', 'app-for-cf'), esc_html__('Tools', 'app-for-cf'), 'manage_options', 'app-for-cf_menu_heading', '', 200);
			add_submenu_page('app-for-cf_caching', esc_html__('HTTP request trace', 'app-for-cf'), esc_html__('HTTP request trace', 'app-for-cf'), 'manage_options', 'app-for-cf_request-trace', [$this, 'displayPage'], 210);

			if (\DigitalPoint\Cloudflare\Helper\WordPress::hasOwnApiToken())
			{
				add_submenu_page('app-for-cf_caching', esc_html__('IP address details', 'app-for-cf'), esc_html__('IP address details', 'app-for-cf'), 'manage_options', 'app-for-cf_ip-details', [$this, 'displayPage'], 220);
				add_submenu_page('app-for-cf_caching', esc_html__('Domain details', 'app-for-cf'), esc_html__('Domain details', 'app-for-cf'), 'manage_options', 'app-for-cf_domain-details', [$this, 'displayPage'], 230);
				add_submenu_page('app-for-cf_caching', esc_html__('WHOIS', 'app-for-cf'), esc_html__('WHOIS', 'app-for-cf'), 'manage_options', 'app-for-cf_whois', [$this, 'displayPage'], 240);
			}

			$hook = add_options_page( esc_html__('Cloudflare', 'app-for-cf'), esc_html__('Cloudflare', 'app-for-cf'), 'manage_options', 'app-for-cf', [$this, 'displaySettingsPage']);
			add_action( "load-$hook", [$this, 'adminHelp']);

			if (!\DigitalPoint\Cloudflare\Helper\Api::check())
			{
				add_action( 'admin_notices', [$this, 'uploadBannerPro']);
			}
			elseif(empty($cloudflareAppOptions['cfR2Bucket']['media']))
			{
				add_action( 'admin_notices', [$this, 'uploadBannerBucketSetup']);
			}

			wp_register_script('app-for-cf_submenu', '', [], false, ['in_footer' => true]); /* @phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion */
			wp_enqueue_script('app-for-cf_submenu');
			wp_add_inline_script('app-for-cf_submenu', 'document.querySelectorAll(".wp-submenu a[href=\'app-for-cf_menu_heading\']").forEach(e=>{e.removeAttribute("href");e.setAttribute("style", "pointer-events:none;font-weight:bold;padding:10px 0 5px 5px;font-size:120%;")})' );
		}
	}

	public function networkAdminMenu()
	{
		add_submenu_page('settings.php', esc_html__('Cloudflare', 'app-for-cf'), esc_html__('Cloudflare', 'app-for-cf'), 'manage_network_options', 'app-for-cf_multisite-settings', [$this, 'displayPage'], 1000);
		add_submenu_page('settings.php', esc_html__('R2 (media storage)', 'app-for-cf'), esc_html__('R2 (media storage)', 'app-for-cf'), 'manage_network_options', 'app-for-cf_multisite-r2', [$this, 'displayPage'], 1010);

		add_action( 'network_admin_edit_app-for-cf-settings', [$this, 'networkSettings']);
	}


	/**
	 * Save multisite network settings
	 */
	public function networkSettings()
	{
		check_admin_referer(); // Nonce check

		update_site_option('app_for_cf_network',
			\DigitalPoint\Cloudflare\Helper\WordPress::sanitizeSettings(!empty($_POST['app_for_cf']) ? $_POST['app_for_cf'] : [], true, true) /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		);

		// The main site should always use the network-wide API token.
		if (!empty(get_site_option('app_for_cf_network')['cloudflareAuth']['token']))
		{
			switch_to_blog(get_main_site_id());
			$option = get_option('app_for_cf');
			if (!empty($option['cloudflareAuth']['token']) || !empty($option['network_exclude']['cloudflareAuth']['token']))
			{
				unset($option['cloudflareAuth']['token']);
				unset($option['network_exclude']['cloudflareAuth']['token']);

				update_option('app_for_cf', $option);
			}
			restore_current_blog();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'app-for-cf_multisite-settings',
					'updated' => true
				),
				network_admin_url('settings.php')
			)
		);

		exit;
	}

	/**
	 * Add help to the Cloudflare App page
	 *
	 * @return false if not the Cloudflare App page
	 */
	public function adminHelp()
	{
		$current_screen = get_current_screen();

		// Screen Content
		if (current_user_can('manage_options'))
		{
			//configuration page
			$current_screen->add_help_tab(
				array(
					'id'		=> 'overview',
					'title'		=> esc_html__( 'Overview' , 'app-for-cf'),
					'content'	=>
						'<p><strong>' . esc_html__( 'App for Cloudflare®' , 'app-for-cf') . '</strong></p>' .
						'<p>' . esc_html__( 'This plugin allows you to control and manage your Cloudflare account from within WordPress. Additionally, it allows you to better utilize Cloudflare services by tightly integrating those services with WordPress.' , 'app-for-cf') . '</p>',
				)
			);

			$current_screen->add_help_tab(
				array(
					'id'		=> 'pro',
					'title'		=> esc_html__( 'Premium' , 'app-for-cf'),
					'content'	=>
						'<p><strong>' . esc_html__( 'Premium Version' , 'app-for-cf') . '</strong></p>' .
						'<p>' . esc_html__( 'There is a Premium version of this plugin that gives you some extra goodness. Zero Trust Access policies, full control over zone settings, firewall rules tuned for WordPress, the ability to store media/attachments seamlessly in the cloud, etc.' , 'app-for-cf') . '</p>'
				)
			);
		}

		// Help Sidebar
		$current_screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'For more information:' , 'app-for-cf') . '</strong></p>' .
			'<p><a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRODUCT_URL . '?utm_source=admin_settings_help&utm_medium=wordpress&utm_campaign=plugin') . '" target="_blank">' . esc_html__( 'Info' , 'app-for-cf') . '</a></p>' .
			'<p><a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_SUPPORT_URL . '?utm_source=admin_settings_help&utm_medium=wordpress&utm_campaign=plugin') . '" target="_blank">' . esc_html__( 'Support' , 'app-for-cf') . '</a></p>' .
			'<p><a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL . '?utm_source=admin_settings_help&utm_medium=wordpress&utm_campaign=plugin') . '" target="_blank">' . esc_html__( 'Premium' , 'app-for-cf') . '</a></p>'
		);
	}

	protected function getUploadBannerBase($transientKey)
	{
		\DigitalPoint\Cloudflare\Helper\WordPress::addAsset('css');
		\DigitalPoint\Cloudflare\Helper\WordPress::addAsset('notice');

		return '<div class="notice notice-info inline cf-app-pro is-dismissible" data-dismiss_key="' . esc_attr($transientKey) . '">
	<div>
		<div class="updated inline">
			<dl><dt>' . __('Egress (bandwidth)', 'app-for-cf') . '</dt><dd>' . __('free', 'app-for-cf') . '</dd></dl>
			<dl><dt>' . __('First 10GB stored', 'app-for-cf') . '</dt><dd>' . __('free', 'app-for-cf') . '</dd></dl>
			<dl><dt>' . __('Per GB stored after 10GB', 'app-for-cf') . '</dt><dd>' . __('$0.015', 'app-for-cf') . '</dd></dl>
		</div>
		<<INSERT_BUTTON>>

		<h3>' . __('Store WordPress media in the cloud with Cloudflare R2', 'app-for-cf') . '</h3>
		<p>' . __('App for Cloudflare® Pro unlocks the ability to free up your server resources and store WordPress media in the cloud with Cloudflare\'s R2 service. Your media files will be geographically closer and delivered faster to users (faster sites are better sites).', 'app-for-cf') . '</p>
		<p>' . __('Example: If you have 26GB of media, the cost would be $0.24 to move them to R2.', 'app-for-cf') . '</p>
		<p>' .
				/* translators: %1$s = <a href...>, %2$s = </a> */
				sprintf(__('You can find info about R2 here: %1$sAnnouncing Cloudflare R2 Storage%2$s', 'app-for-cf'), '<a href="https://blog.cloudflare.com/introducing-r2-object-storage/" style="font-weight:bold;" target="_blank">', '</a>') . '</p>
	</div>
</div>';
	}

	/**
	 * Add info to media upload page if not using Pro.
	 */
	public function uploadBannerPro()
	{
		$transientKey = 'app_for_cf_dismiss_notice_r2_';

		if (get_current_screen()->base === 'upload' && !get_transient($transientKey . wp_get_current_user()->ID))
		{
			echo wp_kses(
				str_replace('<<INSERT_BUTTON>>', '<a class="button-primary" href="' . APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL . '?utm_source=admin_media&utm_medium=wordpress&utm_campaign=plugin" _target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-money-alt"></span>' . esc_html__('App for Cloudflare® Pro', 'app-for-cf') . '</span></a>', $this->getUploadBannerBase($transientKey)),
				'post'
			);
		}
	}

	/**
	 * Add info to media upload page if R2 not setup.
	 */
	public function uploadBannerBucketSetup()
	{
		$transientKey = 'app_for_cf_dismiss_notice_r2_';

		if (get_current_screen()->base === 'upload' && !get_transient($transientKey . wp_get_current_user()->ID))
		{
			echo wp_kses(
				str_replace('<<INSERT_BUTTON>>', '<a class="button-primary" href="' . menu_page_url('app-for-cf_r2', false) . '" _target="_blank"><span aria-hidden="true">' . esc_html__('Configure R2', 'app-for-cf') . '</span></a>', $this->getUploadBannerBase($transientKey)),
				'post'
			);
		}
	}

	public function dashboardSetup()
	{
		wp_add_dashboard_widget(
			'app-for-cf_analytics',
			esc_html__('Cloudflare Analytics', 'app-for-cf'),
			[$this, 'dashboardDisplay']
		);
	}

	public function dashboardDisplay()
	{
		$this->view('dashboard');
	}

	public function noticeNotConfigured()
	{
		$currentScreen = get_current_screen();
		if (isset($currentScreen->id) && strpos($currentScreen->id, 'app-for-cf') !== false)
		{
			$this->displayError(
				sprintf('%1$s<p><a href="%2$s" class="button button-primary">%3$s</a></p>', esc_html__('Cloudflare API token missing or invalid.', 'app-for-cf'), esc_url_raw(menu_page_url('app-for-cf', false)), esc_html__('Settings', 'app-for-cf'))
			);
		}
	}

	public function noticeLastError()
	{
		$this->displayError(sprintf('<strong>%1$s</strong><br /><br />%2$s', esc_html__('Last App For Cloudflare® error:', 'app-for-cf'), get_transient('app_for_cf_last_error')));
	}

	protected function displayError($error)
	{
		echo '<div class="error"><p>' . wp_kses($error, 'post') . '</p></div>';
	}

	public function displayPage($actionOverride = '')
	{
		global $plugin_page;

		if ($actionOverride)
		{
			$method = 'action' . $actionOverride;
		}
		else
		{
			$method = 'action' . ucwords(strtolower(preg_replace('#[^a-z0-9]#i', '', substr($plugin_page ?: (!empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : ''), 11)))); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash */
		}

		$controller = $this->getController();
		if (method_exists($controller, $method))
		{
			$this->getController()->$method();
		}
		else
		{
			/* translators: %s = Class method from controller that is invalid */
			$this->displayError(sprintf(esc_html__('Invalid method: %1$s', 'app-for-cf'), $method));
		}
	}

	public function displaySettingsPage()
	{
		return $this->displayPage('Settings');
	}

	public function filterPluginActionLinks($links, $file)
	{
		if ($file == plugin_basename(APP_FOR_CLOUDFLARE_PLUGIN_DIR . '/app-for-cf.php'))
		{
			\DigitalPoint\Cloudflare\Helper\WordPress::addAsset('css_admin_plugin');

			\DigitalPoint\Cloudflare\Helper\Api::check(true);
			$cloudflareAppInternal = (array)get_transient('acf_int');

			$links = array_merge(
				['settings' => '<a href="' . esc_url_raw(is_network_admin() ? add_query_arg(['page' => 'app-for-cf_multisite-settings'], network_admin_url('settings.php')) : menu_page_url('app-for-cf', false)) . '">' . esc_html__('Settings').'</a>'], /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				$links
			);

			$installed = !empty(\DigitalPoint\Cloudflare\Helper\Api::$version);

			krsort($links);
			end($links);
			$key = key($links);
			$links[$key] .= '<p class="' . ($installed && !empty($cloudflareAppInternal['v']) && $cloudflareAppInternal['v'] && !empty($cloudflareAppInternal['l']) && $cloudflareAppInternal['l'] == \DigitalPoint\Cloudflare\Helper\Api::$version ? 'green' : 'orange') . '"> ' .
				($installed ?
					(!empty($cloudflareAppInternal['v']) && $cloudflareAppInternal['v'] ?
						(!empty($cloudflareAppInternal['l']) && $cloudflareAppInternal['l'] != \DigitalPoint\Cloudflare\Helper\Api::$version ?
							sprintf('<a href="%1$s" target="_blank">%2$s</a><br />%3$s %4$s<br />%5$s %6$s',
								esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL . '?utm_source=admin_plugins' . (is_network_admin() ? '_network': '') . '&utm_medium=wordpress&utm_campaign=plugin'),
								esc_html__('Pro version not up to date.', 'app-for-cf'),
								esc_html__('Installed:', 'app-for-cf'),
								\DigitalPoint\Cloudflare\Helper\Api::$version,
								esc_html__('Latest:', 'app-for-cf'),
								$cloudflareAppInternal['l']
							) :
							sprintf('<a href="%1$s" target="_blank">%2$s</a> (%3$s)',
								esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL . '?utm_source=admin_plugins' . (is_network_admin() ? '_network': '') . '&utm_medium=wordpress&utm_campaign=plugin'),
								esc_html__('Pro version installed', 'app-for-cf'),
								empty($cloudflareAppInternal['l']) ? __('Unknown', 'app-for-cf') : $cloudflareAppInternal['l']
							)
						) :
						(
							is_multisite() && is_plugin_active_for_network('app-for-cf/app-for-cf.php') ?
							/* translators: %1$s = <a href...>, %2$s = </a> */
							sprintf(esc_html__('Pro version installed, but not active. If you have a license, you can %1$senter it here%2$s.', 'app-for-cf'),
								'<a href="' . esc_url_raw(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php'))) . '" target="_blank">',
								'</a>'
							) :
							/* translators: %1$s = <a href...>, %2$s = </a> */
							sprintf(esc_html__('Pro version installed, but not active. Did you %1$senter your license key%2$s?', 'app-for-cf'),
								'<a href="' . esc_url_raw(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php'))) . '" target="_blank">',
								'</a>'
							)
						)
					) :
					sprintf('<a href="%1$s" target="_blank">%2$s</a>',
						esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL . '?utm_source=admin_plugins' . (is_network_admin() ? '_network': '') . '&utm_medium=wordpress&utm_campaign=plugin'),
						esc_html__('Pro version not installed.', 'app-for-cf')
					)
				) .
				'</p>';
		}

		return $links;
	}

	public function allPlugins($plugins)
	{
		unset($plugins['app-for-cf-pro/app-for-cf-pro.php']);
		return $plugins;
	}

	public function installPluginCompleteActions($install_actions, $api, $plugin_file)
	{
		if ($plugin_file === 'app-for-cf-pro/app-for-cf-pro.php')
		{
			unset($install_actions['activate_plugin']);

			$install_actions = [
				'license_key' => sprintf(
					'<a class="button button-primary" href="%s" target="_parent">%s</a>',
					esc_url_raw(menu_page_url('app-for-cf', false)),
					__( 'Enter Pro license key', 'app-for-cf')
				)
			] + $install_actions;
		}
		return $install_actions;
	}

	public function pluginRowMeta($links, $file)
	{
		if ($file == plugin_basename(APP_FOR_CLOUDFLARE_PLUGIN_DIR . '/app-for-cf.php'))
		{
			$links['support'] = '<a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_SUPPORT_URL . '?utm_source=admin_plugins' . (is_network_admin() ? '_network': '') . '&utm_medium=wordpress&utm_campaign=plugin') . '" title="' . esc_attr( esc_html__( 'Visit support forum', 'app-for-cf' ) ) . '">' . esc_html__( 'Support', 'app-for-cf' ) . '</a>';
		}

		return $links;
	}

	public function debugInformation($info)
	{
		if (!empty($info['wp-plugins-inactive']['fields']['App for Cloudflare® Pro']))
		{
			$info['wp-plugins-active']['fields']['App for Cloudflare® Pro'] = $info['wp-plugins-inactive']['fields']['App for Cloudflare® Pro'];
			unset($info['wp-plugins-inactive']['fields']['App for Cloudflare® Pro']);

			ksort($info['wp-plugins-active']['fields']);
		}
		return $info;
	}

	public function adminFooterText($footerText)
	{
		$currentScreen = get_current_screen();

		if (isset($currentScreen->id) && strpos($currentScreen->id, 'app-for-cf') !== false)
		{
			$_type = array(esc_html__('colossal', 'app-for-cf'), esc_html__('elephantine', 'app-for-cf'), esc_html__('glorious', 'app-for-cf'), esc_html__('grand', 'app-for-cf'), esc_html__('huge', 'app-for-cf'), esc_html__('mighty', 'app-for-cf'), esc_html__('sexy', 'app-for-cf'));
			$_type = $_type[array_rand($_type)];

			/* translators: %1$s = App for Cloudflare®, %2$s = random adjective */
			$footerText = sprintf(esc_html__('If you like %1$s, please leave us a %2$s rating. A %3$s thank you in advance!', 'app-for-cf'),
				'<strong>' . esc_html__('App For Cloudflare®', 'app-for-cf') . '</strong>',
				'<a href="https://wordpress.org/support/plugin/app-for-cf/reviews/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				$_type
			);
		}
		return $footerText;
	}

	public function removableQueryArgs($args)
	{
		if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'page=app-for-cf') !== false) /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		{
			$args[] = 'id';
			$args[] = 'rid';
			$args[] = 'action';
			$args[] = 'sub_action';
		}
		return $args;
	}

	public function filterOptionPageCapabilityCloudflare($capability)
	{
		// merging in submitted input because some settings in the array are not available to edit directly and WordPress automatically saves the options based on the contents of $_POST
		$_POST['app_for_cf'] = array_merge((array)get_option('app_for_cf'), (isset($_POST['app_for_cf']) ? (array)$_POST['app_for_cf'] : [])); /* @phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

		// normally shouldn't happen, but in case there were no settings to start with.
		if (isset($_POST['app_for_cf'][0]) && empty($_POST['app_for_cf'][0])) /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
		{
			unset($_POST['app_for_cf'][0]); /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
		}

		return $capability;
	}

	public function filterOptionPageCapabilityCloudflareMultisite($capability)
	{
		// merging in submitted input because some settings in the array are not available to edit directly and WordPress automatically saves the options based on the contents of $_POST
		$_POST['app_for_cf'] = array_merge((array)get_site_option('app_for_cf'), (isset($_POST['app_for_cf']) ? (array)$_POST['app_for_cf'] : [])); /* @phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

		// normally shouldn't happen, but in case there were no settings to start with.
		if (isset($_POST['app_for_cf'][0]) && empty($_POST['app_for_cf'][0])) /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
		{
			unset($_POST['app_for_cf'][0]); /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
		}

		return $capability;
	}

	/*
	 * There is no way to hook into WP_Site_Health->get_test_plugin_version(), so we have to override the entire method just so we can filter the results of get_plugins() (lame!)
	 * Will WordPress ever get the ability to just extend any class/method like every other modern framework?
	 */
	public function filterSiteStatusTestResult($result)
	{
		if (!empty($result['test']) && $result['test'] == 'plugin_version')
		{
			$result = [
				'label'		=> __( 'Your plugins are all up to date' ), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				'status'	=> 'good',
				'badge'		=> [
					'label' => __( 'Security' ), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
					'color' => 'blue',
				],
				'description' => sprintf(
					'<p>%s</p>',
					__( 'Plugins extend your site&#8217;s functionality with things like contact forms, ecommerce and much more. That means they have deep access to your site, so it&#8217;s vital to keep them up to date.' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				),
				'actions'	=> sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url( 'plugins.php' ) ),
					__( 'Manage your plugins' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				),
				'test'		=> 'plugin_version',
			];




			$plugins		= Pub::getInstance()->applyFilters('all_plugins', get_plugins());
			$plugin_updates = get_plugin_updates();

			$plugins_active			= 0;
			$plugins_total			= 0;
			$plugins_need_update	= 0;

			// Loop over the available plugins and check their versions and active state.
			foreach ( $plugins as $plugin_path => $plugin ) {
				$plugins_total++;

				if ( is_plugin_active( $plugin_path ) ) {
					$plugins_active++;
				}

				if ( array_key_exists( $plugin_path, $plugin_updates ) ) {
					$plugins_need_update++;
				}
			}

			// Add a notice if there are outdated plugins.
			if ( $plugins_need_update > 0 ) {
				$result['status'] = 'critical';

				$result['label'] = __( 'You have plugins waiting to be updated' ); /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */

				$result['description'] .= sprintf(
					'<p>%s</p>',
					sprintf(
					/* translators: %d: The number of outdated plugins. */
						_n( /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
							'Your site has %d plugin waiting to be updated.',
							'Your site has %d plugins waiting to be updated.',
							$plugins_need_update
						),
						$plugins_need_update
					)
				);

				$result['actions'] .= sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( network_admin_url( 'plugins.php?plugin_status=upgrade' ) ),
					__( 'Update your plugins' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				);
			} else {
				if ( 1 === $plugins_active ) {
					$result['description'] .= sprintf(
						'<p>%s</p>',
						__( 'Your site has 1 active plugin, and it is up to date.' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
					);
				} elseif ( $plugins_active > 0 ) {
					$result['description'] .= sprintf(
						'<p>%s</p>',
						sprintf(
						/* translators: %d: The number of active plugins. */
							_n( /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
								'Your site has %d active plugin, and it is up to date.',
								'Your site has %d active plugins, and they are all up to date.',
								$plugins_active
							),
							$plugins_active
						)
					);
				} else {
					$result['description'] .= sprintf(
						'<p>%s</p>',
						__( 'Your site does not have any active plugins.' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
					);
				}
			}

			// Check if there are inactive plugins.
			if ( $plugins_total > $plugins_active && ! is_multisite() ) {
				$unused_plugins = $plugins_total - $plugins_active;

				$result['status'] = 'recommended';

				$result['label'] = __( 'You should remove inactive plugins' ); /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */

				$result['description'] .= sprintf(
					'<p>%s %s</p>',
					sprintf(
					/* translators: %d: The number of inactive plugins. */
						_n( /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
							'Your site has %d inactive plugin.',
							'Your site has %d inactive plugins.',
							$unused_plugins
						),
						$unused_plugins
					),
					__( 'Inactive plugins are tempting targets for attackers. If you are not going to use a plugin, you should consider removing it.' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				);

				$result['actions'] .= sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url( 'plugins.php?plugin_status=inactive' ) ),
					__( 'Manage inactive plugins' ) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				);
			}

		}

		return $result;
	}

	public function view($name, array $params = [])
	{
		$name = preg_replace('#[^a-z0-9\-]#i' ,'', $name);
		$className = '\DigitalPoint\Cloudflare\Admin\View\\' . str_replace('-', '', ucwords($name, '-'));

		if (!class_exists($className))
		{
			$className = AbstractView::class;
		}

		$viewClass = new $className($name, $params);
		return $viewClass->getReturn();
	}

	protected function getController()
	{
		return new \DigitalPoint\Cloudflare\Admin\Controller\Cloudflare();
	}


}