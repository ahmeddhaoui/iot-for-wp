<?php
/**
 * Fired during plugin activation
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

// phpcs:ignoreFile
use IOT4WP\CustomPostTypes\BrokerCustomPostType;

class Activation
{

    /**
     * Active function fired on plugin activation to create custom database & flush rewrite rules
     */
    public static function activate(): void
    {
        $database_manger = new Database();
        $database_manger->create_plugin_db_tables();

        add_filter('cron_schedules', array(new Cron(), 'add_cron_schedules'));
        if (!wp_next_scheduled('iot_check_subscribe')) {
            wp_schedule_event(time(), 'iot_60sec', 'iot_check_subscribe');
        }

        if (!wp_next_scheduled('iot_check_daily_data_delete')) {
            wp_schedule_event(time(), 'daily', 'iot_check_daily_data_delete');
        }

        $active_broker_ids = get_option('iot_active_brokers') ?? [];

        if(count($active_broker_ids)){
            foreach ($active_broker_ids as $broker_id){
                update_post_meta($broker_id, BrokerCustomPostType::$broker_status_meta_key, '1');
            }
        }

        update_option('iot_active_brokers', '', false);
        flush_rewrite_rules();
    }

}
