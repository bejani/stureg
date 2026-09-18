<?php
/**
 * Shared application configuration.
 * Environment-specific values belong in config.local.php (never commit that file).
 */
$settings = [
    'app_name' => 'StuReg',
    'app_env' => 'local',
    'app_base_path' => null, // null = detect automatically; '' for domain root, '/stureg' for local subfolder
    'db_host' => '127.0.0.1',
    'db_name' => 'stureg',
    'db_user' => 'root',
    'db_pass' => '',
    'timezone' => 'Asia/Tehran',
];

$local_config = __DIR__ . '/config.local.php';
if (is_file($local_config)) {
    $local_settings = require $local_config;
    if (is_array($local_settings)) {
        $settings = array_merge($settings, $local_settings);
    }
}

const SESSION_NAME = 'stureg_session';

define('APP_NAME', (string)$settings['app_name']);
define('APP_ENV', (string)$settings['app_env']);
define('DB_HOST', (string)$settings['db_host']);
define('DB_NAME', (string)$settings['db_name']);
define('DB_USER', (string)$settings['db_user']);
define('DB_PASS', (string)$settings['db_pass']);

if ($settings['app_base_path'] === null) {
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $settings['app_base_path'] = (strpos($script_path, '/stureg/') === 0 || $script_path === '/stureg') ? '/stureg' : '';
}
define('APP_BASE_PATH', rtrim((string)$settings['app_base_path'], '/'));

date_default_timezone_set((string)$settings['timezone']);

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?>
