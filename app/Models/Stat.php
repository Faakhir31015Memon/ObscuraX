<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Stat
{
    public static function overview(): array
    {
        $v = static fn (string $sql): int => (int) Database::value($sql, [], 0);
        $readerScope = ' INNER JOIN roles r ON r.id = u.role_id ';

        return [
            'readers_total' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user'"),
            'readers_active' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND u.status = 'active'"),
            'readers_verified' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND u.status = 'active' AND u.email_verified_at IS NOT NULL"),
            'readers_pending' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND u.email_verified_at IS NULL"),
            'readers_online' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND u.status = 'active' AND u.last_activity_at >= (NOW() - INTERVAL 5 MINUTE)"),
            'readers_new_today' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND DATE(u.created_at) = CURDATE()"),
            'readers_new_month' => $v("SELECT COUNT(*) FROM users u{$readerScope}WHERE r.slug = 'user' AND u.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"),
            'users_total' => $v('SELECT COUNT(*) FROM users'),
            'users_active' => $v("SELECT COUNT(*) FROM users WHERE status = 'active'"),
            'users_online' => $v("SELECT COUNT(*) FROM users WHERE status = 'active' AND last_activity_at >= (NOW() - INTERVAL 5 MINUTE)"),
            'users_banned' => $v("SELECT COUNT(*) FROM users WHERE status = 'banned'"),
            'users_deactivated' => $v("SELECT COUNT(*) FROM users WHERE status = 'inactive' AND email_verified_at IS NOT NULL"),
            'users_pending' => $v("SELECT COUNT(*) FROM users WHERE status = 'inactive' AND email_verified_at IS NULL"),
            'users_removed' => $v("SELECT COUNT(*) FROM users WHERE status = 'removed'"),
            'users_new_month' => $v('SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")'),
            'users_new_today' => $v('SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()'),
            'admins_total' => $v("SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug IN ('admin','super_admin')"),
            'blogs_total' => $v('SELECT COUNT(*) FROM blogs'),
            'blogs_published' => $v("SELECT COUNT(*) FROM blogs WHERE status = 'published'"),
            'blogs_draft' => $v("SELECT COUNT(*) FROM blogs WHERE status = 'draft'"),
            'blogs_unpublished' => $v("SELECT COUNT(*) FROM blogs WHERE status = 'unpublished'"),
            'blog_views' => $v('SELECT COALESCE(SUM(view_count), 0) FROM blogs'),
            'daily_views' => $v('SELECT COUNT(*) FROM blog_views WHERE viewed_at >= (NOW() - INTERVAL 1 DAY)'),
            'comments_total' => $v('SELECT COUNT(*) FROM comments'),
            'comments_published' => $v("SELECT COUNT(*) FROM comments WHERE status = 'published'"),
            'replies_total' => $v('SELECT COUNT(*) FROM comment_replies'),
            'likes_total' => $v('SELECT COUNT(*) FROM likes'),
            'subscribers' => $v("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed'"),
            'messages_new' => $v("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"),
        ];
    }

    /**
     * Month buckets for the trailing N months (oldest first).
     *
     * @return list<array<string,mixed>>
     */
    public static function monthlySeries(int $months = 12): array
    {
        $months = max(1, min(36, $months));
        $start = date('Y-m-01 00:00:00', strtotime('-' . ($months - 1) . ' months'));

        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $timestamp = strtotime("-$i months", strtotime(date('Y-m-01')));
            $key = date('Y-m', $timestamp);
            $buckets[$key] = [
                'key' => $key,
                'label' => date('M Y', $timestamp),
                'users' => 0,
                'activated' => 0,
                'banned' => 0,
                'removed' => 0,
                'blogs' => 0,
                'comments' => 0,
                'replies' => 0,
                'likes' => 0,
                'views' => 0,
            ];
        }

        $series = [
            'users' => 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS k, COUNT(*) AS c FROM users WHERE created_at >= ? GROUP BY k',
            'activated' => 'SELECT DATE_FORMAT(activated_at, "%Y-%m") AS k, COUNT(*) AS c FROM users WHERE activated_at IS NOT NULL AND activated_at >= ? GROUP BY k',
            'banned' => 'SELECT DATE_FORMAT(banned_at, "%Y-%m") AS k, COUNT(*) AS c FROM users WHERE banned_at IS NOT NULL AND banned_at >= ? GROUP BY k',
            'removed' => 'SELECT DATE_FORMAT(removed_at, "%Y-%m") AS k, COUNT(*) AS c FROM users WHERE removed_at IS NOT NULL AND removed_at >= ? GROUP BY k',
            'blogs' => 'SELECT DATE_FORMAT(published_at, "%Y-%m") AS k, COUNT(*) AS c FROM blogs WHERE status = "published" AND published_at IS NOT NULL AND published_at >= ? GROUP BY k',
            'comments' => 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS k, COUNT(*) AS c FROM comments WHERE created_at >= ? GROUP BY k',
            'replies' => 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS k, COUNT(*) AS c FROM comment_replies WHERE created_at >= ? GROUP BY k',
            'likes' => 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS k, COUNT(*) AS c FROM likes WHERE created_at >= ? GROUP BY k',
            'views' => 'SELECT DATE_FORMAT(viewed_at, "%Y-%m") AS k, COUNT(*) AS c FROM blog_views WHERE viewed_at >= ? GROUP BY k',
        ];

        foreach ($series as $metric => $sql) {
            foreach (Database::fetchAll($sql, [$start]) as $row) {
                if (isset($buckets[$row['k']])) {
                    $buckets[$row['k']][$metric] = (int) $row['c'];
                }
            }
        }

        return array_values(array_reverse($buckets));
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function yearlySeries(int $years = 5): array
    {
        $years = max(1, min(10, $years));
        $start = (date('Y') - $years + 1) . '-01-01 00:00:00';

        $buckets = [];
        for ($i = $years - 1; $i >= 0; $i--) {
            $year = (string) (date('Y') - $i);
            $buckets[$year] = [
                'key' => $year,
                'label' => $year,
                'users' => 0,
                'activated' => 0,
                'banned' => 0,
                'removed' => 0,
                'blogs' => 0,
                'comments' => 0,
                'replies' => 0,
                'likes' => 0,
                'views' => 0,
            ];
        }

        $series = [
            'users' => 'SELECT YEAR(created_at) AS k, COUNT(*) AS c FROM users WHERE created_at >= ? GROUP BY k',
            'activated' => 'SELECT YEAR(activated_at) AS k, COUNT(*) AS c FROM users WHERE activated_at IS NOT NULL AND activated_at >= ? GROUP BY k',
            'banned' => 'SELECT YEAR(banned_at) AS k, COUNT(*) AS c FROM users WHERE banned_at IS NOT NULL AND banned_at >= ? GROUP BY k',
            'removed' => 'SELECT YEAR(removed_at) AS k, COUNT(*) AS c FROM users WHERE removed_at IS NOT NULL AND removed_at >= ? GROUP BY k',
            'blogs' => 'SELECT YEAR(published_at) AS k, COUNT(*) AS c FROM blogs WHERE status = "published" AND published_at IS NOT NULL AND published_at >= ? GROUP BY k',
            'comments' => 'SELECT YEAR(created_at) AS k, COUNT(*) AS c FROM comments WHERE created_at >= ? GROUP BY k',
            'replies' => 'SELECT YEAR(created_at) AS k, COUNT(*) AS c FROM comment_replies WHERE created_at >= ? GROUP BY k',
            'likes' => 'SELECT YEAR(created_at) AS k, COUNT(*) AS c FROM likes WHERE created_at >= ? GROUP BY k',
            'views' => 'SELECT YEAR(viewed_at) AS k, COUNT(*) AS c FROM blog_views WHERE viewed_at >= ? GROUP BY k',
        ];

        foreach ($series as $metric => $sql) {
            foreach (Database::fetchAll($sql, [$start]) as $row) {
                $key = (string) $row['k'];
                if (isset($buckets[$key])) {
                    $buckets[$key][$metric] = (int) $row['c'];
                }
            }
        }

        return array_values($buckets);
    }

    /** @return list<array> */
    public static function topBlogs(string $metric = 'views', int $limit = 6): array
    {
        $limit = max(1, $limit);
        $order = match ($metric) {
            'likes' => '(SELECT COUNT(*) FROM likes l WHERE l.blog_id = b.id) DESC',
            'comments' => '(SELECT COUNT(*) FROM comments c WHERE c.blog_id = b.id AND c.status = "published") DESC',
            default => 'b.view_count DESC',
        };

        return Database::fetchAll(
            "SELECT b.id, b.title, b.slug, b.view_count, b.status, c.name AS category_name,
                    (SELECT COUNT(*) FROM likes l WHERE l.blog_id = b.id) AS like_count,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.blog_id = b.id AND cm.status = 'published') AS comment_count
             FROM blogs b INNER JOIN categories c ON c.id = b.category_id
             WHERE b.status = 'published'
             ORDER BY $order, b.published_at DESC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function topUsers(int $limit = 8): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            "SELECT u.id, u.username, u.avatar, u.created_at, r.slug AS role_slug,
                    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.user_id = u.id) AS like_count,
                    ((SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id)
                     + (SELECT COUNT(*) FROM likes l WHERE l.user_id = u.id)) AS engagement
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.status = 'active'
             ORDER BY engagement DESC, u.created_at ASC LIMIT $limit"
        );
    }

    /** @return list<array> */
    public static function usersByStatus(): array
    {
        return Database::fetchAll('SELECT status, COUNT(*) AS total FROM users GROUP BY status ORDER BY total DESC');
    }

    /** @return list<array> */
    public static function viewsByDay(int $days = 14): array
    {
        $days = max(1, min(60, $days));
        $rows = Database::fetchAll(
            "SELECT DATE(viewed_at) AS day, COUNT(*) AS total
             FROM blog_views
             WHERE viewed_at >= (NOW() - INTERVAL $days DAY)
             GROUP BY day ORDER BY day ASC"
        );
        $map = [];
        foreach ($rows as $row) {
            $map[$row['day']] = (int) $row['total'];
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $series[] = ['label' => date('M j', strtotime($day)), 'value' => $map[$day] ?? 0];
        }
        return $series;
    }

    /** @return list<array> */
    public static function recentRegistrations(int $limit = 8): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            "SELECT u.id, u.username, u.email, u.avatar, u.status, u.created_at, u.email_verified_at, r.slug AS role_slug
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC LIMIT $limit"
        );
    }

    public static function readersTotal(): int
    {
        return (int) Database::value(
            "SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug = 'user'",
            [],
            0
        );
    }

    /** @return list<array> */
    public static function blogCountsByMonth(int $months = 6): array
    {
        return array_map(
            static fn (array $row): array => ['label' => $row['label'], 'value' => $row['blogs']],
            self::monthlySeries($months)
        );
    }
}
