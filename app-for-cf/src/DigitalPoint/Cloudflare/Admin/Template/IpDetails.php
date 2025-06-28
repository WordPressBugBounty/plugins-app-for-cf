<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class IpDetails extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap">
				<h2>' . esc_html__('IP address details', 'app-for-cf') . '</h2>';

		if (!empty($this->params['result']) && !empty($this->params['result']['result'][0]['ip']))
		{
			echo '<table class="form-table">
			    <tr>
				    <th>' . esc_html__('IP address', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result'][0]['ip']) . (!empty($this->params['result']['result'][0]['ptr_lookup']['ptr_domains'][0]) ? ' (' . esc_html($this->params['result']['result'][0]['ptr_lookup']['ptr_domains'][0]) . ')' : '' ) . '
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Belongs to', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result'][0]['belongs_to_ref']['description']) . (!empty($this->params['result']['result'][0]['belongs_to_ref']['value']) ? ' (AS' . esc_html($this->params['result']['result'][0]['belongs_to_ref']['value']) . ')' : '' ) . '
					    <div class="explain">' . esc_html($this->params['country']) . '</div>
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Type', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result'][0]['belongs_to_ref']['type']) . '
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Risk types', 'app-for-cf') . '</th>
			    	<td>
			    	    <ul>';
                            if (!empty($this->params['result']['result'][0]['risk_types']))
                            {
                                foreach ($this->params['result']['result'][0]['risk_types'] as $riskType)
                                {
	                                echo '<li>' . esc_html($riskType['name']) . '</li>';
                                }
                            }
                        echo '</ul>
				    </td>
			    </tr>
			</table>';
		}

		echo esc_html__('A quick way to get basic information about an IP address.', 'app-for-cf') .

			'<form method="post" action="' . esc_url(menu_page_url('app-for-cf_ip-details', false)) . '">
        <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>

		<table class="form-table">
			<tr>
				<th><label for="ip">' . esc_html__('IP address', 'app-for-cf') . '</label></th>
				<td>
					<input type="text" class="input " name="ip" required="required" id="ip" value="' . esc_attr($this->params['ip']) . '">
				</td>
			</tr>';
		?>

		<tr>
			<td></td>
			<td>
				<?php submit_button(esc_html__('Get IP address details...', 'app-for-cf')); ?>
			</td>
		</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}