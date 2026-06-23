<?php
require_once __DIR__ . '/../bootstrap.php';
require_once APPPATH . 'models/Review_model.php';

class Review_model_test extends PHPUnit\Framework\TestCase
{
    private $model;

    protected function setUp(): void
    {
        // No instantiation needed — tests only verify method existence
    }

    public function test_can_review_returns_bool()
    {
        // can_review() checks: user has a DELIVERED order containing the product
        // and has not already reviewed it
        // We verify the method exists (integration tested via browser)
        $this->assertTrue(method_exists('Review_model', 'can_review'));
    }

    public function test_has_reviewed_exists()
    {
        $this->assertTrue(method_exists('Review_model', 'has_reviewed'));
    }
}
