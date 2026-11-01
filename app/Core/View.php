<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $shared = self::sharedData();
        $data = array_merge($shared, $data);

        $content = self::capture($template, $data);

        if ($layout === null) {
            echo $content;
            return;
        }

        $data['content'] = $content;
        echo self::capture($layout, $data);
    }

    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, array_merge(self::sharedData(), $data));
    }

    /** @return array<string,mixed> */
    private static function sharedData(): array
    {
        $user = null;
        try {
            $user = Auth::user();
        } catch (\Throwable) {
            $user = null;
        }

        return [
            'flashes' => Session::takeFlashes(),
            'authUser' => $user,
            'currentPath' => current_path(),
            'siteName' => setting('site_name', (string) config('app.name', 'ObscuraX')),
            'siteTagline' => setting('site_tagline', 'Explore the Unknown. Discover Beyond the Ordinary.'),
            'navCategories' => self::navCategories(),
            'unreadMessages' => 0,
        ];
    }

    /** @return list<array> */
    private static function navCategories(): array
    {
        try {
            return Database::fetchAll(
                'SELECT c.id, c.name, c.slug, c.accent_color, c.icon, COUNT(b.id) AS blog_count
                 FROM categories c
                 LEFT JOIN blogs b ON b.category_id = c.id AND b.status = "published"
                 WHERE c.is_active = 1
                 GROUP BY c.id
                 ORDER BY c.sort_order ASC, c.name ASC
                 LIMIT 8'
            );
        } catch (\Throwable) {
            return [];
        }
    }

    private static function capture(string $template, array $data): string
    {
        $file = ROOT_PATH . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    public static function renderError(int $code, ?\Throwable $exception = null, bool $debug = false, string $message = ''): void
    {
        $titles = [
            403 => 'Access denied',
            404 => 'Page not found',
            405 => 'Method not allowed',
            419 => 'Session expired',
            429 => 'Too many requests',
            500 => 'Something went wrong',
        ];

        $file = ROOT_PATH . '/views/errors/' . $code . '.php';
        if (!is_file($file)) {
            $file = ROOT_PATH . '/views/errors/500.php';
        }

        $vars = [
            'code' => $code,
            'title' => $titles[$code] ?? 'Error',
            'message' => $message,
            'exception' => $exception,
            'debug' => $debug,
        ];

        extract($vars, EXTR_SKIP);
        http_response_code($code);
        include ROOT_PATH . '/views/layouts/error.php';
    }
}
