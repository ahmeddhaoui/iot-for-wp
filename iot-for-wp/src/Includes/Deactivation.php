<?php
/**
 * The file that defines deactivation functions
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\Repository\BrokerRepository;

class Deactivation {
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public static function deactivate( $is_uninstall = false ): void {
		 wp_clear_scheduled_hook( 'iot_check_subscribe' );
		 wp_clear_scheduled_hook( 'iot_check_daily_data_delete' );

		 $brokers        = BrokerRepository::get_all_brokers();
		 $active_brokers = array();

		foreach ( $brokers as $broker ) {
			if ( get_post_meta( $broker->ID, BrokerCustomPostType::$broker_status_meta_key, true ) ) {
				$active_brokers[] = $broker->ID;
			}

			update_post_meta( $broker->ID, BrokerCustomPostType::$broker_status_meta_key, '0' );
		}

		if ( ! $is_uninstall ) {
			update_option( 'iot_active_brokers', $active_brokers, false );
		}

		 flush_rewrite_rules();
	}
}
