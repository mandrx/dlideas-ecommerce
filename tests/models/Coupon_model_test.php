<?php
require_once __DIR__ . '/../bootstrap.php';
require_once APPPATH . 'models/Coupon_model.php';

class Coupon_model_test extends PHPUnit\Framework\TestCase
{
    public function test_validate_method_exists()
    {
        $this->assertTrue(method_exists('Coupon_model', 'validate'));
    }

    public function test_redeem_method_exists()
    {
        $this->assertTrue(method_exists('Coupon_model', 'redeem'));
    }

    public function test_apply_discount_percent()
    {
        $model  = $this->getMockBuilder(Coupon_model::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $coupon = (object)['type' => 'percent', 'value' => 10];
        // apply_discount is public — call via reflection or test it directly
        $result = (new ReflectionClass(Coupon_model::class))
                    ->getMethod('apply_discount')
                    ->invoke($model, $coupon, 100.00);
        $this->assertEquals(10.00, $result);
    }

    public function test_apply_discount_fixed()
    {
        $model  = $this->getMockBuilder(Coupon_model::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $coupon = (object)['type' => 'fixed', 'value' => 15];
        $result = (new ReflectionClass(Coupon_model::class))
                    ->getMethod('apply_discount')
                    ->invoke($model, $coupon, 100.00);
        $this->assertEquals(15.00, $result);
    }
}
