<?php

/**
 * Router for PHP's built-in web server:  php -S localhost:8000 router.php
 * Apache uses .htaccess instead - this file is ignored there.
 */
declare(strict_types=1);

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$blocked = ['/app/', '/config/', '/database/', '/routes/', '/storage/', '/views/'];
foreach ($blocked as $prefix) {
    if (str_starts_with($path, $prefix)) {
        http_response_code(403);
        echo 'Forbidden';
        return true;
    }
}
if (preg_match('#^/\.(env|git)#', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
return true;
