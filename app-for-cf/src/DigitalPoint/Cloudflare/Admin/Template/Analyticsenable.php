<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class AnalyticsEnable extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap analytics_enable">';
		echo '<h2>' . esc_html__('Enable Cloudflare web analytics', 'app-for-cf') . '</h2>
			' . esc_html__('Cloudflare web analytics is a privacy-first system that allows you to measure performance of web pages as experienced by your visitors. Analytics is collected by adding a lightweight bit of JavaScript to your web pages.', 'app-for-cf') . '
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_analytics', false)) . '">
			<input type="hidden" name="sub_action" value="enable"/>
        	<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>';
		?>

		<table class="form-table">
			<tr>
				<td></td>
				<td>
					<label><input type="checkbox" name="exclude_europe" value="1" /><?php esc_html_e('Exclude Europe', 'app-for-cf');?></label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<?php submit_button(esc_html__('Enable Cloudflare web analytics', 'app-for-cf')); ?>
				</td>
			</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}