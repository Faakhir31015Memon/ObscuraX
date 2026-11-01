<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

final class LogController extends Controller
{
    public function index(): void
    {
        $type = (string) ($_GET['type'] ?? 'admin');
        if (!in_array($type, ['admin', 'user'], true)) {
            $type = 'admin';
        }
        $q = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;

        $table = $type === 'admin' ? 'admin_activity_logs' : 'user_activity_logs';
        $userColumn = $type === 'admin' ? 'admin_id' : 'user_id';

        $where = '1 = 1';
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where .= ' AND (l.action LIKE ? OR l.description LIKE ? OR u.username LIKE ?)';
            array_push($params, $like, $like, $like);
        }

        $total = (int) Database::value(
            "SELECT COUNT(*) FROM $table l LEFT JOIN users u ON u.id = l.$userColumn WHERE $where",
            $params,
            0
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $logs = Database::fetchAll(
            "SELECT l.*, u.username, u.full_name, u.avatar
             FROM $table l LEFT JOIN users u ON u.id = l.$userColumn
             WHERE $where
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        $this->view('admin/logs', [
            'pageTitle' => 'Activity logs — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Administrator and reader activity logs.',
            'bodyClass' => 'page-admin-logs',
            'logs' => $logs,
            'type' => $type,
            'q' => $q,
            'paginator' => [
                'items' => $logs,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
            ],
        ], 'layouts/admin');
    }
}
