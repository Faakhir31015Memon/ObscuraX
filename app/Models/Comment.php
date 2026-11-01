<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Comment
{
    /** @return list<array> */
    public static function forBlog(int $blogId, bool $includeHidden = false): array
    {
        $statusSql = $includeHidden ? '' : 'AND c.status = "published"';
        $comments = Database::fetchAll(
            "SELECT c.*, u.username, u.full_name, u.avatar, r.slug AS role_slug,
                    (SELECT COUNT(*) FROM comment_replies cr WHERE cr.comment_id = c.id AND cr.status = 'published') AS reply_count
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN roles r ON r.id = u.role_id
             WHERE c.blog_id = ? $statusSql
             ORDER BY c.created_at ASC",
            [$blogId]
        );

        if ($comments === []) {
            return [];
        }

        $ids = array_column($comments, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $replyStatus = $includeHidden ? '' : 'AND cr.status = "published"';
        $replies = Database::fetchAll(
            "SELECT cr.*, u.username, u.full_name, u.avatar, r.slug AS role_slug
             FROM comment_replies cr
             INNER JOIN users u ON u.id = cr.user_id
             INNER JOIN roles r ON r.id = u.role_id
             WHERE cr.comment_id IN ($placeholders) $replyStatus
             ORDER BY cr.created_at ASC",
            $ids
        );

        $grouped = [];
        foreach ($replies as $reply) {
            $grouped[$reply['comment_id']][] = $reply;
        }
        foreach ($comments as &$comment) {
            $comment['replies'] = $grouped[$comment['id']] ?? [];
        }
        unset($comment);

        return $comments;
    }

    public static function create(int $blogId, int $userId, string $content): int
    {
        return Database::insert('comments', [
            'blog_id' => $blogId,
            'user_id' => $userId,
            'content' => mb_substr(trim($content), 0, 4000),
            'status' => 'published',
            'created_at' => now(),
        ]);
    }

    public static function addReply(int $commentId, int $userId, string $content): int
    {
        return Database::insert('comment_replies', [
            'comment_id' => $commentId,
            'user_id' => $userId,
            'content' => mb_substr(trim($content), 0, 4000),
            'status' => 'published',
            'created_at' => now(),
        ]);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.*, b.slug AS blog_slug, b.title AS blog_title, b.allow_comments
             FROM comments c INNER JOIN blogs b ON b.id = c.blog_id WHERE c.id = ? LIMIT 1',
            [$id]
        );
    }

    public static function findReply(int $id): ?array
    {
        return Database::fetch(
            'SELECT cr.*, c.blog_id FROM comment_replies cr INNER JOIN comments c ON c.id = cr.comment_id WHERE cr.id = ? LIMIT 1',
            [$id]
        );
    }

    /** @return list<array> */
    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT c.*, b.title AS blog_title, b.slug AS blog_slug,
                    (SELECT COUNT(*) FROM comment_replies cr WHERE cr.comment_id = c.id) AS reply_count
             FROM comments c INNER JOIN blogs b ON b.id = c.blog_id
             WHERE c.user_id = ? ORDER BY c.created_at DESC',
            [$userId]
        );
    }

    public static function countForBlog(int $blogId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM comments WHERE blog_id = ? AND status = "published"', [$blogId], 0);
    }

    public static function recent(int $limit = 8): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            "SELECT c.*, u.username, u.avatar, b.title AS blog_title, b.slug AS blog_slug
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN blogs b ON b.id = c.blog_id
             ORDER BY c.created_at DESC LIMIT $limit"
        );
    }

    /**
     * @return array{items:list<array>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], ['published', 'hidden'], true)) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(c.content LIKE ? OR u.username LIKE ? OR b.title LIKE ?)';
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['blog_id'])) {
            $where[] = 'c.blog_id = ?';
            $params[] = (int) $filters['blog_id'];
        }
        if (!empty($filters['author_id'])) {
            $where[] = 'b.author_id = ?';
            $params[] = (int) $filters['author_id'];
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::value(
            "SELECT COUNT(*) FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN blogs b ON b.id = c.blog_id
             WHERE $whereSql",
            $params,
            0
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT c.*, u.username, u.avatar, u.email, b.title AS blog_title, b.slug AS blog_slug,
                    (SELECT COUNT(*) FROM comment_replies cr WHERE cr.comment_id = c.id) AS reply_count
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN blogs b ON b.id = c.blog_id
             WHERE $whereSql
             ORDER BY c.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::update('comments', ['status' => $status], 'id = :id', ['id' => $id]);
    }

    public static function setReplyStatus(int $id, string $status): void
    {
        Database::update('comment_replies', ['status' => $status], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM comments WHERE id = ?', [$id]);
    }

    public static function deleteReply(int $id): void
    {
        Database::query('DELETE FROM comment_replies WHERE id = ?', [$id]);
    }
}
