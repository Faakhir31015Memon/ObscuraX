<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Tag
{
    /** @return list<array> */
    public static function all(): array
    {
        return Database::fetchAll(
            'SELECT t.*, (SELECT COUNT(*) FROM blog_tags bt INNER JOIN blogs b ON b.id = bt.blog_id
             WHERE bt.tag_id = t.id AND b.status = "published") AS blog_count
             FROM tags t ORDER BY t.name ASC'
        );
    }

    /** @return list<array> */
    public static function popular(int $limit = 12): array
    {
        $limit = max(1, $limit);
        return Database::fetchAll(
            "SELECT t.*, COUNT(bt.blog_id) AS blog_count
             FROM tags t
             INNER JOIN blog_tags bt ON bt.tag_id = t.id
             INNER JOIN blogs b ON b.id = bt.blog_id AND b.status = 'published'
             GROUP BY t.id
             ORDER BY blog_count DESC, t.name ASC
             LIMIT $limit"
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM tags WHERE slug = ? LIMIT 1', [$slug]);
    }

    public static function findOrCreate(string $name): int
    {
        $name = trim($name);
        $slug = slugify($name, 'tag');
        $existing = Database::fetch('SELECT id FROM tags WHERE slug = ?', [$slug]);
        if ($existing) {
            return (int) $existing['id'];
        }
        return Database::insert('tags', ['name' => mb_substr($name, 0, 64), 'slug' => $slug]);
    }

    public static function update(int $id, string $name): void
    {
        Database::update('tags', ['name' => mb_substr($name, 0, 64), 'slug' => slugify($name, 'tag')], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM tags WHERE id = ?', [$id]);
    }

    /**
     * Replaces the tag set for a blog from a comma separated string.
     */
    public static function sync(int $blogId, string $tagInput): void
    {
        $names = array_filter(array_map('trim', explode(',', $tagInput)), static fn (string $n): bool => $n !== '');
        $names = array_slice(array_unique($names), 0, 12);

        $ids = [];
        foreach ($names as $name) {
            $ids[] = self::findOrCreate($name);
        }

        if ($ids === []) {
            Database::query('DELETE FROM blog_tags WHERE blog_id = ?', [$blogId]);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        Database::query(
            "DELETE FROM blog_tags WHERE blog_id = ? AND tag_id NOT IN ($placeholders)",
            array_merge([$blogId], $ids)
        );

        foreach ($ids as $tagId) {
            Database::query('INSERT IGNORE INTO blog_tags (blog_id, tag_id) VALUES (?, ?)', [$blogId, $tagId]);
        }
    }

    /** @return list<array> */
    public static function forBlog(int $blogId): array
    {
        return Database::fetchAll(
            'SELECT t.id, t.name, t.slug FROM tags t
             INNER JOIN blog_tags bt ON bt.tag_id = t.id
             WHERE bt.blog_id = ? ORDER BY t.name',
            [$blogId]
        );
    }
}
