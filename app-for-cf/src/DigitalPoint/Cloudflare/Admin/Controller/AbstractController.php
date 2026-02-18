<?php

namespace DigitalPoint\Cloudflare\Admin\Controller;

class AbstractController
{
	use Traits\Phrase;

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
				if (strtolower(__FUNCTION__) === strtolower($method))
				{
					die(esc_html($this->phrase('invalid_action')));
				}

				return $this->$method();
			}
			else
			{
				die ('<div id="message" class="error notice"><p>' . sprintf(esc_html($this->phrase('action_not_found')), '<strong>', '</strong>', esc_html($method)) . '</p></div></body></html>');
			}
		}
	}

	protected function view($name, array $args = [])
	{
		return call_user_func('\\DigitalPoint\\' . preg_replace('#^.*?\\\\(.*?)\\\\.*$#', '$1', __NAMESPACE__) . '\\Base\\Admin::getInstance')->view($name, $args);
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
									if ($value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] === 'on')
									{
										$value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] = true;
									}
									elseif($value[sanitize_key(wp_unslash($key))][sanitize_key(wp_unslash($itemKey))] === 'off')
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

	protected function assertNonce($action = -1)
	{
		if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), $action))
		{
			die ('<div id="message" class="error notice"><p>' . esc_html($this->phrase('security_error')) . '</p></div></body></html>');
		}
	}
}