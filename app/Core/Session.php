<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private static array $config = [];

    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        self::$config = $config;

        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            session_name($config['name'] ?? 'obscurax_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => is_https(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.gc_maxlifetime', (string) (($config['lifetime'] ?? 120) * 60));
        }

        session_start();

        $lifetime = (int) ($config['lifetime'] ?? 120) * 60;
        $lastSeen = $_SESSION['_last_seen'] ?? time();

        if (isset($_SESSION['_started']) && (time() - (int) $lastSeen) > $lifetime) {
            self::destroy();
            session_start();
            $_SESSION['_expired'] = true;
        }

        $_SESSION['_started'] ??= time();
        $_SESSION['_last_seen'] = time();

        $fingerprint = self::fingerprint();
        if (isset($_SESSION['_fingerprint']) && !hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
            self::destroy();
            session_start();
            $_SESSION['_fingerprint'] = $fingerprint;
            return;
        }
        $_SESSION['_fingerprint'] = $fingerprint;

        if (!isset($_SESSION['_regenerated'])) {
            $_SESSION['_regenerated'] = time();
        } elseif ((time() - (int) $_SESSION['_regenerated']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['_regenerated'] = time();
        }
    }

    private static function fingerprint(): string
    {
        return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . config('app.key', 'obscurax'));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    /** @return array<string, list<string>> */
    public static function takeFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public static function flashOld(array $input): void
    {
        unset($input['_token'], $input['password'], $input['password_confirmation'], $input['current_password'], $input['new_password']);
        $_SESSION['_old'] = $input;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_regenerated'] = time();
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function regenerateCaptcha(): string
    {
        return bin2hex(random_bytes(16));
    }
}
