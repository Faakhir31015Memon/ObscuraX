<?php

declare(strict_types=1);

namespace App\Core;

final class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $from = (string) config('mail.from', 'noreply@obscurax.local');
        $fromName = (string) config('mail.from_name', config('app.name', 'ObscuraX'));
        $driver = (string) config('mail.driver', 'log');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($driver === 'log') {
            return self::writeLog($to, $subject, $htmlBody, $from, $fromName);
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::encodeName($fromName) . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: ObscuraX',
        ];

        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $textBody = $textBody !== '' ? $textBody : trim(strip_tags(str_replace(['<br>', '</p>'], "\n", $htmlBody)));

        $sent = @mail($to, $subjectEncoded, $htmlBody, implode("\r\n", $headers));
        if (!$sent) {
            error_log('[ObscuraX] mail() failed for ' . $to . ' - falling back to log driver');
            self::writeLog($to, $subject, $htmlBody, $from, $fromName);
        }
        return $sent;
    }

    public static function render(string $view, array $data = []): string
    {
        $file = ROOT_PATH . '/views/emails/' . $view . '.php';
        if (!is_file($file)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    public static function lastLogFile(): ?string
    {
        return $_SESSION['_last_mail_log'] ?? null;
    }

    private static function encodeName(string $name): string
    {
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }

    private static function writeLog(string $to, string $subject, string $body, string $from, string $fromName): bool
    {
        $dir = ROOT_PATH . '/storage/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.html';
        $meta = sprintf(
            "<!--\nTo: %s\nFrom: %s <%s>\nSubject: %s\nDate: %s\n-->\n",
            $to,
            $fromName,
            $from,
            $subject,
            now()
        );
        @file_put_contents($file, $meta . $body);
        $_SESSION['_last_mail_log'] = 'storage/mail/' . basename($file);
        error_log('[ObscuraX] Mail logged to ' . $file . ' (to: ' . $to . ')');
        return true;
    }
}
