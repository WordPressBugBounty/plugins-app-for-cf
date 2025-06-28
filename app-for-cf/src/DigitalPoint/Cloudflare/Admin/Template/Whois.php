<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Whois extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap">
				<h2>' . esc_html__('WHOIS info', 'app-for-cf') . '</h2>';

		if (!empty($this->params['result']) && !empty($this->params['result']['result']['domain']))
		{
			echo '<table class="form-table">
			    <tr>
				    <th>' . esc_html__('Domain', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['domain']) . '
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Created', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['created_date']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Updated', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['updated_date']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Registrant', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['registrant']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Registrant organization', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['registrant_org']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Country', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['registrant_country']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Email', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['registrant_email']) . '
				    </td>
			    </tr>
			     <tr>
				    <th>' . esc_html__('Registrar', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['registrar']) . '
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Nameservers', 'app-for-cf') . '</th>
			    	<td>
			    	    <ul>';
			if (!empty($this->params['result']['result']['nameservers']))
			{
				foreach ($this->params['result']['result']['nameservers'] as $nameserver)
				{
					echo '<li>' . esc_html($nameserver) . '</li>';
				}
			}
			echo '</ul>
				    </td>
			    </tr>

			</table>';
		}

		echo esc_html__('Enter a domain to retrieve domain registration info about the domain.', 'app-for-cf') .

			'<form method="post" action="' . esc_url(menu_page_url('app-for-cf_whois', false)) . '">
        <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>

		<table class="form-table">
			<tr>
				<th><label for="domain">' . esc_html__('Domain', 'app-for-cf') . '</label></th>
				<td>
					<input type="text" class="input " name="domain" required="required" id="domain" value="' . esc_html($this->params['hostname']) . '">
				</td>
			</tr>';
		?>

		<tr>
			<td></td>
			<td>
				<?php submit_button(esc_html__('Get WHOIS details...', 'app-for-cf')); ?>
			</td>
		</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}