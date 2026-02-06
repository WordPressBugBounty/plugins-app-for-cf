<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class CopySettings extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap r2_enable">
				<h2>' . esc_html__('Copy Cloudflare settings', 'app-for-cf') . '</h2>
			' . esc_html__('This allows you to copy settings from a different Cloudflare zone to this zone.', 'app-for-cf') . '
			<div class="notice notice-warning">
                <p>
                    ' . esc_html__('Use with caution, there is no undo.', 'app-for-cf') . '
                </p>
			</div>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_settings', false)) . '">
			<input type="hidden" name="action" value="copy_settings"/>
        	<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>';

		?>

		<table class="form-table">
			<tr>
				<th><label for="zone"><?php esc_html__('Copy settings from', 'app-for-cf') ?></label></th>
				<td>
					<select name="zone" id="zone" class="input">

						<?php
							$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();
							$currentZone = $cloudflareRepo->option('cfZone');

							echo '<option value="">' . esc_html__('No selection', 'app-for-cf') . '</option>';
							foreach($this->params['zones'] as $zone)
							{
								echo '<option value="' . esc_attr($zone['name']) . '"' . ($zone['name'] === $currentZone ? ' disabled="disabled"' : '') . '>' . esc_html($zone['name']) . '</option>';
							}
						?>
					</select>

				</td>
			</tr>
			<tr>
				<td></td>
				<td data-init="dependent">
					<label>
						<input type="checkbox" class="primary"> <?php esc_html_e('I understand that this will irreversibly copy all settings from the selected zone to this zone (there is no undo).', 'app-for-cf') ?>
					</label>
					<div class="dependent">
						<?php submit_button(esc_html__('Copy settings', 'app-for-cf'), 'primary', 'submit', true, ['disabled' => 'disabled']); ?>
					</div>
				</td>
			</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}