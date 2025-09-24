<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Analytics extends AbstractTemplate
{
	use \DigitalPoint\Cloudflare\Traits\WP;

	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap analytics">';

		if (!empty($this->params['accountId']) && !empty($this->params['site']['site_tag']))
		{
			echo '<div class="alignright">
					<table class="form-table">
						<tr>
							<td>
								<a class="button-primary" href="https://dash.cloudflare.com/' . esc_attr($this->params['accountId']) . '/web-analytics/overview?siteTag~in=' . esc_attr($this->params['site']['site_tag']) . '&excludeBots=Yes&time-window=43200" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-chart-line"></span>' . esc_html__('View analytics', 'app-for-cf') . '</span></a>
							</td>
						</tr>
					</table>
				</div>';
		}

		echo '<h2>' . esc_html__('Cloudflare web analytics', 'app-for-cf') . '</h2>';

		esc_html_e('Cloudflare web analytics is a privacy-first system that allows you to measure performance of web pages as experienced by your visitors. Analytics is collected by adding a lightweight bit of JavaScript to your web pages.', 'app-for-cf');

		$isEnabled = !empty($this->params['status']) && ($this->params['status'] === 'enabled' || $this->params['status'] === 'enabled_no_eu');

		echo '<form method="post" action="' . esc_url(menu_page_url('app-for-cf_analytics', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_analytics"/>
			
			<div class="notice ' . ($isEnabled ? 'notice-success' : 'notice-info') . ' inline flexContainer" style="line-height:1.4em;">
			<div><h3>' . esc_html__('Cloudflare web analytics', 'app-for-cf') . '</h3><div class="explain" style="padding-left:0">' . esc_html__('Automatically add analytics JavaScript to web pages.', 'app-for-cf') . '</div></div>';

		if ($isEnabled)
		{
			echo '<div style="white-space:nowrap;">';

			echo $this->params['status'] === 'enabled' ? esc_html__('Global', 'app-for-cf') : esc_html__('Global (excluding Europe)', 'app-for-cf');

			echo '</div>';
		}
			echo '<div><div>' . '<a href="' . esc_attr(wp_nonce_url(add_query_arg([/*'action' => 'analytics',*/ 'sub_action' => $isEnabled ? 'disable' : 'enable'], esc_url(menu_page_url('app-for-cf_analytics', false))))) . '" data-click="overlay" data-href="' . esc_attr(wp_nonce_url(add_query_arg([/*'action' => 'analytics',*/ 'sub_action' => $isEnabled ? 'disable' : 'enable'], esc_url(menu_page_url('app-for-cf_analytics', false))))) . '" style="display:none;"></a>&nbsp;<input type="checkbox" name="enabled" class="dp-ui-toggle" style="transform:scale(1.5);"    ' . ($isEnabled ? ' data-href="' . esc_attr(wp_nonce_url(add_query_arg([/*'action' => 'analytics',*/ 'sub_action' => 'disable'], esc_url(menu_page_url('app-for-cf_analytics', false))))) . '" ' : '') . ($isEnabled ? 'checked' : '') . '></div></div>
	</div>
	</form></div>';

		echo '<script>
document.querySelectorAll(".dp-ui-toggle").forEach(function(toggle) {
  toggle.addEventListener("click", function(event) {
    const href = toggle.dataset.href;
    if (href) {
      window.location.href = href;
      return;
    }
    const notice = toggle.closest(".notice");
    if (notice) {
      const overlay = notice.querySelector(\'[data-click="overlay"]\');
      if (overlay) {
        overlay.click();
      }
    }
    event.preventDefault();
  });
});
</script>';

	}

}