<?php
/**
 * Register Admin SettingsPage
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

class AdminSettings {


	public static string $iot_for_wp_menu_slug = 'iot-for-wp-settings';
	public string $iot_for_wp_settings_option_name;
	public array $iot_for_wp_settings_options;
	public array $setting_fields;
	protected string $settings_group;
	protected string $setting_page;
	protected string $setting_section;
	public static string $plugin_user_capability;

	public function __construct() {
		$this->iot_for_wp_settings_option_name = 'iot_for_wp_settings_option_name';
		$this->iot_for_wp_settings_options     = get_option( $this->iot_for_wp_settings_option_name ) ?: array();
		$this->settings_group                  = 'iot_for_wp_settings_option_group';
		$this->setting_page                    = 'iot-for-wp-settings-admin';
		$this->setting_section                 = 'iot_for_wp_settings_setting_section';
		$this->setting_fields                  = $this->init_setting_fields();
		$capability                            = $this->iot_for_wp_settings_options['plugin_management'];

		if ( empty( $capability ) ) {
			$capability = 'manage_options';
		}
		self::$plugin_user_capability = $capability;

		add_action( 'admin_menu', array( $this, 'add_plugin_menu_page' ) );
		add_action( 'admin_init', array( $this, 'iot_for_wp_settings_page_init' ) );
	}

	public function add_plugin_menu_page(): void {
		add_menu_page(
			'IoT for WP',
			'IoT for WP',
			self::$plugin_user_capability,
			self::$iot_for_wp_menu_slug,
			array( $this, 'render_admin_menu_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMzgzLjAwMDAwMHB0IiBoZWlnaHQ9IjIxNi4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDM4My4wMDAwMDAgMjE2LjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMjE2LjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKZmlsbD0iIzAwMDAwMCIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTEwIDEwODUgbDAgLTEwNjUgMTYwIDAgMTYwIDAgMCAxMDY1IDAgMTA2NSAtMTYwIDAgLTE2MCAwIDAgLTEwNjV6Ii8+CjxwYXRoIGQ9Ik0yMzQwIDIwMTAgbDAgLTE0MCAyOTAgMCAyOTAgMCAwIC05MjUgMCAtOTI1IDE2MCAwIDE2MCAwIDAgOTI1IDAKOTI1IDI5MCAwIDI5MCAwIDAgMTQwIDAgMTQwIC03NDAgMCAtNzQwIDAgMCAtMTQweiIvPgo8cGF0aCBkPSJNMTQwNyAxNjY5IGMtMzMxIC00NSAtNjEwIC0yOTUgLTY5OCAtNjI1IC0yOSAtMTExIC0yOSAtMjg4IDAgLTM5OQo2NCAtMjQxIDIyNCAtNDM3IDQ0NiAtNTQ2IDEyNiAtNjEgMTg3IC03NCAzNjAgLTc0IDE3NyAwIDIzNiAxMyAzNzAgODAgMjAzCjEwMiAzNDggMjcxIDQyMiA0OTAgMjQgNzMgMjcgOTQgMjcgMjQwIDEgMTg0IC0xMCAyNDIgLTczIDM3MCAtMTM0IDI3MyAtMzk3CjQ0OSAtNzAxIDQ2OCAtNDcgMyAtMTE2IDEgLTE1MyAtNHogbTIxOSAtMzAgYzMxNiAtNDQgNTgxIC0yODEgNjY1IC01OTUgMTYKLTYwIDIxIC0xMDggMjEgLTE5OSAwIC05MSAtNSAtMTM5IC0yMSAtMTk5IC03NiAtMjgzIC0yOTQgLTUwMiAtNTc2IC01NzcKLTExMSAtMzAgLTI4OCAtMzAgLTM5OSAwIC0zMjEgODYgLTU1NSAzNTMgLTU5NyA2ODIgLTQ3IDM2NyAxODYgNzM0IDU0NCA4NTQKMTI5IDQzIDIzMSA1MyAzNjMgMzR6Ii8+CjxwYXRoIGQ9Ik0xNDE1IDE1NTEgYy0xMTcgLTE5IC0yMzIgLTY4IC0zMjcgLTEzOCAtNTAgLTM2IC0xNTggLTE1MCAtMTU4Ci0xNjYgMCAtNiA0OCAtNyAxMjUgLTIgMTEzIDYgMTI1IDUgMTMyIC0xMCAxMSAtMjkgLTExIC01MCAtNTYgLTUzIC0yMyAtMgotNDEgLTggLTQxIC0xNSAwIC03IDU1IC0xNzUgMTIyIC0zNzUgOTYgLTI4NiAxMjQgLTM1OSAxMzIgLTM0NSA1IDEwIDQwIDExMAo3NyAyMjMgbDY2IDIwNSAtNDUgMTI1IGMtMjQgNjkgLTQ5IDEzNyAtNTQgMTUzIC04IDIzIC0xNiAyNyAtNDYgMjcgLTQ4IDAKLTY1IDEyIC02MCA0MSBsMyAyNCAyMTMgMCBjMjI3IDEgMjQ0IC0zIDIyMiAtNDQgLTggLTE2IC0yMSAtMjEgLTUwIC0yMSAtMzAKMCAtNDAgLTQgLTQwIC0xNiAwIC05IDU0IC0xNzYgMTIwIC0zNzIgNjYgLTE5NiAxMjAgLTM1OCAxMjAgLTM2MSAxIC0zIDI4IDgyCjYwIDE4OSA4MyAyNzIgODIgMzI3IC0xMiA0ODUgLTM0IDU3IC00MyA4MSAtNDYgMTMwIC00IDU1IC0xIDY0IDIzIDk0IDE1IDE3CjQxIDM3IDU3IDQ0IGwzMCAxMiAtNDQgMzIgYy02NSA0NyAtMTc0IDk5IC0yNDQgMTE3IC03NSAxOSAtMjEyIDI4IC0yNzkgMTd6Ii8+CjxwYXRoIGQ9Ik0yMTQ3IDEwNzMgYy02IC05NiAtOSAtMTA2IC0xNjEgLTU0NSAtNTMgLTE1MyAtOTYgLTI4MCAtOTYgLTI4NCAwCi0zIDE3IDYgMzggMjEgMjU4IDE5MSAzNjIgNTIxIDI1NiA4MTggLTE1IDQwIC0yNyA3MyAtMjkgNzUgLTEgMSAtNSAtMzcgLTgKLTg1eiIvPgo8cGF0aCBkPSJNODM1IDEwNTggYy05MiAtMzA4IDIwIC02MjEgMjg5IC04MDcgMzMgLTIzIDYxIC0zOSA2MyAtMzcgMiAyIC02NAoxOTAgLTE0OCA0MTcgLTgzIDIyOCAtMTU2IDQyOSAtMTYyIDQ0NyAtNiAxNyAtMTQgMzIgLTE4IDMyIC01IDAgLTE1IC0yNCAtMjQKLTUyeiIvPgo8cGF0aCBkPSJNMTQyMyA0NjIgYy03MSAtMjA4IC05OCAtMjk5IC05MCAtMzA0IDM3IC0yMyAyMzUgLTI4IDMyNyAtOSA5MyAyMAo5MiA2IDEyIDIyMyAtMzkgMTA2IC04NSAyMzQgLTEwMiAyODMgLTE4IDUwIC0zNSA5MyAtMzkgOTcgLTMgNCAtNTIgLTEyNgotMTA4IC0yOTB6Ii8+CjwvZz4KPC9zdmc+Cg==',
		);

		add_submenu_page(
			self::$iot_for_wp_menu_slug,
			'Settings',
			'Settings',
			self::$plugin_user_capability,
			self::$iot_for_wp_menu_slug,
			array( $this, 'render_admin_menu_page' ),
		);
	}

	protected function init_setting_fields(): array {
		return array(
			'notification_status'      =>
				array(
					'title'    => __( 'Enable Notification', 'iot-for-wp' ),
					'callback' => 'render_check_box',

				),
			'email_for_notifications'  =>
				array(
					'title'    => __( 'Email for Notifications', 'iot-for-wp' ),
					'callback' => 'render_text_field',
				),
			'plugin_management'        =>
				array(
					'title'    => __( 'Who can manage Plugin(Broker & Topics Settings) ?', 'iot-for-wp' ),
					'callback' => 'plugin_management_callback',
				),
			'delete_iot_data_status'   =>
				array(
					'title'    => __( 'Delete IoT Data automatically?', 'iot-for-wp' ),
					'callback' => 'render_check_box',
				),
			'delete_iot_data_interval' =>
				array(
					'title'    => __( 'Delete IoT Data interval', 'iot-for-wp' ),
					'callback' => 'render_number_field',
				),
			'save_logs_status'         =>
				array(
					'title'    => __( 'Save logs to Database?', 'iot-for-wp' ),
					'callback' => 'render_check_box',
				),
			'delete_logs_status'       =>
				array(
					'title'    => __( 'Delete Logs automatically?', 'iot-for-wp' ),
					'callback' => 'render_check_box',
				),
			'delete_logs_interval'     =>
				array(
					'title'    => __( 'Delete Logs interval', 'iot-for-wp' ),
					'callback' => 'render_number_field',
				),
		);
	}

	public function render_admin_menu_page(): void {

		if ( ! current_user_can( self::$plugin_user_capability ) ) {
			wp_die( 'Unauthorized user' );
		}
		?>
		<div class="wrap">
			<h2>IoT for WP</h2>
			<p></p>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->settings_group );
				do_settings_sections( $this->setting_page );
				?>
				<div id="iot-settings-actions-btn">
					<button class="button button-secondary" type="button" value="check_db_tables"
							id="check-database"><?php echo esc_attr__( 'Check & Repair Database Tables', 'iot-for-wp' ); ?></button>
					<button class="button button-secondary" type="button" value="delete_all_iot_data"
							id="delete-iot-data"><?php echo esc_attr__( 'Delete all IoT Data', 'iot-for-wp' ); ?></button>
					<button class="button button-secondary" type="button" value="delete_all_logs"
							id=""><?php echo esc_attr__( 'Delete all Logs', 'iot-for-wp' ); ?></button>
				</div>
				<?php
				submit_button();
				?>
			</form>
		</div>
		<hr>
		<img src="<?php echo esc_attr( IOT_FOR_WP_PLUGIN_URI . '/frontend/img/logo-iot.png' ); ?>" width="auto" height="30px">
		<span>Version: <?php echo esc_attr( IotForWp::$version ); ?></span>
		<?php
	}

	public function iot_for_wp_settings_page_init(): void {
		register_setting(
			$this->settings_group,
			$this->iot_for_wp_settings_option_name,
			array( $this, 'iot_for_wp_settings_sanitize' )
		);

		add_settings_section(
			$this->setting_section,
			'Settings',
			'',
			$this->setting_page
		);

		foreach ( $this->setting_fields as $setting_field_id => $setting_field_data ) {
			add_settings_field(
				$setting_field_id,
				$setting_field_data['title'],
				array( $this, $setting_field_data['callback'] ),
				$this->setting_page,
				$this->setting_section,
				array(
					$setting_field_id,
				)
			);
		}
	}

	protected function iot_for_wp_settings_sanitize( array $inputs ): array {
		$sanitary_values = array();
		foreach ( $inputs as $key => $value ) {
			if ( is_string( $value ) ) {
				$value = sanitize_text_field( $value );
			}
			$sanitary_values[ $key ] = $value;

		}
		return $sanitary_values;
	}

	public function render_number_field( array $args ): void {
		$option_name = $args[0];
		printf(
			'<input name="iot_for_wp_settings_option_name[%1$s]" id="%1$s" value="%2$s" type="number" min="1" max="120"> Days',
			$option_name,
			isset( $this->iot_for_wp_settings_options[ $option_name ] ) ? esc_attr( $this->iot_for_wp_settings_options[ $option_name ] ) : '',
		);
	}

	public function render_text_field( array $args ): void {
		$option_name = $args[0];
		$class       = isset( $args[1] ) ?: 'regular-text';
		$type        = isset( $args[2] ) ?: 'text';
		printf(
			'<input name="iot_for_wp_settings_option_name[%1$s]" id="%1$s" value="%2$s" class="%3$s" type="%4$s">',
			$option_name,
			isset( $this->iot_for_wp_settings_options[ $option_name ] ) ? esc_attr( $this->iot_for_wp_settings_options[ $option_name ] ) : '',
			esc_attr( $class ),
			esc_attr( $type )
		);
	}

	public function render_check_box( array $args ): void {
		$option_name = $args[0];
		printf(
			'<input type="checkbox" name="iot_for_wp_settings_option_name[%1$s]" id="%1$s" value="%1$s" %2$s>',
			$option_name,
			( isset( $this->iot_for_wp_settings_options[ $option_name ] ) && $this->iot_for_wp_settings_options[ $option_name ] === $option_name ) ? 'checked' : ''
		);
	}

	public function plugin_management_callback(): void {
		?>
		<select name="iot_for_wp_settings_option_name[plugin_management]" id="plugin_management">
			<?php $selected = ( isset( $this->iot_for_wp_settings_options['plugin_management'] ) && 'manage_options' === $this->iot_for_wp_settings_options['plugin_management'] ) ? 'selected' : ''; ?>
			<option value="manage_options" <?php echo esc_attr( $selected ); ?>>Only Admin</option>
			<?php $selected = ( isset( $this->iot_for_wp_settings_options['plugin_management'] ) && 'edit_pages' === $this->iot_for_wp_settings_options['plugin_management'] ) ? 'selected' : ''; ?>
			<option value="edit_pages" <?php echo esc_attr( $selected ); ?>>Admin + Editor</option>
			<?php $selected = ( isset( $this->iot_for_wp_settings_options['plugin_management'] ) && 'edit_posts' === $this->iot_for_wp_settings_options['plugin_management'] ) ? 'selected' : ''; ?>
			<option value="edit_posts" <?php echo esc_attr( $selected ); ?>>Admin + Editor + Author</option>
		</select>
		<?php
	}

}
