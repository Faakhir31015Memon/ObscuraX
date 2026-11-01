<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Settings;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $config = $GLOBALS['config'] ?? [];
        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }
        return $config;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('is_https')) {
    function is_https(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $path = ltrim($path, '/');
        return $base . ($path === '' ? '/' : '/' . $path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = ROOT_PATH . '/' . $path;
        $version = is_file($file) ? (string) filemtime($file) : '1';
        return url($path) . '?v=' . $version;
    }
}

if (!function_exists('uploaded_url')) {
    function uploaded_url(?string $path, string $fallback = ''): string
    {
        if ($path === null || trim($path) === '') {
            return $fallback === '' ? asset('assets/img/avatar-default.svg') : $fallback;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }
        return url('uploads/' . ltrim($path, '/'));
    }
}

if (!function_exists('cover_url')) {
    function cover_url(?string $path): string
    {
        return uploaded_url($path, asset('assets/img/blogs/occult-1.jpg'));
    }
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        return '/' . trim($uri, '/');
    }
}

if (!function_exists('is_active')) {
    function is_active(string $path, bool $exact = true): bool
    {
        $current = current_path();
        $path = '/' . trim($path, '/');
        if ($exact) {
            return $current === $path;
        }
        return $path === '/' ? $current === '/' : str_starts_with($current, $path);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return Settings::get($key, $default);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return Auth::can($permission);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): string
    {
        $old = Session::get('_old', []);
        $value = $old[$key] ?? $default;
        return is_array($value) ? implode(', ', $value) : (string) $value;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        if (preg_match('#^https?://#i', $path)) {
            header('Location: ' . $path, true, $status);
        } else {
            header('Location: ' . url($path), true, $status);
        }
        exit;
    }
}

if (!function_exists('back')) {
    function back(string $fallback = '/'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($referer !== '' && $host !== '' && (string) parse_url($referer, PHP_URL_HOST) === preg_replace('/:\d+$/', '', $host)) {
            header('Location: ' . $referer);
            exit;
        }
        redirect($fallback);
    }
}

if (!function_exists('abort')) {
    function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        \App\Core\View::renderError($status, null, false, $message);
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}

if (!function_exists('json_response')) {
    function json_response(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('input')) {
    function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text, string $fallback = 'post'): string
    {
        $text = trim(mb_strtolower($text, 'UTF-8'));
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text === '' ? $fallback : $text;
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M j, Y'): string
    {
        if (empty($date) || str_starts_with((string) $date, '0000')) {
            return '—';
        }
        return date($format, strtotime((string) $date));
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $date): string
    {
        return format_date($date, 'M j, Y \a\t g:i A');
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $date): string
    {
        if (empty($date)) {
            return '—';
        }
        $timestamp = strtotime((string) $date);
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'just now';
        }
        $units = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
        ];
        foreach ($units as $label => $seconds) {
            if ($diff >= $seconds) {
                $value = (int) floor($diff / $seconds);
                return $value . ' ' . $label . ($value > 1 ? 's' : '') . ' ago';
            }
        }
        return 'just now';
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $text, int $limit = 160, string $end = '…'): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $limit)) . $end;
    }
}

if (!function_exists('reading_time')) {
    function reading_time(string $content): int
    {
        $words = str_word_count(strip_tags($content));
        return max(1, (int) ceil($words / 220));
    }
}

if (!function_exists('number_short')) {
    function number_short(int|float $number): string
    {
        if ($number >= 1000000) {
            return rtrim(rtrim(number_format($number / 1000000, 1), '0'), '.') . 'M';
        }
        if ($number >= 1000) {
            return rtrim(rtrim(number_format($number / 1000, 1), '0'), '.') . 'K';
        }
        return (string) $number;
    }
}

if (!function_exists('share_links')) {
    function share_links(string $url, string $title): array
    {
        $u = rawurlencode($url);
        $t = rawurlencode($title);
        return [
            'x' => "https://twitter.com/intent/tweet?url={$u}&text={$t}",
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$u}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$u}",
            'whatsapp' => "https://wa.me/?text={$t}%20{$u}",
        ];
    }
}

if (!function_exists('client_ip')) {
    function client_ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('array_get')) {
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        return $array[$key] ?? $default;
    }
}
