<?php
/**
 * MQTT Protocol
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\Protocols;

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

	public function __construct( array $broker_settings ) {
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

	public function get_connection_settings( $broker_settings ): ConnectionSettings {
		$connection_settings = new ConnectionSettings();
		$connection_settings
			->setUsername( $broker_settings['user'] )
			->setPassword( $broker_settings['password'] )
			->setKeepAliveInterval( $broker_settings['keep-alive-interval'] )
			->setReconnectAutomatically( false )
			->setMaxReconnectAttempts( 0 );
		return $connection_settings;
	}

	public function get_mqtt_client( $broker_settings ): MqttClient {
		return new MqttClient( $broker_settings['server'], $broker_settings['port'], $broker_settings['clientID'] );
	}

	public function connect(): bool {
		if ( ! $this->mqtt_client->isConnected() ) {
			try {
				$this->mqtt_client->connect( $this->mqtt_connection_settings );
			} catch ( ConfigurationInvalidException | ConnectingToBrokerFailedException $e ) {
				if ( true === WP_DEBUG ) {
                    // phpcs:disable WordPress.PHP.DevelopmentFunctions
					error_log( print_r( $e->getMessage(), true ) );
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

	public function subscribe( string $topic, callable $callback, bool $loop ): void {
		$this->mqtt_client->subscribe( $topic, $callback );

		if ( $loop ) {
			$this->mqtt_client->loop();
		}
	}

	public function unsubscribe( string $topic ): void {
		$this->mqtt_client->unsubscribe( $topic );
	}
}
