<?php

namespace DigitalPoint\Cloudflare\Turnstile;

class HtmlForms extends AbstractTurnstile
{
	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onHtmlForms']))
		{
			add_filter('hf_form_markup', [$this, 'filterFormMarkup']);
			add_filter('hf_ignored_field_names', [$this, 'filterIgnoredFieldNames']);
			add_filter('hf_validate_form', [$this, 'filterValidateForm'], 10, 3);
		}
	}

	public function filterFormMarkup($result)
	{
		$this->addTurnstileScript();
		return $result . $this->addTurnstileHtml(false);
	}

	public function filterIgnoredFieldNames($ignoredFieldNames)
	{
		$ignoredFieldNames[] = 'cf-turnstile-response';
		return $ignoredFieldNames;
	}

	public function filterValidateForm($errorCode, $form, $data)
	{
		$form->messages['turnstile_no_response'] = $this->generateError('turnstile_no_response', null, true)->get_error_message();
		$form->messages['turnstile_invalid'] = $this->generateError('turnstile_invalid', null, true)->get_error_message();

		$turnstileResponse = $this->getTurnstileResponse();

		if (!$turnstileResponse)
		{
			return 'turnstile_no_response';
		}
		else
		{
			$response = $this->getCloudflareRepo()->verifyTurnstileResponse($turnstileResponse);
			if (empty($response['success']))
			{
				return 'turnstile_invalid';
			}
		}

		return $errorCode;
	}
}
