<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class ManageAdminController extends Controller
{
    public function index(): void
    {
        $admins = Database::fetchAll(
            "SELECT u.id, u.username, u.email, u.full_name, u.avatar, u.status, u.created_at, u.last_login_at,
                    u.must_change_password, r.slug AS role_slug, r.name AS role_name,
                    a.title, a.department, a.is_disabled, a.created_at AS admin_since
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN admins a ON a.user_id = u.id
             WHERE r.slug IN ('admin', 'super_admin')
             ORDER BY r.level DESC, u.created_at ASC"
        );

        $this->view('admin/admins', [
            'pageTitle' => 'Administrators — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage administrator accounts.',
            'bodyClass' => 'page-admin-admins',
            'admins' => $admins,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $validator = new Validator($_POST, [
            'username' => 'required|alpha_dash|min:3|max:24|unique:users,username',
            'email' => 'required|email|max:190|unique:users,email',
            'full_name' => 'nullable|max:120',
            'password' => 'required|strong_password|min:8|max:72',
            'title' => 'nullable|max:96',
            'department' => 'nullable|max:96',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/admins');
        }

        $now = now();
        $userId = Database::transaction(function () use ($now): int {
            $id = User::create([
                'role_id' => User::roleId('admin'),
                'username' => trim((string) input('username')),
                'email' => mb_strtolower(trim((string) input('email'))),
                'password_hash' => password_hash((string) input('password'), PASSWORD_DEFAULT),
                'full_name' => trim((string) input('full_name', '')) ?: null,
                'status' => 'active',
                'email_verified_at' => $now,
                'activated_at' => $now,
                'must_change_password' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Database::insert('admins', [
                'user_id' => $id,
                'title' => trim((string) input('title', 'Administrator')) ?: 'Administrator',
                'department' => trim((string) input('department', '')) ?: null,
                'created_by' => Auth::id(),
                'created_at' => $now,
            ]);

            return $id;
        });

        Activity::admin((int) Auth::id(), 'admin.create', 'Created administrator @' . input('username'), 'user', $userId);
        Session::clearOld();

        flash('success', 'Administrator created. They must choose a new password at first sign-in.');
        redirect('/admin/admins');
    }

    public function update(string $id): void
    {
        $user = $this->guardTarget($id, false);

        $validator = new Validator($_POST, [
            'username' => 'required|alpha_dash|min:3|max:24|unique:users,username,' . (int) $user['id'],
            'email' => 'required|email|max:190|unique:users,email,' . (int) $user['id'],
            'full_name' => 'nullable|max:120',
            'title' => 'nullable|max:96',
            'department' => 'nullable|max:96',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/admins');
        }

        User::update((int) $user['id'], [
            'username' => trim((string) input('username')),
            'email' => mb_strtolower(trim((string) input('email'))),
            'full_name' => trim((string) input('full_name', '')) ?: null,
            'updated_at' => now(),
        ]);

        Database::query(
            'UPDATE admins SET title = ?, department = ? WHERE user_id = ?',
            [trim((string) input('title', 'Administrator')) ?: 'Administrator', trim((string) input('department', '')) ?: null, (int) $user['id']]
        );

        Activity::admin((int) Auth::id(), 'admin.update', 'Updated administrator @' . input('username'), 'user', (int) $user['id']);
        Session::clearOld();

        flash('success', 'Administrator updated.');
        redirect('/admin/admins');
    }

    public function toggleDisabled(string $id): void
    {
        $user = $this->guardTarget($id, true);
        if ($user['role_slug'] === 'super_admin') {
            flash('error', 'Super administrators cannot be disabled here.');
            redirect('/admin/admins');
        }

        $disabled = (int) ($user['is_disabled'] ?? 0) === 1 ? 0 : 1;
        Database::query('UPDATE admins SET is_disabled = ? WHERE user_id = ?', [$disabled, (int) $user['id']]);
        User::setStatus((int) $user['id'], $disabled ? 'inactive' : 'active');

        Activity::admin(
            (int) Auth::id(),
            $disabled ? 'admin.disable' : 'admin.enable',
            ($disabled ? 'Disabled' : 'Enabled') . ' administrator @' . $user['username'],
            'user',
            (int) $user['id']
        );

        flash('success', '@' . $user['username'] . ' has been ' . ($disabled ? 'disabled' : 'enabled') . '.');
        redirect('/admin/admins');
    }

    public function destroy(string $id): void
    {
        $user = $this->guardTarget($id, true);

        if ($user['role_slug'] === 'super_admin') {
            $supers = (int) Database::value("SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug = 'super_admin' AND u.status = 'active'", [], 0);
            if ($supers <= 1) {
                flash('error', 'The last active super administrator cannot be removed.');
                redirect('/admin/admins');
            }
        }

        Database::transaction(function () use ($user): void {
            Database::query('DELETE FROM admins WHERE user_id = ?', [(int) $user['id']]);
            User::update((int) $user['id'], [
                'role_id' => User::roleId('user'),
                'status' => 'active',
                'updated_at' => now(),
            ]);
        });

        Activity::admin((int) Auth::id(), 'admin.demote', 'Demoted @' . $user['username'] . ' to reader', 'user', (int) $user['id']);

        flash('success', '@' . $user['username'] . ' is now a reader account.');
        redirect('/admin/admins');
    }

    private function guardTarget(string $id, bool $allowSelf): array
    {
        $user = Database::fetch(
            "SELECT u.*, r.slug AS role_slug, a.is_disabled
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN admins a ON a.user_id = u.id
             WHERE u.id = ? LIMIT 1",
            [(int) $id]
        );

        if ($user === null || !in_array($user['role_slug'], ['admin', 'super_admin'], true)) {
            abort(404, 'That administrator could not be found.');
        }
        if (!$allowSelf && (int) $user['id'] === (int) Auth::id()) {
            flash('error', 'Edit your own details from the profile page.');
            redirect('/admin/admins');
        }
        if ($allowSelf && (int) $user['id'] === (int) Auth::id()) {
            flash('error', 'You cannot change or remove your own administrator account.');
            redirect('/admin/admins');
        }

        return $user;
    }
}
