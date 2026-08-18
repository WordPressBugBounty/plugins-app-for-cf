<?php
namespace DigitalPoint\Cloudflare\Turnstile;
class ElementorPro extends AbstractTurnstile
{
	protected $inlineScriptAdded = false;

	protected function initHooks()
	{
		if (!empty($this->turnstileOptions['onElementorPro']))
		{
			add_action('wp_enqueue_scripts', [$this, 'actionWpEnqueueScripts']);
			add_filter('elementor/widget/render_content', [$this, 'filterElementorWidgetRenderContent'], 10, 2);
			add_action('elementor_pro/forms/validation', [$this, 'actionFormsValidation'], 10, 2);
		}
	}

	public function actionWpEnqueueScripts()
	{
		// Elementor's element cache serves widget HTML straight from cache and re-enqueues that page's scripts
		// by handle only, without ever running the render_content filter again. If the handle isn't registered
		// on that request the enqueue is a silent no-op, and a cached page ends up with a dead widget while
		// validation is still live. So register it up front (weightless until something enqueues it) and hang
		// the inline script on it here.
		wp_register_script('turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, ['strategy' => 'defer', 'in_footer' => true]); /* @phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion, PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent */
		wp_add_inline_script('turnstile', $this->getInlineScript());
		$this->inlineScriptAdded = true;
	}



	public function filterElementorWidgetRenderContent($widgetContent, $class)
	{
		if (!($class instanceof \ElementorPro\Modules\Forms\Widgets\Form))
		{
			return $widgetContent;
		}

		// The editor canvas and the preview iframe are just normal frontend requests, so this filter runs in both
		// of them as well. No point burning challenges on a form nobody can submit (and the widget gets in the way
		// while you are dragging fields around), so bail out of either.
		if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode())
		{
			return $widgetContent;
		}

		// Don't double-captcha a form that already has one on it.
		if ($this->hasCaptchaField($class->get_settings_for_display('form_fields')))
		{
			return $widgetContent;
		}

		$this->addTurnstileScript();

		if (!$this->inlineScriptAdded)
		{
			$this->inlineScriptAdded = true;
			wp_add_inline_script('turnstile', $this->getInlineScript());
		}

		// Deliberately not the implicit "cf-turnstile" class here. The implicit scan renders at page load and hands
		// back no widgetId, and Elementor submits the form over AJAX with single use tokens, so we need a widgetId
		// to call turnstile.reset() with after every attempt. The id is per widget rather than a fixed one so a
		// page with more than one form on it gets a working widget on each of them instead of just the first.
		$turnstileHtml = str_replace('class="cf-turnstile"', 'id="cf-turnstile-' . esc_attr($class->get_id()) . '" class="cf-turnstile-elementor"', $this->addTurnstileHtml(false));
		$turnstileHtml = '<div class="elementor-field-group elementor-column elementor-field-type-turnstile elementor-col-100">' . $turnstileHtml . '</div>';

		// preg_replace() reads "\" and "$" in the replacement as backreferences, so neutralise them first.
		$replacement = str_replace(['\\', '$'], ['\\\\', '\\$'], $turnstileHtml);

		// Elementor gives us no hook next to the submit button, so we have to do a little string surgery to land in
		// front of it. As a direct child of the fields wrapper it also ends up in the last step of a multi-step form.
		$injected = 0;
		$widgetContent = preg_replace('#<div class="elementor-field-group elementor-column elementor-field-type-submit#', $replacement . '$0', $widgetContent, 1, $injected);

		if (!$injected)
		{
			// Field group markup changed on us. Under the button is less pretty, but it is still inside the <form>,
			// so the token still gets posted and the form doesn't end up validated without a widget on it.
			$widgetContent = preg_replace('#</form>#', $replacement . '$0', $widgetContent, 1, $injected);
		}

		return $widgetContent;
	}

	public function actionFormsValidation($record, $ajaxHandler)
	{
		// Has to mirror the skip in filterElementorWidgetRenderContent() exactly, otherwise a form with its own
		// captcha on it never gets a widget but still gets validated, and can't be submitted at all.
		if ($this->hasCaptchaField($record->get_form_settings('form_fields')))
		{
			return;
		}

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
			// add_error_message() puts the message on the form, but on its own it does not stop the submission:
			// Form_Record::validate() only returns false when $ajax_handler->errors is non-empty, and the submit
			// actions (email included) run on its say-so. The widget is injected rather than being a real form
			// field, so there is no field id to hang the error off — an empty-id add_error() is what actually
			// blocks the send.
			$ajaxHandler->add_error_message($error->get_error_message());
			$ajaxHandler->add_error('', '');
		}
	}

	protected function hasCaptchaField($formFields)
	{
		if (empty($formFields) || !is_array($formFields))
		{
			return false;
		}

		foreach ($formFields as $formField)
		{
			if (!empty($formField['field_type']) && in_array($formField['field_type'], ['recaptcha', 'recaptcha_v3', 'hcaptcha', 'turnstile'], true))
			{
				return true;
			}
		}

		return false;
	}

	protected function getInlineScript()
	{
		return
			'window.addEventListener("elementor/frontend/init", function () {' .
				'elementorFrontend.hooks.addAction("frontend/element_ready/form.default", function ($scope) {' .
					'var el = $scope[0].querySelector(".cf-turnstile-elementor");' .
					'if (!el || el.dataset.cfRendered) { return; }' .
					'var boot = function () {' .
						// api.js is deferred, so it may well not be there yet when the element handler runs.
						'if (!window.turnstile) { setTimeout(boot, 350); return; }' .
						'el.dataset.cfRendered = "1";' .
						// The same form widget can appear twice on a page (global templates), which means duplicate ids.
						'if (document.getElementById(el.id) !== el) { el.id += "-" + (++window._cfTurnstileN || (window._cfTurnstileN = 1)); }' .
						'var widgetId = turnstile.render("#" + el.id, {sitekey: el.dataset.sitekey, size: "flexible"});' .
						'el.dataset.cfWidgetId = widgetId;' .
						// Tokens are single use and only good for 300 seconds, and Elementor never reloads the page,
						// so get a fresh one after every attempt. form-sender triggers "reset" when the submission
						// succeeded and "error" on every failure path. jQuery, because they trigger with jQuery.
						'jQuery(el).closest("form").on("reset error", function () {' .
							'try { turnstile.reset(widgetId); } catch (e) {}' .
						'});' .
					'};' .
					// Next tick, so the form-steps handler is done re-parenting field groups into steps first.
					// Moving an iframe in the DOM reloads it, which would orphan an already rendered widget.
					'setTimeout(boot, 0);' .
				'});' .
			'});' .
			// Popup documents are printed into the page up front, so the widget will have rendered inside a hidden
			// container and its token may be stale by the time somebody actually opens the popup.
			'jQuery(document).on("elementor/popup/show", function (e, popupId) {' .
				'var widgets = document.querySelectorAll("#elementor-popup-modal-" + popupId + " .cf-turnstile-elementor[data-cf-widget-id]");' .
				'for (var i = 0; i < widgets.length; i++) {' .
					'try { turnstile.reset(widgets[i].dataset.cfWidgetId); } catch (e) {}' .
				'}' .
			'});';
	}
}
