<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    /** @return list<array> */
    public static function all(bool $onlyActive = false): array
    {
        $sql = 'SELECT c.*,
                    (SELECT COUNT(*) FROM blogs b WHERE b.category_id = c.id AND b.status = "published") AS blog_count
                FROM categories c';
        if ($onlyActive) {
            $sql .= ' WHERE c.is_active = 1';
        }
        $sql .= ' ORDER BY c.sort_order ASC, c.name ASC';
        return Database::fetchAll($sql);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE slug = ? LIMIT 1', [$slug]);
    }

    public static function create(array $data): int
    {
        return Database::insert('categories', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('categories', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $count = (int) Database::value('SELECT COUNT(*) FROM blogs WHERE category_id = ?', [$id], 0);
        if ($count > 0) {
            return false;
        }
        Database::query('DELETE FROM categories WHERE id = ?', [$id]);
        return true;
    }

    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = slugify($source, 'category');
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM categories WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if (!Database::fetch($sql, $params)) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }

    /** @return list<array> */
    public static function withStats(): array
    {
        return Database::fetchAll(
            'SELECT c.*,
                    COUNT(b.id) AS blog_count,
                    COALESCE(SUM(b.view_count), 0) AS views
             FROM categories c
             LEFT JOIN blogs b ON b.category_id = c.id
             GROUP BY c.id
             ORDER BY blog_count DESC'
        );
    }
}
