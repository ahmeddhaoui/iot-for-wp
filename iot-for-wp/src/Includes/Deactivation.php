<?php
/**
 * The file that defines deactivation functions
 *
 * @package    Iot_For_Wp
 */

declare(strict_types=1);

namespace IOT4WP\Includes;

class Deactivation {

	public static function deactivate(): void {
		 wp_clear_scheduled_hook( 'iot_check_subscribe' );
	}
}
