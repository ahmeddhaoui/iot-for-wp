<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 *
 * @package    Iot_For_Wp
 */

use IOT4WP\CustomPostTypes\BrokerCustomPostType;
use IOT4WP\CustomPostTypes\TopicCustomPostType;
use IOT4WP\Includes\Database;
use IOT4WP\Includes\Deactivation;

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}


//deactivate all Plugin Features
Deactivation::deactivate(true);

//delete Database Tables
Database::drop_plugin_tables();

//Delete Post Types Data
$post_types = [
    BrokerCustomPostType::$custom_post_name,
    TopicCustomPostType::$custom_post_name,
];

foreach ($post_types as $post_type) {
    $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ]
    );

    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
}

//Delete Plugin Options
$options = [
    'iot_for_wp_settings_option_name',
    'iot_subscribe_ids',
    'iot_last_run',
    'iot_active_brokers',
];

foreach ($options as $option) {
    delete_option($option);
}