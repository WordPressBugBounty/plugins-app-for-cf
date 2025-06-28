<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class PageRule extends AbstractTable
{
	protected function extra_tablenav($which) {
		if ($which == 'top')
		{
			echo '<div class="tablenav-pages">';
			echo '<a class="button-secondary" href="' . esc_url_raw($this->_args['dash_base']) . '/rules" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-external"></span>' . esc_html__('View in Cloudflare', 'app-for-cf') . '</span></a>
</div>';
		}
	}

	protected function get_table_classes()
	{
		return ['page_rule', 'widefat', $this->_args['plural'], 'centerLastColumn'];
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
			if ($status != 'enable_pr')
			{
				$actions['enable_pr-selected'] = esc_html__('Enable', 'app-for-cf');
			}
			if ($status != 'disable_pr')
			{
				$actions['disable_pr-selected'] = esc_html__('Disable', 'app-for-cf');
			}
			if ($status != 'delete_pr')
			{
				$actions['delete_pr-selected'] = esc_html__('Delete', 'app-for-cf');
			}
		}

		return $actions;
	}

	public function single_row($item)
	{
		echo '<tr class="' . ($item['status'] == 'active' ? 'active' : 'paused') . '">';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	protected function column_description($item)
	{
		echo '<span class="row-title"><strong>' . esc_html(@$item['targets'][0]['constraint']['value']) . '</strong></span>';

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();

			if ($item['status'] == 'disabled')
			{
				echo '<span class="enable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'enable_pr'], $url))) . '" title="' . esc_html__('Enable', 'app-for-cf') . '">' . esc_html__('Enable', 'app-for-cf') . '</a></span>';
			}
			else
			{
				echo '<span class="disable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'disable_pr'], $url))) . '" title="' . esc_html__('Disable', 'app-for-cf') . '">' . esc_html__('Disable', 'app-for-cf') . '</a></span>';
			}

			echo ' | <span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'delete_pr'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}

	protected function column_settings($item)
	{
		echo '<div class="pairs">';

		foreach ($item['actions'] as $action)
		{
			echo '<dl class="pairs--columns pairs--rightLabel">
				<dt>' . esc_html($action['id_phrase']) . '</dt>';
			if (is_array($action['value_phrase']))
			{
				echo '<dd><ul class="listInline listInline--bullet">';
				foreach ($action['value_phrase'] as $item)
				{
					echo '<li>' . esc_html($item['key']) . '</li>';
				}

				echo '</ul></dd>';
			}
			else
			{
				echo '<dd>' . esc_html($action['value_phrase']) . '</dd>';
			}
			echo '</dl>';
		}

		echo '</div>';
	}



	protected function phrase($key, $value = null)
	{
		$phrases = [];

		if (!empty($phrases[$key]))
		{
			if (substr_count($key, '.'))
			{
				return $phrases[$key];
			}

			return sprintf($phrases[$key], $value);
		}
		return $key;
	}

}