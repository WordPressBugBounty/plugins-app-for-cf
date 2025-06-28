<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Dashboard extends AbstractTemplate
{
	protected function template()
	{
	//	$this->addAsset('js');
		$this->addAsset('css');
		$this->addAsset('chart');

echo '
		<div id="cfRange" class="switch-field switch-toggle switch-candy" data-action="app-for-cf_analytics">
			<input name="range" id="switchFieldDay" type="radio" value="day" checked><label for="switchFieldDay">Day</label>
			<input name="range" id="switchFieldWeek" type="radio" value="week"><label for="switchFieldWeek">Week</label>
			<input name="range" id="switchFieldMonth" type="radio" value="month"><label for="switchFieldMonth">Month</label>
			<input name="range" id="switchFieldYear" type="radio" value="year"><label for="switchFieldYear">Year</label>
</div>';

		echo '<div class="cfStats">
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Visits', 'app-for-cf')])) .  '" data-decimals="2" data-type="num" data-data="' . esc_js(wp_json_encode(['unique'])) .  '">
					<label>' . esc_html__('Unique visitors', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsUniquesChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Total', 'app-for-cf'), __('Encrypted', 'app-for-cf')])) .  '" data-decimals="2" data-type="num" data-data="' . esc_js(wp_json_encode(['requests', 'encryptedRequests'])) .  '" data-color="' . esc_js(wp_json_encode([null, 'rgba(34,218,30,0.7)'])) .  '">
					<label>' . esc_html__('Total requests', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsRequestsChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Cached', 'app-for-cf')])) .  '" data-decimals="2" data-type="percent" data-data="' . esc_js(wp_json_encode(['percentCached'])) .  '" data-color="' . esc_js(wp_json_encode(['rgba(246,130,31,0.7)'])) .  '">
					<label>' . esc_html__('Percent cached', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsPercentCachedChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Total', 'app-for-cf'), __('Encrypted', 'app-for-cf')])) .  '" data-decimals="0" data-type="bytes" data-data="' . esc_js(wp_json_encode(['bytes', 'encryptedBytes'])) .  '" data-color="' . esc_js(wp_json_encode([null, 'rgba(34,218,30,0.7)'])) .  '">
					<label>' . esc_html__('Data served', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsBytesChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Cached', 'app-for-cf')])) .  '" data-decimals="0" data-type="bytes" data-data="' . esc_js(wp_json_encode(['cachedBytes'])) .  '">
					<label>' . esc_html__('Data cached', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsBytesCachedChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
				<div class="displayData" data-label="' . esc_js(wp_json_encode([__('Threats', 'app-for-cf')])) .  '" data-decimals="0" data-type="num" data-data="' . esc_js(wp_json_encode(['threats'])) .  '" data-color="' . esc_js(wp_json_encode(['rgba(246,28,31,0.7)'])) .  '">
					<label>' . esc_html__('Threats', 'app-for-cf') . '<div></div></label>
					<div class="chartContainer">
						<canvas id="cfAnalyticsThreatsChart" style="width:100%;height:80px"></canvas>
					</div>
				</div>
			</div>';
	}


}