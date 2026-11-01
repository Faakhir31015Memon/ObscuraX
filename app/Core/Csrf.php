<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validate(?string $token): bool
    {
        $token = $token ?? ($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($token)
            && $token !== ''
            && isset($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    public static function rotate(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    public static function enforce(): void
    {
        if (!self::validate(null)) {
            if (is_ajax()) {
                json_response(['ok' => false, 'message' => 'Your session expired. Please refresh the page and try again.'], 419);
            }
            http_response_code(419);
            flash('error', 'Your session expired or the request could not be verified. Please try again.');
            redirect(current_path());
        }
    }
}
