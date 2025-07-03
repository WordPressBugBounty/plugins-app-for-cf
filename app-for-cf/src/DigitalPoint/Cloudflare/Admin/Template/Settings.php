<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class Settings extends AbstractTemplate
{
    use \DigitalPoint\Cloudflare\Traits\WP;

	protected function template()
	{
        $this->addAsset('css');
		$this->addAsset('js');

		$appForCloudflareOptions = get_option('app_for_cf');

		$currentUser = wp_get_current_user();

		$cloudflareAppInternal = \DigitalPoint\Cloudflare\Helper\Api::check();

		$helper = new \DigitalPoint\Cloudflare\Helper\WordPress();

        if (!empty($this->params['error']))
		{
			echo '<div class="error"><p>' . wp_kses(sprintf('<strong>%1$s</strong><br /><br />%2$s', esc_html__('App For Cloudflare® error:', 'app-for-cf'), esc_html($this->params['error'])), 'post') . '</p></div>';
        }

		?>
<div class="wrap" id="app-for-cf_settings">

	<h2><?php esc_html_e( 'App for Cloudflare®' , 'app-for-cf');?></h2>

    <?php

    if ($this->params['tokenId'])
    {
        ?>
    <div class="tablenav">
        <div class="alignleft" style="padding-right:3em;">
            <table class="form-table">
                <tr>
                    <th>
					    <?php
                            echo esc_html($this->params['settings']['top'][1]['title']);
					    ?>
                    </th>
                    <td>
					    <?php
					        $this->showSetting($this->params['settings']['top'][1]);
					    ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="alignleft">
            <table class="form-table">
                <tr>
                    <th>
                        <?php
                            echo esc_html($this->params['settings']['top'][0]['title']);
                        ?>
                    </th>
                    <td>
	                    <?php
                            $this->showSetting($this->params['settings']['top'][0]);
	                    ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="alignright">
            <table class="form-table">
                <tr>
                    <td>
                        <?php
                            echo '<a class="button-primary" data-click="overlay" href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'easy'], esc_url(menu_page_url('app-for-cf_settings', false))))) . '"><span aria-hidden="true"><span class="dashicons dashicons-welcome-learn-more"></span>' . esc_html__('Easy config', 'app-for-cf') . '</span></a>';
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
    }
	?>
<h3 class="nav-tab-wrapper clear" id="dp_tabs">
	<a class="nav-tab" id="setup-tab" href="#top#setup"><?php esc_html_e( 'Setup', 'app-for-cf' ); ?></a>
	<?php
	if ($this->params['tokenId'])
	{
		?>
    <a class="nav-tab" id="ssl_tls-tab" href="#top#ssl_tls"><?php esc_html_e( 'SSL/TLS', 'app-for-cf' ); ?></a>
    <a class="nav-tab" id="security-tab" href="#top#security"><?php esc_html_e( 'Security', 'app-for-cf' ); ?></a>
    <a class="nav-tab" id="speed-tab" href="#top#speed"><?php esc_html_e( 'Speed', 'app-for-cf' ); ?></a>
    <a class="nav-tab" id="caching-tab" href="#top#caching"><?php esc_html_e( 'Caching', 'app-for-cf' ); ?></a>
    <a class="nav-tab" id="network-tab" href="#top#network"><?php esc_html_e( 'Network', 'app-for-cf' ); ?></a>
    <a class="nav-tab" id="scrape_shield-tab" href="#top#scrape_shield"><?php esc_html_e('Scrape Shield', 'app-for-cf' ); ?></a>
	<?php
	}
    ?>
</h3>


<div class="tab-wrapper">

	<aside id="app-for-cf_sidebar_wrapper">
		<div id="app-for-cf_sidebar">

			<div class="postbox support">
				<h4><?php esc_html_e('Support / Feature Requests', 'app-for-cf'); ?></h4>
				<div>
					<?php esc_html_e('App for Cloudflare® is user request driven, so if there\'s something you want it to do that it doesn\'t already do, or just have a question, simply ask!', 'app-for-cf'); ?>
				</div>
				<div style="margin-top: 10px;">
					<?php printf('<a class="button button-primary" href="%1$s" target="_blank">%2$s</a>',
						esc_url(APP_FOR_CLOUDFLARE_SUPPORT_URL . '?utm_source=admin_settings&utm_medium=wordpress&utm_campaign=plugin'),
						esc_html__('Support forum', 'app-for-cf')
					); ?>
				</div>
			</div>

			<?php
			if (!$helper->isLocaleSupported($locales))
			{
				?>

				<div class="postbox translation">
					<h4><?php
						esc_html_e('Translation / Localization', 'app-for-cf');
						?></h4>

					<div>
						<?php
							/* translators: %1$s = <a href=...>, %2$s = </a> */
							printf(esc_html__('If you would like to help translate App for Cloudflare® into your language, please visit the %1$swordpress.org translation site%2$s and you can help in translating.', 'app-for-cf'), '<a href="' . esc_url('https://translate.wordpress.org/projects/wp-plugins/app-for-cf/') . '" target="_blank">', '</a>');
						?>
					</div>
				</div>
				<?php
			}

			if ($cloudflareAppInternal)
			{
			?>

				<div class="postbox has-pro" style="border:2px dashed green;padding-right: 5px;">
					<h4><?php esc_html_e('Premium Version Installed', 'app-for-cf'); ?></h4>
					<div>
						<?php esc_html_e('Thanks for your support. The Premium version of this addon has been activated.', 'app-for-cf'); ?>
					</div>
				</div>
			<?php

			}
			?>


			<div class="postbox pro">
				<h4><?php esc_html_e('Extra Features In Premium Version', 'app-for-cf'); ?></h4>
				<div>
					<ul>
                        <li>
							<?php esc_html_e('Lock down admin area with Zero Trust Access policies', 'app-for-cf'); ?>
                        </li>
                        <li>
							<?php esc_html_e('Manage page rules', 'app-for-cf'); ?>
                        </li>
                        <li>
							<?php esc_html_e('Custom cache rules', 'app-for-cf'); ?>
                            <ul>
                                <li>
			                        <?php esc_html_e('Force static content caching', 'app-for-cf'); ?>
                                </li>
                                <li>
			                        <?php esc_html_e('Allow caching of public R2 bucket', 'app-for-cf'); ?>
                                </li>
                            </ul>
                        </li>
                        <li>
							<?php esc_html_e('Custom firewall rules', 'app-for-cf'); ?>
                            <ul>
                                <li>
	                                <?php esc_html_e('Block visitors by countries', 'app-for-cf'); ?>
                                </li>
                                <li>
		                            <?php esc_html_e('Force challenge when logging in or registering', 'app-for-cf'); ?>
                                </li>
                                <li>
		                            <?php esc_html_e('Block traffic by user agent', 'app-for-cf'); ?>
                                </li>
                                <li>
		                            <?php esc_html_e('Auto-block IPs of spammers', 'app-for-cf'); ?>
                                </li>
                                <li>
		                            <?php esc_html_e('Block traffic by IP address or network (temporary or permanent)', 'app-for-cf'); ?>
                                </li>
                            </ul>
                        </li>

                        <li>
							<?php esc_html_e('Store WordPress media in the cloud with R2', 'app-for-cf'); ?>
                        </li>
                        <li>
							<?php esc_html_e('Backup & restore rules and configuration (can use to apply site A config to site B as well)', 'app-for-cf'); ?>
                        </li>
					</ul>
					<?php

					echo '<div style="text-align:center;"><a class="button-primary" href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL) . '?utm_source=admin_settings&utm_medium=wordpress&utm_campaign=plugin" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-money-alt"></span>' . esc_html__('Purchase Premium version', 'app-for-cf') . '</span></a></div>';

					?>
				</div>
			</div>

		</div>
	</aside>



<form method="post" action="options.php" id="settingsForm">
    <input type="hidden" id="dp_current_tab" name="current_tab" value="setup" />
    <?php
    settings_fields('app-for-cf-group');
    do_settings_sections('app-for-cf-group');
    ?>
</form>


	<table class="form-table" id="cf-app_settings">

        <tr class="group_setup tab_content">
            <th scope="row"><?php esc_html_e('API token', 'app-for-cf');?></th>
            <td>
	            <?php printf('<a class="button button-primary alignright" href="%1$s" target="_blank"><span class="dashicons dashicons-rest-api"></span>%2$s</a>',
		            @$appForCloudflareOptions['cloudflareAuth']['token'] ? esc_url('https://dash.cloudflare.com/profile/api-tokens') : esc_url('https://dash.cloudflare.com/profile/api-tokens?permissionGroupKeys=%5B%7B%22key%22%3A%22access%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22access_acct%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22account_analytics%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22analytics%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22billing%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22request_tracer%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22intel%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22bot_management%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22cache%22%2C%22type%22%3A%22purge%22%7D%2C%7B%22key%22%3A%22cache_settings%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22challenge_widgets%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22firewall_services%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22page_rules%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22ssl_and_certificates%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22workers_r2%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22workers_scripts%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone_settings%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone_waf%22%2C%22type%22%3A%22edit%22%7D%5D&name=WordPress'),
		            esc_html__('API tokens', 'app-for-cf')
	            ); ?>

	            <?php printf('%s (<a href="%s" target="_blank">%s</a>):', esc_html__('Create a token for your zone(s) with the following permissions', 'app-for-cf'), esc_url(APP_FOR_CLOUDFLARE_PRODUCT_URL . 'threads/permissions-needed-for-app-for-cloudflare%C2%AE.3/?utm_source=permissions&utm_medium=wordpress&utm_campaign=plugin'), esc_html__('why', 'app-for-cf')); ?>
                <ul>
                    <li>
	                    <?php
	                    /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Access: Apps and Policies: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Access: Organizations, Identity Providers, and Groups: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Account Analytics: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Allow Request Tracer: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
		                printf(esc_html__('Account.Billing: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Intel: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Turnstile: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Workers R2 Storage: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Account.Workers Scripts: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Read, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Analytics: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Read', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Bot Management: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Purge, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Cache Purge: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Purge', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Cache Rules: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Firewall Services: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Page Rules: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.SSL and Certificates: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
                    <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Zone: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
                    </li>
	                <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Zone Settings: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
	                </li>
	                <li>
		                <?php
		                /* translators: %1$s = Edit, %2$s = <strong>, %3$s = </strong> */
                        printf(esc_html__('Zone.Zone WAF: %2$s%1$s%3$s', 'app-for-cf'), esc_html__('Edit', 'app-for-cf'), '<strong>', '</strong>'); ?>
	                </li>
                </ul>

                <?php

                if (is_multisite() && !empty(get_site_option('app_for_cf_network')['cloudflareAuth']['token']))
                {
                    $useMultisiteToken = empty($appForCloudflareOptions['cloudflareAuth']['token']) && empty($appForCloudflareOptions['network_exclude']['cloudflareAuth']['token']);

	                echo '<div data-init="dependent">';
	                echo '<label><input form="settingsForm" type="radio" class="primary" name="fromMultisite[cloudflareAuth][token]" value="1"' . ($useMultisiteToken ? ' checked' : '') . '> ' . esc_html__('Use multisite network API token', 'app-for-cf') . '</label>';
	                echo '<div class="explain">';
                    echo ($useMultisiteToken && is_main_site() ? esc_html__('The main site of a multisite network always uses the multisite network API token.', 'app-for-cf') : esc_html__('There is a network-wide API token set, this option will use it.', 'app-for-cf')) . '</div>';

                    echo '</div><div data-init="dependent">';
	                echo '<label><input form="settingsForm" type="radio" class="primary" name="fromMultisite[cloudflareAuth][token]" value="0"' . (!$useMultisiteToken ? ' checked' : '') . ($useMultisiteToken && is_main_site() ? ' disabled' : '') . '> ' . esc_html__('Set API token for just this site', 'app-for-cf') . '</label>';
	                echo '<div class="dependent">
                        <input type="text" name="app_for_cf[cloudflareAuth][token]" id="cf-app_cloudflareAuth_token"
                           form="settingsForm"
                           class="hide_value"
                           style="width: 90%;"
                           placeholder="'. esc_html__('API token', 'app-for-cf') . '"
                           value="' . esc_attr(!empty($appForCloudflareOptions['cloudflareAuth']['token']) ? $appForCloudflareOptions['cloudflareAuth']['token'] : '') .'"/>
                                <div class="explain">';

                                /* translators: %1$s = Zone Token ID (from Cloudflare account) */
	                             echo ((!empty($appForCloudflareOptions['cfTokenId']) && !empty($appForCloudflareOptions['cloudflareAuth']['token'])) ? sprintf(esc_html__('Token ID: %1$s', 'app-for-cf'), esc_html($appForCloudflareOptions['cfTokenId'])) : '') . '</div>
                        </div>';

	                echo '</div>';
                }
                else
                {
                    echo '<input type="text" name="app_for_cf[cloudflareAuth][token]" id="cf-app_cloudflareAuth_token"
                       form="settingsForm"
                       class="hide_value"
                       style="width: 90%;"
                       placeholder="' . esc_html__('API token', 'app-for-cf') .'"
                       value="' . esc_attr(!empty($appForCloudflareOptions['cloudflareAuth']['token']) ? $appForCloudflareOptions['cloudflareAuth']['token'] : '') .'"/>
                            <div class="explain">';
	                        /* translators: %1$s = Zone Token ID (from Cloudflare account) */
                            echo (!empty($appForCloudflareOptions['cfTokenId']) ? sprintf(esc_html__('Token ID: %1$s', 'app-for-cf'), esc_html($appForCloudflareOptions['cfTokenId'])) : '') . '</div>';
                }
                ?>
            </td>
        </tr>



		<?php
			if (!empty(\DigitalPoint\Cloudflare\Helper\Api::$version))
			{
		?>

		<tr class="group_setup tab_content">
			<th scope="row"><?php esc_html_e('License key', 'app-for-cf');?></th>
			<td>
				<textarea name="app_for_cf[cfLicenseKey]" id="cf-app_cloudflareLicenseKey"
			       form="settingsForm"
			       rows="2"
			       style="width: 90%;"><?php echo esc_attr(@$appForCloudflareOptions['cfLicenseKey']); ?></textarea>
				<div class="explain"><?php
					/* translators: %1$s = <a href=...>, %2$s = </a>, %3$s = <a href=...>, %4$s = </a> */
                    printf(esc_html__('This is your license key for the %1$sPremium version of App for Cloudflare®%2$s. You can find your existing licenses %3$sunder your account over here%4$s.', 'app-for-cf'), '<a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL) . '?utm_source=license&utm_medium=wordpress&utm_campaign=plugin" target="_blank">', '</a>', '<a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRODUCT_URL) . 'account/licenses?utm_source=license&utm_medium=wordpress&utm_campaign=plugin" target="_blank">', '</a>');?></div>
			</td>
		</tr>

		<?php
			}
		?>





		<tr class="group_setup tab_content">
			<th scope="row">

				<?php
					if (empty($appForCloudflareOptions['cloudflareAuth']['token']) && empty($appForCloudflareOptions['cfTurnstile']['siteKey']))
					{
					?>
						<div><a class="button button-primary" href="http://dash.cloudflare.com/?to=/:account/turnstile/add" target="_blank"><span class="dashicons dashicons-plus-alt"></span><?php esc_html_e('Setup', 'app-for-cf') ?></a>

						<div class="explain"><?php
							/* translators: %1$s = site hostname */
							echo sprintf (esc_html__('Create a managed Turnstile widget for your hostname (%1$s).', 'app-for-cf'), esc_attr(wp_parse_url($this->getSiteUrl(), PHP_URL_HOST)));
							?></div>
						</div>

					<?php
					}
				?>
			</th>
			<td>

				<div data-init="dependent">
					<input type="hidden" name="app_for_cf[cfTurnstile][]" form="settingsForm" value="0">
					<?php

					if (!empty($appForCloudflareOptions['cfAccountId']) && !empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']))
					{
						$cloudflareRepo = new \DigitalPoint\Cloudflare\Repository\Cloudflare();
					?>
						<div class="alignright">
							<a class="button button-primary" href="<?php echo esc_url($cloudflareRepo->getTurnstileSiteUrlEdit($appForCloudflareOptions['cfTurnstile']['siteKey'])); ?>" target="_blank"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Settings') /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */ ?></a>
							<a class="button button-primary" href="<?php echo esc_url($cloudflareRepo->getTurnstileSiteUrl($appForCloudflareOptions['cfTurnstile']['siteKey'])); ?>" target="_blank"><span class="dashicons dashicons-chart-bar"></span><?php esc_html_e('Analytics', 'app-for-cf') ?></a>
						</div>
					<?php
					}
					elseif (!empty($appForCloudflareOptions['cloudflareAuth']['token']))
					{
						echo '<a class="button button-primary alignright" href="' . esc_attr(wp_nonce_url(add_query_arg(['action' => 'turnstile_widget_add'], esc_url(menu_page_url('app-for-cf_caching', false))))) . '" data-click="overlay"><span class="dashicons dashicons-plus-alt"></span>' . esc_html__('Setup in Cloudflare', 'app-for-cf') . '</a>';
					}
					?>
					<label><input form="settingsForm" type="checkbox" class="primary" value="0" <?php echo (!empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']) ? ' checked' : '') ?>><?php esc_html_e('Use Turnstile CAPTCHA', 'app-for-cf') ?></label>
					<div class="explain"><?php
						/* translators: %1$s = <a href=...>, %2$s = </a> */
						printf(esc_html__('%1$sTurnstile%2$s is a free, privacy-focused CAPTCHA that helps block spam bots.', 'app-for-cf'), '<a href="https://blog.cloudflare.com/turnstile-ga/" target="_blank">', '</a>');?></div>
					<div class="dependent">
						<label><?php esc_html_e('Site key:', 'app-for-cf');?>
							<input type="text" name="app_for_cf[cfTurnstile][siteKey]" id="cf-app_cloudflareTurnstileSiteKey"
							   form="settingsForm"
							   style="width: 100%;"
							   value="<?php echo esc_attr(@$appForCloudflareOptions['cfTurnstile']['siteKey']); ?>"<?php disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey'])); ?>/>
						</label>
						<label><?php esc_html_e('Secret key:', 'app-for-cf');?>
							<input type="text" name="app_for_cf[cfTurnstile][secretKey]" id="cf-app_cloudflareTurnstileSecretKey"
								form="settingsForm"
								class="hide_value"
								style="width: 100%;"
								value="<?php echo esc_attr(@$appForCloudflareOptions['cfTurnstile']['secretKey']); ?>"<?php disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey'])); ?>/>
						</label>

						<label>
							<?php esc_html_e('For:', 'app-for-cf');?>
						</label>
							<div class="dependent">
								<label><input form="settingsForm" type="checkbox" class="primary" value="1" name="app_for_cf[cfTurnstile][onRegister]"<?php echo esc_attr(trim(' ' . (!empty(@$appForCloudflareOptions['cfTurnstile']['onRegister']) ? ' checked' : '') . ' ' . disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']), false))) ?>><?php esc_html_e('Registration', 'app-for-cf') ?></label>
								<label><input form="settingsForm" type="checkbox" class="primary" value="1" name="app_for_cf[cfTurnstile][onLogin]"<?php echo esc_attr(trim(' ' . (!empty(@$appForCloudflareOptions['cfTurnstile']['onLogin']) ? ' checked' : '') . ' ' . disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']), false))) ?>><?php esc_html_e('Login', 'app-for-cf') ?></label>
								<label><input form="settingsForm" type="checkbox" class="primary" value="1" name="app_for_cf[cfTurnstile][onPassword]"<?php echo esc_attr(trim(' ' . (!empty(@$appForCloudflareOptions['cfTurnstile']['onPassword']) ? ' checked' : '') . ' ' . disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']), false))) ?>><?php esc_html_e('Password reset', 'app-for-cf') ?></label>
								<label><input form="settingsForm" type="checkbox" class="primary" value="1" name="app_for_cf[cfTurnstile][onComment]"<?php echo esc_attr(trim(' ' . (!empty(@$appForCloudflareOptions['cfTurnstile']['onComment']) ? ' checked' : '') . ' ' . disabled(true, empty(@$appForCloudflareOptions['cfTurnstile']['siteKey']), false))) ?>><?php esc_html_e('Comment') /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */ ?></label>
							</div>
					</div>
				</div>
			</td>
		</tr>






		<tr class="group_setup tab_content">
			<th scope="row"><?php esc_html_e('Block spammer IPs', 'app-for-cf');?></th>
			<td<?php echo (!intval(@$cloudflareAppInternal) ? ' class="pro"' : ''); ?>>

                <label for="cf-app_cloudflareBlockIpsSpamClean">
                    <input type="hidden" name="app_for_cf[cloudflareBlockIpsSpamClean]" form="settingsForm" value="0">
                    <input name="app_for_cf[cloudflareBlockIpsSpamClean]" type="checkbox" id="cf-app_cloudflareBlockIpsSpamClean" form="settingsForm" value="1" <?php checked('1', empty($appForCloudflareOptions['cloudflareBlockIpsSpamClean']) ? false : $appForCloudflareOptions['cloudflareBlockIpsSpamClean'] ); disabled(0, intval(@$cloudflareAppInternal)); ?>>
					<?php
					    esc_html_e('Block IP address on spam flag in comment queue', 'app-for-cf');
					?>
                </label>
                <div class="explain"><?php esc_html_e('This will block the IP addresses used by a user when their comment was flagged as spam.', 'app-for-cf');?></div>


				<?php esc_html_e('Days that firewall rules last:', 'app-for-cf');?>
                <input type="number" name="app_for_cf[cloudflareFirewallExpireDays]"
                       id="cf-app_cloudflareFirewallExpireDays"
                       form="settingsForm"
                       min="1" max="90" step="1"
                       value="<?php echo esc_attr(@$appForCloudflareOptions['cloudflareFirewallExpireDays']); ?>"<?php disabled(0, intval(@$cloudflareAppInternal)); ?>/>
                <div class="explain"><?php esc_html_e('For automatically created firewall rules (for example blocking the IP addresses of spammers), this is the number of days until the firewall rule expires.', 'app-for-cf');?></div>
			</td>
		</tr>

        <tr class="group_setup tab_content">
            <th scope="row"><?php esc_html_e('External data URL', 'app-for-cf');?></th>
            <td<?php echo (!intval(@$cloudflareAppInternal) ? ' class="pro"' : ''); ?>>
                <input type="text" name="app_for_cf[cfExternalDataUrl]" id="cf-app_cloudflareExternalDataUrl"
                       form="settingsForm"
                       style="width: 90%;"
                       value="<?php echo esc_attr(@$appForCloudflareOptions['cfExternalDataUrl']); ?>"<?php disabled(0, intval(@$cloudflareAppInternal)); ?>/>
                <div class="explain"><?php
	                /* translators: %1$s = <a href=...>, %2$s = </a> */
	                printf(esc_html__('This is the URL where your media is stored. Normally you don\'t need to edit this (it\'s automatically created and set when you %1$senable R2 storage for your media%2$s).', 'app-for-cf'), \DigitalPoint\Cloudflare\Helper\Api::$version ? '<a href="' . esc_url_raw(menu_page_url('app-for-cf_r2', false)) . '">' : '', \DigitalPoint\Cloudflare\Helper\Api::$version ? '</a>' : '');?></div>
            </td>
        </tr>

        <?php if ((class_exists('Imagick') && \Imagick::queryformats('WEBP')) || function_exists('imagewebp')) { ?>

        <tr class="group_setup tab_content">
            <th scope="row"></th>
            <td<?php echo (!intval(@$cloudflareAppInternal) ? ' class="pro"' : ''); ?>>

                <div data-init="dependent">
                    <input type="hidden" name="app_for_cf[cfWebpCompression]" form="settingsForm" value="0">
                    <label><input form="settingsForm" type="checkbox" class="primary" value="0" <?php echo (@$appForCloudflareOptions['cfWebpCompression'] ? ' checked' : '') . disabled(0, intval(@$cloudflareAppInternal)) ?>><?php esc_html_e('Convert uploaded media to WebP', 'app-for-cf') ?></label>
                    <div class="dependent">
	                    <?php esc_html_e('Compression level:', 'app-for-cf');?>
                        <input type="number" name="app_for_cf[cfWebpCompression]" id="cf-app_cloudflareWebpCompression"
                               form="settingsForm"
                               step="1"
                               min="10"
                               max="100"
                               <?php echo @$appForCloudflareOptions['cfWebpCompression'] ? '' : 'disabled' ?>
                               value="<?php echo esc_attr(@$appForCloudflareOptions['cfWebpCompression'] ?: 80); ?>"<?php disabled(0, intval(@$cloudflareAppInternal)); ?>/>

                        <div class="explain"><?php
	                        esc_html_e('If your server supports ImageMagick or GD with WebP, you can convert PNG and JPG images to WebP automatically when they are uploaded as media.', 'app-for-cf');
                        ?></div>
                    </div>
                </div>
            </td>
        </tr>

        <?php } ?>

		<tr class="group_setup tab_content">
			<th scope="row"></th>
			<td>
				<input type="hidden" name="app_for_cf[cfImagesTransform]" form="settingsForm" value="0">
				<label for="app_cloudflareImagesTransform">
					<input type="checkbox" name="app_for_cf[cfImagesTransform]" id="app_cloudflareImagesTransform"
						   form="settingsForm"
						   value="1" <?php checked('1', !empty($appForCloudflareOptions['cfImagesTransform'])); ?>>
					<?php esc_html_e('Transform media images', 'app-for-cf');?>
				</label>
				<div class="explain"><?php
					/* translators: %1$s = <a href=...>, %2$s = </a> */
					printf(esc_html__('Utilize Cloudflare Images Transform service to serve media (when displayed via %1$simage or media blocks%2$s) in the best format/compression for the browser. For example, users with modern browsers will receive images as AVIF or WebP, while older browsers will receive older image formats. Additionally, users on very slow connections will receive slightly lower quality images.', 'app-for-cf'), '<a href="https://wordpress.org/documentation/article/image-block/" target="_blank">', '</a>');
					echo '<br /><br />';

					if (empty($appForCloudflareOptions['cfAccountId']) || empty($appForCloudflareOptions['cfZoneId']))
					{
						$url = 'http://dash.cloudflare.com/?to=/:account/images/delivery-zones';
					}
					else
					{
						/* translators: %1$s = Cloudflare zccount ID, %2$s = Cloudflare zone ID */
						$url = sprintf('https://dash.cloudflare.com/%1$s/images/delivery-zones/%2$s/settings', $appForCloudflareOptions['cfAccountId'], $appForCloudflareOptions['cfZoneId']);
					}

					/* translators: %1$s = <a href=...>, %2$s = </a> */
					printf(esc_html__('Make sure you have %1$simage transformations%2$s enabled for your Cloudflare zone.', 'app-for-cf'), '<a href="' . esc_url($url) . '" target="_blank">', '</a>');

					?></div>
			</td>
		</tr>

		<tr class="group_setup tab_content">
			<th scope="row"></th>
			<td>
				<input type="hidden" name="app_for_cf[cloudflarePreload]" form="settingsForm" value="0">
				<label for="app_cloudflarePreload">
					<input type="checkbox" name="app_for_cf[cloudflarePreload]" id="app_cloudflarePreload"
						   form="settingsForm"
						   value="1" <?php checked('1', !empty($appForCloudflareOptions['cloudflarePreload'])); ?>>
					<?php esc_html_e('Preload resources', 'app-for-cf');?>
				</label>
				<div class="explain"><?php
					/* translators: %1$s = <a href=...>, %2$s = </a> */
					printf(esc_html__('Leverages HTTP Link header to instruct a browser which resources to preload. A maximum of 10 resources will be preloaded. Can be used in conjunction with the %1$sEarly Hints%2$s Cloudflare setting.', 'app-for-cf'), '<a href="' . esc_url('https://blog.cloudflare.com/early-hints/') . '" target="_blank">', '</a>');
				?></div>
			</td>
		</tr>

        <tr class="group_setup tab_content">
            <th scope="row"></th>
            <td>
                <input type="hidden" name="app_for_cf[cfPurgeCacheOnAdminBar]" form="settingsForm" value="0">
                <label for="app_cloudflarePurgeCacheOnAdminBar">
                    <input type="checkbox" name="app_for_cf[cfPurgeCacheOnAdminBar]" id="app_cloudflarePurgeCacheOnAdminBar"
                           form="settingsForm"
                           value="1" <?php checked('1', !empty($appForCloudflareOptions['cfPurgeCacheOnAdminBar'])); ?>>
					<?php esc_html_e('Purge cache button in admin bar', 'app-for-cf');?>
                </label>
                <div class="explain"><?php esc_html_e('Allows purging Cloudflare cache with a single click in the admin bar.', 'app-for-cf');?></div>
            </td>
        </tr>

        <tr class="group_setup tab_content">
            <th scope="row"><?php esc_html_e('Permissions to settings', 'app-for-cf');?></th>
            <td>
				<input type="hidden" name="app_for_cf[LockSettingsUserId]" form="settingsForm" value="0">
                <label for="app_cloudflareLockSettingsUserId">
                    <input type="checkbox" name="app_for_cf[LockSettingsUserId]" id="app_cloudflareLockSettingsUserId"
                       form="settingsForm"
                       value="<?php echo esc_attr(@$currentUser->ID); ?>" <?php checked('1', empty($appForCloudflareOptions['LockSettingsUserId']) ? false : $appForCloudflareOptions['LockSettingsUserId'] ); ?>>
                    <?php esc_html_e('Only your user account', 'app-for-cf');?>
                </label>
                <div class="explain"><?php esc_html_e('If you want to disable all other admin accounts from having access to Cloudflare settings, use this option. Keep in mind, that only your account will have access to change any Cloudflare settings, so if you delete this WordPress account or use a different WordPress account, you can be locked out of these settings.', 'app-for-cf');?></div>
            </td>
        </tr>

        <tr class="group_setup tab_content">
            <td></td>
            <td>
    	        <?php submit_button(null, 'primary', 'submit', true, ['form' => 'settingsForm']); ?>
            </td>
        </tr>


		<?php

		if ($this->params['tokenId'])
        {

			echo '<input form="cfSettingsForm" type="hidden" name="_wpnonce" value="'. esc_attr(wp_create_nonce()) .'"/>';

            foreach ($this->params['settings'] as $section => $sectionSettings)
            {
                if ($section == 'top')
                {
                    continue;
                }

                echo '<tr class="group_' . esc_html($section) . ' tab_content">
                    <td><table class="form-table">';
                    foreach ($sectionSettings as $setting)
                    {
                        if (!empty($setting['defaults']['subsection_label']))
                        {
                            echo '<tr class="subsection border"><th colspan="2">' . esc_html($setting['defaults']['subsection_label']) . '</th></tr>';
                        }

                        echo '<tr class="border' . (empty($setting['defaults']['beta']) ? '' : ' beta') . (empty($setting['defaults']['deprecated']) ? '' : ' deprecated') . '"><th scope="row"><span class="main">'. esc_html($setting['title']) . '</span><div class="explain">' . esc_html($setting['explain']) . '</div></th><td>';

                        $this->showSetting($setting);

                        echo '</td></tr>';
                    }

                echo '</table></td></tr>';
            }
		}
		?>

	</table>
</div>
</div>

<?php
	}


    protected function showSetting($setting)
    {
	    if (!empty($setting['defaults']['type']) && $setting['defaults']['type'] == 'select')
	    {
		    $this->typeSelect($setting);
	    }
        elseif (!empty($setting['defaults']['type']) && $setting['defaults']['type'] == 'radio')
	    {
		    $this->typeRadio($setting);
	    }
        elseif (!empty($setting['defaults']['type']) && $setting['defaults']['type'] == 'checkbox')
	    {
		    $this->typeCheckbox($setting);
	    }
        elseif (!empty($setting['defaults']['macro']))
	    {
		    $method = 'macro' . ucwords($setting['defaults']['macro']);
		    if(method_exists($this, $method))
		    {
			    $this->$method($setting);
		    }
	    }
	    else
	    {
		    $this->typeToggle($setting);
	    }
    }

    protected function typeSelect($setting)
    {
	    echo '<select form="cfSettingsForm" name="' . esc_attr($setting['id']) . '" value="' . esc_attr($setting['options']['value']) . '"' . (empty($setting['options']['editable']) ? ' disabled' : '') . '>';
	    foreach ($setting['defaults']['values'] as $value => $phrase)
	    {
		    echo '<option value="' . esc_attr($value) . '"' . ($value == $setting['options']['value'] ? ' selected' : '') . '>' . esc_html($this->phrase($phrase)) . '</option>';
	    }
	    echo '</select>';
    }

    protected function typeRadio($setting)
    {
	    foreach ($setting['defaults']['values'] as $value => $phrase)
	    {
		    echo '<label><input form="cfSettingsForm" type="radio" name="' . esc_attr($setting['id']) . '" value="' . esc_attr($value) . '"' . ($value == $setting['options']['value'] ? ' checked' : '') . '> ' . esc_html($this->phrase($phrase)) . '</label>';
	    }

    }
    protected function typeCheckbox($setting)
    {
	    foreach ($setting['defaults']['values'] as $value => $phrase)
	    {
		    echo '<label><input form="cfSettingsForm" type="checkbox" name="' . esc_attr($setting['id']) . '[' . esc_attr($value) . ']" value="1"' . (!empty($setting['options']['value'][$value]) && $setting['options']['value'][$value] == 'on' ? ' checked' : '') . '> ' . esc_html($this->phrase($setting['defaults']['values'][$value])) . '</label>';
	    }
    }
    protected function typeToggle($setting)
    {
	    echo '<input form="cfSettingsForm" type="checkbox" name="' . esc_attr($setting['id']) . '" value="1" class="dp-ui-toggle" style="transform:scale(1.5);"' .
		    (!empty($setting['options']['value']) && ($setting['options']['value'] === 'on' || $setting['options']['value'] === true || ($setting['options']['value'] == 2 && $setting['defaults']['good'] == 2)) ? ' checked' : '') .
		    (empty($setting['options']['editable']) ? ' disabled' : '') .
		    ' title="' . esc_html($this->phrase('enable_disable_x', ['title' => $setting['id']])) . '"' .
		    '>';
    }


    protected function macroHsts($setting)
    {
        echo '<div data-init="dependent" style="white-space:nowrap;">';
	    echo '<label><input form="cfSettingsForm" type="checkbox" class="primary" name="' . esc_attr($setting['id']) . '[strict_transport_security][enabled]"' . (!empty($setting['options']['value']['strict_transport_security']['enabled']) && $setting['options']['value']['strict_transport_security']['enabled'] == 'on' ? ' checked' : '') . '> ' . esc_html($setting['defaults']['values']['strict_transport_security']['enabled']) . '</label>';
        echo '<div class="dependent">
                    <select form="cfSettingsForm" name="' . esc_attr($setting['id']) . '[strict_transport_security][max_age]" style="margin-bottom:5px;">';
                        foreach ($setting['defaults']['max_age_options'] as $value => $phrase)
                        {
                            echo '<option value="' . esc_attr($value) . '"' . ($value == $setting['options']['value']['strict_transport_security']['max_age'] ? ' selected' : '') . '>' . esc_html($phrase) . '</option>';
                        }
                    echo '</select>

	                <label><input form="cfSettingsForm" type="checkbox" name="' . esc_attr($setting['id']) . '[strict_transport_security][include_subdomains]"' . (!empty($setting['options']['value']['strict_transport_security']['include_subdomains']) ? ' checked' : '') . '> ' . esc_html($setting['defaults']['values']['strict_transport_security']['include_subdomains']) . '</label>
	                <label><input form="cfSettingsForm" type="checkbox" name="' . esc_attr($setting['id']) . '[strict_transport_security][preload]"' . (!empty($setting['options']['value']['strict_transport_security']['preload']) ? ' checked' : '') . '> ' . esc_html($setting['defaults']['values']['strict_transport_security']['preload']) . '</label>
	                <label><input form="cfSettingsForm" type="checkbox" name="' . esc_attr($setting['id']) . '[strict_transport_security][nosniff]"' . (!empty($setting['options']['value']['strict_transport_security']['nosniff']) ? ' checked' : '') . '> ' . esc_html($setting['defaults']['values']['strict_transport_security']['nosniff']) . '</label>
              </div>';
	    echo '</div>';
    }
}