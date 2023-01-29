<?php
/**
 * Class Connector
 *
 * @package    Iot_For_Wp
 */

// phpcs:ignoreFile
namespace IOT4WP\Repository;

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;

class Connector
{
    public static string $option_last_run_time_name = 'iot_last_run';

    public function __construct()
    {
        add_action('wp_ajax_test_connections', array($this, 'test_connections'));
    }

    public static function get_protocol_object_by_protocol_class_name(string $protocol_class_name, int $broker_id, array $connection_settings): object
    {
        $protocol_class_name = str_replace('_', '\\', $protocol_class_name);
        return call_user_func($protocol_class_name . '::get_instance', $broker_id, $connection_settings);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function test_connections(): void
    {
        $post_data = wp_unslash($_POST);

        if (!wp_verify_nonce($post_data['nonce'], 'test_connections')) {
            echo 'Invalid Action';
            die;
        }

        $protocol_object = self::get_protocol_object_by_protocol_class_name($post_data['protocol'], (int)$post_data['post_id'], $post_data['broker_data']);
        $response = $protocol_object->connect();

        if ($response) {
            echo 'Connection success';
            $protocol_object->disconnect();
        } else {
            echo 'Connection Failed';
        }
        die;
    }

    public function check_start_subscribe(): void
    {
        $last_run_time = get_option(self::$option_last_run_time_name);

        if (time() - $last_run_time > 600) { //cron was not running
            update_option(SubscribeRepository::$subscribe_list_option, [], false);
            TopicsRepository::reset_all_subscriptions();
            sleep(10);//wait after reset
        }

        $subscribe_list = get_option(SubscribeRepository::$subscribe_list_option) ?? [];
        update_option(self::$option_last_run_time_name, time(), false);

        if (!count($subscribe_list)) {
            return;
        }

        $topic_id = array_keys($subscribe_list)[0];
        SubscribeRepository::remove_topic_id_from_list($topic_id);

        $subscriber = SubscribeRepository::get_instance(get_the_title($topic_id));
        $subscriber->start_subscribe($topic_id);
    }

    public static function publish_message(int $topic_id, string $message): void
    {
        $topic_path = get_post_meta($topic_id, TopicCustomPostType::$topic_meta_topic_path, true);
		$broker_id                    = (int) get_post_meta( $topic_id, TopicCustomPostType::$topic_meta_broker_id, true );
		$broker_status                = BrokerRepository::is_broker_active( $broker_id );
		$protocol_name                = get_post_meta( $broker_id, BrokerCustomPostType::$broker_protocol_meta_key, true );
		$protocol_connection_settings = get_post_meta( $broker_id, BrokerCustomPostType::$broker_protocol_settings_meta_key, true );
		$protocol_object              = Connector::get_protocol_object_by_protocol_class_name( $protocol_name, $broker_id, $protocol_connection_settings );

        if(!$broker_status){
            return;
        }
        ob_start();
        if($protocol_object->connect()){
            $protocol_object->publish($topic_path, $message);
        }
        ob_clean();
    }
}
