<?php
/**
 * Class for custom cron jobs
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

use IOT4WP\Repository\Connector;

// phpcs:ignoreFile
class Cron
{

    public function __construct()
    {
        add_action('init', array(__CLASS__, 'add_cron_job_actions'));
        add_filter('cron_schedules', array(__CLASS__, 'add_cron_schedules'));
    }


    public static function add_cron_schedules(array $schedules): array
    {
        if (!isset($schedules['iot_60sec'])) {
            $schedules['iot_60sec'] = array(
                'interval' => 60,
                'display' => __('Once every 1 Minute'),
            );
        }
        return $schedules;
    }

    public static function add_cron_job_actions(): void
    {
        $connector = new Connector();
        add_action('iot_check_subscribe', array($connector, 'check_start_subscribe'));
        add_action('iot_check_daily_data_delete', [__CLASS__, 'check_iot_data_daily']);
    }

    public static function check_iot_data_daily(): void
    {
        $settings = get_option('iot_for_wp_settings_option_name') ?? array();

        if (isset($settings['delete_iot_data_status']) && 'on' === $settings['delete_iot_data_status']) {
            Database::delete_data_from_table(Database::$iot_data_table_name, $settings['delete_iot_data_interval']);
        }

        if (isset($settings['delete_logs_status']) && 'on' === $settings['delete_logs_status']) {
            Database::delete_data_from_table(Database::$iot_logs_table_name, $settings['delete_logs_interval']);
        }
    }
}
