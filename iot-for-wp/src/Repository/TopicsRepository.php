<?php
/**
 * Topic Repository
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Repository;

use IOT4WP\CustomPostTypes\TopicCustomPostType;
use WP_Post;

// phpcs:ignoreFile
class TopicsRepository {

	public static string $is_subscribed_meta_key = 'is_topic_subscribed';

	/**
     * @return WP_Post[]
	 */
	public static function get_all_topics( int $active_subscribe = 0, int $active_publish = 0 ): array {
		$args = array(
			'post_type'      => TopicCustomPostType::$custom_post_name,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$subscribe_meta_query = array(
			'key'     => TopicCustomPostType::$topic_meta_subscribe_status,
			'value'   => '1',
			'compare' => '==',
		);

		$publish_meta_query = array(
			'key'     => TopicCustomPostType::$topic_meta_publish_status,
			'value'   => '1',
			'compare' => '==',
		);

		if ( $active_publish && $active_subscribe ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				$subscribe_meta_query,
				$publish_meta_query,
			);
		}

		if ( $active_subscribe ) {
			$args['meta_query'] = array( $subscribe_meta_query );
		}

		if ( $active_publish ) {
			$args['meta_query'] = array( $publish_meta_query );
		}

		return get_posts( $args );
	}

	public static function is_topic_subscribed( int $topic_id ): bool {
		return get_post_meta( $topic_id, self::$is_subscribed_meta_key, true );
	}

	public static function get_subscribe_status( int $post_id ): bool {
		global $wpdb;
		$sql          = $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %s and meta_key = '%s';",
			$post_id,
			TopicCustomPostType::$topic_meta_subscribe_status
		);
		$query_result = $wpdb->get_results( $sql, OBJECT );

		return (int) $query_result[0]->meta_value;
	}

	public static function get_last_message_by_topic_id( int $topic_id ): string {
		global $wpdb;
		$sql      = $wpdb->prepare( "SELECT raw_data FROM `{$wpdb->prefix}iot_data`  WHERE post_id = %s ORDER BY `created_date` DESC;", $topic_id );
		$response = $wpdb->get_results( $sql, ARRAY_A );
		$message  = '';
		if ( isset( $response[0]['raw_data'] ) && $wpdb->last_error === '' ) {
			$message = $response[0]['raw_data'];
		}
		return $message;
	}

    public static function reset_all_subscriptions(): void
    {
        $topics = self::get_all_topics(1);

        foreach ($topics as $topic) {
            update_post_meta($topic->ID, TopicCustomPostType::$topic_meta_subscribe_status, '0');
            update_post_meta($topic->ID, self::$is_subscribed_meta_key, '0');
            sleep(2);
            update_post_meta($topic->ID, TopicCustomPostType::$topic_meta_subscribe_status, '1');
            SubscribeRepository::add_topic_id_to_list($topic->ID);
        }
	}

}
