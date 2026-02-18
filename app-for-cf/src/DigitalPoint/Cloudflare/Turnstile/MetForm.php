<?php
namespace DigitalPoint\Cloudflare\Turnstile;
class MetForm extends AbstractTurnstile
{
	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onMetForm']))
		{
			add_filter('elementor/widget/render_content', [$this, 'filterElementorWidgetRenderContent'], 10, 2);;
			add_filter('mf_after_validation_check', [$this, 'filterAfterValidationCheck']);;
		}
	}

	public function filterElementorWidgetRenderContent($widgetContent, $class)
	{
		if ($class instanceof \Elementor\MetForm_Input_Button)
		{
			$this->addTurnstileScript();
			// Ugly, but MetForms renders HTML after the fact with JavaScript insertion...
			wp_add_inline_script('turnstile', 'window.addEventListener("elementor/frontend/init",()=>{turnstile.render("#cf-turnstile")});');
			$widgetContent .= '<br />' . str_replace('class=', 'id="cf-turnstile" class=', $this->addTurnstileHtml(false));
		}
		return $widgetContent;
	}

	public function filterAfterValidationCheck($filterValidate)
	{
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
			$filterValidate['is_valid'] = false;
			$filterValidate['message'] = $error->get_error_message();
		}

		return $filterValidate;
	}
}
