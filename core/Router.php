<?php

/**
 * Router
 *
 * Maps incoming URL requests to the appropriate controller and method.
 * Supports static routes and dynamic routes with named parameters.
 *
 * Usage in config/routes.php:
 *   $router->get('/', 'HomeController', 'index');
 *   $router->get('/team/{slug}', 'TeamController', 'show');
 *   $router->post('/{admin_path}/verify', 'AuthController', 'verifyOtp');
 */

class Router
{
    private array $routes = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->register('GET', $path, $controller, $method);
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->register('POST', $path, $controller, $method);
    }

    /**
     * Store a route definition.
     */
    private function register(string $httpMethod, string $path, string $controller, string $method): void
    {
        $this->routes[] = [
            'http_method' => $httpMethod,
            'path'        => $path,
            'controller'  => $controller,
            'method'      => $method,
        ];
    }

    /**
     * Resolve the current request to a controller and method.
     * Extracts named parameters from dynamic routes (e.g. {slug}, {id}).
     *
     * @throws RuntimeException if no matching route is found (404)
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {

            if ($route['http_method'] !== $requestMethod) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $requestPath);

            if ($params === null) {
                continue;
            }

            // Route matched — load and call the controller
            $controllerClass = $route['controller'];
            $controllerMethod = $route['method'];

            require_once __DIR__ . '/../app/Controllers/' . $controllerClass . '.php';

            $controller = new $controllerClass();
            $controller->$controllerMethod($params);
            return;
        }

        // No route matched
        $this->notFound();
    }

    /**
     * Match a route path against the current request path.
     * Returns an array of named parameters if matched, null if not.
     *
     * Example:
     *   route path:   /team/{slug}
     *   request path: /team/anna-mueller
     *   returns:      ['slug' => 'anna-mueller']
     */
    private function matchRoute(string $routePath, string $requestPath): ?array
    {
        // Convert route path to a regex pattern
        // {slug} → (?P<slug>[^/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            // Return only named captures (the actual parameters)
            return array_filter(
                $matches,
                fn($key) => !is_int($key),
                ARRAY_FILTER_USE_KEY
            );
        }

        return null;
    }

    /**
     * Handle 404 — no route matched.
     */
    private function notFound(): void
    {
        http_response_code(404);
        require_once __DIR__ . '/../app/Controllers/BaseController.php';
        (new BaseController())->renderNotFound();
    }
}
