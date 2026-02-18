<?php

namespace DigitalPoint\Cloudflare\Admin\View\Traits;

trait Phrase
{
	protected function abstractPhrase($key)
	{
		switch ($key)
		{
			case 'template_not_found':
				/* translators: %1$s = <strong>, %2$s = </strong> */
				return __('Template not found: %1$s%3$s%2$s (%4$s).', 'app-for-cf');
				break;
		}
		return '';
	}
}