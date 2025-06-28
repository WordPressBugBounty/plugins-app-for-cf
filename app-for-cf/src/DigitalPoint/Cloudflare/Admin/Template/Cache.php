<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Cache extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo wp_kses('<div class="wrap">
				<h2>' . esc_html__('Purge cache', 'app-for-cf') . '</h2>
			' . __('Clear cached files to force Cloudflare to fetch a fresh version of those files from your web server.', 'app-for-cf') . '
			<div class="notice notice-warning inline">
                <p>
                    ' . __('Note: Purging the cache may temporarily degrade performance for your website and increase load on your origin.', 'app-for-cf') . '
                </p>
			</div>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_cache', false)) . '">
        	<input type="hidden" name="_wpnonce" value="' . wp_create_nonce() .'"/>',
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

		?>

		<table class="form-table">
			<tr>
				<td></td>
				<td>
					<?php submit_button(esc_html__('Purge Cloudflare cache...', 'app-for-cf')); ?>
				</td>
			</tr>
		</table>

		<?php
		echo wp_kses('</form></div>',
			[
				'form' => [],
				'div' => []
			]
		);

		return false;
	}
}