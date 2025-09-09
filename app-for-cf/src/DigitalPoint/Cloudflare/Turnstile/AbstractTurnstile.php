<?php
namespace DigitalPoint\Cloudflare\Turnstile;

class AbstractTurnstile
{
	protected static $instances = [];
	protected $turnstileOptions = [];

	/**
	 * Protected constructor. Use {@link getInstance()} instead.
	 */
	protected function __construct($turnstileOptions)
	{
		$this->turnstileOptions = $turnstileOptions;
	}

	public static final function getInstance(array $turnstileOptions = [])
	{
		$class = static::class;
		if (empty(static::$instances[$class]))
		{
			static::$instances[$class] = new $class($turnstileOptions);
			static::$instances[$class]->initHooks();
		}
		return static::$instances[$class];
	}

	protected function addTurnstileScript()
	{
		wp_enqueue_script('turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, ['strategy' => 'defer', 'in_footer' => true]); /* @phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion, PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent */
	}

	protected function addTurnstileHtml($print = true)
	{
		$html = '<style>#login{min-width:350px;}</style><div class="cf-turnstile" data-sitekey="' . esc_attr($this->turnstileOptions['siteKey']) . '" data-size="flexible" data-callback="javascriptCallback"></div>';

		if ($print)
		{
			echo $html; /* @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		}
		else
		{
			return $html;
		}
	}

	protected function isXmlRest()
	{
		return
			(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) ||
			(defined('REST_REQUEST') && REST_REQUEST);
	}

	protected function getTurnstileResponse()
	{
		return !empty($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : ''; /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
	}

	protected function generateError($errorKey, $existingErrorClass = null, $createErrorClass = false, $die = false)
	{
		$errors = [
			'turnstile_no_response' => __('No Turnstile response', 'app-for-cf'),
			'turnstile_invalid' => __('Invalid Turnstile challenge', 'app-for-cf')
		];

		$error = !empty($errors[$errorKey]) ? $errors[$errorKey] : __('Unknown Turnstile error', 'app-for-cf');

		if ($existingErrorClass instanceof \WP_Error)
		{
			$existingErrorClass->add($errorKey, $error);
		}
		elseif ($createErrorClass || $die)
		{
			$error = new \WP_Error($errorKey, $error);
		}

		if ($createErrorClass)
		{
			return $error;
		}
		elseif($die)
		{
			wp_die(esc_html($error), 400, ['back_link' => true]);
		}
	}

	protected function getCloudflareRepo()
	{
		return new \DigitalPoint\Cloudflare\Repository\Cloudflare();
	}

	/**
	 * Initializes WordPress hooks
	 */
	protected function initHooks()
	{
	}

}