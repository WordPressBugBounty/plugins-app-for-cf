<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class CachingGuestPage extends AbstractTemplate
{
	use \DigitalPoint\Cloudflare\Traits\WP;

	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap">
				<h2>' . esc_html__('Cache pages for guests at network edge', 'app-for-cf') . '</h2>
				
			<form method="post" action="' . esc_url(menu_page_url('app-for-cf_caching', false)) . '">
			<input type="hidden" name="action" value="guest_page_cache"/>
        	<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>';
		?>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('Cache time', 'app-for-cf');?></th>
				<td>
					<select name="seconds">

						<?php
						$maxCacheTime = apply_filters('nonce_life', DAY_IN_SECONDS) - 3600;

						$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();
						$options = $cloudflareRepo->option(null);

						$cacheTime = (int)@$options['cfPageCachingSeconds'];

						$default = 21600; // 6 hours

						foreach ([300, 900, 1800, 3600, 7200, 14400, 21600, 43200, 86400] as $seconds)
						{
							if ($seconds < $maxCacheTime)
							{
								echo '<option value="' . esc_attr($seconds) . '"' . ((!$cacheTime && $seconds == $default) || $seconds == $cacheTime ? ' selected' : '') . '>' . esc_html($cloudflareRepo->timeToHumanReadable($seconds)) . '</option>';
							}
						}

						?>

					</select>

				</td>
			</tr>

			<tr>
				<td></td>
				<td>
					<?php submit_button(esc_html__('Set Guest Page Caching...', 'app-for-cf')); ?>
				</td>
			</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}