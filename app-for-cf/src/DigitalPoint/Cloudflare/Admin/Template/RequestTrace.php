<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class RequestTrace extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap">
				<h2>' . esc_html__('Cloudflare HTTP request trace', 'app-for-cf') . '</h2>';

		if (!empty($this->params['result']) && !empty($this->params['result']['result']['trace']))
		{
			$requestTraceTable = new \DigitalPoint\Cloudflare\Format\Table\RequestTrace([
				'rules' => $this->params['result']['result']['trace'],
			]);

			$requestTraceTable->prepare_items();
			$requestTraceTable->views();
			$requestTraceTable->display();

            return false;
		}

		echo esc_html__('The request trace tool simulates a request passing through Cloudflare\'s network. This allows you see which Cloudflare products or rules are being applied to the request.', 'app-for-cf') .

			'<form method="post" action="' . esc_url(menu_page_url('app-for-cf_request-trace', false)) . '">
        <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>

		<table class="form-table">
			<tr>
				<th><label for="url">' . esc_html__('URL', 'app-for-cf') . '</label></th>
				<td>
					<input type="url" class="input" name="url" required="required" id="url" style="width:90%;" value="' . esc_url($this->params['url']) . '">
				</td>
			</tr>
			<tr>
				<th><label for="method">' . esc_html__('Method', 'app-for-cf') . '</label></th>
				<td>
					<select name="method" id="method" class="input">
						<option value="GET" selected="selected">GET</option>
						<option value="HEAD">HEAD</option>
						<option value="POST">POST</option>
						<option value="PUT">PUT</option>
						<option value="DELETE">DELETE</option>
						<option value="CONNECT">CONNECT</option>
						<option value="OPTIONS">OPTIONS</option>
						<option value="TRACE">TRACE</option>
						<option value="PATCH">PATCH</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="protocol">' . esc_html__('Protocol', 'app-for-cf') . '</label></th>
				<td>
					<select name="protocol" id="protocol" class="input">
						<option value="HTTP/1.0">HTTP/1.0</option>
						<option value="HTTP/1.1" selected="selected">HTTP/1.1</option>
						<option value="HTTP/2">HTTP/2</option>
						<option value="HTTP/3">HTTP/3</option>
					</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td data-init="dependent">
					<label><input type="checkbox" class="primary"> ' . esc_html__('Bot score', 'app-for-cf') . '</label>
					<div class="flexRow range dependent">
						<div>' . esc_html__('Bot', 'app-for-cf') . '</div>
						<div><input type="range" name="bot_score" value="50" min="1" max="99" step="1" oninput="this.nextElementSibling.value = this.value" disabled/><output>50</output></div>
						<div>' . esc_html__('Human', 'app-for-cf') . '</div>
					</div>
					<div class="explain">' . esc_html__('A bot score is a score from 1 to 99 that indicates how likely that request came from a bot. For example, a score of 1 means Cloudflare is quite certain the request was automated, while a score of 99 means Cloudflare is quite certain the request came from a human.', 'app-for-cf') . '</div>
				</td>
			</tr>
			<tr>
				<th><label for="country">' . esc_html__('Country', 'app-for-cf') . '</label></th>
				<td>
					<select name="country" id="country" class="input">
						<option></option>';
                        foreach ($this->params['countries'] as $countryCode => $country)
                        {
                            echo '<option value="' . esc_attr($countryCode) . '">' . esc_html($country) . '</option>';
                        }
                    echo '</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<label><input type="checkbox" name="skip_challenge" value="1" class="primary"> ' . esc_html__('Skip challenge', 'app-for-cf') . '</label>
					<div class="explain">' . esc_html__('Whether to skip any challenges for tracing request (e.g.: captcha).', 'app-for-cf') . '</div>
				</td>
			</tr>
			<tr>
				<th></th>
				<td data-init="dependent">
					<label><input type="checkbox" class="primary"> ' . esc_html__('Threat score', 'app-for-cf') . '</label>
					<div class="flexRow range dependent">
						<div>' . esc_html__('Low risk', 'app-for-cf') . '</div>
						<div><input type="range" name="threat_score" value="50" min="0" max="100" step="1" oninput="this.nextElementSibling.value = this.value" disabled/><output>50</output></div>
						<div>' . esc_html__('High risk', 'app-for-cf') . '</div>
					</div>
					<div class="explain">' . esc_html__('Represents a Cloudflare threat score from 0–100, where 0 indicates low risk. Values above 10 may represent spammers or bots, and values above 40 identify bad actors on the Internet. It is rare to see values above 60.', 'app-for-cf') . '</div>
				</td>
			</tr>';
		?>

		<tr>
			<td></td>
			<td>
				<?php submit_button(esc_html__('Test...', 'app-for-cf')); ?>
			</td>
		</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}