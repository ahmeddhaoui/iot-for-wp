<?php

use IOT4WP\Includes\Cron;
use IOT4WP\Includes\Database;

class TestCronjobsDatabase extends \PHPUnit\Framework\TestCase
{
    protected $random_days;
    protected $old_date;

    public function setUp(): void
    {
        //set automatically delete for Data
        global $wpdb;
        $rand_days = (string)rand(2, 50);
        $this->random_days = $rand_days;
        $settings_data = array(
            'delete_iot_data_status' => 'on',
            'delete_iot_data_interval' => '2',
            'delete_logs_status' => 'on',
            'delete_logs_interval' => '2',
        );
        update_option('iot_for_wp_settings_option_name', $settings_data);

        $test_dates = [];
        $old_date = date('Y-m-d H:i:s', strtotime(sprintf('- %s day', $rand_days), time()));
        $this->old_date = $old_date;
        $test_dates[] = date('Y-m-d H:i:s', time());
        $test_dates[] = $old_date;
        //insert data to Database
        foreach ($test_dates as $test_date) {
            $wpdb->insert($wpdb->prefix . Database::$iot_logs_table_name,
                array(
                    'id' => 'NULL',
                    'type' => 'TEST',
                    'created_date' => $test_date,
                    'message' => 'TEST MESSAGE: ' . $test_date
                )
            );

            $wpdb->insert(
                $wpdb->prefix . Database::$iot_data_table_name,
                array(
                    'id' => 'NULL',
                    'post_id' => 10,
                    'created_date' => $test_date,
                    'topic' => 'TEST',
                    'raw_data' => 'TEST: ' . $test_date,
                    'formated_data' => 'TEST: ' . $test_date,
                )
            );
        }
    }

    public function test_cronjobs_database(): void
    {
        $this->setUp();
        global $wpdb;
        echo 'Test-Data(Random Days): ' . $this->random_days. "\n";
        Cron::check_iot_data_daily();


        $data_iot_count = $wpdb->get_var(
            sprintf("SELECT count(*) from %s where topic='TEST'",
                $wpdb->prefix . Database::$iot_data_table_name
            )
        );

        $logs_iot_count = $wpdb->get_var(
            sprintf("SELECT count(*) from %s where type='TEST'",
                $wpdb->prefix . Database::$iot_logs_table_name
            )
        );

        if($data_iot_count > 1){
            echo("✓ Test Delete IoT Data \n");
            $this->assertTrue(true);
        }else{
            echo("X Test Delete IoT Data \n");
            $this->assertTrue(false);
        }

        if($logs_iot_count > 1){
            echo("✓ Test Delete IoT Logs \n");
            $this->assertTrue(true);
        }else{
            echo("X Test Delete IoT Logs \n");
            $this->assertTrue(false);
        }
        ob_flush();

        $this->clean_up();
    }


    public function clean_up(): void
    {
        Database::delete_plugin_table_data(Database::$iot_logs_table_name);
        Database::delete_plugin_table_data(Database::$iot_data_table_name);
    }
}