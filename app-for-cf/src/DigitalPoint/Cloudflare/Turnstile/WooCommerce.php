<?php

namespace DigitalPoint\Cloudflare\Turnstile;

class WooCommerce extends AbstractTurnstile
{
	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onWooCommerceRegister']))
		{
			add_action('woocommerce_register_form', [$this, 'actionWooCommerceForm']);
			add_action('woocommerce_register_post', [$this, 'actionWooCommerceFormPost'], 10, 3);
		}

		if (!empty($this->turnstileOptions['onWooCommerceLogin']))
		{
			add_action('woocommerce_login_form', [$this, 'actionWooCommerceForm']);

			// WooCommerce uses WordPress core authenticate action, don't replicate code.
			add_action('authenticate', [WordPress::getInstance($this->turnstileOptions), 'authenticate'], 99, 1);
		}

		if (!empty($this->turnstileOptions['onWooCommercePassword']))
		{
			add_action('woocommerce_lostpassword_form', [$this, 'actionWooCommerceForm']);

			// WooCommerce uses WordPress core lostpassword_post action, don't replicate code.
			add_action('lostpassword_post', [WordPress::getInstance($this->turnstileOptions), 'lostPasswordPost'], 10, 1);
		}
	}

	public function actionWooCommerceForm()
	{
		$this->addTurnstileScript();
		$this->addTurnstileHtml();
	}

	public function actionWooCommerceFormPost($username, $email, $errors)
	{
		if (!$this->isXmlRest() && !is_checkout())
		{
			$turnstileResponse = $this->getTurnstileResponse();

			if (!$turnstileResponse)
			{
				$this->generateError('turnstile_no_response', $errors);
			}
			else
			{
				$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);
				if (empty($response['success']))
				{
					$this->generateError('turnstile_invalid', $errors);
				}
			}
		}
	}
}