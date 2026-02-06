<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class Access extends AbstractTable
{

	protected function extra_tablenav($which) {
		if ($which == 'top')
		{
			echo '<div class="tablenav-pages">';
			echo '<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(array('action' => 'admin_policy'), esc_url(menu_page_url('app-for-cf_access', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-shield""></span>' . esc_html__('Create admin access policy', 'app-for-cf') . '</span></a>
<a class="button-secondary" href="' . esc_url_raw($this->_args['dash_base']) . '/access/apps" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-external"></span>' . esc_html__('View in Cloudflare', 'app-for-cf') . '</span></a>
</div>';
		}
	}

	protected function get_table_classes()
	{
		return ['access', 'widefat', 'centerLastColumn'];
	}

	public function get_columns()
	{
		return [
			'cb'			=> \DigitalPoint\Cloudflare\Helper\Api::$version ? '<input type="checkbox" />' : '',
			'description'	=> '',
			'policies'		=> esc_html__('Policies', 'app-for-cf'),
		];
	}

	protected function get_bulk_actions()
	{
		global $status;

		$actions = [];

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			if ($status !== 'delete_ac')
			{
				$actions['delete_ac-selected'] = esc_html__('Delete', 'app-for-cf');
			}
		}

		return $actions;
	}

	public function single_row($item)
	{
		echo '<tr class="active">';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	protected function column_description($item)
	{
		echo '<span class="row-title"><strong>' . esc_html(@$item['name']) . '</strong></span>';
		echo '<div class="explain">' . esc_html($item['domain']) . '</div>';

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();

			echo '<span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'delete_ac'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}

	protected function column_policies($item)
	{
		echo '<div class="pairs">';

		foreach ($item['policies'] as $policy)
		{
			echo '<dl class="pairs--columns pairs--rightLabel">
					<dt>' . esc_html($policy['name']) . ' (' . esc_html($this->phrase($policy['decision'])) . ')</dt>';
			if (is_array($policy['include']) && count($policy['include']))
			{
				echo wp_kses($this->getPolicyType('include', $policy['include'], $this->_args['groups']), 'post');
			}
			echo '</dl>';
		}

		echo '</div>';
	}

	protected function getPolicyType($title, $group, $groups)
	{
		echo '<dd><u>' . esc_html($this->phrase($title)) . '</u>
				<ul class="listInline listInline--bullet">';

		foreach ($group as $item)
		{
			foreach ($item as $type => $value)
			{
				if ($type === 'everyone')
				{
					echo '<li>' . esc_html($this->phrase('everyone')) . '</li>';
				}
				else
				{
					foreach ($value as $data)
					{
						if ($type === 'group')
						{
							echo esc_html($groups[$item['group']['id']]['name']);
						}
						elseif ($type === 'email_domain')
						{
							echo '<li>@' . esc_html($data) . '</li>';
						}
						else
						{
							echo '<li>' . esc_html($data) . '</li>';
						}
					}
				}
			}
		}
		echo '</ul></dd>';
	}


	protected function phrase($key)
	{
		$phrases = [
			'allow' => esc_html__('allow', 'app-for-cf'),
			'deny' => esc_html__('deny', 'app-for-cf'),
			'non_identity' => esc_html__('non-identity', 'app-for-cf'),
			'bypass' => esc_html__('bypass', 'app-for-cf'),

			'everyone' => esc_html__('Everyone', 'app-for-cf'),
			'include' => esc_html__('Include', 'app-for-cf'),
			'require' => esc_html__('Require', 'app-for-cf'),
			'exclude' => esc_html__('Exclude', 'app-for-cf'), /* @phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude */
		];

		if (!empty($phrases[$key]))
		{
			return $phrases[$key];
		}
		return $key;
	}

}