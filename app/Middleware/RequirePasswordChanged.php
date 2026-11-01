<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class RequirePasswordChanged
{
    private const ALLOWED = ['/account/security', '/logout'];

    public static function handle(): void
    {
        if (!Auth::mustChangePassword()) {
            return;
        }
        if (in_array(current_path(), self::ALLOWED, true)) {
            return;
        }
        flash('error', 'For security reasons you must choose a new password before continuing.');
        redirect('/account/security');
    }
}
