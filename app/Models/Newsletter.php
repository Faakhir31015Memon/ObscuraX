<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Newsletter
{
    /**
     * @return array{status:string,message:string}
     */
    public static function subscribe(string $email, string $ip): array
    {
        $email = mb_strtolower(trim($email));
        $existing = Database::fetch('SELECT * FROM newsletter_subscribers WHERE email = ?', [$email]);

        if ($existing) {
            if ($existing['status'] === 'subscribed') {
                return ['status' => 'exists', 'message' => 'You are already subscribed with this email address.'];
            }
            Database::update('newsletter_subscribers', [
                'status' => 'subscribed',
                'token' => bin2hex(random_bytes(32)),
                'unsubscribed_at' => null,
                'confirmed_at' => now(),
                'ip_address' => $ip,
            ], 'id = :id', ['id' => (int) $existing['id']]);
            return ['status' => 'renewed', 'message' => 'Welcome back! Your subscription has been reactivated.'];
        }

        Database::insert('newsletter_subscribers', [
            'email' => $email,
            'token' => bin2hex(random_bytes(32)),
            'status' => 'subscribed',
            'ip_address' => $ip,
            'confirmed_at' => now(),
            'created_at' => now(),
        ]);

        return ['status' => 'created', 'message' => 'You are subscribed. Watch your inbox for new discoveries.'];
    }

    public static function unsubscribeByToken(string $token): bool
    {
        $row = Database::fetch('SELECT id FROM newsletter_subscribers WHERE token = ? LIMIT 1', [$token]);
        if (!$row) {
            return false;
        }
        Database::update('newsletter_subscribers', [
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ], 'id = :id', ['id' => (int) $row['id']]);
        return true;
    }

    /**
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $where = '1 = 1';
        $params = [];
        if ($status !== '') {
            $where = 'status = ?';
            $params[] = $status;
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM newsletter_subscribers WHERE $where", $params, 0);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll(
            "SELECT * FROM newsletter_subscribers WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    /** @return list<array> */
    public static function subscribed(): array
    {
        return Database::fetchAll('SELECT email, created_at FROM newsletter_subscribers WHERE status = "subscribed" ORDER BY created_at DESC');
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM newsletter_subscribers WHERE id = ?', [$id]);
    }

    public static function count(string $status = 'subscribed'): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM newsletter_subscribers WHERE status = ?', [$status], 0);
    }
}
