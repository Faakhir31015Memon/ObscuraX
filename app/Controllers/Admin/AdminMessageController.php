<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Contact;

final class AdminMessageController extends Controller
{
    public function index(): void
    {
        $status = (string) ($_GET['status'] ?? '');
        if (!in_array($status, ['new', 'read', 'archived'], true)) {
            $status = '';
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $messages = Contact::paginate($page, 15, $status);

        $counts = ['new' => 0, 'read' => 0, 'archived' => 0];
        foreach (Database::fetchAll('SELECT status, COUNT(*) AS total FROM contact_messages GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        $counts['total'] = array_sum($counts);

        $this->view('admin/messages', [
            'pageTitle' => 'Messages — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Contact form messages.',
            'bodyClass' => 'page-admin-messages',
            'messages' => $messages,
            'filters' => ['status' => $status],
            'counts' => $counts,
        ], 'layouts/admin');
    }

    public function markRead(string $id): void
    {
        $message = $this->findOrFail($id);
        Contact::markRead((int) $message['id']);
        Activity::admin((int) Auth::id(), 'message.read', 'Marked message from ' . $message['email'] . ' as read', 'message', (int) $message['id']);

        flash('success', 'Message marked as read.');
        back('/admin/messages');
    }

    public function archive(string $id): void
    {
        $message = $this->findOrFail($id);
        Contact::archive((int) $message['id']);
        Activity::admin((int) Auth::id(), 'message.archive', 'Archived message from ' . $message['email'], 'message', (int) $message['id']);

        flash('success', 'Message archived.');
        back('/admin/messages');
    }

    public function destroy(string $id): void
    {
        $message = $this->findOrFail($id);
        Contact::delete((int) $message['id']);
        Activity::admin((int) Auth::id(), 'message.delete', 'Deleted message from ' . $message['email'], 'message', (int) $message['id']);

        flash('success', 'Message deleted.');
        back('/admin/messages');
    }

    private function findOrFail(string $id): array
    {
        $message = Contact::find((int) $id);
        if ($message === null) {
            abort(404, 'That message could not be found.');
        }
        return $message;
    }
}
