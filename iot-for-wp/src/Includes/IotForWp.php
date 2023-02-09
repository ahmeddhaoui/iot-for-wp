<?php
/**
 * The file that defines the core plugin class
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

use IOT4WP\Repository\Connector;
use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;
use IOT4WP\Protocols\MqttProtocol;
use IOT4WP\Repository\PublishRepository;

class IotForWp {


	public static string $plugin_name = 'iot-for-wp';
	public static string $version     = '1.0.0';
	public static array $protocols;

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'load_classes' ), 0 );
		add_action( 'init', array( __CLASS__, 'register_protocols' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_app_scripts' ) );
	}

	public static function load_classes(): void {
		new Database();
		new AdminSettings();
		new BrokerCustomPostType();
		new TopicCustomPostType();
		new Connector();
		new Cron();
		new PublishRepository();
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
			$admin_style_path = IOT_FOR_WP_PLUGIN_URI . 'assets/admin.min.css';
		}

		wp_register_style( 'iot4wp-admin-style', $admin_style_path, false, self::$version );
		wp_enqueue_style( 'iot4wp-admin-style' );

		wp_localize_script(
			'iot4wp-admin-script',
			'iot4wp_admin_script',
			array(
				'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
				'handel_settings_action_nonce' => wp_create_nonce( 'handel_settings_action_nonce' ),
				'test_connections'             => wp_create_nonce( 'test_connections' ),
				'save_topics'                  => wp_create_nonce( 'save_topics' ),
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

	public static function register_protocols(): void {
		self::$protocols = array( MqttProtocol::class );
	}
}
