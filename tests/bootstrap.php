<?php
// Load .env values manually for tests
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($val));
    }
}

// Define CI3 constants needed by models
define('BASEPATH',   realpath(__DIR__ . '/../system') . '/');
define('APPPATH',    realpath(__DIR__ . '/../application') . '/');
define('FCPATH',     realpath(__DIR__ . '/..') . '/');
define('ENVIRONMENT', 'testing');

// Load constants config manually
require_once APPPATH . 'config/constants.php';

// Minimal CI3 stubs so model files can be included without the full framework
if (!class_exists('MY_Model')) {
    class MY_Model {
        protected $db;
        protected $table   = '';
        protected $primary = 'id';
        public function __construct() {}
    }
}
