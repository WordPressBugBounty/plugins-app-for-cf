<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class MultisiteSettings extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('css');

		$appForCloudflareOptions = get_site_option('app_for_cf_network');

		$helper = new \DigitalPoint\Cloudflare\Helper\WordPress();

        if (!empty($_GET['updated'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
        {
	        echo wp_kses_post(wp_get_admin_notice(
		        __('Settings saved.'), /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
		        [
			        'type'        => 'success',
			        'dismissible' => true,
			        'id'          => 'message',
		        ]
	        ));
        }

		?>
		<div class="wrap" id="app-for-cf_settings">

			<h2><?php esc_html_e( 'Network-wide Cloudflare settings' , 'app-for-cf');?></h2>

			<div class="notice notice-warning">
				<p>
					<?php
                        esc_html_e('This allows you to apply certain settings to all the sites in your network. If a setting shouldn\'t be applied to all sites, leave it blank. For example if your API token is not setup for all sites (or it\'s impossible because not all sites are in the same Cloudflare account), it should be left blank. In that case, you would set the API token on a per site basis in your "normal" options for each site.', 'app-for-cf');
					?>
				</p>
			</div>

            <?php
                if(!is_plugin_active_for_network('app-for-cf/app-for-cf.php'))
                {
                 ?>
                    <div class="notice notice-warning">
                        <h4><?php esc_html_e('Network Activate App for Cloudflare® plugin', 'app-for-cf'); ?></h4>
                        <p>
			                <?php
			                /* translators: %1$s = <a href...>, %2$s = </a> */
			                echo sprintf(esc_html__('For best results, you should %1$snetwork activate the App for Cloudflare® plugin%2$s so it\'s available to all sites in your network.', 'app-for-cf'), sprintf('<a href="%1$s">', esc_url(network_admin_url('plugins.php'))), '</a>');
			                ?>
                        </p>
                    </div>
                 <?php
                }
            ?>

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
									esc_url(APP_FOR_CLOUDFLARE_SUPPORT_URL . '?utm_source=admin_settings_network&utm_medium=wordpress&utm_campaign=plugin'),
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
						?>

					</div>
				</aside>

				<form method="post" action="<?php echo esc_url(add_query_arg( 'action', 'app-for-cf-settings', 'edit.php')) ?>" id="settingsForm">
					<input type="hidden" id="dp_current_tab" name="current_tab" value="setup" />
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce()); ?>" />
                    <?php wp_referer_field(); ?>
				</form>


				<table class="form-table" id="cf-app_settings">

					<tr class="group_setup tab_content">
						<th scope="row"><?php esc_html_e('API token', 'app-for-cf');?></th>
						<td>
							<?php printf('<a class="button button-primary alignright" href="%1$s" target="_blank"><span class="dashicons dashicons-rest-api"></span>%2$s</a>',
								@$appForCloudflareOptions['cloudflareAuth']['token'] ? esc_url('https://dash.cloudflare.com/profile/api-tokens') : esc_url('https://dash.cloudflare.com/profile/api-tokens?permissionGroupKeys=%5B%7B%22key%22%3A%22access%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22access_acct%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22account_analytics%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22analytics%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22billing%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22request_tracer%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22intel%22%2C%22type%22%3A%22read%22%7D%2C%7B%22key%22%3A%22bot_management%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22cache%22%2C%22type%22%3A%22purge%22%7D%2C%7B%22key%22%3A%22cache_settings%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22challenge_widgets%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22firewall_services%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22page_rules%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22ssl_and_certificates%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22workers_r2%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22workers_scripts%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone_settings%22%2C%22type%22%3A%22edit%22%7D%2C%7B%22key%22%3A%22zone_waf%22%2C%22type%22%3A%22edit%22%7D%5D&name=WordPress'),
								esc_html__('API tokens', 'app-for-cf')
							); ?>

							<?php printf('%s (<a href="%s" target="_blank">%s</a>):', esc_html__('Create a token for the zones in your network with the following permissions', 'app-for-cf'), esc_url(APP_FOR_CLOUDFLARE_PRODUCT_URL . 'threads/permissions-needed-for-app-for-cloudflare%C2%AE.3/?utm_source=permissions_network&utm_medium=wordpress&utm_campaign=plugin'), esc_html__('why', 'app-for-cf')); ?>
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

							<input type="text" name="app_for_cf[cloudflareAuth][token]" id="cf-app_cloudflareAuth_token"
							       form="settingsForm"
							       class="hide_value"
							       style="width: 90%;"
							       placeholder="<?php esc_html_e('API token', 'app-for-cf');?>"
							       value="<?php echo esc_attr(@$appForCloudflareOptions['cloudflareAuth']['token']); ?>"/>
							<div class="explain"><?php
								/* translators: %1$s = Zone Token ID (from Cloudflare account) */
								(!empty($appForCloudflareOptions['cfTokenId']) ? printf(esc_html__('Token ID: %1$s', 'app-for-cf'), esc_html($appForCloudflareOptions['cfTokenId'])) : '');?></div>

							<?php esc_html_e('If the sites in your network use top-level domains that are all on the same Cloudflare account, this allows the site owners/admins to manage the Cloudflare settings for their zone from within their WordPress admin area.', 'app-for-cf'); ?>
						</td>
					</tr>

					<tr class="group_setup tab_content">
						<td></td>
						<td>
							<?php submit_button(null, 'primary', 'submit', true, ['form' => 'settingsForm']); ?>
						</td>
					</tr>

				</table>
			</div>
		</div>

		<?php
	}
}