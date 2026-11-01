<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = new App\Core\Router();
require __DIR__ . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
