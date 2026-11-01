<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Validator;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Like;

final class EngagementController extends Controller
{
    public function like(string $id): void
    {
        $user = Auth::user();
        $blog = Blog::find((int) $id);

        if ($blog === null || $blog['status'] !== 'published') {
            if (is_ajax()) {
                json_response(['ok' => false, 'message' => 'Article not found.'], 404);
            }
            abort(404);
        }

        $result = Like::toggle((int) $blog['id'], (int) $user['id']);
        Activity::user((int) $user['id'], $result['liked'] ? 'like' : 'unlike', 'Article: ' . $blog['title']);

        if (is_ajax()) {
            json_response([
                'ok' => true,
                'liked' => $result['liked'],
                'count' => $result['count'],
                'message' => $result['liked'] ? 'Saved to your liked articles.' : 'Removed from your liked articles.',
            ]);
        }

        flash('success', $result['liked'] ? 'You liked this article.' : 'Your like was removed.');
        redirect('/blog/' . $blog['slug']);
    }

    public function comment(string $id): void
    {
        $user = Auth::user();
        $blog = Blog::find((int) $id);

        if ($blog === null || $blog['status'] !== 'published') {
            abort(404);
        }
        if ((int) $blog['allow_comments'] !== 1) {
            flash('error', 'Comments are closed for this article.');
            redirect('/blog/' . $blog['slug']);
        }

        $validator = new Validator($_POST, ['content' => 'required|min:2|max:2000']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/blog/' . $blog['slug']);
        }

        $content = trim((string) input('content'));
        $commentId = Comment::create((int) $blog['id'], (int) $user['id'], $content);
        Activity::user((int) $user['id'], 'comment', 'Commented on: ' . $blog['title']);

        flash('success', 'Your comment has been posted.');
        redirect('/blog/' . $blog['slug'] . '#comment-' . $commentId);
    }

    public function reply(string $id): void
    {
        $user = Auth::user();
        $comment = Comment::find((int) $id);

        if ($comment === null || (int) $comment['allow_comments'] !== 1) {
            abort(404);
        }

        $validator = new Validator($_POST, ['content' => 'required|min:2|max:2000']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/blog/' . $comment['blog_slug']);
        }

        $replyId = Comment::addReply((int) $comment['id'], (int) $user['id'], trim((string) input('content')));
        Activity::user((int) $user['id'], 'reply', 'Replied on: ' . $comment['blog_title']);

        flash('success', 'Your reply has been posted.');
        redirect('/blog/' . $comment['blog_slug'] . '#reply-' . $replyId);
    }

    public function csrfToken(): never
    {
        json_response(['token' => Csrf::token()]);
    }
}
