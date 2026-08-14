<?php
// Licensing Server Entry Point (key.sandslab.com)

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Model.php';

// Auto-run schema setup if tables do not exist
try {
    $pdo = Model::getDB();
    $stmt = $pdo->query("SHOW TABLES LIKE 'licensing_admins'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            // Split by semicolon + newline to execute single queries
            $queries = preg_split("/;[\r\n]+/", $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
        }
    }
} catch (Exception $e) {
    // Fail silently, error will report naturally when app queries
}

// Instantiate Router
$router = new Router();

// Web Panel Routes
$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/logout', ['AuthController', 'logout']);

$router->get('/', ['LicenseController', 'index']);
$router->get('/licenses/create', ['LicenseController', 'showCreate']);
$router->post('/licenses/create', ['LicenseController', 'create']);
$router->post('/licenses/toggle', ['LicenseController', 'toggleStatus']);
$router->get('/licenses/view', ['LicenseController', 'viewDetails']);

// Public Client Verification API Endpoint
$router->post('/api/activate', ['ApiController', 'activate']);

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
