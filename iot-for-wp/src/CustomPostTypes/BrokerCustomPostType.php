<?php
/**
 * Broker Custom Broker
 *
 * @package    Iot_For_Wp
 */

namespace IOT4WP\CustomPostTypes;

use IOT4WP\Includes\AdminSettings;
use IOT4WP\Includes\IotForWp;

class BrokerCustomPostType {


	public static string $custom_post_name                  = 'iot_broker';
	public static string $broker_status_meta_key            = 'broker_status';
	public static string $broker_protocol_meta_key          = 'broker_protocol';
	public static string $broker_protocol_settings_meta_key = 'broker_protocol_settings';

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_broker_custom_post_type' ) );
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'save_post_' . self::$custom_post_name, array( $this, 'save_broker_meta_data' ) );
	}

	public static function register_broker_custom_post_type(): void {
		$labels = array(
			'name'               => _x( 'Brokers', 'Broker General Name', 'iot-for-wp' ),
			'singular_name'      => _x( 'Broker', 'Broker Singular Name', 'iot-for-wp' ),
			'all_items'          => __( 'Broker', 'iot-for-wp' ),
			'add_new_item'       => __( 'Add New Broker', 'iot-for-wp' ),
			'add_new'            => __( 'Add New', 'iot-for-wp' ),
			'edit_item'          => __( 'Edit Broker', 'iot-for-wp' ),
			'update_item'        => __( 'Update Broker', 'iot-for-wp' ),
			'view_item'          => __( 'View Broker', 'iot-for-wp' ),
			'view_items'         => __( 'View Brokers', 'iot-for-wp' ),
			'search_items'       => __( 'Search Broker', 'iot-for-wp' ),
			'not_found'          => __( 'Not found', 'iot-for-wp' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'iot-for-wp' ),
		);
		$args   = array(
			'label'               => __( 'IoT Broker', 'iot-for-wp' ),
			'description'         => __( 'IoT Broker with custom IoT Protocol and Connection Settings', 'iot-for-wp' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 1,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capabilty'           => AdminSettings::$plugin_user_capability,
		);
		register_post_type( self::$custom_post_name, $args );
	}

	public function add_submenu_page(): void {
		add_submenu_page(
			AdminSettings::$iot_for_wp_menu_slug,
			__( 'Broker' ),
			__( 'IoT Broker' ),
			'manage_options',
			'edit.php?post_type=' . self::$custom_post_name
		);

		add_meta_box(
			'broker_data',
			__( 'Broker Settings' ),
			array( $this, 'add_broker_meta_box_data' ),
			self::$custom_post_name
		);
	}

	public function add_broker_meta_box_data(): void {
		$iot_protocols = IotForWp::$protocols;
		$protocols     = array();
		$post_id       = get_the_ID();

		$broker_status            = get_post_meta( $post_id, self::$broker_status_meta_key, true ) === '1' ? 'checked' : '';
		$broker_protocol          = get_post_meta( $post_id, self::$broker_protocol_meta_key, true );
		$broker_protocol_settings = get_post_meta( $post_id, self::$broker_protocol_settings_meta_key, true );

		foreach ( $iot_protocols as $protocol ) {
			$protocols[ str_replace( '\\', '_', $protocol ) ] = array(
				'title'    => $protocol::get_protocol_name(),
				'settings' => $protocol::get_protocol_settings_fields(),
			);
		}

		?>
		<div class="row">
			<h3 style="display: inline;">Status</h3>
			<input type="checkbox" name="broker_status" <?php echo esc_attr( $broker_status ); ?>>
		</div>

		<div class="row">
			<h3>Protocol</h3>
			<select style="width: 100%" name="broker_protocol" id="broker_protocol">
				<?php
				foreach ( $protocols as $key => $data ) :
					$selected = $key === $broker_protocol ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $data['title'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php foreach ( $protocols as $id => $data ) : ?>
		<div id="<?php echo esc_attr( $id ); ?>">
			<h3><?php echo esc_attr( $data['title'] . ' Settings' ); ?></h3>
			<p><?php esc_attr_e( '* required', 'iot-for-wp' ); ?></p>
			<table class="form-table">
				<?php
				foreach ( $data['settings'] as $key => $setting ) :
					$title         = $setting['title'];
					$title        .= $setting['required'] ? __( '*', 'iot-for-wp' ) : '';
					$default_value = $setting['value'] ?? '';
					$value         = $broker_protocol_settings[ $key ] ?? $default_value;
					$required      = isset( $setting['readonly'] ) && $setting['readonly'] ? 'disabled' : '';
					?>
					<tr>
						<?php
						echo sprintf(
							'<th><h4 style="display: inline;">%s</h4></th> <td><input style="" class="regular-text" name=%s type="%s" value="%s" %s autocomplete="new-password" data-key="%s" placeholder="%s" %s></td>',
							esc_attr( $title ),
							wp_json_encode( $id . '[' . $key . ']' ),
							esc_attr( $setting['type'] ),
							esc_attr( $value ),
							esc_attr( true === $setting['required'] ? 'required' : '' ),
							esc_attr( $key ),
							esc_attr( $setting['placeholder'] ?? '' ),
							esc_attr( $required )
						);
						?>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<div class="broker-buttons" style="margin-top: 50px; float: right;">
			<button class="button button-secondary" type="button" id="broker-test-connection">Test Connection</button>
		</div>
	<?php endforeach; ?>
		<?php
		wp_nonce_field( 'test_connections', 'nonce' );
	}

	/**
	 * Save Broker Meta Data
	 *
	 * @SuppressWarnings(PHPMD)
	 * @param int $post_id
	 */
	public function save_broker_meta_data( int $post_id ): void {
		$post_data = wp_unslash( $_POST );

		if ( ! isset( $post_data['broker_protocol'] ) || ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'test_connections' ) ) {
			return;
		}

		$post_data       = wp_unslash( $_POST );
		$broker_status   = isset( $post_data['broker_status'] ) && 'on' === $post_data['broker_status'] ?: 0;
		$broker_protocol = $post_data['broker_protocol'];
		$broker_data     = $post_data[ $broker_protocol ];

		update_post_meta( $post_id, self::$broker_status_meta_key, $broker_status );
		update_post_meta( $post_id, self::$broker_protocol_meta_key, $broker_protocol );
		update_post_meta( $post_id, self::$broker_protocol_settings_meta_key, $broker_data );
	}
}
