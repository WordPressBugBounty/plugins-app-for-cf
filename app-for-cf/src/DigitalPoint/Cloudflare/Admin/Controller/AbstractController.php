<?php

namespace DigitalPoint\Cloudflare\Admin\Controller;

class AbstractController
{
	public function __construct()
	{
		if (!empty($_REQUEST['action2']) && strlen($_REQUEST['action2']) > 2) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		{
			$_REQUEST['action'] = sanitize_text_field(wp_unslash($_REQUEST['action2'])); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		}

		// WTF?!?! why is WordPress slashing superglobals?  Magic quotes haven't been a thing for a long time.  WordPress is so dumb sometimes.
		// Handling it in the filter() method so it doesn't get flagged for review because we are "processing the whole $_REQUEST stack".
		// $_REQUEST = stripslashes_deep($_REQUEST);
	}

	protected function error($message)
	{
		die('<div class="error notice"><p>' . wp_kses($message, 'post') . '</p></div></body></html>');
	}

	protected function handleAction()
	{
		$action = $this->filter('action', 'str');

		if (!empty($action))
		{
			$method = 'action' . str_replace(['-', '_'], '', ucwords($action, '-_'));

			if (method_exists($this, $method))
			{
				if (strtolower(__FUNCTION__) == strtolower($method))
				{
					die(esc_html__('Invalid action', 'app-for-cf'));
				}

				return call_user_func([$this, $method]);
			}
			else
			{
				/* translators: %1$s = <strong>, %2$s = </strong> */
				die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Action not found: %1$s%3$s%2$s.', 'app-for-cf'), '<strong>', '</strong>', esc_html($method)) . '</p></div></body></html>');
			}
		}
	}

	protected function view($name, array $args = [])
	{
		return \DigitalPoint\Cloudflare\Base\Admin::getInstance()->view($name, $args);
	}

	protected function isPost()
	{
		return !empty($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post'; /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
	}

	protected function filter($key, $type)
	{
		switch ($type)
		{
			case 'str':
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					return sanitize_text_field(wp_unslash($_REQUEST[$key])); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				}
				return '';

			case 'key':
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					return sanitize_key(wp_unslash($_REQUEST[$key])); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				}
				return '';

			case 'uint':
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					$value = (int)wp_unslash($_REQUEST[$key]); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
					if ($value < 0)
					{
						$value = 0;
					}
				}
				else
				{
					$value = 0;
				}
				return $value;

			case 'bool':
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					return (bool)$_REQUEST[$key]; /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				}
				return false;

			case 'array-key':
				$value = [];
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					if (is_array($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
					{
						foreach ($_REQUEST[$key] as $key => $item) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
						{
							$value[sanitize_key(wp_unslash($key))] = sanitize_key(wp_unslash($item));
						}
					}
				}
				return $value;

			case 'array-str':
				$value = [];
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					if (is_array($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
					{
						foreach ($_REQUEST[$key] as $key => $item) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
						{
							$value[sanitize_key(wp_unslash($key))] = sanitize_text_field(wp_unslash($item));
						}
					}
				}
				return $value;

			case 'array-array-str':
				$value = [];
				if (!empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
				{
					if (is_array($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
					{
						foreach ($_REQUEST[$key] as $key => $item) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
						{
							if (is_array($item))
							{
								foreach ($item as $itemKey => $itemItem)
								{
									$value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] = sanitize_text_field(wp_unslash($itemItem));
									if ($value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] == 'on')
									{
										$value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] = true;
									}
									elseif($value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] == 'off')
									{
										$value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] = false;
									}
								}
							}
						}
					}
				}
				return $value;
			}

		return null;
	}

	protected function assertHasOwnDomain()
	{
		if (!\DigitalPoint\Cloudflare\Helper\WordPress::hasOwnDomain())
		{
			die ('<div id="message" class="error notice"><p>' . esc_html__('Feature requires your your site to have its own domain.', 'app-for-cf') . '</p></div></body></html>');
		}
	}

	protected function assertHasOwnApiToken()
	{
		if (!\DigitalPoint\Cloudflare\Helper\WordPress::hasOwnApiToken())
		{
			/* translators: %1$s = <a href...>, %2$s = </a> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Feature requires your your site to have its own %1$sCloudflare API token%2$s.', 'app-for-cf'), sprintf('<a href="%1$s">', esc_url(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php')))), '</a>') . '</p></div></body></html>');
		}
	}

	protected function assertNonce($action = -1)
	{
		if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), $action))
		{
			die ('<div id="message" class="error notice"><p>' . esc_html__('Security error, go back, reload and retry.', 'app-for-cf') . '</p></div></body></html>');
		}
	}

	protected function assertHasKey($key)
	{
		if (empty($_REQUEST[$key])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Missing: %1$s%3$s%2$s', 'app-for-cf'), '<strong>', '</strong>', esc_html($key)) . '</p></div></body></html>');
		}
	}

	protected function assertHasChecked()
	{
		if(empty($_REQUEST['checked'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			die ('<div id="message" class="error notice"><p>' . esc_html__('No items selected.', 'app-for-cf') . '</p></div></body></html>');
		}
	}

	protected function assertCanZip()
	{
		if (!class_exists('ZipArchive'))
		{
			/* translators: %1$s = <code>, %2$s = </code> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Cloudflare backup function is only supported if you have %1$sZipArchive%2$s support. You may need to ask your host to enable this.', 'app-for-cf'), '<code>', '</code>') . '</p></div></body></html>');
		}
	}


	protected function getCloudflareRepo()
	{
		return new \DigitalPoint\Cloudflare\Repository\Cloudflare();
	}
}