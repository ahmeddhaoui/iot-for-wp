<?php
/**
 * The file that defines the core plugin class
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

class IotForWp {


	public static string $plugin_name = 'iot-for-wp';
	public static string $version     = '1.0.0';

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'load_classes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_app_scripts' ) );
	}

	public static function load_classes(): void {
		new Database();
		new AdminSettings();
	}

	public function load_admin_scripts(): void {
		$admin_script_path = IOT_FOR_WP_PLUGIN_URI . 'frontend/js/admin.js';

		if ( file_exists( IOT_FOR_WP_PLUGIN_PATH . 'assets/admin.min.js' ) ) {
			$admin_script_path = IOT_FOR_WP_PLUGIN_URI . 'assets/admin.min.js';
		}

		wp_register_script( 'iot4wp-admin-script', $admin_script_path, array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'iot4wp-admin-script' );

		$admin_style_path = IOT_FOR_WP_PLUGIN_URI . 'frontend/css/admin.css';

		if ( file_exists( IOT_FOR_WP_PLUGIN_PATH . 'assets/admin.min.css' ) ) {
			$admin_style_path = IOT_FOR_WP_PLUGIN_URI . 'assets/admin.min.js';
		}

		wp_register_style( 'iot4wp-admin-style', $admin_style_path, false, self::$version );
		wp_enqueue_style( 'iot4wp-admin-style' );

		wp_localize_script(
			'iot4wp-admin-script',
			'iot4wp_admin_script',
			array(
				'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
				'handel_settings_action_nonce' => wp_create_nonce( 'handel_settings_action_nonce' ),
			)
		);
	}

	public function load_app_scripts(): void {
		$admin_script_path = IOT_FOR_WP_PLUGIN_URI . 'frontend/js/app.js';

		if ( file_exists( IOT_FOR_WP_PLUGIN_PATH . 'assets/app.min.js' ) ) {
			$admin_script_path = IOT_FOR_WP_PLUGIN_URI . 'assets/app.min.js';
		}

		wp_register_script( 'iot4wp-app-script', $admin_script_path, array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'iot4wp-app-script' );

		$admin_style_path = IOT_FOR_WP_PLUGIN_URI . 'frontend/css/app.css';

		if ( file_exists( IOT_FOR_WP_PLUGIN_PATH . 'assets/app.min.css' ) ) {
			$admin_style_path = IOT_FOR_WP_PLUGIN_URI . 'assets/app.min.js';
		}

		wp_register_style( 'iot4wp-app-style', $admin_style_path, false, self::$version );
		wp_enqueue_style( 'iot4wp-app-style' );
	}
}
