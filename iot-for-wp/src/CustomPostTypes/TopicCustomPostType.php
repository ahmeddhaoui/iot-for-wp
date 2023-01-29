<?php
/**
 * Topic Custom Post Type
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\CustomPostTypes;

use IOT4WP\Includes\AdminSettings;
use IOT4WP\Repository\BrokerRepository;
use IOT4WP\Repository\SubscribeRepository;
use WP_Post;

class TopicCustomPostType {


	public static string $custom_post_name                = 'iot_topics';
	public static string $topic_meta_broker_id            = 'iot_topic_broker';
	public static string $topic_meta_topic_path           = 'iot_topic_path';
	public static string $topic_meta_topic_subscribe_data = 'iot_topic_subscribe_data';
	public static string $topic_meta_topic_publish_data   = 'iot_topic_publish_data';
	public static string $topic_meta_subscribe_status     = 'iot_topic_subscribe_status';
	public static string $topic_meta_publish_status       = 'iot_topic_publish_status';

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_iot_topics_custom_post_type' ) );
		add_action( 'admin_menu', array( $this, 'add_topic_submenu_page' ) );
		add_action( 'save_post_' . self::$custom_post_name, array( $this, 'save_topic_meta_data' ) );
	}

	public static function register_iot_topics_custom_post_type(): void {
		$labels = array(
			'name'               => _x( 'Topics', 'Topic General Name', 'iot-for-wp' ),
			'singular_name'      => _x( 'Topic', 'Topic Singular Name', 'iot-for-wp' ),
			'all_items'          => __( 'Topic', 'iot-for-wp' ),
			'add_new_item'       => __( 'Add New Topic', 'iot-for-wp' ),
			'add_new'            => __( 'Add New', 'iot-for-wp' ),
			'edit_item'          => __( 'Edit Topic', 'iot-for-wp' ),
			'update_item'        => __( 'Update Topic', 'iot-for-wp' ),
			'view_item'          => __( 'View Topic', 'iot-for-wp' ),
			'view_items'         => __( 'View Topics', 'iot-for-wp' ),
			'search_items'       => __( 'Search Topic', 'iot-for-wp' ),
			'not_found'          => __( 'Not found', 'iot-for-wp' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'iot-for-wp' ),
		);
		$args   = array(
			'label'               => __( 'IoT Topic', 'iot-for-wp' ),
			'description'         => __( 'IoT Topic Config for IoT subscribe and publish', 'iot-for-wp' ),
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

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function add_topic_submenu_page(): void {
		add_submenu_page(
			AdminSettings::$iot_for_wp_menu_slug,
			__( 'Topics' ),
			__( 'IoT Topics' ),
			'manage_options',
			'edit.php?post_type=' . self::$custom_post_name
		);

		$brokers = BrokerRepository::get_all_brokers();

		if ( ! count( $brokers ) ) {
			add_meta_box(
				'no_broker',
				__( 'Error no Broker found!', 'iot-for-wp' ),
				array( $this, 'render_no_broker_message' ),
				self::$custom_post_name
			);
			return;
		}

		add_meta_box(
			'topic_data',
			__( 'Topic & Connection Settings', 'iot-for-wp' ),
			array( $this, 'add_topic_broker_settings' ),
			self::$custom_post_name,
			'advanced',
			'default',
			$brokers
		);

		add_meta_box(
			'topic_subscribe_data',
			__( 'Subscribe Settings & Actions', 'iot-for-wp' ),
			array( $this, 'add_topic_subscribe_settings' ),
			self::$custom_post_name
		);

		add_meta_box(
			'topic_publish_data',
			__( 'Publish Settings & Actions', 'iot-for-wp' ),
			array( $this, 'add_topic_publish_settings' ),
			self::$custom_post_name
		);
	}

	public function render_no_broker_message(): void {
		echo esc_html(
			printf(
				'You should add at least a Broker to config Topics. You can add and manage Broker <a href="%s">here</a>',
				home_url() . '/wp-admin/edit.php?post_type=iot_broker'
			)
		);
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function add_topic_broker_settings( WP_Post $post, array $data ): void {
		$broker_id        = get_post_meta( $post->ID, self::$topic_meta_broker_id, true );
		$topic_path       = get_post_meta( $post->ID, self::$topic_meta_topic_path, true );
		$subscribe_status = get_post_meta( $post->ID, self::$topic_meta_subscribe_status, true );
		$publish_status   = get_post_meta( $post->ID, self::$topic_meta_publish_status, true );

		?>
		<div class="row">
			<table class="form-table">
				<tbody>
				<tr>
					<th><h4><?php echo esc_attr_e( 'Select Broker', 'iot-for-wp' ); ?></h4></th>
					<td>
						<select style="width: 100%" name="topic[broker_id]" required>
							<?php foreach ( $data['args'] as $broker ) : ?>
								<option value="<?php echo esc_attr( $broker->ID ); ?>" <?php selected( $broker_id, $broker->ID ); ?>><?php echo esc_attr( $broker->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><h4><?php echo esc_attr_e( 'Topic (Please define the Topic to use', 'iot-for-wp' ); ?></h4></th>
					<td><input class="large-text" type="text" name="topic[path]" placeholder="sensor/temperatur"
							   value="<?php echo esc_attr( $topic_path ); ?>" required></td>
				</tr>
				<tr>
					<th>
						<label for="topic_subscribe"><?php esc_attr_e( 'Active Subscribe to Topic', 'iot-for-wp' ); ?></label>
					</th>
					<td><input id="topic_subscribe" type="checkbox"
							   name="topic[topic_subscribe][status]" <?php echo esc_attr( $subscribe_status ? 'checked' : '' ); ?>>
					</td>
				</tr>
				<tr>
					<th>
						<label for="topic_publish"><?php echo esc_attr_e( 'Active Publish to Topic', 'iot-for-wp' ); ?></label>
					</th>
					<td><input id="topic_publish" type="checkbox"
							   name="topic[topic_publish][status]"<?php echo esc_attr( $publish_status ? 'checked' : '' ); ?>>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function add_topic_subscribe_settings( WP_Post $post ): void {
		$format_settings      = array(
			'plaintext' => array(
				__( 'Extract certain value using Regex.', 'iot-for-wp' ),
				__( 'Example: /^[0-9]+$/', 'iot-for-wp' ),
			),
			'json'      => array(
				__( 'Save only certain values from JSON', 'iot-for-wp' ),
				__( 'keys separated with semicolon like key1;key2...', 'iot-for-wp' ),
			),
			'xml'       => array(
				__( 'Save only certain values from XML', 'iot-for-wp' ),
				__( 'Node Names separated with semicolon like key1;key2...', 'iot-for-wp' ),
			),
		);
		$post_types           = get_post_types( array( 'public' => true ), 'object' );
		$posts                = get_posts(
			array(
				'post_type'      => array_keys( $post_types ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);
		$topic_subscribe_data = get_post_meta( $post->ID, self::$topic_meta_topic_subscribe_data, true );
		?>
		<div class="row">
			<h3><?php esc_attr_e( 'Data Format Settings', 'iot-for-wp' ); ?></h3>
			<table class="form-table">
				<tbody>
				<tr>
					<th><?php echo esc_attr_e( 'Format incoming data', 'iot-for-wp' ); ?></th>
					<td>
						<select id="data-input-format-select" name="topic[topic_subscribe][data_input_format]">
							<option value="plaintext" <?php selected( $topic_subscribe_data['data_input_format'], 'plaintext' ); ?>><?php echo esc_attr_e( 'Plain Text', 'iot-for-wp' ); ?></option>
							<option value="json"<?php selected( $topic_subscribe_data['data_input_format'], 'json' ); ?>><?php echo esc_attr_e( 'JSON', 'iot-for-wp' ); ?></option>
							<option value="xml" <?php selected( $topic_subscribe_data['data_input_format'], 'xml' ); ?>><?php echo esc_attr_e( 'XML', 'iot-for-wp' ); ?></option>
						</select>
					</td>
					<?php
					foreach ( $format_settings as $key => $format_setting ) :
						$random_id = uniqid();
						?>
						<th style="display:none;"
							class="data-input-format format-config-<?php echo esc_attr( $key ); ?>"><label
									for="id-<?php echo esc_attr( $random_id ); ?>"><?php echo esc_attr( $format_setting[0] ); ?></label>
						</th>
						<td style="display:none;" class="data-input-format format-config-<?php echo esc_attr( $key ); ?>">
							<input type="checkbox" name="topic[topic_subscribe][<?php echo esc_attr( $key ); ?>][option]"
								   id="id-<?php echo esc_attr( $random_id ); ?>"
								<?php echo isset( $topic_subscribe_data[ $key ]['option'] ) ? 'checked' : ''; ?>
							>
						</td>
						<td style="display:none;" class="data-input-format format-config-<?php echo esc_attr( $key ); ?>">
							<input width="100%" class="regular-text <?php echo esc_attr( $key ); ?>" type="text"
								   name="topic[topic_subscribe][<?php echo esc_attr( $key ); ?>][field]"
								   value="<?php echo esc_attr( $topic_subscribe_data[ $key ]['field'] ); ?>"
								   placeholder="<?php echo esc_attr( $format_setting['1'] ); ?>"
							>
						</td>
						<?php
					endforeach;
					?>
				</tr>
				<tr>
					<th>
						<label for="save_to_db_only_on_change"><?php echo esc_attr_e( 'Save Value to Database only if value changed?', 'iot-for-wp' ); ?></label>
					</th>
					<td><input id="save_to_db_only_on_change" name="topic[topic_subscribe][save_to_db_only_on_change]"
							   type="checkbox" <?php echo isset( $topic_subscribe_data['save_to_db_only_on_change'] ) ? 'checked' : ''; ?>>
					</td>
					<td>
						<button type="button" class="button-secondary"
								data-topic-id="<?php the_ID(); ?>" disabled><?php esc_attr_e( 'Delete All Topic Subscribe Data', 'iot-for-wp' ); ?> </button>
					</td>
				</tr>
				</tbody>
			</table>
			<hr>
			<h3><?php echo esc_attr_e( 'WordPress Actions', 'iot-for-wp' ); ?></h3>
			<p><strong>Use placeholder {{data}}</strong> for your Content. Example: for Regex can use data index
				like<strong>{{data[0]}}</strong>
				for Json and XML format: <strong>{{data[your-key]}}</strong>.</p>
			<table class="subscribe-action-table">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'Status', 'iot-for-wp' ); ?></th>
					<th><?php esc_attr_e( 'Action', 'iot-for-wp' ); ?></th>
					<th><?php esc_attr_e( 'Data', 'iot-for-wp' ); ?></th>
					<th></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
				</tr>
				</tfoot>
				<tbody>
				<tr>
					<td>
						<input name="topic[topic_subscribe][action_status]"
							   type="checkbox" <?php echo isset( $topic_subscribe_data['action_status'] ) ? 'checked' : ''; ?>>
					</td>
					<td>
						<select id="subscribe_action_select" name="topic[topic_subscribe][subscribe_actions][type]">
							<option value="new_post" <?php selected( $topic_subscribe_data['subscribe_actions']['type'], 'new_post' ); ?>>
								<?php esc_attr_e( 'Create new Post', 'iot-for-wp' ); ?>
							</option>
							<option value="update_post" <?php selected( $topic_subscribe_data['subscribe_actions']['type'], 'update_post' ); ?>>
								<?php esc_attr_e( 'Update Post', 'iot-for-wp' ); ?>
							</option>
						</select>
					</td>
					<p><?php esc_attr_e( 'Blank fields will not be changed', 'iot-for-wp' ); ?></p>
					<td id="subscribe-data-col">
						<table style="width: 100%" id="new_post">
							<tr>
								<th><label>Post Type:</label></th>
								<td>
									<select name="topic[topic_subscribe][subscribe_actions][new_post][post_type]">
										<?php foreach ( $post_types as $key => $object ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>"
												<?php selected( $topic_subscribe_data['subscribe_actions']['new_post']['post_type'], $key ); ?>>
												<?php echo esc_attr( ucfirst( $object->name ) ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Post Title', 'iot-for-wp' ); ?></label></th>
								<td><input name="topic[topic_subscribe][subscribe_actions][new_post][post_title]"
										   class="regular-text large-text"
										   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['new_post']['post_title'] ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Post Content', 'iot-for-wp' ); ?></label></th>
								<td><textarea style="width: 100%"
											  name="topic[topic_subscribe][subscribe_actions][new_post][post_content]"
											  rows="10"
											  cols="80"><?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['new_post']['post_content'] ?? '' ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Custom Post Meta', 'iot-for-wp' ); ?></label></th>
								<td>
									<p>
										<input name="topic[topic_subscribe][subscribe_actions][new_post][meta_key]"
											   style="width: 33%" type="text" placeholder="post meta key"
											   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['new_post']['meta_key'] ?? '' ); ?>">
										<input name="topic[topic_subscribe][subscribe_actions][new_post][meta_value]"
											   style="width: 33%" type="text" placeholder="post meta value"
											   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['new_post']['meta_value'] ?? '' ); ?>">
									</p>
																	</td>
							</tr>
						</table>
						<table style="width: 100%" id="update_post">
							<tr>
								<th><label>Post: </label></th>
								<td>
									<select name="topic[topic_subscribe][subscribe_actions][update_post][post_id]">
										<?php foreach ( $posts as $post ) : ?>
											<option value="<?php echo esc_attr( $post->ID ); ?>"
												<?php selected( $topic_subscribe_data['subscribe_actions']['update_post']['post_id'], $post->ID ); ?>>
												<?php echo esc_attr( $post->post_title ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Post Title', 'iot-for-wp' ); ?></label></th>
								<td><input name="topic[topic_subscribe][subscribe_actions][update_post][post_title]"
										   class="regular-text large-text"
										   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['update_post']['post_title'] ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Post Content', 'iot-for-wp' ); ?></label></th>
								<td><textarea style="width: 100%"
											  name="topic[topic_subscribe][subscribe_actions][update_post][post_content]"
											  rows="10"
											  cols="80"><?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['update_post']['post_content'] ?? '' ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Custom Post Meta', 'iot-for-wp' ); ?></label></th>
								<td>
									<p id="
									<?php
									$cm_id = uniqid();
									echo esc_attr( 'cm-' . $cm_id );
									?>
									">
										<input name="topic[topic_subscribe][subscribe_actions][update_post][meta_key]"
											   style="width: 33%" type="text" placeholder="post meta key"
											   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['update_post']['meta_key'] ?? '' ); ?>">
										<input name="topic[topic_subscribe][subscribe_actions][update_post][meta_value]"
											   style="width: 33%" type="text" placeholder="post meta value"
											   value="<?php echo esc_attr( $topic_subscribe_data['subscribe_actions']['update_post']['meta_value'] ?? '' ); ?>"
										>
																			</p>
																										</td>
							</tr>
						</table>
					</td>
					<td>
						<button type="button" class="button-secondary delete-row"
								disabled><?php esc_attr_e( 'Delete', 'iot-for-wp' ); ?></button>
					</td>
				</tr>
				</tbody>
			</table>
			<br>
			<button type="button" class="button-secondary"
					disabled><?php esc_attr_e( 'Add new Action', 'iot-for-wp' ); ?></button>
		</div>
		<?php
	}

	public function add_topic_publish_settings( WP_Post $post ): void {
		$topic_publish_data = get_post_meta( $post->ID, self::$topic_meta_topic_publish_data, true );
		$post_types         = get_post_types( array( 'public' => true ), 'object' );
		$posts              = get_posts(
			array(
				'post_type'      => array_keys( $post_types ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);
		?>
		<div class="row">
			<h3><?php esc_attr_e( 'Publish Actions', 'iot-for-wp' ); ?></h3>
			<p><?php esc_attr_e( 'Configuration for Publish Message. ' ); ?></p>
			<table class="subscribe-action-table">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'Status', 'iot-for-wp' ); ?></th>
					<th><?php esc_attr_e( 'Action', 'iot-for-wp' ); ?></th>
					<th><?php esc_attr_e( 'Data', 'iot-for-wp' ); ?></th>
					<th></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
				</tr>
				</tfoot>
				<tbody>
				<tr>
					<td>
						<input name="topic[topic_publish][action_status]"
							   type="checkbox" <?php echo isset( $topic_publish_data['action_status'] ) ? 'checked' : ''; ?>>
					</td>
					<td>
						<select id="publish_action_select" name="topic[topic_publish][subscribe_actions][type]">
							<option value="new_to_publish" <?php selected( $topic_publish_data['subscribe_actions']['type'], 'new_to_publish' ); ?>>
								<?php esc_attr_e( 'New Post Created', 'iot-for-wp' ); ?>
							</option>
							<option value="post_updated" <?php selected( $topic_publish_data['subscribe_actions']['type'], 'post_updated' ); ?>>
								<?php esc_attr_e( 'Post updated', 'iot-for-wp' ); ?>
							</option>
						</select>
					</td>
					<p><?php esc_attr_e( 'Placeholder: {{post_id}}, {{post_title}}, {{post_content}}, to use meta value: {{meta_key}} ', 'iot-for-wp' ); ?></p>
					<td id="publish-data-col">
						<table style="width: 100%" id="new_to_publish">
							<tr>
								<th><label>Post Type:</label></th>
								<td>
									<select name="topic[topic_publish][subscribe_actions][new_to_publish][post_type]">
										<?php foreach ( $post_types as $key => $object ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>"
												<?php selected( $topic_publish_data['subscribe_actions']['new_to_publish']['post_type'], $key ); ?>>
												<?php echo esc_attr( ucfirst( $object->name ) ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Data to Publish', 'iot-for-wp' ); ?></label></th>
								<td><textarea style="width: 100%"
											  name="topic[topic_publish][subscribe_actions][new_to_publish][data]"
											  rows="10"
											  cols="80"><?php echo esc_attr( $topic_publish_data['subscribe_actions']['new_to_publish']['data'] ?? '' ); ?></textarea>
								</td>
							</tr>

						</table>
						<table style="width: 100%" id="post_updated">
							<tr>
								<th><label>Post: </label></th>
								<td>
									<select name="topic[topic_publish][subscribe_actions][post_updated][post_id]">
										<?php foreach ( $posts as $post ) : ?>
											<option value="<?php echo esc_attr( $post->ID ); ?>"
												<?php selected( $topic_publish_data['subscribe_actions']['post_updated']['post_id'], $post->ID ); ?>>
												<?php echo esc_attr( $post->post_title ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_attr_e( 'Data to Publish', 'iot-for-wp' ); ?></label></th>
								<td><textarea style="width: 100%"
											  name="topic[topic_publish][subscribe_actions][post_updated][data]"
											  rows="10"
											  cols="80"><?php echo esc_attr( $topic_publish_data['subscribe_actions']['post_updated']['data'] ?? '' ); ?></textarea>
								</td>
							</tr>
						</table>
					</td>
					<td>
					</td>
				</tr>
				</tbody>
			</table>
			<br>
			<button type="button" class="button-secondary"
					disabled><?php esc_attr_e( 'Add new Action', 'iot-for-wp' ); ?></button>
		</div>
		<?php
		wp_nonce_field( 'save_topics', 'nonce' );
	}

	/**
	 * Save Topic Meta Data
	 *
	 * @SuppressWarnings(PHPMD)
	 * @param int $post_id
	 */
	public function save_topic_meta_data( int $post_id ): void {
		$post_data = wp_unslash( $_POST );

		if ( ! isset( $post_data['topic'] ) || wp_verify_nonce( 'nonce', 'save_topics' ) ) {
			return;
		}

		$subscribe_status = isset( $post_data['topic']['topic_subscribe']['status'] ) && 'on' === $post_data['topic']['topic_subscribe']['status'] ?: 0;
		$publish_status   = isset( $post_data['topic']['topic_publish']['status'] ) && 'on' === $post_data['topic']['topic_publish']['status'] ?: 0;
		update_post_meta( $post_id, self::$topic_meta_broker_id, $post_data['topic']['broker_id'] ?? '' );
		update_post_meta( $post_id, self::$topic_meta_topic_path, $post_data['topic']['path'] ?? '' );
		update_post_meta( $post_id, self::$topic_meta_topic_subscribe_data, $post_data['topic']['topic_subscribe'] ?? '' );
		update_post_meta( $post_id, self::$topic_meta_topic_publish_data, $post_data['topic']['topic_publish'] ?? '' );
		update_post_meta( $post_id, self::$topic_meta_subscribe_status, $subscribe_status );
		update_post_meta( $post_id, self::$topic_meta_publish_status, $publish_status );

		if ( $subscribe_status ) {
			SubscribeRepository::add_topic_id_to_list( $post_id );
		} else {
			SubscribeRepository::remove_topic_id_from_list( $post_id );
		}
	}
}
