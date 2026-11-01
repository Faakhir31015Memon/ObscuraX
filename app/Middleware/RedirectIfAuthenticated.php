<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class RedirectIfAuthenticated
{
    public static function handle(): void
    {
        if (Auth::check()) {
            redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
        }
    }
}
