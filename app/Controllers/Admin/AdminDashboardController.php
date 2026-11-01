<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Stat;
use App\Models\User;

final class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $isSuper = Auth::isSuperAdmin();

        $stats = Stat::overview();
        $monthly = Stat::monthlySeries(12);
        $viewsByDay = Stat::viewsByDay(14);

        $myBlogs = Blog::forAdmin([], (int) $user['id'], false, 100);
        $myBlogsTotal = count($myBlogs);
        $myPublished = count(array_filter($myBlogs, static fn (array $b): bool => $b['status'] === 'published'));
        $myViews = array_sum(array_map(static fn (array $b): int => (int) $b['view_count'], $myBlogs));

        $this->view('admin/dashboard', [
            'pageTitle' => 'Dashboard — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Administration dashboard.',
            'bodyClass' => 'page-admin-dashboard',
            'charts' => true,
            'isSuper' => $isSuper,
            'stats' => $stats,
            'monthly' => $monthly,
            'viewsByDay' => $viewsByDay,
            'myBlogs' => array_slice($myBlogs, 0, 6),
            'myBlogsTotal' => $myBlogsTotal,
            'myPublished' => $myPublished,
            'myViews' => $myViews,
            'topBlogs' => Stat::topBlogs('views', 5),
            'topUsers' => Stat::topUsers(5),
            'recentRegistrations' => Stat::recentRegistrations(6),
            'recentComments' => Comment::recent(6),
            'blogStatus' => Blog::countsByStatus(),
            'online' => $isSuper ? User::recentlyOnline(5, 8) : [],
        ], 'layouts/admin');
    }

    public function password(): void
    {
        $this->view('admin/password', [
            'pageTitle' => 'Change password — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Update your administrator password.',
            'bodyClass' => 'page-admin-password',
            'user' => Auth::user(),
        ], 'layouts/admin');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();
        $current = (string) input('current_password');
        $new = (string) input('password');

        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
            back('/admin/password');
        }

        $validator = new Validator($_POST, [
            'password' => 'required|strong_password|min:8|max:72|confirmed',
        ]);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/admin/password');
        }

        if (password_verify($new, $user['password_hash'])) {
            flash('error', 'Choose a password that differs from your current one.');
            back('/admin/password');
        }

        User::updatePassword((int) $user['id'], $new);
        Activity::admin((int) $user['id'], 'password.change', 'Password changed from the admin panel');
        Activity::user((int) $user['id'], 'password_change', 'Password changed from the admin panel');
        Auth::refresh();
        Session::regenerate();

        flash('success', 'Your password has been updated.');
        redirect('/admin');
    }
}
