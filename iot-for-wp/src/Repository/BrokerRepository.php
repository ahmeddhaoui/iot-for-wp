<?php
/**
 * Repository Broker
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Repository;

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use WP_Post;

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
}
