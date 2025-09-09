<?php

namespace DigitalPoint\Cloudflare\Turnstile;

class WPForms extends AbstractTurnstile
{
	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onWPForms']))
		{
			add_action('wpforms_display_submit_before', [$this, 'actionDisplaySubmitBefore']);
			add_action('wpforms_process_before', [$this, 'actionProcessBefore'], 10, 2);
		}
	}

	public function actionDisplaySubmitBefore($formData)
	{
		$this->addTurnstileScript();
		$this->addTurnstileHtml();
	}

	public function actionProcessBefore($entry, $formData)
	{
		$turnstileResponse = $this->getTurnstileResponse();

		$error = false;

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
			wpforms()->process->errors[$formData['id']]['header'] = $error->get_error_message();
		}
	}
}
