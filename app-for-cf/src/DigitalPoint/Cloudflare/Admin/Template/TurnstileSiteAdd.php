<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class TurnstileSiteAdd extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap r2_enable">
				<h2>' . esc_html__('Create Turnstile site', 'app-for-cf') . '</h2>
			' . esc_html__('This will create a Turnstile site in your Cloudflare account for this domain.  The automatically created Turnstile site will be set to use Managed as the Widget Type (you can edit it if you\'d like after it\'s created).', 'app-for-cf') . '
			<div class="notice notice-warning">
                <p>
                    ' . esc_html__('The Turnstile keys will automatically be populated from your newly created Turnstile site and your site will be set to utilize Turnstile as its CAPTCHA option.', 'app-for-cf') . '
                </p>
			</div>
			
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_caching', false)) . '">
			<input type="hidden" name="action" value="turnstile_widget_add"/>
        	<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>';

		?>

		<table class="form-table">
			<tr>
				<td></td>
				<td>
					<?php submit_button(esc_html__('Create Turnstile site', 'app-for-cf')); ?>
				</td>
			</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}