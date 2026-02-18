<?php

namespace DigitalPoint\Cloudflare\Admin\Controller\Traits;

trait Phrase
{
	protected function abstractPhrase($key)
	{
		switch ($key)
		{
			case 'invalid_action':
				return __('Invalid action', 'app-for-cf');
				break;
			case 'action_not_found':
				/* translators: %1$s = <strong>, %2$s = </strong> */
				return __('Action not found: %1$s%3$s%2$s.', 'app-for-cf');
				break;
			case 'security_error':
				return __('Security error, go back, reload and retry.', 'app-for-cf');
		}
		return '';
	}
}