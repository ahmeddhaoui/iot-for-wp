<?php
/**
 * MQTT Protocol
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Protocols;

use IOT4WP\Includes\Logger;
use PhpMqtt\Client\Exceptions\ConfigurationInvalidException;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

/**
 * MQTT Protocol Functions
 */
class MqttProtocol extends AbstractIotProtocol {


	protected MqttClient $mqtt_client;
	protected ConnectionSettings $mqtt_connection_settings;
	protected array $broker_settings;
	private static $instance = null;

	public static function get_instance( int $broker_id, array $broker_settings ) {
		if ( null === self::$instance[ $broker_id ] ) {
			self::$instance[ $broker_id ] = new self( $broker_settings );
		}

		return self::$instance[ $broker_id ];
	}

	private function __construct( array $broker_settings ) {
		$this->broker_settings          = $broker_settings;
		$this->mqtt_client              = $this->get_mqtt_client( $broker_settings );
		$this->mqtt_connection_settings = $this->get_connection_settings( $broker_settings );
	}

	public static function get_protocol_name(): string {
		return 'MQTT';
	}

	public static function get_protocol_settings_fields(): array {
		return array(
			'server'              => array(
				'title'    => 'Server',
				'type'     => 'text',
				'required' => true,
			),
			'user'                => array(
				'title'    => 'User',
				'type'     => 'text',
				'required' => true,
			),
			'password'            => array(
				'title'    => 'Password',
				'type'     => 'password',
				'required' => true,
			),
			'port'                => array(
				'title'    => 'Port',
				'type'     => 'text',
				'required' => true,
			),
			'clientID'            => array(
				'title'    => 'ClientID',
				'type'     => 'text',
				'required' => false,
			),
			'keep-alive-interval' => array(
				'title'    => 'Keep alive Intervall',
				'type'     => 'text',
				'required' => false,
			),
			'last-will-topic'     => array(
				'title'    => 'Last Will Topic',
				'type'     => 'text',
				'required' => false,
			),
			'last-will-message'   => array(
				'title'    => 'Last Will Message',
				'type'     => 'text',
				'required' => false,
			),
		);
	}

	public function get_connection_settings( array $broker_settings ): ConnectionSettings {
		$connection_settings = new ConnectionSettings();
		$connection_settings
			->setUsername( $broker_settings['user'] )
			->setPassword( $broker_settings['password'] )
			->setKeepAliveInterval( 10 )
			->setReconnectAutomatically( true )
			->setLastWillMessage( '0' )
			->setMaxReconnectAttempts( 0 );
		return $connection_settings;
	}

	public function get_mqtt_client( $broker_settings ): MqttClient {
		return new MqttClient( $broker_settings['server'], $broker_settings['port'], wp_rand() );
	}

	public function connect(): bool {
		if ( ! $this->mqtt_client->isConnected() ) {
			try {
				$this->mqtt_client->connect( $this->mqtt_connection_settings );
			} catch ( ConfigurationInvalidException | ConnectingToBrokerFailedException $e ) {
				if ( true === WP_DEBUG ) {
                    // phpcs:disable WordPress.PHP.DevelopmentFunctions
					Logger::log( 'Error: ', 'Cant Connect for Server ' . $this->broker_settings['server'] . ' with Message: ' . $e->getMessage() );
                    // phpcs:enable
				}
				return false;
			}
		}
		return $this->mqtt_client->isConnected();
	}

	public function disconnect(): void {
		$this->mqtt_client->disconnect();
	}

	public function publish( string $topic, string $message ): void {
		$this->mqtt_client->publish( $topic, $message );
	}

	public function subscribe( string $topic, callable $callback, $loop, $quality_of_service ): void {
		$this->mqtt_client->subscribe( $topic, $callback, $quality_of_service );

		if ( $loop ) {
			$this->mqtt_client->loop();
		}
	}

	public function unsubscribe( string $topic ): void {
		$this->mqtt_client->unsubscribe( $topic );
	}

	public function is_connected(): bool {
		return $this->mqtt_client->isConnected();
	}

	public function register_loop_event_handler( callable $callback ): void {
		$this->mqtt_client->registerLoopEventHandler( $callback );
	}
}
