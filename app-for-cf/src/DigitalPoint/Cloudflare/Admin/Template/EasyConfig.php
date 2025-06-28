<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class EasyConfig extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap r2_enable">
				<h2>' . esc_html__('Automatic config', 'app-for-cf') . '</h2>
			' . esc_html__('Automatic configuration will optimize some Cloudflare settings for WordPress.', 'app-for-cf') . '
			<div class="notice notice-warning">
                <p>
                    ' . esc_html__('If Cloudflare is working great for you, there\'s no need to do this (this does not give you any secret/hidden options or anything beyond the settings that are shown here). There is no undo for this, so it would be a good idea to make a note of what your settings currently are, in case you want to review/revert any settings.', 'app-for-cf') . '
                </p>
			</div>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_settings', false)) . '">
			<input type="hidden" name="action" value="easy"/>
        	<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>';

		?>

		<table class="form-table">
			<tr>
				<td></td>
				<td>
					<?php submit_button(esc_html__('Config some settings automatically...', 'app-for-cf')); ?>
				</td>
			</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}