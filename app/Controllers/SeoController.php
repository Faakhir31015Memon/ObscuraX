<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Blog;
use App\Models\Category;

final class SeoController
{
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $urls = [];
        foreach (['/', '/blogs', '/about', '/contact', '/privacy', '/terms'] as $path) {
            $urls[] = ['loc' => url($path), 'priority' => $path === '/' ? '1.0' : '0.6', 'lastmod' => date('Y-m-d')];
        }

        foreach (Category::all(true) as $category) {
            $urls[] = ['loc' => url('/category/' . $category['slug']), 'priority' => '0.8', 'lastmod' => date('Y-m-d')];
        }

        $blogs = Database::fetchAll(
            'SELECT slug, updated_at, published_at FROM blogs WHERE status = "published" AND published_at <= NOW() ORDER BY published_at DESC LIMIT 5000'
        );
        foreach ($blogs as $blog) {
            $urls[] = [
                'loc' => url('/blog/' . $blog['slug']),
                'priority' => '0.9',
                'lastmod' => date('Y-m-d', strtotime((string) ($blog['updated_at'] ?: $blog['published_at'] ?: 'now'))),
            ];
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $entry) {
            echo '  <url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1) . '</loc>'
                . '<lastmod>' . $entry['lastmod'] . '</lastmod>'
                . '<priority>' . $entry['priority'] . '</priority></url>' . "\n";
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /account',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /uploads/',
            'Allow: /',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];
        echo implode("\n", $lines);
    }
}
