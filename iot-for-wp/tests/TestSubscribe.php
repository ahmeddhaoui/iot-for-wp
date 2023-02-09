<?php

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;
use IOT4WP\Includes\Database;
use PhpMqtt\Client\MqttClient;

class TestSubscribe extends \PHPUnit\Framework\TestCase
{

    protected string $test_topic = 'iot4wp/test';
    protected int $test_broker_id;
    protected int $test_topic_id;
    protected array $test_broker_settings;
    protected array $post_metas = [];

    public function setUp(): void
    {

        $this->test_broker_settings = $test_broker_settings = array(
            'server' => 'broker.mqttdashboard.com',
            'user' => 'iot4wp',
            'password' => '',
            'port' => '1883',
            'keep-alive-interval' => '60',
            'last-will-topic' => $this->test_topic,
            'last-will-message' => 'disconnected',
            'qos' => '0',
            'max-reconnect' => '3',
        );

        $this->topic_subscribe_test_data = array(
            'data_input_format' => 'plaintext',
            'plaintext' =>
                array(
                    'field' => '',
                ),
            'json' =>
                array(
                    'field' => '',
                ),
            'xml' =>
                array(
                    'field' => '',
                ),
            'subscribe_actions' =>
                array(
                    'type' => 'new_post',
                    'new_post' =>
                        array(
                            'post_type' => 'post',
                            'post_title' => '',
                            'post_content' => '',
                            'meta_key' => '',
                            'meta_value' => '',
                        ),
                    'update_post' =>
                        array(
                            'post_id' => '',
                            'post_title' => '',
                            'post_content' => '',
                            'meta_key' => '',
                            'meta_value' => '',
                        ),
                ),
        );

        //insert Broker
        $this->test_broker_id = $test_broker_id = wp_insert_post([
            'post_type' => BrokerCustomPostType::$custom_post_name,
            'post_title' => 'Test Broker',
            'post_status' => 'publish',
        ]);

        $this->post_metas[] = update_post_meta($test_broker_id, BrokerCustomPostType::$broker_status_meta_key, 1);
        $this->post_metas[] = update_post_meta($test_broker_id, BrokerCustomPostType::$broker_protocol_meta_key, 'IOT4WP_Protocols_MqttProtocol');
        $this->post_metas[] = update_post_meta($test_broker_id, BrokerCustomPostType::$broker_protocol_settings_meta_key, $test_broker_settings);

        //insert Topic
        $this->test_topic_id = $test_topic_id = wp_insert_post([
            'post_type' => TopicCustomPostType::$custom_post_name,
            'post_title' => $this->test_topic,
            'post_status' => 'publish',
        ]);

        $this->post_metas[] = update_post_meta($test_topic_id, TopicCustomPostType::$topic_meta_topic_path, $this->test_topic);
        $this->post_metas[] = update_post_meta($test_topic_id, TopicCustomPostType::$topic_meta_broker_id, $test_broker_id);
        $this->post_metas[] = update_post_meta($test_topic_id, TopicCustomPostType::$topic_meta_subscribe_status, '1');
        $this->post_metas[] = update_post_meta($test_topic_id, TopicCustomPostType::$topic_meta_topic_subscribe_data, $this->topic_subscribe_test_data);

        update_option('iot_subscribe_ids', [$test_topic_id]);
    }

    public function test_subscribe_function(): void
    {
        global $wpdb;
        $number_of_test_messages = rand(10, 50);
        $messages = [];
        $mqtt = new MqttClient($this->test_broker_settings['server'], (int)$this->test_broker_settings['port'], rand());
        $mqtt->connect();

        for ($i = 1; $i <= $number_of_test_messages; $i++) {
            $message = 'UnitTest Subscribe Message! TEST-' . $i;
            $mqtt->publish($this->test_topic, 'UnitTest Subscribe Message! TEST-' . $i,
                $this->test_broker_settings['qos']
            );
            $messages[] = $message;
        }

        $mqtt->disconnect();
        sleep(3);
        $count_incoming_messages = $wpdb->get_var(
            sprintf("SELECT count(*) from %s",
                $wpdb->prefix . Database::$iot_data_table_name,
                $this->test_topic_id,
            )
        );

        if ($count_incoming_messages != $number_of_test_messages) {
            echo("Message send: " . $number_of_test_messages . "\n");
            echo("Message found in DB: " . $count_incoming_messages . "\n");
            echo("X Number of received Messages not matches with published Messages! \n");
            $this->assertTrue(false);
        } else {
            echo("Message send: " . $number_of_test_messages . "\n");
            echo("Message found in DB: " . $count_incoming_messages . "\n");
            echo("✓ Number of received Messages matches with published Messages! \n");
            $this->assertTrue(true);
        }

        $this->test_posts($messages);

        ob_flush();

        Database::delete_plugin_table_data(Database::$iot_data_table_name);
    }

    private function test_posts($messages): void
    {
        $posts = get_posts(
            [
                'post_type' => 'post',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]
        );

        foreach ($posts as $post) {
            if (!in_array($post->post_content, $messages)) {
                echo "X Post with Message: " . $post->post_content . " is not matching with the send Message Content!";
                $this->assertTrue(false);
            }
            wp_delete_post($post->ID, true);
        }
        echo "✓ Posts created and Content Match !";
        $this->assertTrue(true);
    }
}