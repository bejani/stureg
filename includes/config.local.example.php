<?php
// Copy to config.local.php and set values for the current server.
// Never commit config.local.php or upload it to a public repository.
return [
    'app_name' => 'StuReg',
    'app_env' => 'local', // local or production
    'app_base_path' => '/stureg', // use '' when the app is at the domain root
    'db_host' => '127.0.0.1',
    'db_name' => 'stureg',
    'db_user' => 'root',
    'db_pass' => '',
    'timezone' => 'Asia/Tehran',
];

// InfinityFree example (edit with your actual values):
// 'app_env' => 'production',
// 'app_base_path' => '',
// 'db_host' => 'sqlXXX.infinityfree.com',
// 'db_name' => 'if0_XXXXXXX_stureg',
// 'db_user' => 'if0_XXXXXXX',
// 'db_pass' => 'YOUR_DATABASE_PASSWORD',
