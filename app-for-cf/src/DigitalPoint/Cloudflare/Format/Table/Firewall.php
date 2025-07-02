<?php

namespace DigitalPoint\Cloudflare\Format\Table;
class Firewall extends AbstractTable
{
	protected function extra_tablenav($which) {
		if ($which == 'top')
		{
			if (\DigitalPoint\Cloudflare\Helper\Api::$version)
			{
				echo '<div class="tablenav-pages">';

				// Not using for now because WordPress really doesn't segment stuff for clients and servers, unfortunately.
				// echo '<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(array('action' => 'rule_internal_directories'), esc_url(menu_page_url('app-for-cf_firewall', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-lock" style="vertical-align:middle;padding-right:4px;"></span>' . esc_html__('Block internal directories', 'app-for-cf') . '</span></a>';
				echo '<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(array('action' => 'rule_ai_scrapers'), esc_url(menu_page_url('app-for-cf_firewall', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-networking"></span>' . esc_html__('Block AI scrapers', 'app-for-cf') . '</span></a>
<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(array('action' => 'rule_country_block'), esc_url(menu_page_url('app-for-cf_firewall', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-admin-site-alt3"></span>' . esc_html__('Manage country blocking', 'app-for-cf') . '</span></a>
</div>';
			}
		}
	}

	protected function get_table_classes()
	{
		return ['firewall', 'widefat', $this->_args['plural']];
	}

	public function get_columns()
	{
		return [
			'cb'				=> \DigitalPoint\Cloudflare\Helper\Api::$version ? '<input type="checkbox" />' : '',
			'description'		=> '',
			'action'			=> esc_html__('Action', 'app-for-cf'),
			'using'				=> esc_html__('Using', 'app-for-cf'),
			'24h_solve_rate'	=> esc_html__('24h solve rate', 'app-for-cf'),
		];
	}

	protected function get_bulk_actions()
	{
		global $status;

		$actions = [];

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			if ($status != 'enable')
			{
				$actions['enable-selected'] = esc_html__('Enable', 'app-for-cf');
			}
			if ($status != 'disable')
			{
				$actions['disable-selected'] = esc_html__('Disable', 'app-for-cf');
			}
			if ($status != 'delete')
			{
				$actions['delete-selected'] = esc_html__('Delete', 'app-for-cf');
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

		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			echo '<div class="row-actions visible">';

			$url = $this->getCurrentUrl();
			if ($item['enabled'])
			{
				echo '<span class="disable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'disable'], $url))) . '" title="' . esc_html__('Disable', 'app-for-cf') . '">' . esc_html__('Disable', 'app-for-cf') . '</a></span>';
			}
			else
			{
				echo '<span class="enable"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'enable'], $url))) . '" title="' . esc_html__('Enable', 'app-for-cf') . '">' . esc_html__('Enable', 'app-for-cf') . '</a></span>';
			}

			echo ' | <span class="delete"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['id' => $item['id'], 'action' => 'delete'], $url))) . '" title="' . esc_html__('Delete', 'app-for-cf') . '">' . esc_html__('Delete', 'app-for-cf') . '</a></span>';

			echo '</div>';
		}
	}

	protected function column_action($item)
	{
		echo esc_html($this->phrase($item['action']));

	}

	protected function column_using($item)
	{
		if (is_array($item['using']))
		{
			$phrased = [];

			foreach ($item['using'] as $single)
			{
				$phrased[] = esc_html($this->phrase($single));
			}

			echo wp_kses('<ul class="using" title="' . esc_attr($item['expression']) . '"><li>' . implode('</li><li>', $phrased) . '</li></ul>', 'post');
		}
	}

	protected function column_24h_solve_rate($item)
	{
		echo '<a href="' . esc_url_raw($this->_args['dash_base']) . '/security/events?rule-id=' . esc_attr($item['id']) . '" target="_blank">' . (!empty($item['captcha_solve_rate']['issued']) ? esc_html(number_format_i18n(((empty($item['captcha_solve_rate']['solved']) ? 0 : $item['captcha_solve_rate']['solved']) / $item['captcha_solve_rate']['issued']) * 100, 2)) . '%' : '-') . '</a>';
	}

	protected function phrase($key)
	{
		$phrases = [
			'allow' => esc_html__('Allow', 'app-for-cf'),
			'block' => esc_html__('Block', 'app-for-cf'),
			'bypass' => esc_html__('Bypass', 'app-for-cf'),
			'challenge' => esc_html__('Legacy CAPTCHA', 'app-for-cf'),
			'js_challenge' => esc_html__('JavaScript challenge', 'app-for-cf'),
			'managed_challenge' => esc_html__('Managed challenge', 'app-for-cf'),
			'skip' => esc_html__('Skip', 'app-for-cf'),
			'log' => esc_html__('Log', 'app-for-cf'),

			'as_num' => esc_html__('AS Num', 'app-for-cf'),
			'cookie' => esc_html__('Cookie', 'app-for-cf'),
			'country' => esc_html__('Country', 'app-for-cf'),
			'continent' => esc_html__('Continent', 'app-for-cf'),
			'hostname' => esc_html__('Hostname', 'app-for-cf'),
			'ip_source_address' => esc_html__('IP Source Address', 'app-for-cf'),
			'referer' => esc_html__('Referer', 'app-for-cf'),
			'request_method' => esc_html__('Request Method', 'app-for-cf'),
			'ssl_https' => esc_html__('SSL/HTTPS', 'app-for-cf'),
			'uri_full' => esc_html__('URL Full', 'app-for-cf'),
			'uri' => esc_html__('URI', 'app-for-cf'),
			'uri_path' => esc_html__('URI Path', 'app-for-cf'),
			'uri_query_string' => esc_html__('URI Query String', 'app-for-cf'),
			'http_version' => esc_html__('HTTP Version', 'app-for-cf'),
			'user_agent' => esc_html__('User Agent', 'app-for-cf'),
			'x_forwarded_for' => esc_html__('X-Forwarded-For', 'app-for-cf'),
			'client_certificate_verified' => esc_html__('Client Certificate Verified', 'app-for-cf'),
			'known_bots' => esc_html__('Known Bots', 'app-for-cf'),
			'threat_score' => esc_html__('Threat Score', 'app-for-cf'),
			'verified_bot_category' => esc_html__('Verified Bot Category', 'app-for-cf'),
		];

		if (!empty($phrases[$key]))
		{
			return $phrases[$key];
		}
		return '';
	}



}