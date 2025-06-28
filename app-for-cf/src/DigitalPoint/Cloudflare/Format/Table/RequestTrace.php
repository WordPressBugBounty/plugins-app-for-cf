<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class RequestTrace extends AbstractTable
{
	protected function get_table_classes()
	{
		return ['request_trace', 'widefat'];
	}

	public function get_columns()
	{
		return [
			'step_name'	=> esc_html__('Name', 'app-for-cf'),
			'type'		=> esc_html__('Type', 'app-for-cf'),
			'matched'	=> esc_html__('Matched', 'app-for-cf'),
			'trace'		=> esc_html__('Trace', 'app-for-cf'),
		];
	}

	protected function column_step_name($item)
	{
		echo esc_html($item['step_name']);
	}

	protected function column_type($item)
	{
		echo esc_html($item['type']);
	}

	protected function column_matched($item)
	{
		if (empty($item['matched']))
		{
			echo '<span class="dashicons dashicons-dismiss"></span>';
		}
		else
		{
			echo '<span class="dashicons dashicons-yes-alt"></span>';
		}
	}

	protected function column_trace($item)
	{
		if (!empty($item['trace']))
		{
			foreach($item['trace'] as $key => $value)
			{
				echo '<h3 class="block-minorHeader">' . (!empty($value['method']) ? esc_html($value['method']) : '') . '</h3>';
				echo '<div class="block-body">';
				$this->nested($key, $value);
				echo '</div>';
			}
		}
	}

	protected function nested($key, $item)
	{
		if (is_array($item))
		{
			echo '<dl>
					<dt style="font-weight:bold;">' . esc_html($key) .'</dt>
					<dd>';
			foreach($item as $subKey => $subItem)
			{
				$this->nested($subKey, $subItem);
			}

			echo '</dd>
			</dl>';
		}
		else
		{
			echo '<dl class="pairs pairs--columns pairs--rightLabel pairs--fluidSmall">
				<dt>' . esc_html($key) . '</dt><dd>' . esc_html($item) . '</dd>
			</dl>';
		}
	}

	protected function phrase($key)
	{
		return '';
	}

}