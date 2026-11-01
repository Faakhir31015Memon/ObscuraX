<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

$config = require ROOT_PATH . '/config/config.php';

$GLOBALS['config'] = $config;

date_default_timezone_set($config['app']['timezone']);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require ROOT_PATH . '/app/helpers.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\View;

if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------- base path
$basePath = '';
if ($config['app']['url'] !== '') {
    $basePath = rtrim((string) parse_url($config['app']['url'], PHP_URL_PATH), '/');
} else {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $basePath = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;
}
define('BASE_PATH', $basePath);

// ---------------------------------------------------------------- security
define('CSP_NONCE', base64_encode(random_bytes(16)));

if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header(sprintf(
        "Content-Security-Policy: default-src 'self'; img-src 'self' data: blob: https:; media-src 'self' https:; "
        . "style-src 'self' 'unsafe-inline'; script-src 'self' 'nonce-%s'; font-src 'self' data:; "
        . "connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'",
        CSP_NONCE
    ));
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

Session::start($config['session']);

// ---------------------------------------------------------------- errors
set_exception_handler(static function (Throwable $e) use ($config): void {
    $isAjax = is_ajax();
    error_log(sprintf('[ObscuraX] %s: %s in %s:%d', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()));

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $e::class . ': ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }

    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => false,
            'message' => $config['app']['debug'] ? $e->getMessage() : 'Server error. Please try again.',
        ]);
        return;
    }

    http_response_code(500);
    try {
        View::renderError(500, $e, $config['app']['debug']);
    } catch (Throwable) {
        echo 'Internal Server Error';
    }
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[ObscuraX] fatal: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
        if (!headers_sent()) {
            http_response_code(500);
        }
    }
});

// Warm the database connection so a bad .env fails loudly and early.
if (PHP_SAPI !== 'cli' || getenv('OBSCURAX_SKIP_DB') !== '1') {
    try {
        Database::pdo();
    } catch (Throwable $e) {
        if (getenv('OBSCURAX_SKIP_DB') === '1') {
            // installer probes the connection itself
        } else {
            throw $e;
        }
    }
}
