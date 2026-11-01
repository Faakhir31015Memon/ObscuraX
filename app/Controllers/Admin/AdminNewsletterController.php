<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Newsletter;

final class AdminNewsletterController extends Controller
{
    public function index(): void
    {
        $status = (string) ($_GET['status'] ?? '');
        if (!in_array($status, ['subscribed', 'unsubscribed', 'pending'], true)) {
            $status = '';
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $subscribers = Newsletter::paginate($page, 25, $status);

        $counts = ['subscribed' => 0, 'unsubscribed' => 0, 'pending' => 0];
        foreach (Database::fetchAll('SELECT status, COUNT(*) AS total FROM newsletter_subscribers GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        $counts['total'] = array_sum($counts);

        $this->view('admin/newsletter', [
            'pageTitle' => 'Newsletter — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Newsletter subscribers.',
            'bodyClass' => 'page-admin-newsletter',
            'subscribers' => $subscribers,
            'filters' => ['status' => $status],
            'counts' => $counts,
        ], 'layouts/admin');
    }

    public function export(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="obscurax-newsletter-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Email', 'Status', 'IP address', 'Subscribed at', 'Unsubscribed at'], ',', '"', '');

        foreach (Database::fetchAll('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC') as $row) {
            fputcsv($out, [
                $row['email'],
                $row['status'],
                $row['ip_address'],
                $row['confirmed_at'],
                $row['unsubscribed_at'],
            ], ',', '"', '');
        }

        fclose($out);
        Activity::admin((int) Auth::id(), 'newsletter.export', 'Exported the newsletter subscriber list');
        exit;
    }

    public function destroy(string $id): void
    {
        $row = Database::fetch('SELECT * FROM newsletter_subscribers WHERE id = ? LIMIT 1', [(int) $id]);
        if ($row === null) {
            abort(404, 'That subscriber could not be found.');
        }

        Newsletter::delete((int) $row['id']);
        Activity::admin((int) Auth::id(), 'newsletter.delete', 'Removed subscriber ' . $row['email'], 'newsletter', (int) $row['id']);

        flash('success', 'Subscriber removed.');
        back('/admin/newsletter');
    }
}
