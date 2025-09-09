<?php
/**
 * App for Cloudflare® plugin.
 *
 * @package   DigitalPoint\Cloudflare
 * @copyright Copyright (C) 2022-2025, Digital Point - app-help@appforcf.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: App for Cloudflare®
 * Version:     1.9.5
 * Plugin URI:  https://appforcf.com/?utm_source=uri&utm_medium=wordpress&utm_campaign=plugin
 * Description: Allows you to manage your Cloudflare account/zone from within WordPress. Options to do most everything (control settings, caching of HTML pages and static assets, protect admin area with Zero Trust Network Access, store media in the cloud with R2, rule management, firewall management, DMARC management, view analytics, etc).
 * Author:      Digital Point
 * Author URI:  https://appforcf.com/?utm_source=author&utm_medium=wordpress&utm_campaign=plugin
 * Text Domain: app-for-cf
 * License:     GPL v3
 * Requires at least: 5.2
 * Requires PHP: 5.4.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined( 'ABSPATH')) exit;

define('APP_FOR_CLOUDFLARE_VERSION', '1.9.5');
define('APP_FOR_CLOUDFLARE_MINIMUM_WP_VERSION', '5.2');  // Late static binding in PHP 5.3 and traits require PHP 5.4.  See:  https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
define('APP_FOR_CLOUDFLARE_PRODUCT_URL', 'https://appforcf.com/');
define('APP_FOR_CLOUDFLARE_PRO_PRODUCT_URL', 'https://appforcf.com/items/app-for-cloudflare%C2%AE-pro.1/');
define('APP_FOR_CLOUDFLARE_SUPPORT_URL', 'https://appforcf.com/support/questions-and-support.2/');

define('APP_FOR_CLOUDFLARE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APP_FOR_CLOUDFLARE_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once APP_FOR_CLOUDFLARE_PLUGIN_DIR . '/src/DigitalPoint/Cloudflare/Base/Pub.php';

$publicClass = 'DigitalPoint\Cloudflare\Base\Pub';

spl_autoload_register([$publicClass, 'autoload']);

register_activation_hook( __FILE__, [$publicClass, 'plugin_activation']);
register_deactivation_hook( __FILE__, [$publicClass, 'plugin_deactivation']);

$publicClass::getInstance();

if (is_admin())
{
	DigitalPoint\Cloudflare\Base\Admin::getInstance();
}