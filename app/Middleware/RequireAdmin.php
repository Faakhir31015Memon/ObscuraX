<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class RequireAdmin
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Session::put('_intended', current_path());
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
        if (!Auth::isAdmin()) {
            abort(403, 'Administrator access is required for this area.');
        }
        if (Auth::mustChangePassword() && current_path() !== '/admin/password') {
            redirect('/admin/password');
        }
    }
}
