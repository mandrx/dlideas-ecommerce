<?php
require_once __DIR__ . '/../bootstrap.php';
require_once APPPATH . 'models/Order_model.php';

class Order_model_test extends PHPUnit\Framework\TestCase
{
    public function test_get_for_seller_method_exists()
    {
        $this->assertTrue(method_exists('Order_model', 'get_for_seller'));
    }

    public function test_get_detail_for_seller_method_exists()
    {
        $this->assertTrue(method_exists('Order_model', 'get_detail_for_seller'));
    }

    public function test_count_for_seller_method_exists()
    {
        $this->assertTrue(method_exists('Order_model', 'count_for_seller'));
    }
}
