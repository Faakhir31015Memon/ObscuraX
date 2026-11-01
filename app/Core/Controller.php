<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    /**
     * Validates input; on failure flashes the first error, restores the form and
     * redirects back so the user can correct the problem.
     */
    protected function validateOrFail(array $input, array $rules, ?string $fallback = null): array
    {
        $validator = new Validator($input, $rules);
        if ($validator->fails()) {
            Session::flashOld($input);
            flash('error', (string) $validator->firstError());
            back($fallback ?? '/');
        }
        Session::clearOld();
        return $validator->validated();
    }

    protected function authorize(string $permission): void
    {
        if (!Auth::can($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    protected function requireAdmin(): array
    {
        $user = Auth::user();
        if ($user === null || !Auth::isAdmin()) {
            abort(403, 'Administrator access is required.');
        }
        return $user;
    }

    protected function requireSuperAdmin(): array
    {
        $user = Auth::user();
        if ($user === null || !Auth::isSuperAdmin()) {
            abort(403, 'Super administrator access is required.');
        }
        return $user;
    }

    protected function success(string $message, string $path): never
    {
        flash('success', $message);
        redirect($path);
    }

    protected function error(string $message, string $path): never
    {
        flash('error', $message);
        redirect($path);
    }
}
