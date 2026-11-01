<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class RequirePermission
{
    public static function handle(string $permission): void
    {
        if (!Auth::check()) {
            Session::put('_intended', current_path());
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
        if (!Auth::can($permission)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
}
