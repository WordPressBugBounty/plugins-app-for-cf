<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
class R2 extends AbstractTemplate
{
	protected function template()
	{
		$this->addAsset('js');
		$this->addAsset('css');

		echo '<div class="wrap r2" id="app-for-cf_settings">
				<h2>' . esc_html__('Cloudflare object storage (R2)', 'app-for-cf') . '</h2>
				
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
			</div>
		</aside>

		<?php

		/* translators: %1$s = <a href...>, %2$s = </a>, %3$s = URL */
		echo sprintf(esc_html__('This allows you to utilize a %1$scloud-based R2%2$s bucket for the media uploads for your site (giving you the ability to offload storage space and network bandwidth from your servers).', 'app-for-cf'), '<a href="' . esc_url_raw('https://blog.cloudflare.com/introducing-r2-object-storage/') . '" target="_blank">', '</a>') . '<br /><br />' .

		'<b>' . esc_html__('Requirements:', 'app-for-cf') . '</b><ul style="list-style:disc;padding-left:15px;">
                <li>' .
					/* translators: %1$s = <a href...>, %2$s = </a>, %3$s = URL */
					sprintf(esc_html__('Needs a %1$sCloudflare API token%2$s', 'app-for-cf'), '<a href="' . esc_url_raw(add_query_arg(['page' => 'app-for-cf'], admin_url('options-general.php'))) . '">', '</a>') .
			(empty(get_option('app_for_cf')['cloudflareAuth']['token']) ? ' <span class="dashicons dashicons-dismiss" style="color:orangered;"></span>' : ' <span class="dashicons dashicons-yes-alt" style="color:green;"></span>') .
			'</li>
                <li>' . /* translators: %1$s = <a href...>, %2$s = </a> */
                    sprintf(esc_html__('Needs an %1$sApp for Cloudflare® Pro%2$s license', 'app-for-cf'), '<a href="' . esc_url_raw(APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL) . '?utm_source=admin_settings_r2&utm_medium=wordpress&utm_campaign=plugin' . '" target="_blank">', '</a>') . ' <span class="dashicons dashicons-dismiss" style="color:orangered;"></span></li>
            </ul>';

		echo '<b>' . esc_html__('Benefits:', 'app-for-cf') . '</b><ul style="list-style:disc;padding-left:15px;">
                <li>' . esc_html__('Your WordPress media (images, video, audio, PDF, etc.) are stored in Cloudflare\'s data centers around the world.', 'app-for-cf') . '
                	<ul style="list-style:disc;padding-left:15px;margin-top:6px;">
                		<li>' . esc_html__('The first 10GB stored is free.', 'app-for-cf') . '</li>
                		<li>' . esc_html__('Your site will be faster (media is cacheable so site visitors only need to fetch from the closest data center to them).', 'app-for-cf') . '</li>
                		<li>' . esc_html__('You don\'t need to worry about backing up your media (media is stored in multiple data centers, not on your server).', 'app-for-cf') . '</li>
					</ul>
                </li>
                <li>' . esc_html__('Easy/automatic setup process that allows you to create (or select an existing) bucket.', 'app-for-cf') . '</li>
                <li>' . esc_html__('Uses the standard Media interface for WordPress.', 'app-for-cf') . '</li>
                <li>' . esc_html__('Saves your server resources (your server doesn\'t need to store media on disk or use bandwidth when they are requested).', 'app-for-cf') . '</li>
                <li>' . esc_html__('Show basic R2 analytics and logs.', 'app-for-cf') . '</li>
            </ul>';

		echo '</div></div>';

	}

}