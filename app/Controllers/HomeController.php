<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Stat;
use App\Models\Tag;

final class HomeController extends Controller
{
    public function index(): void
    {
        $featured = Blog::featured(3);
        if (count($featured) < 3) {
            $featured = Blog::latest(3);
        }

        $categories = Category::all(true);
        $popular = Blog::popular(5);
        $latest = Blog::latest(6);
        $mostLiked = Blog::mostLiked(3);
        $tags = Tag::popular(14);

        $overview = Stat::overview();
        $stats = [
            'blogs' => $overview['blogs_published'],
            'readers' => $overview['readers_total'],
            'verified' => $overview['readers_verified'],
            'views' => $overview['blog_views'],
            'likes' => $overview['likes_total'],
        ];

        $this->view('home', [
            'pageTitle' => setting('site_name', 'ObscuraX') . ' — ' . setting('site_tagline', 'Explore the Unknown'),
            'metaDescription' => setting('site_description', 'ObscuraX explores black magic and the occult, sexual health and education, and ethical hacking and cybersecurity.'),
            'bodyClass' => 'page-home',
            'featured' => $featured,
            'categories' => $categories,
            'popular' => $popular,
            'latest' => $latest,
            'mostLiked' => $mostLiked,
            'tags' => $tags,
            'stats' => $stats,
        ]);
    }

    public function subscribe(): void
    {
        $validator = new Validator($_POST, ['email' => 'required|email|max:190']);
        if ($validator->fails()) {
            if (is_ajax()) {
                json_response(['ok' => false, 'message' => (string) $validator->firstError()], 422);
            }
            flash('error', (string) $validator->firstError());
            back('/');
        }

        $result = Newsletter::subscribe((string) input('email'), client_ip());
        $message = $result['message'];

        if (is_ajax()) {
            json_response(['ok' => true, 'message' => $message]);
        }
        flash($result['status'] === 'exists' ? 'info' : 'success', $message);
        back('/');
    }

    public function unsubscribe(string $token): void
    {
        $token = preg_replace('/[^a-f0-9]/i', '', $token) ?? '';
        $done = Newsletter::unsubscribeByToken($token);

        $this->view('newsletter/unsubscribed', [
            'pageTitle' => 'Newsletter — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Newsletter subscription status.',
            'done' => $done,
        ], 'layouts/app');
    }
}
