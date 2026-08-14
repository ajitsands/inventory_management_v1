<?php
// Base Controller Class for Licensing Server

class Controller {
    /**
     * Start session if not already started
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Fix for cPanel ALT-PHP session path permissions issues
            $savePath = session_save_path();
            if (empty($savePath) || !is_writable($savePath)) {
                $tempPath = sys_get_temp_dir();
                if (is_writable($tempPath)) {
                    session_save_path($tempPath);
                }
            }
            session_start();
        }
    }

    /**
     * Render a view template
     */
    protected function view(string $name, array $data = []) {
        $viewFile = __DIR__ . "/../app/Views/{$name}.php";
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("View template {$name} not found.");
        }
    }

    /**
     * Redirect to another page
     */
    protected function redirect(string $url) {
        $base = dirname($_SERVER['SCRIPT_NAME']);
        if ($base === '/' || $base === '\\') {
            $base = '';
        }
        header("Location: " . $base . $url);
        exit;
    }

    /**
     * Output JSON data
     */
    protected function json($data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Enforce authentication for protected dashboard routes
     */
    protected function requireAuth() {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/login');
        }
    }
}
