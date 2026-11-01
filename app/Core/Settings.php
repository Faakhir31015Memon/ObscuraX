<?php

declare(strict_types=1);

namespace App\Core;

final class Settings
{
    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** @return array<string,string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        try {
            $rows = Database::fetchAll('SELECT setting_key, setting_value FROM website_settings');
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = (string) $row['setting_value'];
            }
            self::$cache = $settings;
        } catch (\Throwable) {
            self::$cache = [];
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $all = self::all();
        return array_key_exists($key, $all) && $all[$key] !== '' ? $all[$key] : $default;
    }

    public static function has(string $key): bool
    {
        return self::get($key, '') !== '';
    }

    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        $existing = Database::fetch('SELECT id FROM website_settings WHERE setting_key = ?', [$key]);
        if ($existing) {
            Database::update('website_settings', ['setting_value' => (string) $value], 'setting_key = :key', ['key' => $key]);
        } else {
            Database::insert('website_settings', [
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'setting_group' => $group,
            ]);
        }
        self::$cache = null;
    }

    public static function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            self::set((string) $key, is_scalar($value) ? (string) $value : '', $group);
        }
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
