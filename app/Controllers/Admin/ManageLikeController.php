<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Like;

final class ManageLikeController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'blog_id' => (int) ($_GET['blog_id'] ?? 0),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $this->view('admin/likes', [
            'pageTitle' => 'Likes — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Every like across the platform.',
            'bodyClass' => 'page-admin-likes',
            'likes' => Like::paginate($filters, $page, 20),
            'filters' => $filters,
            'blogs' => Database::fetchAll('SELECT id, title FROM blogs ORDER BY title ASC'),
        ], 'layouts/admin');
    }

    public function destroy(string $id): void
    {
        $row = Database::fetch(
            'SELECT l.*, u.username, b.title FROM likes l
             INNER JOIN users u ON u.id = l.user_id
             INNER JOIN blogs b ON b.id = l.blog_id
             WHERE l.id = ? LIMIT 1',
            [(int) $id]
        );
        if ($row === null) {
            abort(404, 'That like could not be found.');
        }

        Like::delete((int) $row['id']);
        Activity::admin((int) Auth::id(), 'like.delete', 'Removed @' . $row['username'] . ' like on "' . $row['title'] . '"', 'like', (int) $row['id']);

        flash('success', 'Like removed.');
        back('/admin/likes');
    }
}
