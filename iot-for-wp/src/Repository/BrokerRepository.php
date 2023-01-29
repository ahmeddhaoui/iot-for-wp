<?php
/**
 * Repository Broker
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Repository;

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;
use WP_Post;

// phpcs:ignoreFile
class BrokerRepository {

	/**
	 * @return WP_Post[]
	 */
	public static function get_all_brokers(): array {
		return get_posts(
			array(
				'post_type'      => BrokerCustomPostType::$custom_post_name,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);
	}

	public static function is_broker_active( int $broker_id ): bool {
		global $wpdb;
		$sql          = $wpdb->prepare("SELECT meta_value FROM %s WHERE post_id = %s and meta_key = %s;",
			$wpdb->postmeta,
			$broker_id,
			BrokerCustomPostType::$broker_status_meta_key
		);
		$query_result = $wpdb->get_results( $sql, OBJECT );

		return (int) $query_result[0]->meta_value;
	}
}
