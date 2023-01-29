<?php
/**
 * Publish Repo to handle Push Events based on WordPress Actions
 *
 * @package Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Repository;

use IOT4WP\CustomPostTypes\TopicCustomPostType;

class PublishRepository {


	public function __construct() {
		add_action( 'post_updated', array( __CLASS__, 'handle_publish_actions' ), 99 );
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public static function handle_publish_actions( $post_id ): void {
		if ( ! is_int( $post_id ) ) {
			$post_id = $post_id->ID;
		}

		$topics            = TopicsRepository::get_all_topics( 0, 1 );
		$current_post_type = get_post_type( $post_id );

		if ( ! count( $topics ) || ! $post_id || ! $current_post_type ) {
			return;
		}

		foreach ( $topics as $topic ) {
			$topic_publish_data = get_post_meta( $topic->ID, TopicCustomPostType::$topic_meta_topic_publish_data, true );

			if ( ! isset( $topic_publish_data['action_status'] ) ) {
				continue;
			}

			$publish_action_type = $topic_publish_data['publish_actions']['type'];
			$action_data         = $topic_publish_data['publish_actions'][ $publish_action_type ];

			if ( '' === $action_data['data'] ) {
				return;
			}

			if ( 'new_to_publish' === $publish_action_type ) {
				self::topic_publish_new_post( $post_id, $current_post_type, $action_data, $topic->ID );
			} elseif ( 'post_updated' === $publish_action_type ) {
				self::topic_publish_update_post( $post_id, $current_post_type, $action_data, $topic->ID );
			}
		}
	}

	public static function topic_publish_new_post( int $post_id, string $current_post_type, array $data, int $topic_id ): void {
		$target_post_type = $data['post_type'];

		if ( $target_post_type !== $current_post_type ) {
			return;
		}

		self::create_message_to_publish( $data['data'], $post_id, $current_post_type, $topic_id );
	}

	public static function topic_publish_update_post( int $post_id, string $current_post_type, array $data, int $topic_id ): void {
		$post_id_target   = (int) $data['post_id'];
		$target_post_type = get_post_type( $post_id_target );

		if ( $target_post_type !== $current_post_type || $post_id_target !== $post_id ) {
			return;
		}

		self::create_message_to_publish( $data['data'], $post_id, $current_post_type, $topic_id );
	}

	private static function match_meta_keys( string $text ): array {
		preg_match_all( '/(?<=\{{).+?(?=\}})/', $text, $keys_in_content );
		return count( $keys_in_content ) ? $keys_in_content[0] : array();
	}


	public static function create_message_to_publish( $data, int $post_id, string $current_post_type, int $topic_id ): void {
		$publish_message = $data;
		$post_title      = get_the_title( $post_id );
		$post_content    = get_post_field( 'post_content', $post_id );

		$publish_message = str_replace(
			array( '{{post_id}}', '{{post_title}}', '{{post_content}}' ),
			array( $post_id, $post_title, $post_content ),
			$publish_message
		);

		$meta_keys = self::match_meta_keys( $publish_message );

		if ( count( $meta_keys ) ) {
			foreach ( $meta_keys as $meta_key ) {
				if ( metadata_exists( $current_post_type, $post_id, $meta_key ) ) {
					$value           = get_post_meta( $post_id, $meta_key, true );
					$publish_message = str_replace( '{{' . $meta_key . '}}', $value, $publish_message );
				}
			}
		}

		Connector::publish_message( $topic_id, $publish_message );
	}
}
