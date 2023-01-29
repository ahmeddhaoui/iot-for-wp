<?php
/**
 * Class for custom cron jobs
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;
use IOT4WP\Repository\Connector;

// phpcs:ignoreFile
class Cron {

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'add_cron_job_actions' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedules' ) );
	}


	public static function add_cron_schedules( array $schedules ): array {
		if ( ! isset( $schedules['iot_60sec'] ) ) {
			$schedules['iot_60sec'] = array(
				'interval' => 60,
				'display'  => __( 'Once every 1 Minute' ),
			);
		}
		return $schedules;
	}

	public static function add_cron_job_actions(): void {
		$connector = new Connector();
		add_action( 'iot_check_subscribe', array( $connector, 'check_start_subscribe' ) );
	}
}
