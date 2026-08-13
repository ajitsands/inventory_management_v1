<?php
// Database Configuration

// Auto-detect production vs local environment
$isLocal = in_array(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'), ['127.0.0.1', '::1']) 
           && (php_sapi_name() === 'cli' || strpos(($_SERVER['HTTP_HOST'] ?? 'localhost'), 'localhost') !== false);

return [
    'host'     => 'localhost',
    'database' => $isLocal ? 'inventory_system_db' : 'sandsl23_inventory_db',
    'username' => $isLocal ? 'root' : 'sandsl23_inventory_user',
    'password' => 'S@nds1@b',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
