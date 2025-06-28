<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Firewall extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap firewall">
				<h2>' . esc_html__('Firewall Rules', 'app-for-cf') .
			(\DigitalPoint\Cloudflare\Helper\Api::$version ? ' <a href="' . esc_url_raw($this->params['dash_base']) . '/security/waf/firewall-rules/new" target="_blank" class="add-new-h2">' . esc_html__('Create rule', 'app-for-cf') . '</a>' : '') .
			'</h2>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_firewall', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_firewall"/>';

		$this->bulkActionNotice();

		$firewallTable = new \DigitalPoint\Cloudflare\Format\Table\Firewall([
			'plural' => 'rules',
			'rules' => $this->params['rules'],
			'dash_base' => $this->params['dash_base']
		]);

		$firewallTable->prepare_items();
		$firewallTable->views();
		$firewallTable->display();

		echo '</form></div>';


		echo '<div class="wrap user_agent" style="margin-top:50px;">
				<h2>' . esc_html__('User agents', 'app-for-cf') .
			(\DigitalPoint\Cloudflare\Helper\Api::$version ? ' <a data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'user-agent-create'], esc_url(menu_page_url('app-for-cf_firewall', false))))) . '" class="add-new-h2">' . esc_html__('Create user agent rule', 'app-for-cf') . '</a>' : '') .
			'</h2>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_firewall', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_firewall"/>';

		$this->bulkActionNotice('_ua');

		$firewallTable = new \DigitalPoint\Cloudflare\Format\Table\UserAgent([
			'plural' => 'user_agents',
			'rules' => $this->params['rules_user_agent']
		]);

		$firewallTable->prepare_items();
		$firewallTable->views();
		$firewallTable->display();

		echo '</form></div>';

		echo '<div class="wrap ip" style="margin-top:50px;">
				<h2>' . esc_html__('IP addresses', 'app-for-cf') .
			(\DigitalPoint\Cloudflare\Helper\Api::$version ? ' <a data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'ip-create'], esc_url(menu_page_url('app-for-cf_firewall', false))))) . '" class="add-new-h2">' . esc_html__('Create IP address rule', 'app-for-cf') . '</a>' : '') .
			'</h2>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_firewall', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_firewall"/>';

		$this->bulkActionNotice('_ip');

		$ipTable = new \DigitalPoint\Cloudflare\Format\Table\IP([
			'plural' => 'ips',
			'rules' => $this->params['rules_ip']
		]);

		$ipTable->prepare_items();
		$ipTable->views();
		$ipTable->display();

		echo '</form></div>';
	}

	protected function bulkActionNotice($postfix = '')
	{
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'enable' . $postfix) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Rule %1$senabled%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'enable' . $postfix . '-selected') /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Selected rules %1$senabled%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'disable' . $postfix) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Rule %1$sdisabled%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'disable' . $postfix . '-selected') /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Selected rules %1$sdisabled%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' . $postfix) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Rule %1$sdeleted%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' . $postfix . '-selected') /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo '<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Selected rules %1$sdeleted%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>';
		}
	}

}