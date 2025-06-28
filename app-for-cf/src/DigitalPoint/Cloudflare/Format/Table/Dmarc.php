<?php

namespace DigitalPoint\Cloudflare\Format\Table;

class Dmarc extends AbstractTable
{
	protected function get_table_classes()
	{
		return ['dmarc', 'widefat', 'centerLastColumn'];
	}

	public function get_columns()
	{
		return [
			'source'		=> esc_html__('Source', 'app-for-cf'),
			'volume'		=> esc_html__('Volume', 'app-for-cf'),
			'dmarc_pass'	=> esc_html__('DMARC pass', 'app-for-cf'),
			'spf_aligned'	=> esc_html__('SPF aligned', 'app-for-cf'),
			'dkim_aligned'	=> esc_html__('DKIM aligned', 'app-for-cf'),
			'ip_count'		=> esc_html__('IP count', 'app-for-cf'),
		];
	}

	public function single_row($item)
	{
		echo '<tr>';
		$this->single_row_columns($item);
		echo '</tr>';
	}

	protected function column_source($item)
	{
		echo esc_html($item['org_name']);
	}
	protected function column_volume($item)
	{
		echo number_format($item['total']);
	}
	protected function column_dmarc_pass($item)
	{
		echo number_format($item['average']['dmarc'] * 100) . '%';
	}
	protected function column_spf_aligned($item)
	{
		echo number_format($item['average']['spf'] * 100) . '%';
	}
	protected function column_dkim_aligned($item)
	{
		echo number_format($item['average']['dkim'] * 100) . '%';
	}
	protected function column_ip_count($item)
	{
		echo number_format($item['ips']);
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