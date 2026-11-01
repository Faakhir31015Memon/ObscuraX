<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Mailer;

final class Verification
{
    public static function issue(int $userId, string $email): string
    {
        $token = bin2hex(random_bytes(32));
        Database::query('DELETE FROM email_verifications WHERE user_id = ? AND verified_at IS NULL', [$userId]);
        Database::insert('email_verifications', [
            'user_id' => $userId,
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 86400),
            'created_at' => now(),
        ]);
        return $token;
    }

    public static function send(string $email, string $username, string $token): void
    {
        Mailer::send(
            $email,
            'Verify your ObscuraX account',
            Mailer::render('verify-email', [
                'username' => $username,
                'verifyUrl' => url('/verify-email/' . $token),
            ])
        );
    }

    public static function devLinkEnabled(): bool
    {
        return (bool) config('app.debug') && (string) config('mail.driver') === 'log';
    }
}
