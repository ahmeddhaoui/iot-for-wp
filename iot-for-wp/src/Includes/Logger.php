<?php
/**
 * The file Class Logger for writing logs to Database
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;
// phpcs:ignoreFile
class Logger {


	public static function log( string $type, string $message ): void {
		$settings = get_option( 'iot_for_wp_settings_option_name' ) ?? array();

		if ( ! count( $settings ) ) {
			return;
		}

		$save_to_db          = isset( $settings['save_logs_status'] );
		$notification_status = isset( $settings['notification_status'] );
		$user_email          = '' !== $settings['email_for_notifications'] ? $settings['email_for_notifications'] : get_bloginfo( 'admin_email' );

		if ( true === WP_DEBUG ) {
			error_log( $message );
		}

		if ( $save_to_db ) {
			Database::save_log_to_db( $type, $message );
		}

		if ( $notification_status ) {
			self::notify_user( $user_email, $message );
		}
	}

	public static function notify_user( string $user_email, string $message ): void {
		wp_mail(
			$user_email,
			__( 'IoT For WP Notification', 'iot-for-wp' ),
			$message
		);
	}
}
