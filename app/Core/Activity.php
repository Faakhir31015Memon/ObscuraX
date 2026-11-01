<?php

declare(strict_types=1);

namespace App\Core;

final class Activity
{
    public static function user(?int $userId, string $action, string $description = ''): void
    {
        if ($userId === null || $userId <= 0) {
            return;
        }
        try {
            Database::insert('user_activity_logs', [
                'user_id' => $userId,
                'action' => mb_substr($action, 0, 64),
                'description' => mb_substr($description, 0, 255),
                'ip_address' => client_ip(),
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            error_log('[ObscuraX] activity log failed: ' . $e->getMessage());
        }
    }

    public static function admin(int $adminId, string $action, string $description = '', ?string $targetType = null, ?int $targetId = null): void
    {
        try {
            Database::insert('admin_activity_logs', [
                'admin_id' => $adminId,
                'action' => mb_substr($action, 0, 64),
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => mb_substr($description, 0, 255),
                'ip_address' => client_ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            error_log('[ObscuraX] admin log failed: ' . $e->getMessage());
        }
    }
}
