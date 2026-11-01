<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{method:string,pattern:string,regex:string,params:list<string>,handler:mixed,middleware:list<string>}> */
    private array $routes = [];

    public function get(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function add(string $method, string $pattern, mixed $handler, array $middleware = []): void
    {
        $params = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}#',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                $constraint = $m[2] ?? '[^/]+';
                return '(?P<' . $m[1] . '>' . $constraint . ')';
            },
            $pattern
        );

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => '#^' . $regex . '/?$#',
            'params' => $params,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method) === 'HEAD' ? 'GET' : strtoupper($method);
        $path = '/' . trim(rawurldecode((string) parse_url($uri, PHP_URL_PATH)), '/');

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }
        if ($path === '/index.php' || $path === '') {
            $path = '/';
        }

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            if (($method === 'POST') && !str_starts_with($path, '/webhook')) {
                Csrf::enforce();
            }

            foreach ($route['middleware'] as $middleware) {
                $this->runMiddleware($middleware);
            }

            $arguments = [];
            foreach ($route['params'] as $name) {
                $arguments[] = $matches[$name] ?? null;
            }

            $handler = $route['handler'];
            if (is_array($handler) && is_string($handler[0])) {
                $controller = new $handler[0]();
                $result = $controller->{$handler[1]}(...$arguments);
                if (is_string($result)) {
                    echo $result;
                }
                return;
            }

            $result = $handler(...$arguments);
            if (is_string($result)) {
                echo $result;
            }
            return;
        }

        if ($allowedMethods !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            View::renderError(405);
            return;
        }

        View::renderError(404);
    }

    private function runMiddleware(string $middleware): void
    {
        [$name, $parameter] = array_pad(explode(':', $middleware, 2), 2, null);

        $map = [
            'guest' => \App\Middleware\RedirectIfAuthenticated::class,
            'auth' => \App\Middleware\Authenticate::class,
            'admin' => \App\Middleware\RequireAdmin::class,
            'super' => \App\Middleware\RequireSuperAdmin::class,
        ];

        if ($name === 'can' && $parameter !== null) {
            \App\Middleware\RequirePermission::handle($parameter);
            return;
        }

        if (!isset($map[$name])) {
            throw new \RuntimeException('Unknown middleware: ' . $name);
        }

        $map[$name]::handle();
    }
}
