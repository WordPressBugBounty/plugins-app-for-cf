<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Caching extends AbstractTemplate
{
	use \DigitalPoint\Cloudflare\Traits\WP;

	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo wp_kses('<div class="wrap caching">
				<h2>' . esc_html__('Cloudflare guest page caching', 'app-for-cf') . '</h2>',
			[
				'div' => [
					'class' => []
				],
				'h2' => [
				],
			]
		);

		echo wp_kses(
			/* translators: %1$s = <a href...>, %2$s = </a>, %3$s = Percentage, %4$d = Milliseconds */
			sprintf(__('This allows you to enable rendered pages of your site to be cached for guests (never for logged in users) in Cloudflare\'s data centers around the world for a certain amount of time. The upside is that pages are delivered to users incredibly fast (%1$s%3$s of the world is less then %4$dms from a Cloudflare data center%2$s).', 'app-for-cf'), '<a href="https://www.cloudflare.com/network/" target="_blank">', '</a>', '95%', 50),
			[
				'a' => [
					'href' => [],
					'target' => []
				]
			]
		);

		echo wp_kses('<div class="wrap flexContainer">',
			[
				'div' => [
					'class' => []
				],
			]
		);

		echo wp_kses('<div class="notice notice-warning inline" style="flex-grow:2;"><h3>' . __('Guest page caching is enabled when:', 'app-for-cf') . '</h3>
<ul class="ul-disc">
	<li>' . __('Option to cache pages for guests is enabled', 'app-for-cf') . '</li>
	<li>' . __('User is a guest (not logged in) and they have not left a comment', 'app-for-cf') . '</li>
	<li>' . __('The page is a GET request', 'app-for-cf') . '</li>
	<li>' . __('The page has a content type of "text/html"', 'app-for-cf') . '</li>
	<li>' . __('The page route is not to login or register', 'app-for-cf') . '</li>
	<li>' . __('WordPress debugging mode is not enabled for the user', 'app-for-cf') . '</li>
</ul></div>',
			[
				'div' => [
					'class' => [],
					'style' => []
				],
				'h3' => [
				],
				'ul' => [
					'class' => [],
				],
				'li' => [
				],
			]
		);


		echo wp_kses('<div class="notice inline">
			<h3>' . __('Measure improvement', 'app-for-cf') . '</h3>
			<div>
				' . __('You can quantify the improvements by testing your site\'s speed with GTmetrix (run a test with guest page caching disabled and again when it\'s enabled).', 'app-for-cf') . '
			</div>
			<div>
				<form method="post" action="https://gtmetrix.com/analyze.html" target="_blank">
					<input type="hidden" name="url" value="' . site_url() . '">
					<button type="submit" class="button button-primary" value="bar"><span class="dashicons dashicons-performance"></span> '. __('Run test', 'app-for-cf') .'</button>
				</form>
			</div>
		</div>',
			[
				'div' => [
					'class' => [],
				],
				'h3' => [
				],
				'form' => [
					'method' => [],
					'action' => [],
					'target' => [],
				],
				'input' => [
					'type' => [],
					'name' => [],
					'value' => [],
				],
				'button' => [
					'type' => [],
					'class' => [],
					'value' => [],
				],
				'span' => [
					'class' => [],
				],
			]
		);

		echo wp_kses('</div>',
			[
				'div' => []
			]
		);

		$appForCloudflareOptions = $this->option(null);

		$cacheTime = (int)@$appForCloudflareOptions['cfPageCachingSeconds'];

		$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();

		echo '<form method="post" action="' . esc_url(menu_page_url('app-for-cf_caching', false)) . '">
			<input type="hidden" name="page" value="app-for-cf_caching"/>
			
			<div class="notice ' . ($cacheTime ? 'notice-success' : 'notice-info') . ' inline flexContainer" style="line-height:1.4em;">
			<div><h3>' . esc_html__('Cache pages for guests', 'app-for-cf') . '</h3><div class="explain" style="padding-left:0">' . esc_html__('Instruct Cloudflare to cache HTML pages for guests in their data centers around the world.', 'app-for-cf') . '</div></div>
			<div style="white-space:nowrap;"><a href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'guest_page_cache'], esc_url(menu_page_url('app-for-cf_caching', false))))) . '" data-click="overlay">' . ($cacheTime ? esc_html($cloudflareRepo->timeToHumanReadable($cacheTime)) : '') . '</a></div>
			<div><div>' . '<input type="checkbox" name="enabled" class="dp-ui-toggle" style="transform:scale(1.5);"' . ($cacheTime ? ' data-href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'guest_page_cache', 'sub_action' => 'disable'], esc_url(menu_page_url('app-for-cf_caching', false))))) . '"' : '') . ($cacheTime ? 'checked' : '') . '></div></div>
	</div>';

		echo wp_kses('</form></div>',
			[
				'form' => [],
				'div' => []
			]
		);

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