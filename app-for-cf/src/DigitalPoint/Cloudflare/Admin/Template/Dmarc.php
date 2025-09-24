<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Dmarc extends AbstractTemplate
{
	protected function template()
	{
	//	$this->addAsset('js');
		$this->addAsset('css');
		$this->addAsset('chart');

		echo '<div class="wrap dmarc">
				<h2>' . esc_html__('DMARC Management', 'app-for-cf') .
			'</h2>' . esc_html__('Track third parties who are sending emails on your behalf.', 'app-for-cf');

		echo '<div class="notice notice-warning inline">
				    <p>
				        ' . esc_html__('DMARC stands for Domain-based Message Authentication, Reporting & Conformance , and is used to gather info from email servers about emails they receive from your domain. Cloudflare has the ability to receive DMARC reports on behalf of your domain, which allows you to find servers that may be sending spoofed emails from your domain.', 'app-for-cf') . '
				    </p>
				</div>';

		echo '<div class="tablenav top">
			<div class="tablenav-pages">
				<a class="button-secondary" href="' . esc_url($this->params['management_url']) . '" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-external"></span>' . esc_html__('View in Cloudflare', 'app-for-cf') . '</span></a>
		</div>
	</div>';

		echo '
		<table class="wp-list-table dmarc widefat centerLastColumn"><tr><td>
		<div id="cfRange" class="switch-field switch-toggle switch-candy" data-action="app-for-cf_stats-dmarc">
			<input name="range" id="switchFieldWeek" type="radio" value="week" checked><label for="switchFieldWeek">Week</label>
			<input name="range" id="switchFieldMonth" type="radio" value="month"><label for="switchFieldMonth">Month</label>
</div>';

		echo '<div id="app-for-cf_analytics" class="cfStats">
				<div class="displayData" data-label="' . esc_js(wp_json_encode([esc_html__('DMARC fail', 'app-for-cf'), esc_html__('DMARC pass', 'app-for-cf')])) . '" data-decimals="2" data-type="num" data-data="' . esc_js(wp_json_encode(["fail","pass"])) . '" data-color="' . esc_js(wp_json_encode(["rgba(238,74,70,0.7)", null])) . '"  style="width:100%;max-width:none;">
					<label>' . esc_html__('Email volume', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsDmarcChart" style="width:100%;height:300px"></canvas>
					</div>
				</div>
			</div>
		</td></tr></table>';


		$dmarcTable = new \DigitalPoint\Cloudflare\Format\Table\Dmarc([
			'rules' => $this->params['sources'],
		]);

		$dmarcTable->prepare_items();
		$dmarcTable->views();
		$dmarcTable->display();

		echo '</div>';
	}
}