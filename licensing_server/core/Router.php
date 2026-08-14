<?php
// Pure PHP Lightweight Regex Router for Licensing Server

class Router {
    private array $routes = [];

    public function add(string $method, string $path, $handler) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function get(string $path, $handler) {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, $handler) {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri) {
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Strip base path if hosted in subdirectory
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\' && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (is_array($route['handler'])) {
                    list($controllerClass, $methodName) = $route['handler'];
                    require_once __DIR__ . "/../app/Controllers/$controllerClass.php";
                    $controller = new $controllerClass();
                    return call_user_func_array([$controller, $methodName], $params);
                }

                if (is_callable($route['handler'])) {
                    return call_user_func_array($route['handler'], $params);
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'API Route Not Found', 'path' => $path]);
        } else {
            echo "<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>";
        }
        exit;
    }
}
