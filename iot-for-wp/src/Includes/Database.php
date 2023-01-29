<?php
/**
 * Plugin Database Functions
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;
// phpcs:ignoreFile
class Database {


	public static string $iot_data_table_name = 'iot_data';
	public static string $iot_logs_table_name = 'iot_logs';

	public function __construct() {
		add_action( 'wp_ajax_handel_settings_action', array( $this, 'handel_settings_action' ) );
	}

	/**
	 * Create Custom DB Tables
	 */
	public function create_plugin_db_tables(): string {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql_iot_data = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}" . self::$iot_data_table_name . "` (
                    id bigint NOT NULL AUTO_INCREMENT KEY,
                    post_id bigint NOT NULL,
                    created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    topic LONGTEXT NOT NULL,
                    raw_data LONGTEXT,
                    formated_data LONGTEXT
                    ) $charset_collate;";

		$sql_iot_logs = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}" . self::$iot_logs_table_name . "` (
                    id bigint NOT NULL AUTO_INCREMENT KEY,
                    type varchar(250),
                    created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    message LONGTEXT
                    ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_iot_data );
		dbDelta( $sql_iot_logs );

		return $wpdb->last_error;
	}

	public function delete_plugin_table_data( string $table_name ): string {
		global $wpdb;
		$table = $wpdb->prefix . sanitize_text_field($table_name);
		$sql = "TRUNCATE TABLE {$table}";
		$wpdb->query($sql);
		return $wpdb->last_error;
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function handel_settings_action(): void {

		$post_data = wp_unslash( $_POST );

		if ( ! wp_verify_nonce( $post_data['nonce'], 'handel_settings_action_nonce' ) || ! isset( $post_data['target_function'] ) || '' === $post_data['target_function'] || ! in_array( $post_data['target_function'], array( 'check_db_tables', 'delete_all_iot_data', 'delete_all_logs' ) ) ) {
			echo 'Invalid Action!';
			die();
		}

		$target        = sanitize_text_field( $post_data['target_function'] );
		$response_data = '';

		switch ( $target ) {
			case 'check_db_tables':
				$db_response   = $this->create_plugin_db_tables();
				$response_data = __( 'Database Tables checked & repaired!', 'iot-for-wp' );
				if ( '' !== $db_response ) {
					$response_data = $db_response;
				}
				break;
			case 'delete_all_iot_data':
				$db_response   = $this->delete_plugin_table_data( self::$iot_data_table_name );
				$response_data = __( 'IoT Data deleted successfully.', 'iot-for-wp' );
				if ( '' !== $db_response ) {
					$response_data = $db_response;
				}
				break;
			case 'delete_all_logs':
				$db_response   = $this->delete_plugin_table_data( self::$iot_logs_table_name );
				$response_data = __( 'Logs deleted successfully.', 'iot-for-wp' );
				if ( '' !== $db_response ) {
					$response_data = $db_response;
				}
				break;
		}

		echo esc_attr( $response_data );
		exit();
	}
}
