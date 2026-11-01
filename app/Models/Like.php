<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Like
{
    public static function isLiked(int $blogId, ?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }
        return Database::fetch('SELECT id FROM likes WHERE blog_id = ? AND user_id = ?', [$blogId, $userId]) !== null;
    }

    /**
     * @return array{liked:bool,count:int}
     */
    public static function toggle(int $blogId, int $userId): array
    {
        $existing = Database::fetch('SELECT id FROM likes WHERE blog_id = ? AND user_id = ?', [$blogId, $userId]);
        if ($existing) {
            Database::query('DELETE FROM likes WHERE id = ?', [(int) $existing['id']]);
            $liked = false;
        } else {
            Database::insert('likes', ['blog_id' => $blogId, 'user_id' => $userId, 'created_at' => now()]);
            $liked = true;
        }
        return ['liked' => $liked, 'count' => self::countForBlog($blogId)];
    }

    public static function countForBlog(int $blogId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM likes WHERE blog_id = ?', [$blogId], 0);
    }

    public static function countForUser(int $userId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM likes WHERE user_id = ?', [$userId], 0);
    }

    /** @return list<array> */
    public static function blogsLikedBy(int $userId, int $limit = 50): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            "SELECT b.*, c.name AS category_name, c.slug AS category_slug, c.accent_color,
                    u.username AS author_username, l.created_at AS liked_at,
                    (SELECT COUNT(*) FROM likes x WHERE x.blog_id = b.id) AS like_count
             FROM likes l
             INNER JOIN blogs b ON b.id = l.blog_id
             INNER JOIN categories c ON c.id = b.category_id
             INNER JOIN users u ON u.id = b.author_id
             WHERE l.user_id = ? AND b.status = 'published'
             ORDER BY l.created_at DESC LIMIT $limit",
            [$userId]
        );
    }

    /**
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(u.username LIKE ? OR b.title LIKE ?)';
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            array_push($params, $like, $like);
        }
        if (!empty($filters['blog_id'])) {
            $where[] = 'l.blog_id = ?';
            $params[] = (int) $filters['blog_id'];
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::value(
            "SELECT COUNT(*) FROM likes l
             INNER JOIN users u ON u.id = l.user_id
             INNER JOIN blogs b ON b.id = l.blog_id
             WHERE $whereSql",
            $params,
            0
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT l.*, u.username, u.avatar, b.title AS blog_title, b.slug AS blog_slug, c.name AS category_name
             FROM likes l
             INNER JOIN users u ON u.id = l.user_id
             INNER JOIN blogs b ON b.id = l.blog_id
             INNER JOIN categories c ON c.id = b.category_id
             WHERE $whereSql
             ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM likes WHERE id = ?', [$id]);
    }

    public static function total(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM likes', [], 0);
    }
}
