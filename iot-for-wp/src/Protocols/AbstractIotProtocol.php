<?php
/**
 * Abstract Class for Protocols
 *
 * @package    Iot_For_Wp
 */

namespace IoT4WP\Protocols;

abstract class AbstractIotProtocol {

	abstract public static function get_protocol_name();
	abstract public static function get_protocol_settings_fields();
	abstract public function connect();
	abstract public function disconnect();
	abstract public function publish( string $topic, string $message);
	abstract public function subscribe( string $topic, callable $callback, $loop, $quality_of_service);
	abstract public function unsubscribe( string $topic);

}
