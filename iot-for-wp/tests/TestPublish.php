<?php

use IOT4WP\Repository\Connector;

class TestPublish extends \PHPUnit\Framework\TestCase
{

    public function test_publish(): void
    {
        $message = 'Publish TEST';
        try {
            Connector::publish_message(527, $message);
            echo("✓ Message: " . $message . " published successfully! \n");
            $this->assertTrue(true);
        } catch (Exception $exception) {
            echo("X Message: " . $message . "  publishing failed ! \n");
            $this->assertTrue(false);
        }
        ob_flush();
    }

}