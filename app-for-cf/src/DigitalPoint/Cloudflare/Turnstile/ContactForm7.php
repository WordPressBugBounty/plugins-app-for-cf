<?php
namespace DigitalPoint\Cloudflare\Turnstile;
class ContactForm7 extends AbstractTurnstile
{
	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onContactForm7']))
		{
			add_filter('wpcf7_form_elements', [$this, 'filterFormElements']);
			add_filter('wpcf7_spam', [$this, 'filterSpam'], 9, 2);
		}
	}

	public function filterFormElements($result)
	{
		$this->addTurnstileScript();
		return $result . $this->addTurnstileHtml(false);
	}

	public function filterSpam($spam, $submission)
	{
		if ($spam)
		{
			return $spam;
		}

		$error = false;

		$turnstileResponse = $this->getTurnstileResponse();

		if (!$turnstileResponse)
		{
			$error = $this->generateError('turnstile_no_response', null, true);
		}
		else
		{
			$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);
			if (empty($response['success']))
			{
				$error = $this->generateError('turnstile_invalid', null, true);
			}
		}

		if ($error)
		{
			$submission->add_spam_log([
				'agent' => 'turnstile',
				'reason' => $error->get_error_message(),
			]);

			$spam = true;
		}

		return $spam;
	}
}
