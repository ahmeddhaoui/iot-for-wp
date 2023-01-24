<?php
/**
 * Class Connector
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Controller;

use ReflectionClass;

class Connector {

	public function __construct() {
		add_action( 'wp_ajax_test_connections', array( $this, 'test_connections' ) );
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function test_connections(): void {
		$post_data = wp_unslash( $_POST );

		if ( ! wp_verify_nonce( $post_data['nonce'], 'test_connections' ) ) {
			echo 'Invalid Action';
			die;
		}

		$protocol_class_name = str_replace( '_', '\\', $post_data['protocol'] );
		$protocol_class      = new ReflectionClass( $protocol_class_name );
		$protocol_object     = $protocol_class->newInstance( $post_data['broker_data'] );
		$response            = $protocol_object->connect();

		if ( $response ) {
			echo 'Connection success';
			$protocol_object->disconnect();
		} else {
			echo 'Connection Failed';
		}
		die;
	}
}
