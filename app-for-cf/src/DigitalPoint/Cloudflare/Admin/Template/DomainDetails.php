<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class DomainDetails extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		echo '<div class="wrap">
				<h2>' . esc_html__('Domain details', 'app-for-cf') . '</h2>';

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
				    <th>' . esc_html__('Type', 'app-for-cf') . '</th>
			    	<td>
					    ' . esc_html($this->params['result']['result']['type']) . '
					    <div class="explain">' . esc_html($this->params['result']['result']['notes']) . '</div>
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Resolves to', 'app-for-cf') . '</th>
			    	<td>
			    	    <ul>';
			if (!empty($this->params['result']['result']['resolves_to_refs']))
			{
				foreach ($this->params['result']['result']['resolves_to_refs'] as $ip)
				{
					echo '<li>' . esc_html($ip['value']) . '</li>';
				}
			}
			echo '</ul>
				    </td>
			    </tr>
			    <tr>
				    <th>' . esc_html__('Content categories', 'app-for-cf') . '</th>
			    	<td>
			    	    <ul>';
			if (!empty($this->params['result']['result']['content_categories']))
			{
				foreach ($this->params['result']['result']['content_categories'] as $category)
				{
					echo '<li>' . esc_html($category['name']) . '</li>';
				}
			}
			echo '</ul>
				    </td>
			    </tr>
			</table>';
		}

		echo esc_html__('This should be the domain without sub-domains. Eg. example.com, not www.example.com', 'app-for-cf') .

			'<form method="post" action="' . esc_url(menu_page_url('app-for-cf_domain-details', false)) . '">
        <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce()) .'"/>

		<table class="form-table">
			<tr>
				<th><label for="domain">' . esc_html__('Domain', 'app-for-cf') . '</label></th>
				<td>
					<input type="text" class="input " name="domain" required="required" id="domain" value="' . esc_attr($this->params['hostname']) . '">
				</td>
			</tr>';
		?>

		<tr>
			<td></td>
			<td>
				<?php submit_button(esc_html__('Get domain details...', 'app-for-cf')); ?>
			</td>
		</tr>
		</table>

		<?php
		echo '</form></div>';

		return false;
	}
}