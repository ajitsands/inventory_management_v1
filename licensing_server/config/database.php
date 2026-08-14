<?php
// Licensing Server Database Configuration

// Auto-detect production vs local environment
$isLocal = in_array(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'), ['127.0.0.1', '::1'])
    && (php_sapi_name() === 'cli' || strpos(($_SERVER['HTTP_HOST'] ?? 'localhost'), 'localhost') !== false);

return [
    'host' => $isLocal ? '127.0.0.1' : 'localhost',
    'database' => $isLocal ? 'applicationkey' : 'sandsl23_key_db',
    'username' => $isLocal ? 'root' : 'sandsl23_key_user',
    'password' => 'S@nds1@b', // Matches local and server password
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
