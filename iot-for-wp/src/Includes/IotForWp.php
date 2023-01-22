<?php
/**
 * The file that defines the core plugin class
 *
 * @package    Iot_For_Wp
 */

declare( strict_types=1 );

namespace IOT4WP\Includes;

class IotForWp {

	protected string $plugin_name;
	protected string $version;

	public function __construct() {
		$this->version     = '1.0.0';
		$this->plugin_name = 'iot-for-wp';
	}

	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	public function get_version(): string {
		return $this->version;
	}
}
