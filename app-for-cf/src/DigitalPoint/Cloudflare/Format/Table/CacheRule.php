<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class CacheRule extends AbstractTable
{
	protected function extra_tablenav($which) {
		if ($which === 'top')
		{
			echo '<div class="tablenav-pages">';
			echo '<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(array('action' => 'static_content'), esc_url(menu_page_url('app-for-cf_rules', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-database-view"></span>' . esc_html__('Cache static content', 'app-for-cf') . '</span></a>
<a class="button-secondary" href="' . esc_attr($this->_args['dash_base']) . '/caching/cache-rules" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-external"></span>' . esc_html__('View in Cloudflare', 'app-for-cf') . '</span></a>
</div>';
		}
	}

	protected function get_table_classes()
	{
		return ['cache_rule', 'widefat', $this->_args['plural'], 'centerLastColumn'];
	}

	public function get_columns()
	{
		return [
			'cb'			=> \DigitalPoint\Cloudflare\Helper\Api::$version ? '<input type="checkbox" />' : '',
			'description'	=> '',
			'settings'		=> esc_html__('Settings', 'app-for-cf'),
		];
	}

	protected function get_bulk_actions()
	{
		global $status;

		$actions = [];

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			if ($status !== 'enable_cr')
			{
				$actions['enable_cr-selected'] = esc_html__('Enable', 'app-for-cf');
			}
			if ($status !== 'disable_cr')
			{
				$actions['disable_cr-selected'] = esc_html__('Disable', 'app-for-cf');
			}
			if ($status !== 'delete_cr')
			{
				$actions['delete_cr-selected'] = esc_html__('Delete', 'app-for-cf');
			}
		}

		return $actions;
	}

	public function single_row($item)
	{
		echo '<tr class="' . ($item['enabled'] ? 'active' : 'paused') . '">';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	protected function column_description($item)
	{
		echo '<span class="row-title"><strong>' . esc_html(@$item['description']) . '</strong></span>';
		echo '<div class="explain">' . esc_html($item['expression']) . '</div>';

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();

			if (!$item['enabled'])
			{
				echo '<span class="enable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'rid' => $item['ruleset_id'], 'action' => 'enable_cr'], $url))) . '" title="' . esc_html__('Enable', 'app-for-cf') . '">' . esc_html__('Enable', 'app-for-cf') . '</a></span>';
			}
			else
			{
				echo '<span class="disable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'rid' => $item['ruleset_id'], 'action' => 'disable_cr'], $url))) . '" title="' . esc_html__('Disable', 'app-for-cf') . '">' . esc_html__('Disable', 'app-for-cf') . '</a></span>';
			}

			echo ' | <span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'rid' => $item['ruleset_id'], 'action' => 'delete_cr'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}


	protected function column_settings($item)
	{
		echo '<div class="pairs">';

		foreach ($item['action_parameters_output'] as $action)
		{
			echo '<dl class="pairs--columns pairs--rightLabel">
				<dt>' . esc_html($action['id_phrase']) . '</dt>';
			if (is_array($action['value']))
			{
				echo '<dd><ul class="listInline listInline--bullet">';
				foreach ($action['value'] as $item)
				{
					echo '<li>' . esc_html($item) . '</li>';
				}

				echo '</ul></dd>';
			}
			else
			{
				echo '<dd>' . esc_html($action['value']) . '</dd>';
			}
			echo '</dl>';
		}
		echo '</div>';
	}

	protected function phrase($key)
	{

		$phrases = [
		];

		if (!empty($phrases[$key]))
		{
			return $phrases[$key];
		}
		return $key;
	}

}