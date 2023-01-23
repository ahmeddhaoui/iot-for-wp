<?php
/**
 * The plugin bootstrap file
 *
 * @since             1.0.0
 * @package           Iot_For_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       IoT for WP
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       IOT for WordPress is a Plugin to connect IOT Objects with WordPress and communicate with them throw protocols like MQTT.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Ahmed Dhaoui
 * Author URI:        https://techda.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       iot-for-wp
 * Domain Path:       /languages
 */

declare(strict_types=1);

use IOT4WP\Includes\Activation;
use IOT4WP\Includes\Deactivation;
use IOT4WP\Includes\IotForWp;

defined('ABSPATH') or exit();

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

define('IOT_FOR_WP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('IOT_FOR_WP_PLUGIN_URI', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, function (){
	Activation::activate();
});

register_deactivation_hook(__FILE__, function (){
	Deactivation::deactivate();
});

if(class_exists( IotForWp::class)){
	new IotForWp();
}

