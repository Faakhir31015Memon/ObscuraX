<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class RequireSuperAdmin
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Session::put('_intended', current_path());
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
        if (!Auth::isSuperAdmin()) {
            abort(403, 'Super administrator access is required.');
        }
        if (Auth::mustChangePassword() && current_path() !== '/admin/password') {
            redirect('/admin/password');
        }
    }
}
