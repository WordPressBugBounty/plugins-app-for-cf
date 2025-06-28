<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class MultisiteR2 extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap r2" id="app-for-cf_settings">
				<h2>' . esc_html__('Network-wide Cloudflare object storage (R2) for media', 'app-for-cf') . '</h2>
				
				<div class="notice notice-warning">
				    <p>
				        '
			/* translators: %1$s = <strong>, %2$s = </strong> */
			. sprintf(esc_html__('Cloudflare R2 is a global object storage system that is designed to replace AWS S3 for many types of workloads (in the case of WordPress, R2 can be used to store images/attachments/media). There are no egress (bandwidth) fees and it\'s reasonably priced (%1$sfree for the first 10GB%2$s, $0.015 per GB after that).', 'app-for-cf'), '<strong>', '</strong>') . '
				    </p>
				</div>';
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
								esc_url_raw(APP_FOR_CLOUDFLARE_SUPPORT_URL . '?utm_source=admin_settings_network&utm_medium=wordpress&utm_campaign=plugin'),
								esc_html__('Support forum', 'app-for-cf')
							); ?>
						</div>
					</div>

					<div class="postbox pro" style="display:block;">
						<h4><?php esc_html_e('Extra Features In Premium Version', 'app-for-cf'); ?></h4>
						<div>
							<ul>
								<li>
									<?php esc_html_e('All the extra Pro features for the site/hostname the license it\'s assigned to.', 'app-for-cf'); ?>
								</li>
							</ul>
						</div>

						<h4><?php esc_html_e('Multisite Network Specific Features', 'app-for-cf'); ?></h4>
						<div>
							<ul>
								<li>
									<?php esc_html_e('A single Pro license for the primary site hostname can allow all sites in the network to store their uploaded media on a CDN in the cloud within a single R2 bucket.', 'app-for-cf'); ?>
								</li>
							</ul>
							<?php

							echo '<div style="text-align:center;"><a class="button-primary" href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL) . '?utm_source=admin_settings_network&utm_medium=wordpress&utm_campaign=plugin" target="_blank"><span aria-hidden="true"><span class="dashicons dashicons-money-alt"></span>' . esc_html__('Purchase Premium version', 'app-for-cf') . '</span></a></div>';

							?>
						</div>
					</div>

				</div>
			</aside>
        
			<?php

		echo esc_html__('This allows you to utilize a single cloud-based R2 bucket for the media uploads for all the sites in your network (giving you the ability to offload storage space and network bandwidth from your servers).', 'app-for-cf') .
			/* translators: %1$s = <strong>, %2$s = </strong>, %3$s = hostname of the primary site */
			'<p style="font-size:150%;">' . sprintf(esc_html__('This is a feature of App for Cloudflare® Pro. It requires a single license for the hostname of the primary site of your network (%1$s%3$s%2$s).', 'app-for-cf'), '<strong>', '</strong>', esc_html(wp_parse_url(network_home_url(), PHP_URL_HOST))) . '</p>';

		echo '<b>' . esc_html__('Features:', 'app-for-cf') . '</b><ul style="list-style:disc;padding-left:15px;">
                <li>' .
                        /* translators: %1$s = <a href...>, %2$s = </a>, %3$s = URL */
                        sprintf(esc_html__('Specify a Cloudflare API token under the %1$sNetwork Cloudflare settings%2$s that isn\'t shown to individual sites.', 'app-for-cf'), sprintf('<a href="%1$s">', esc_url_raw(add_query_arg(['page' => 'app-for-cf_multisite-settings'], network_admin_url('settings.php')))), '</a>') . '</li>
                <li>' . esc_html__('Allows a single R2 bucket to be chosen/created during setup.', 'app-for-cf') . '</li>
                <li>' . esc_html__('Show basic R2 analytics and logs.', 'app-for-cf') . '</li>
                <li>' . esc_html__('A single public domain for the R2 bucket gets used across all sites (for example it could be cdn.randomsite.com (the domain you use needs to be a zone on your Cloudflare account).', 'app-for-cf') . '</li>
                <li>' .
                        /* translators: %1$s = <strong>, %2$s = </strong>, %3$s = URL */
                        sprintf(esc_html__('Storing media in the bucket prepends {siteId} to the beginning of the image path. For example if it was site #50 and WordPress would have named something as `2023/10/image.png`, the final URL will look something like this: %1$s%3$s%2$s', 'app-for-cf'), '<strong>', '</strong>', esc_url('https://cdn.randomsite.com/50/2023/10/image.png')) . '
                </li>
            
            </ul>';

		echo '</div></div>';

	}

}