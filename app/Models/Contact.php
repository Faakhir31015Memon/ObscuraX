<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Contact
{
    public static function create(array $data): int
    {
        return Database::insert('contact_messages', [
            'name' => mb_substr($data['name'], 0, 96),
            'email' => mb_substr($data['email'], 0, 190),
            'subject' => mb_substr($data['subject'], 0, 191),
            'message' => mb_substr($data['message'], 0, 5000),
            'status' => 'new',
            'ip_address' => client_ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(int $page = 1, int $perPage = 15, string $status = ''): array
    {
        $where = '1 = 1';
        $params = [];
        if ($status !== '') {
            $where = 'status = ?';
            $params[] = $status;
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM contact_messages WHERE $where", $params, 0);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll(
            "SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function markRead(int $id): void
    {
        Database::update('contact_messages', ['status' => 'read'], 'id = :id', ['id' => $id]);
    }

    public static function archive(int $id): void
    {
        Database::update('contact_messages', ['status' => 'archived'], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function unreadCount(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM contact_messages WHERE status = "new"', [], 0);
    }
}
