<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Access extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo wp_kses('<div class="wrap access">
				<h2>' . esc_html__('Cloudflare Zero Trust Access policies', 'app-for-cf') . '</h2>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_access', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_access"/>',
			[
				'div' => [
					'class' => []
				],
				'h2' => [

				],
				'form' => [
					'method' => [],
					'action' => [],
				],
				'input' => [
					'type' => [],
					'name' => [],
					'value' => []
				]
			]
		);

		$this->bulkActionNotice('_ac');

		$firewallTable = new \DigitalPoint\Cloudflare\Format\Table\Access([
			'plural' => 'access',
			'rules' => $this->params['apps'],
			'groups' => $this->params['groups'],
			'dash_base' => $this->params['dash_base']
		]);

		$firewallTable->prepare_items();
		$firewallTable->views();
		$firewallTable->display();

		echo wp_kses('</form></div>',
			[
				'form' => [],
				'div' => []
			]
		);
	}

	protected function bulkActionNotice($postfix = '')
	{
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' . $postfix) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo wp_kses('<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Access app %1$sdeleted%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>',
				[
					'div' => [
						'class' => []
					],
					'p' => [],
					'strong' => []
				]
			);
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' . $postfix . '-selected') /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			echo wp_kses('<div id="message" class="updated notice inline is-dismissible"><p>' . sprintf(esc_html__('Selected Access apps %1$sdeleted%2$s.', 'app-for-cf'), '<strong>', '</strong>') . '</p></div>',
				[
					'div' => [
						'class' => []
					],
					'p' => [],
					'strong' => []
				]
			);
		}
	}

}