<?php
require_once __DIR__ . '/../bootstrap.php';

class Payment_library_test extends PHPUnit\Framework\TestCase
{
    public function test_payment_php_file_exists()
    {
        $this->assertFileExists(APPPATH . 'libraries/Payment.php');
    }

    public function test_payment_config_keys_defined()
    {
        $config = [];
        include APPPATH . 'config/payment.php';
        $this->assertArrayHasKey('stripe_secret_key', $config);
        $this->assertArrayHasKey('stripe_publishable_key', $config);
        $this->assertArrayHasKey('stripe_webhook_secret', $config);
    }
}
