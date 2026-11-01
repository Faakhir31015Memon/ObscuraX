<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Session;

final class Blog
{
    private const SELECT_BASE = 'SELECT b.*, c.name AS category_name, c.slug AS category_slug, c.accent_color,
            u.username AS author_username, u.full_name AS author_full_name, u.avatar AS author_avatar,
            (SELECT COUNT(*) FROM likes l WHERE l.blog_id = b.id) AS like_count,
            (SELECT COUNT(*) FROM comments cm WHERE cm.blog_id = b.id AND cm.status = "published") AS comment_count
        FROM blogs b
        INNER JOIN categories c ON c.id = b.category_id
        INNER JOIN users u ON u.id = b.author_id';

    public static function find(int $id): ?array
    {
        return Database::fetch(self::SELECT_BASE . ' WHERE b.id = ? LIMIT 1', [$id]);
    }

    public static function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = self::SELECT_BASE . ' WHERE b.slug = ?';
        if ($publishedOnly) {
            $sql .= ' AND b.status = "published" AND b.published_at <= NOW()';
        }
        return Database::fetch($sql . ' LIMIT 1', [$slug]);
    }

    /**
     * @param array{q?:string,category?:string,tag?:string,author?:int,sort?:string,featured?:bool,status?:string} $filters
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $where = ['b.status = "published"', 'b.published_at <= NOW()'];
        $params = [];
        $joins = '';

        if (!empty($filters['q'])) {
            $like = '%' . addcslashes(trim((string) $filters['q']), '%_\\') . '%';
            $where[] = '(b.title LIKE ? OR b.excerpt LIKE ? OR b.content LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['author'])) {
            $where[] = 'b.author_id = ?';
            $params[] = (int) $filters['author'];
        }
        if (!empty($filters['tag'])) {
            $joins .= ' INNER JOIN blog_tags bt ON bt.blog_id = b.id INNER JOIN tags tg ON tg.id = bt.tag_id';
            $where[] = 'tg.slug = ?';
            $params[] = $filters['tag'];
        }
        if (!empty($filters['featured'])) {
            $where[] = 'b.is_featured = 1';
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['draft', 'published', 'unpublished'], true)) {
            $where = array_values(array_filter($where, static fn (string $clause): bool => !str_starts_with($clause, 'b.status')));
            $where[] = 'b.status = ?';
            $params[] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);

        $total = (int) Database::value(
            "SELECT COUNT(DISTINCT b.id) FROM blogs b
             INNER JOIN categories c ON c.id = b.category_id
             INNER JOIN users u ON u.id = b.author_id
             $joins WHERE $whereSql",
            $params,
            0
        );

        $order = match ($filters['sort'] ?? 'latest') {
            'popular' => 'b.view_count DESC, b.published_at DESC',
            'liked' => 'like_count DESC, b.published_at DESC',
            'commented' => 'comment_count DESC, b.published_at DESC',
            'oldest' => 'b.published_at ASC',
            default => 'b.published_at DESC',
        };

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            self::SELECT_BASE . " $joins WHERE $whereSql GROUP BY b.id ORDER BY $order LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    /** @return list<array> */
    public static function featured(int $limit = 3): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.status = 'published' AND b.published_at <= NOW() AND b.is_featured = 1
             ORDER BY b.published_at DESC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function latest(int $limit = 6, ?int $excludeId = null): array
    {
        $limit = max(1, $limit);
        $exclude = $excludeId ? ' AND b.id <> ' . $excludeId : '';
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.status = 'published' AND b.published_at <= NOW() $exclude
             ORDER BY b.published_at DESC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function popular(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.status = 'published' AND b.published_at <= NOW()
             ORDER BY b.view_count DESC, b.published_at DESC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function mostLiked(int $limit = 6): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.status = 'published' AND b.published_at <= NOW()
             ORDER BY like_count DESC, b.published_at DESC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function related(array $blog, int $limit = 3): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.status = 'published' AND b.published_at <= NOW()
             AND b.id <> ? AND b.category_id = ?
             ORDER BY b.published_at DESC LIMIT $limit",
            [(int) $blog['id'], (int) $blog['category_id']]
        );
    }

    /** @return list<array> */
    public static function byAuthor(int $authorId, int $limit = 20): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            self::SELECT_BASE . " WHERE b.author_id = ? AND b.status = 'published' AND b.published_at <= NOW()
             ORDER BY b.published_at DESC LIMIT $limit",
            [$authorId]
        );
    }

    /** @return list<array> */
    public static function forAdmin(array $filters, int $adminId, bool $allAuthors, int $limit = 100): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (!$allAuthors) {
            $where[] = 'b.author_id = ?';
            $params[] = $adminId;
        }
        if (!empty($filters['status'])) {
            $where[] = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $where[] = 'b.title LIKE ?';
            $params[] = $like;
        }
        if (!empty($filters['category'])) {
            $where[] = 'b.category_id = ?';
            $params[] = (int) $filters['category'];
        }
        $limit = max(1, min(300, $limit));
        return Database::fetchAll(
            "SELECT b.*, c.name AS category_name, u.username AS author_username,
                    (SELECT COUNT(*) FROM likes l WHERE l.blog_id = b.id) AS like_count,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.blog_id = b.id) AS comment_count
             FROM blogs b
             INNER JOIN categories c ON c.id = b.category_id
             INNER JOIN users u ON u.id = b.author_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY b.created_at DESC LIMIT $limit",
            $params
        );
    }

    public static function create(array $data): int
    {
        $data['slug'] = self::uniqueSlug($data['slug'] ?? $data['title']);
        return Database::insert('blogs', $data);
    }

    public static function update(int $id, array $data): void
    {
        if (array_key_exists('slug', $data)) {
            $data['slug'] = self::uniqueSlug($data['slug'] ?? $data['title'] ?? '', $id);
        }
        Database::update('blogs', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM blogs WHERE id = ?', [$id]);
    }

    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = slugify($source, 'post');
        $slug = $base;
        $counter = 2;
        while (true) {
            $sql = 'SELECT id FROM blogs WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if (!Database::fetch($sql, $params)) {
                return $slug;
            }
            $slug = $base . '-' . $counter;
            $counter++;
            if ($counter > 500) {
                return $base . '-' . bin2hex(random_bytes(3));
            }
        }
    }

    /**
     * Records a unique-per-session view and keeps the denormalised counter in sync.
     */
    public static function recordView(int $blogId, ?int $userId): void
    {
        $seen = Session::get('_viewed', []);
        if (in_array($blogId, $seen, true)) {
            return;
        }
        $seen[] = $blogId;
        Session::put('_viewed', array_slice($seen, -200));

        Database::query('UPDATE blogs SET view_count = view_count + 1 WHERE id = ?', [$blogId]);
        Database::insert('blog_views', [
            'blog_id' => $blogId,
            'user_id' => $userId,
            'ip_hash' => sha1(client_ip() . '|' . config('app.key', 'obscurax')),
            'viewed_at' => now(),
        ]);
    }

    public static function countsByStatus(): array
    {
        $rows = Database::fetchAll('SELECT status, COUNT(*) AS total FROM blogs GROUP BY status');
        $counts = ['draft' => 0, 'published' => 0, 'unpublished' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        $counts['total'] = array_sum($counts);
        return $counts;
    }

    public static function canManage(array $blog, array $user): bool
    {
        if (($user['role_slug'] ?? '') === 'super_admin') {
            return true;
        }
        return (int) $blog['author_id'] === (int) $user['id'];
    }

    public static function touchPublishedAt(array &$data, string $status): void
    {
        if ($status === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
    }
}
