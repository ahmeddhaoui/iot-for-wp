<?php
/**
 * Fired during plugin activation
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

// phpcs:ignoreFile
class Activation {

	/**
	 * Active function fired on plugin activation to create custom database & flush rewrite rules
	 */
	public static function activate(): void {
		$database_manger = new Database();
		$database_manger->create_plugin_db_tables();

		add_filter( 'cron_schedules', array( new Cron(), 'add_cron_schedules' ) );
		if ( ! wp_next_scheduled( 'iot_check_subscribe' ) ) {
			wp_schedule_event( time(), 'iot_60sec', 'iot_check_subscribe' );
		}

		flush_rewrite_rules();
	}

}
