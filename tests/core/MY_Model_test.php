<?php
use PHPUnit\Framework\TestCase;

class MY_Model_test extends TestCase
{
    private $db;
    private $model;

    protected function setUp(): void
    {
        // Build a real DB connection for integration testing
        $this->db = new mysqli(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'ci3_ecomm',
            (int)(getenv('DB_PORT') ?: 3306)
        );

        if ($this->db->connect_error) {
            $this->markTestSkipped('MySQL not available: ' . $this->db->connect_error);
        }

        // Minimal CI3 stub so MY_Model can instantiate
        if (!class_exists('CI_Model')) {
            eval('class CI_Model { public $db; public function __construct() {} }');
        }

        require_once APPPATH . 'core/MY_Model.php';

        $this->model = new class extends MY_Model {
            protected $table   = 'users';
            protected $primary = 'id';
        };

        // Inject a raw mysqli wrapper that speaks Query Builder interface
        // MY_Model_test uses a direct mysqli connection to verify logic independently
        $this->model->db = $this->db;
    }

    public function test_table_property_is_declared(): void
    {
        $reflection = new ReflectionProperty($this->model, 'table');
        $reflection->setAccessible(true);
        $this->assertSame('users', $reflection->getValue($this->model));
    }

    public function test_primary_property_defaults_to_id(): void
    {
        $model = new class extends MY_Model {
            protected $table = 'categories';
        };
        $reflection = new ReflectionProperty($model, 'primary');
        $reflection->setAccessible(true);
        $this->assertSame('id', $reflection->getValue($model));
    }
}
