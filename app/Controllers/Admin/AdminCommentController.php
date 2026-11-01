<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Comment;

final class AdminCommentController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => (string) ($_GET['status'] ?? ''),
        ];
        if (!Auth::isSuperAdmin()) {
            $filters['author_id'] = (int) Auth::id();
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $comments = Comment::paginate($filters, $page, 15);

        $replies = [];
        if ($comments['items'] !== []) {
            $ids = array_map(static fn (array $c): int => (int) $c['id'], $comments['items']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = Database::fetchAll(
                "SELECT cr.*, u.username, u.avatar
                 FROM comment_replies cr INNER JOIN users u ON u.id = cr.user_id
                 WHERE cr.comment_id IN ($placeholders)
                 ORDER BY cr.created_at ASC",
                $ids
            );
            foreach ($rows as $row) {
                $replies[(int) $row['comment_id']][] = $row;
            }
        }

        $this->view('admin/comments', [
            'pageTitle' => 'Comments — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Moderate and reply to comments.',
            'bodyClass' => 'page-admin-comments',
            'comments' => $comments,
            'replies' => $replies,
            'filters' => $filters,
            'canDelete' => Auth::can('comment.delete'),
        ], 'layouts/admin');
    }

    public function status(string $id): void
    {
        $comment = Comment::find((int) $id);
        if ($comment === null) {
            abort(404, 'That comment could not be found.');
        }
        $this->assertInScope($comment);

        $replyId = (int) input('reply_id', 0);
        if ($replyId > 0) {
            $reply = Comment::findReply($replyId);
            if ($reply === null || (int) $reply['comment_id'] !== (int) $comment['id']) {
                abort(404, 'That reply could not be found.');
            }
            $status = $reply['status'] === 'hidden' ? 'published' : 'hidden';
            Comment::setReplyStatus($replyId, $status);
            Activity::admin((int) Auth::id(), 'comment.reply.status', ucfirst($status) . ' a reply on "' . $comment['blog_title'] . '"', 'comment_reply', $replyId);
        } else {
            $status = $comment['status'] === 'hidden' ? 'published' : 'hidden';
            Comment::setStatus((int) $comment['id'], $status);
            Activity::admin((int) Auth::id(), 'comment.status', ucfirst($status) . ' a comment on "' . $comment['blog_title'] . '"', 'comment', (int) $comment['id']);
        }

        flash('success', 'Comment visibility updated.');
        back('/admin/comments');
    }

    public function reply(string $id): void
    {
        $comment = Comment::find((int) $id);
        if ($comment === null) {
            abort(404, 'That comment could not be found.');
        }
        $this->assertInScope($comment);

        $validator = new Validator($_POST, ['content' => 'required|min:2|max:4000']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/admin/comments');
        }

        $replyId = Comment::addReply((int) $comment['id'], (int) Auth::id(), (string) input('content'));
        Activity::admin((int) Auth::id(), 'comment.reply', 'Replied to a comment on "' . $comment['blog_title'] . '"', 'comment_reply', $replyId);

        flash('success', 'Reply posted.');
        back('/admin/comments');
    }

    public function destroy(string $id): void
    {
        if (!Auth::can('comment.delete')) {
            abort(403, 'You do not have permission to delete comments.');
        }

        $comment = Comment::find((int) $id);
        if ($comment === null) {
            abort(404, 'That comment could not be found.');
        }
        $this->assertInScope($comment);

        $replyId = (int) input('reply_id', 0);
        if ($replyId > 0) {
            $reply = Comment::findReply($replyId);
            if ($reply === null || (int) $reply['comment_id'] !== (int) $comment['id']) {
                abort(404, 'That reply could not be found.');
            }
            Comment::deleteReply($replyId);
            Activity::admin((int) Auth::id(), 'comment.reply.delete', 'Deleted a reply on "' . $comment['blog_title'] . '"', 'comment_reply', $replyId);
        } else {
            Comment::delete((int) $comment['id']);
            Activity::admin((int) Auth::id(), 'comment.delete', 'Deleted a comment on "' . $comment['blog_title'] . '"', 'comment', (int) $comment['id']);
        }

        flash('success', 'Comment removed.');
        back('/admin/comments');
    }

    private function assertInScope(array $comment): void
    {
        if (Auth::isSuperAdmin()) {
            return;
        }
        $authorId = (int) Database::value('SELECT author_id FROM blogs WHERE id = ?', [(int) $comment['blog_id']], 0);
        if ($authorId !== (int) Auth::id()) {
            abort(403, 'You can only moderate comments on articles you wrote.');
        }
    }
}
