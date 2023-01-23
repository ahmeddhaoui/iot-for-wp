<?php
/**
 * Fired during plugin activation
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

class Activation {


	/**
	 * Active function fired on plugin activation to create custom database & flush rewrite rules
	 */
	public static function activate(): void {
		$database_manger = new Database();
		$database_manger->create_plugin_db_tables();
		flush_rewrite_rules();
	}

}
