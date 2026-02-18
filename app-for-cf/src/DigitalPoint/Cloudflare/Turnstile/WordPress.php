<?php
namespace DigitalPoint\Cloudflare\Turnstile;
class WordPress extends AbstractTurnstile
{
	protected $verifyRan = false;

	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onRegister']) || !empty($this->turnstileOptions['onLogin']) || !empty($this->turnstileOptions['onPassword']))
		{
			add_action('login_enqueue_scripts', [$this, 'loginEnqueueScripts']);
		}

		if (!empty($this->turnstileOptions['onRegister']))
		{
			add_action('register_form', [$this, 'registerForm']);
			add_action('registration_errors', [$this, 'registrationErrors'], null, 3);

			// Multisite
			add_action('signup_extra_fields', [$this, 'signupExtraFields']);
			add_filter('wpmu_validate_user_signup', [$this, 'wpmuValidateUserSignup']);
		}

		if (!empty($this->turnstileOptions['onLogin']))
		{
			add_action('login_form', [$this, 'loginForm']);
			add_filter('authenticate', [$this, 'authenticate'], 99, 1);
		}

		if (!empty($this->turnstileOptions['onPassword']))
		{
			add_action('lostpassword_form', [$this, 'lostPasswordForm']);
			add_action('lostpassword_post',[$this, 'lostPasswordPost']);
		}

		if (!empty($this->turnstileOptions['onComment']))
		{
			add_filter('comment_form_submit_button', [$this, 'commentFormSubmitButton']);
			add_action('pre_comment_on_post', [$this, 'preCommentOnPost']);
		}
	}

	public function registerForm()
	{
		$this->addTurnstileHtml();
	}

	public function registrationErrors($errors, $sanitized_user_login, $user_email)
	{
		if ($this->verifyRan)
		{
			return $errors;
		}

		$this->verifyRan = true;

		if ($this->isXmlRest() || empty($sanitized_user_login))
		{
			return $errors;
		}

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

		return $errors;
	}

	public function signupExtraFields()
	{
		$this->loginEnqueueScripts();
		$this->addTurnstileHtml();
	}

	public function wpmuValidateUserSignup($result)
	{
		// Bypass if they are already logged in (creating a user in the admin area)
		if (current_user_can('manage_options'))
		{
			return $result;
		}

		$turnstileResponse = $this->getTurnstileResponse();

		if (!$turnstileResponse)
		{
			$this->generateError('turnstile_no_response', $result['errors']);
		}
		else
		{
			$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);

			if (empty($response['success']))
			{
				$this->generateError('turnstile_invalid', $result['errors']);
			}
		}

		return $result;
	}

	public function loginEnqueueScripts()
	{
		$this->addTurnstileScript();
	}

	public function loginForm()
	{
		$this->addTurnstileHtml();
	}

	public function authenticate($user)
	{
		if ($this->verifyRan)
		{
			return $user;
		}

		$this->verifyRan = true;

		if (
			$this->isXmlRest() ||
			(empty($user) || empty($user->ID)) ||
			(
				empty($_POST['log']) && /* WordPress core */ /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
				empty($_POST['username']) /* WooCommerce */ /* @phpcs:ignore WordPress.Security.NonceVerification.Missing */
			)
		)
		{
			return $user;
		}

		$turnstileResponse = $this->getTurnstileResponse();

		if (!$turnstileResponse)
		{
			$user = $this->generateError('turnstile_no_response', null, true);
		}
		else
		{
			$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);
			if (empty($response['success']))
			{
				$user = $this->generateError('turnstile_invalid', null, true);
			}
		}

		return $user;
	}

	public function lostPasswordForm()
	{
		$this->addTurnstileHtml();
	}

	public function lostPasswordPost($errors)
	{
		// Not ideal to use a fake username/password, but lets us leverage the existing method without duplicating code
		$this->registrationErrors($errors, 'nullUsername', 'nullEmail');
	}

	public function commentFormSubmitButton($submit_button)
	{
		$this->loginEnqueueScripts();
		$this->addTurnstileHtml();
		return $submit_button;
	}

	public function preCommentOnPost($commentdata)
	{
		if ($this->isXmlRest())
		{
			return $commentdata;
		}

		$turnstileResponse = $this->getTurnstileResponse();
		if (!$turnstileResponse)
		{
			$this->generateError('turnstile_no_response', null, true, true);
		}
		else
		{
			$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);
			if (empty($response['success']))
			{
				$this->generateError('turnstile_invalid', null, true, true);
			}
		}

		return $commentdata;
	}
}
