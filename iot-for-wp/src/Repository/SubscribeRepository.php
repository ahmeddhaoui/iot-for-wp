<?php

declare(strict_types=1);

namespace IOT4WP\Repository;

use Exception;
use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;
use IOT4WP\Includes\Database;
use IOT4WP\Includes\Logger;
use function IOT4WP\Includes\Logger;

// phpcs:ignoreFile
class SubscribeRepository
{

    private static $instance = null;
    private string $topic;

    public static function get_instance(string $topic)
    {
        if (null === self::$instance[$topic]) {
            self::$instance[$topic] = new self($topic);
        }

        return self::$instance[$topic];
    }

    private function __construct($topic)
    {
        $this->topic = $topic;
    }

    public static string $subscribe_list_option = 'iot_subscribe_ids';

    public static function add_topic_id_to_list(int $topic_id): void
    {
        $subscribe_list = get_option(self::$subscribe_list_option) ?? array();

        if (isset($subscribe_list[$topic_id])) {
            return;
        }

        $subscribe_list[$topic_id] = time();
        update_option(self::$subscribe_list_option, $subscribe_list, false);
    }

    public static function remove_topic_id_from_list(int $topic_id): void
    {
        $subscribe_list = get_option(self::$subscribe_list_option) ?? array();

        if (!isset($subscribe_list[$topic_id])) {
            return;
        }

        unset($subscribe_list[$topic_id]);
        update_option(self::$subscribe_list_option, $subscribe_list, false);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function start_subscribe(int $topic_id): void
    {
        $topic_subscription_status = TopicsRepository::is_topic_subscribed($topic_id);
        $broker_id = (int)get_post_meta($topic_id, TopicCustomPostType::$topic_meta_broker_id, true);
        $broker_status = BrokerRepository::is_broker_active($broker_id);
        $protocol_name = get_post_meta($broker_id, BrokerCustomPostType::$broker_protocol_meta_key, true);
        $protocol_connection_settings = get_post_meta($broker_id, BrokerCustomPostType::$broker_protocol_settings_meta_key, true);
        $protocol_object = Connector::get_protocol_object_by_protocol_class_name($protocol_name, $broker_id, $protocol_connection_settings);
        $topic_subscribe_config = get_post_meta($topic_id, TopicCustomPostType::$topic_meta_topic_subscribe_data, true);

        if ($topic_subscription_status || !$broker_status) {
            return; // nothing to do: Broker is disabled!
        }

        try {
            if (!$protocol_object->connect()) {
                return;
            }
            global $wpdb;
            $topic_path = get_post_meta($topic_id, TopicCustomPostType::$topic_meta_topic_path, true);

            $protocol_object->register_loop_event_handler(
                function ($mqtt_client) use ($topic_id, $broker_id, $wpdb) {
                    if (!TopicsRepository::get_subscribe_status($topic_id) || !BrokerRepository::is_broker_active($broker_id)) {
                        $mqtt_client->interrupt();
                        update_post_meta($topic_id, TopicsRepository::$is_subscribed_meta_key, '0');
                        update_post_meta($topic_id, TopicCustomPostType::$topic_meta_subscribe_status, '0');
                    }
                }
            );

            $protocol_object->subscribe(
                $topic_path,
                function ($topic_path, $message) use ($topic_id, $wpdb, $topic_subscribe_config) {
                    update_post_meta($topic_id, TopicsRepository::$is_subscribed_meta_key, '1');
                    // check if Message Content changed
                    if (TopicsRepository::get_last_message_by_topic_id($topic_id) === $message && isset($topic_subscribe_config['save_to_db_only_on_change']) && $topic_subscribe_config['save_to_db_only_on_change'] === 'on') {
                        return;
                    }

                    // Format Data
                    $data_format = $topic_subscribe_config['data_input_format'];
                    $formated_data = '';
                    $message_array = array();

					if ( isset( $topic_subscribe_config[ $data_format ]['option'] ) && $topic_subscribe_config[ $data_format ]['option'] === 'on' && $topic_subscribe_config[ $data_format ]['field'] !== '' ) {
						$format = $topic_subscribe_config[ $data_format ]['field'];
						if ( $data_format === 'plaintext' ) {
							preg_match_all( $format, $message, $matches);
                            if(count($matches)){
                                foreach ($matches[0] as $key => $match) {
                                    $message_array['data['.$key.']'] = $match;
                                    $formated_data = serialize($message_array);
                                }
                            }
						}

                        if ($data_format === 'json' || $data_format === 'xml') {

                            $data = $message;

                            if ($data_format === 'json') {
                                $message_array = json_decode($data, true);
                            }

                            if ($data_format === 'xml') {
                                $message_array = json_decode(json_encode((array)simplexml_load_string($message)), true);
                            }

                            if (is_array($message_array)) {
                                $keys = explode(';', $format);
                                foreach ($keys as $key) {
                                    if (key_exists($key, $message_array)) {
                                        $formated_data .= $message_array[$key];
                                    }
                                }
                            }
                        }
                    }

                    // Save Data to IoT DB
                    $wpdb->insert(
                        $wpdb->prefix . Database::$iot_data_table_name,
                        array(
                            'id' => 'NULL',
                            'post_id' => $topic_id,
                            'created_date' => wp_date('Y-m-d H:i:s'),
                            'topic' => $topic_path,
                            'raw_data' => $message,
                            'formated_data' => $formated_data !== '' ? json_encode($formated_data) : $message,
                        )
                    );

                    // Do WordPress Action
                    if (isset($topic_subscribe_config['action_status']) && $topic_subscribe_config['action_status'] === 'on') {
                        $action_type = $topic_subscribe_config['subscribe_actions']['type'];
                        $action_data = $topic_subscribe_config['subscribe_actions'][$action_type];
                        $meta_key = $action_data['meta_key'] ?? '';
                        $meta_value = $action_data['meta_value'] ?? '';
                        unset($action_data['meta_key']);
                        unset($action_data['meta_value']);
                        $post_id = null;

                        if (isset($action_data['post_id']) && $action_data['post_id'] !== '') {
                            $post_id = $action_data['post_id'];
                            unset($action_data['post_id']);
                        }


                        $args = array();
                        foreach ($action_data as $key => $value) {
                            if ($value !== '') {
                                if (count($message_array)) {
                                    preg_match_all('/(?<=\{{).+?(?=\}})/', $value, $keys_in_content);
                                    if (count($keys_in_content)) {
                                        foreach ($keys_in_content[0] as $item) {
                                             if (isset($message_array[$item])) {
                                                $value = str_replace('{{' . $item . '}}', $message_array[$item], $value);
                                            }
                                        }
                                    }
                                }
                                $args[$key] = str_replace('{{message}}', $message, $value);
                            }
                        }

                        $args['ID'] = $post_id;
                        $args['post_status'] = 'publish';
                        $saved_post_id = wp_insert_post($args);

                        if ($meta_value !== '' && $meta_key !== '') {
                            update_post_meta($saved_post_id, $meta_key, $meta_value);
                        }
                    }

                },
                1,
                0
            );

        } catch (Exception $e) {
            Logger::log('Error', 'Subscribe to Topic: '. $topic_path . ' on Server '. get_the_title($broker_id). ' Failed! Error Message: '. $e->getMessage());
        }
    }
}
