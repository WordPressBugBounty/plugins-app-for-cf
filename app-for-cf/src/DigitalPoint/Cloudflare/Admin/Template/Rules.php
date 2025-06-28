<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Rules extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap firewall">
				<h2>' . esc_html__('Page Rules', 'app-for-cf') .
			(\DigitalPoint\Cloudflare\Helper\Api::$version ? ' <a href="' . esc_url_raw($this->params['dash_base']) . '/rules" target="_blank" class="add-new-h2">' . esc_html__('Create page rule', 'app-for-cf') . '</a>' : '') .
			'</h2>
			
			<form method="post" action="' . esc_url_raw(menu_page_url('app-for-cf_rules', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_rules"/>';

		$this->bulkActionNotice('_pr');

		$firewallTable = new \DigitalPoint\Cloudflare\Format\Table\PageRule([
			'plural' => 'page_rules',
			'rules' => $this->params['page_rules'],
			'dash_base' => $this->params['dash_base']
		]);

		$firewallTable->prepare_items();
		$firewallTable->views();
		$firewallTable->display();

		echo '</form></div>';

		echo '<div class="wrap user_agent" style="margin-top:50px;">
				<h2>' . esc_html__('Cache Rules', 'app-for-cf') .
			(\DigitalPoint\Cloudflare\Helper\Api::$version ? ' <a href="' . esc_url_raw($this->params['dash_base']) . '/caching/cache-rules/new" target="_blank" class="add-new-h2">' . esc_html__('Create cache rule', 'app-for-cf') . '</a>' : '') .
			'</h2>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_rules', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_rules"/>';

		$this->bulkActionNotice('_cr');

		$firewallTable = new \DigitalPoint\Cloudflare\Format\Table\CacheRule([
			'plural' => 'cache_rules',
			'rules' => $this->params['cache_rules'],
			'dash_base' => $this->params['dash_base']
		]);

		$firewallTable->prepare_items();
		$firewallTable->views();
		$firewallTable->display();

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