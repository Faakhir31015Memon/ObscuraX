<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public const STATUSES = ['active', 'inactive', 'banned', 'removed'];

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1',
            [$id]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch(
            'SELECT u.*, r.slug AS role_slug FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.email = ? LIMIT 1',
            [$email]
        );
    }

    public static function findByUsername(string $username): ?array
    {
        return Database::fetch(
            'SELECT u.*, r.slug AS role_slug FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.username = ? LIMIT 1',
            [$username]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('users', $data);
    }

    public static function roleId(string $slug): int
    {
        return (int) Database::value('SELECT id FROM roles WHERE slug = ?', [$slug], 1);
    }

    public static function update(int $id, array $data): void
    {
        if ($data === []) {
            return;
        }
        Database::update('users', $data, 'id = :id', ['id' => $id]);
    }

    public static function publicProfile(string $username): ?array
    {
        return Database::fetch(
            'SELECT u.id, u.username, u.full_name, u.bio, u.avatar, u.website, u.created_at, u.activated_at,
                    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id AND c.status = "published") AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.user_id = u.id) AS like_count
             FROM users u
             WHERE u.username = ? AND u.status = "active"
             LIMIT 1',
            [$username]
        );
    }

    /**
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)';
            $like = '%' . addcslashes((string) $filters['q'], '%_\\') . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['role'])) {
            $where[] = 'r.slug = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['verified'])) {
            $where[] = $filters['verified'] === 'yes' ? 'u.email_verified_at IS NOT NULL' : 'u.email_verified_at IS NULL';
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::value(
            "SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE $whereSql",
            $params,
            0
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name,
                    (SELECT COUNT(*) FROM blogs b WHERE b.author_id = u.id) AS blog_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.user_id = u.id) AS like_count
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE $whereSql
             ORDER BY u.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Active',
            'inactive' => 'Deactivated',
            'banned' => 'Banned',
            'removed' => 'Removed',
            default => ucfirst($status),
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'banned' => 'danger',
            'removed' => 'dark',
            default => 'secondary',
        };
    }

    public static function avatar(?string $path): string
    {
        return uploaded_url($path, asset('assets/img/avatar-default.svg'));
    }

    public static function setStatus(int $id, string $status, array $extra = []): void
    {
        $data = array_merge(['status' => $status], $extra);

        if ($status === 'active') {
            $data['removed_at'] = null;
            $data['banned_at'] = null;
            $data['banned_reason'] = null;
        }
        if ($status === 'banned') {
            $data['banned_at'] = now();
        }
        if ($status === 'removed') {
            $data['removed_at'] = now();
        }

        self::update($id, $data);
    }

    /** @return list<array> */
    public static function recentlyOnline(int $minutes = 5, int $limit = 20): array
    {
        $minutes = max(1, $minutes);
        $limit = max(1, min(200, $limit));
        return Database::fetchAll(
            "SELECT u.id, u.username, u.avatar, u.last_activity_at, r.slug AS role_slug
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.last_activity_at >= (NOW() - INTERVAL $minutes MINUTE) AND u.status = 'active'
             ORDER BY u.last_activity_at DESC
             LIMIT $limit"
        );
    }

    public static function updatePassword(int $id, string $plainPassword, bool $clearForce = true): void
    {
        $data = [
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'password_changed_at' => now(),
        ];
        if ($clearForce) {
            $data['must_change_password'] = 0;
        }
        self::update($id, $data);
    }
}
