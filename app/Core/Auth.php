<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static bool $loaded = false;
    private static ?array $user = null;
    /** @var list<string>|null */
    private static ?array $permissions = null;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;

        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }

        $row = Database::fetch(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name, r.level AS role_level
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1',
            [(int) $id]
        );

        if (!$row || in_array($row['status'], ['banned', 'removed', 'inactive'], true)) {
            unset($_SESSION['user_id']);
            self::$user = null;
            return null;
        }

        self::$user = $row;
        self::touchPresence((int) $row['id'], $row['last_activity_at'] ?? null);

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user() === null ? null : (int) self::$user['id'];
    }

    public static function role(): ?string
    {
        return self::$user['role_slug'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), ['admin', 'super_admin'], true);
    }

    public static function isSuperAdmin(): bool
    {
        return self::role() === 'super_admin';
    }

    public static function mustChangePassword(): bool
    {
        return (int) (self::$user['must_change_password'] ?? 0) === 1;
    }

    /**
     * Verifies credentials and returns a result describing what happened.
     *
     * @return array{ok:bool, message:string, code:string, user?:array}
     */
    public static function attempt(string $identifier, string $password): array
    {
        $identifier = trim($identifier);

        if (self::isThrottled($identifier)) {
            return [
                'ok' => false,
                'code' => 'throttled',
                'message' => 'Too many failed attempts. Please wait 15 minutes before trying again.',
            ];
        }

        $user = Database::fetch(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name, r.level AS role_level
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? OR u.username = ? LIMIT 1',
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::recordAttempt($identifier, false);
            return ['ok' => false, 'code' => 'invalid', 'message' => 'Incorrect email/username or password.'];
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            Database::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = :id', ['id' => (int) $user['id']]);
        }

        if ($user['status'] === 'banned') {
            self::recordAttempt($identifier, false);
            return ['ok' => false, 'code' => 'banned', 'message' => 'This account has been suspended. Please contact the site administrator.'];
        }

        if ($user['status'] === 'removed') {
            self::recordAttempt($identifier, false);
            return ['ok' => false, 'code' => 'removed', 'message' => 'This account is no longer available.'];
        }

        if ($user['status'] === 'inactive') {
            self::recordAttempt($identifier, false);
            if ($user['email_verified_at'] === null) {
                return [
                    'ok' => false,
                    'code' => 'unverified',
                    'message' => 'Please verify your email address before signing in.',
                    'user' => $user,
                ];
            }
            return [
                'ok' => false,
                'code' => 'deactivated',
                'message' => 'Your account is deactivated. Please contact the site administrator.',
            ];
        }

        self::recordAttempt($identifier, true);
        self::login($user);

        return ['ok' => true, 'code' => 'success', 'message' => 'Welcome back, ' . $user['username'] . '.', 'user' => $user];
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['_auth_time'] = time();

        Database::update('users', [
            'last_login_at' => now(),
            'last_login_ip' => client_ip(),
            'last_activity_at' => now(),
        ], 'id = :id', ['id' => (int) $user['id']]);

        Activity::user((int) $user['id'], 'login', 'Signed in from ' . client_ip());

        self::$loaded = true;
        self::$user = $user;
        self::$permissions = null;
    }

    public static function logout(): void
    {
        if (($id = self::id()) !== null) {
            Activity::user($id, 'logout', 'Signed out');
        }
        $_SESSION = [];
        Session::regenerate();
        Csrf::rotate();
        self::$loaded = true;
        self::$user = null;
        self::$permissions = null;
    }

    /** @return list<string> */
    public static function permissions(): array
    {
        if (self::$permissions !== null) {
            return self::$permissions;
        }
        if (!self::check()) {
            return self::$permissions = [];
        }
        if (self::isSuperAdmin()) {
            return self::$permissions = ['*'];
        }
        $rows = Database::fetchAll(
            'SELECT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN users u ON u.role_id = rp.role_id
             WHERE u.id = ?',
            [self::id()]
        );
        return self::$permissions = array_column($rows, 'slug');
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }
        $permissions = self::permissions();
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public static function refresh(): void
    {
        self::$loaded = false;
        self::$user = null;
        self::$permissions = null;
        self::user();
    }

    public static function isThrottled(string $identifier): bool
    {
        $count = (int) Database::value(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = ? OR ip_address = ?) AND successful = 0 AND attempted_at > (NOW() - INTERVAL 15 MINUTE)',
            [$identifier, client_ip()],
            0
        );
        return $count >= 6;
    }

    private static function recordAttempt(string $identifier, bool $successful): void
    {
        Database::insert('login_attempts', [
            'email' => mb_substr($identifier, 0, 190),
            'ip_address' => client_ip(),
            'successful' => $successful ? 1 : 0,
            'attempted_at' => now(),
        ]);

        if ($successful) {
            Database::query('DELETE FROM login_attempts WHERE email = ? AND successful = 0', [$identifier]);
        }
    }

    private static function touchPresence(int $userId, ?string $lastActivity): void
    {
        if ($lastActivity !== null && (time() - strtotime($lastActivity)) < 60) {
            return;
        }
        try {
            Database::update('users', ['last_activity_at' => now()], 'id = :id', ['id' => $userId]);
            if (self::$user !== null) {
                self::$user['last_activity_at'] = now();
            }
        } catch (\Throwable) {
            // presence tracking must never break a page render
        }
    }
}
