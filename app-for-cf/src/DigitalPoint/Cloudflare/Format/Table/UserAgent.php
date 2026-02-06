<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class UserAgent extends AbstractTable
{
	protected function get_table_classes()
	{
		return ['user_agent', 'widefat', $this->_args['plural']];
	}

	public function get_columns()
	{
		return [
			'cb'			=> \DigitalPoint\Cloudflare\Helper\Api::$version ? '<input type="checkbox" />' : '',
			'description'	=> '',
			'action'		=> esc_html__('Action', 'app-for-cf'),
			'user_agent'	=> esc_html__('User agent', 'app-for-cf'),
		];
	}

	protected function get_bulk_actions()
	{
		global $status;

		$actions = [];

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			if ($status !== 'delete_ua')
			{
				$actions['enable_ua-selected'] = esc_html__('Enable', 'app-for-cf');
			}
			if ($status !== 'disable_ua')
			{
				$actions['disable_ua-selected'] = esc_html__('Disable', 'app-for-cf');
			}
			if ($status !== 'delete_ua')
			{
				$actions['delete_ua-selected'] = esc_html__('Delete', 'app-for-cf');
			}
		}
		return $actions;
	}

	public function single_row($item)
	{
		echo '<tr class="' . ($item['paused'] ? 'paused' : 'active') . '">';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	protected function column_description($item)
	{
		echo '<span class="row-title"><strong>' . esc_html(@$item['description']) . '</strong></span>';

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();
			if ($item['paused'])
			{
				echo '<span class="enable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'enable_ua'], $url))) . '" title="' . esc_html__('Enable', 'app-for-cf') . '">' . esc_html__('Enable', 'app-for-cf') . '</a></span>';
			}
			else
			{
				echo '<span class="disable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'disable_ua'], $url))) . '" title="' . esc_html__('Disable', 'app-for-cf') . '">' . esc_html__('Disable', 'app-for-cf') . '</a></span>';
			}
			echo ' | <span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'delete_ua'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}

	protected function column_action($item)
	{
		echo esc_html($this->phrase($item['mode']));

	}

	protected function column_user_agent($item)
	{
		echo esc_html($item['configuration']['value']);
	}

	protected function phrase($key)
	{
		$phrases = [
			'block' => esc_html__('Block', 'app-for-cf'),
			'challenge' => esc_html__('Legacy CAPTCHA', 'app-for-cf'),
			'js_challenge' => esc_html__('JavaScript challenge', 'app-for-cf'),
			'managed_challenge' => esc_html__('Managed challenge', 'app-for-cf'),
		];

		if (!empty($phrases[$key]))
		{
			return $phrases[$key];
		}
		return '';
	}

}