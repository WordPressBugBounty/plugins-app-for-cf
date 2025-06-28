<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class IP extends AbstractTable
{
	protected function get_table_classes()
	{
		return ['user_agent', 'widefat', $this->_args['plural']];
	}

	public function get_columns()
	{
		return [
			'cb'		=> \DigitalPoint\Cloudflare\Helper\Api::$version ? '<input type="checkbox" />' : '',
			'ip'		=> '',
			'action'	=> esc_html__('Action', 'app-for-cf'),
			'created'	=> esc_html__('Created', 'app-for-cf'),
		];
	}

	protected function get_bulk_actions()
	{
		global $status;

		$actions = [];

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			if ($status != 'delete_ip')
			{
				$actions['delete_ip-selected'] = esc_html__('Delete', 'app-for-cf');
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

	protected function column_ip($item)
	{
		echo '<span class="row-title"><strong>' . esc_html(@$item['configuration']['value']) . '</strong></span><div class="explain">' . esc_html(@$item['notes']) . '</div>';

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();

			echo '<span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'delete_ip'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}

	protected function column_action($item)
	{
		echo esc_html($this->phrase($item['mode']));
	}

	protected function column_created($item)
	{
		echo esc_html(sprintf(
			/* translators: %s = Date / time (from WordPress core) */
			__('%1$s at %2$s'), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
			wp_date( __('Y/m/d'), intval($item['date_created'])), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
			wp_date( __('g:i a'), intval($item['date_created'])) /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
		));
	}

	protected function phrase($key)
	{
		$phrases = [
			'allow' => esc_html__('Allow', 'app-for-cf'),
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