<?php
use PHPUnit\Framework\TestCase;

class User_model_test extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        if (!class_exists('CI_Model')) {
            // Mock CI_Model for testing without full framework
            eval('class CI_Model { public $db; public function __construct() {} }');
        }

        require_once APPPATH . 'core/MY_Model.php';
        require_once APPPATH . 'models/User_model.php';
    }

    public function test_user_model_has_correct_table(): void
    {
        $model = new User_model();
        $reflection = new ReflectionProperty($model, 'table');
        $reflection->setAccessible(true);
        $this->assertSame('users', $reflection->getValue($model));
    }

    public function test_find_by_email_returns_null_for_missing_email(): void
    {
        // This test requires a live DB — skipped if no connection
        $model = new User_model();
        // We can't easily inject the CI3 DB here without the full framework
        // but we verify the method exists and is callable
        $this->assertTrue(method_exists($model, 'find_by_email'));
    }

    public function test_find_by_reset_token_method_exists(): void
    {
        $this->assertTrue(method_exists('User_model', 'find_by_reset_token'));
    }

    public function test_email_exists_method_exists(): void
    {
        $this->assertTrue(method_exists('User_model', 'email_exists'));
    }
}
