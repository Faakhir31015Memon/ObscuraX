<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Stat;
use App\Models\User;

final class ManageUserController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'role' => (string) ($_GET['role'] ?? ''),
            'status' => (string) ($_GET['status'] ?? ''),
            'verified' => (string) ($_GET['verified'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $this->view('admin/users/index', [
            'pageTitle' => 'Users — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage reader accounts.',
            'bodyClass' => 'page-admin-users',
            'users' => User::paginate($filters, $page, 15),
            'filters' => $filters,
            'roles' => Database::fetchAll('SELECT slug, name FROM roles ORDER BY level ASC'),
            'stats' => Stat::overview(),
        ], 'layouts/admin');
    }

    public function show(string $id): void
    {
        $user = User::find((int) $id);
        if ($user === null) {
            abort(404, 'That user could not be found.');
        }

        $activity = Database::fetchAll(
            'SELECT * FROM user_activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20',
            [(int) $user['id']]
        );
        $comments = Database::fetchAll(
            'SELECT c.*, b.title AS blog_title, b.slug AS blog_slug
             FROM comments c INNER JOIN blogs b ON b.id = c.blog_id
             WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 8',
            [(int) $user['id']]
        );
        $blogs = Database::fetchAll(
            'SELECT id, title, slug, status, view_count, created_at FROM blogs WHERE author_id = ? ORDER BY created_at DESC LIMIT 10',
            [(int) $user['id']]
        );
        $counts = [
            'blogs' => (int) Database::value('SELECT COUNT(*) FROM blogs WHERE author_id = ?', [(int) $user['id']], 0),
            'comments' => (int) Database::value('SELECT COUNT(*) FROM comments WHERE user_id = ?', [(int) $user['id']], 0),
            'likes' => (int) Database::value('SELECT COUNT(*) FROM likes WHERE user_id = ?', [(int) $user['id']], 0),
        ];

        $this->view('admin/users/show', [
            'pageTitle' => $user['username'] . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'User account details.',
            'bodyClass' => 'page-admin-user',
            'user' => $user,
            'activity' => $activity,
            'comments' => $comments,
            'blogs' => $blogs,
            'counts' => $counts,
            'isPrivileged' => in_array($user['role_slug'], ['admin', 'super_admin'], true),
        ], 'layouts/admin');
    }

    public function activate(string $id): void
    {
        $user = $this->guardTarget($id);
        if ($user['status'] === 'active') {
            flash('info', 'That account is already active.');
            back('/admin/users/' . $user['id']);
        }

        User::setStatus((int) $user['id'], 'active');
        Activity::admin((int) Auth::id(), 'user.activate', 'Activated @' . $user['username'], 'user', (int) $user['id']);
        Activity::user((int) $user['id'], 'account_activated', 'Account activated by an administrator');

        flash('success', '@' . $user['username'] . ' is now active.');
        back('/admin/users/' . $user['id']);
    }

    public function deactivate(string $id): void
    {
        $user = $this->guardTarget($id);
        User::setStatus((int) $user['id'], 'inactive');
        Activity::admin((int) Auth::id(), 'user.deactivate', 'Deactivated @' . $user['username'], 'user', (int) $user['id']);

        flash('success', '@' . $user['username'] . ' has been deactivated.');
        back('/admin/users/' . $user['id']);
    }

    public function ban(string $id): void
    {
        $user = $this->guardTarget($id);
        $reason = trim((string) input('reason', ''));
        if (mb_strlen($reason) < 3) {
            flash('error', 'Give a short reason for the ban.');
            back('/admin/users/' . $user['id']);
        }

        User::setStatus((int) $user['id'], 'banned', ['banned_reason' => mb_substr($reason, 0, 255)]);
        Activity::admin((int) Auth::id(), 'user.ban', 'Banned @' . $user['username'] . ': ' . $reason, 'user', (int) $user['id']);

        flash('success', '@' . $user['username'] . ' has been banned.');
        back('/admin/users/' . $user['id']);
    }

    public function unban(string $id): void
    {
        $user = $this->guardTarget($id);
        if ($user['status'] !== 'banned') {
            flash('info', 'That account is not currently banned.');
            back('/admin/users/' . $user['id']);
        }

        User::setStatus((int) $user['id'], 'active');
        Activity::admin((int) Auth::id(), 'user.unban', 'Unbanned @' . $user['username'], 'user', (int) $user['id']);
        Activity::user((int) $user['id'], 'account_unbanned', 'Account ban lifted by an administrator');

        flash('success', '@' . $user['username'] . ' has been unbanned.');
        back('/admin/users/' . $user['id']);
    }

    public function remove(string $id): void
    {
        $user = $this->guardTarget($id);
        User::setStatus((int) $user['id'], 'removed');
        Activity::admin((int) Auth::id(), 'user.remove', 'Removed @' . $user['username'], 'user', (int) $user['id']);

        flash('success', '@' . $user['username'] . ' has been marked as removed.');
        back('/admin/users/' . $user['id']);
    }

    private function guardTarget(string $id): array
    {
        $user = User::find((int) $id);
        if ($user === null) {
            abort(404, 'That user could not be found.');
        }
        if ((int) $user['id'] === (int) Auth::id()) {
            flash('error', 'You cannot change the status of your own account here.');
            back('/admin/users/' . $user['id']);
        }
        if (in_array($user['role_slug'], ['admin', 'super_admin'], true)) {
            flash('error', 'Administrator accounts are managed from the Administrators page.');
            back('/admin/users/' . $user['id']);
        }
        return $user;
    }
}
