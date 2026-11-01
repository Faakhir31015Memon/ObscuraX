<?php

declare(strict_types=1);

/**
 * Loads key=value pairs from .env into the environment without overwriting
 * variables that are already present (real env vars win in production).
 */
$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $value = substr($value, 1, -1);
        }
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

$debug = filter_var($env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);

// Database credentials live in config/db.php so shared hosts can edit one file.
// Values in db.php take precedence; if it is missing, DB_* from .env is used.
$dbFile = __DIR__ . '/db.php';
$db = is_file($dbFile) ? require $dbFile : [];
$db = is_array($db) ? $db : [];

$dbValue = static function (string $key, string $envKey, string $default) use ($db, $env): string {
    if (array_key_exists($key, $db) && (string) $db[$key] !== '') {
        return (string) $db[$key];
    }
    return $env($envKey, $default);
};

return [
    'app' => [
        'name' => $env('APP_NAME', 'ObscuraX'),
        'env' => $env('APP_ENV', 'production'),
        'debug' => $debug,
        'url' => rtrim($env('APP_URL', ''), '/'),
        'timezone' => $env('APP_TIMEZONE', 'UTC'),
        'key' => $env('APP_KEY', ''),
    ],
    'db' => [
        'host' => $dbValue('host', 'DB_HOST', '127.0.0.1'),
        'port' => $dbValue('port', 'DB_PORT', '3306'),
        'name' => $dbValue('name', 'DB_NAME', 'obscurax'),
        'user' => $dbValue('user', 'DB_USER', 'root'),
        'pass' => (string) ($db['pass'] ?? $env('DB_PASS', '')),
        'charset' => (string) ($db['charset'] ?? 'utf8mb4'),
    ],
    'mail' => [
        'driver' => $env('MAIL_DRIVER', 'log'),
        'from' => $env('MAIL_FROM', 'noreply@obscurax.local'),
        'from_name' => $env('MAIL_FROM_NAME', 'ObscuraX'),
        'host' => $env('MAIL_HOST', ''),
        'port' => (int) $env('MAIL_PORT', '587'),
        'username' => $env('MAIL_USERNAME', ''),
        'password' => $env('MAIL_PASSWORD', ''),
        'encryption' => $env('MAIL_ENCRYPTION', 'tls'),
    ],
    'uploads' => [
        'max_image_mb' => (int) $env('UPLOAD_MAX_IMAGE_MB', '5'),
        'image_mimes' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ],
    ],
    'session' => [
        'lifetime' => (int) $env('SESSION_LIFETIME_MINUTES', '120'),
        'name' => 'obscurax_session',
    ],
];
