<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Tag;
use App\Models\User;

final class BlogController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => (string) input('q', ''),
            'category' => (string) input('category', ''),
            'tag' => (string) input('tag', ''),
            'sort' => (string) input('sort', 'latest'),
        ];
        $page = max(1, (int) input('page', 1));
        $blogs = Blog::paginate($filters, $page, 9);

        $this->view('blogs/index', [
            'pageTitle' => 'All Articles — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Browse every ObscuraX investigation across the occult, sexual health and cybersecurity.',
            'blogs' => $blogs,
            'categories' => Category::all(true),
            'tags' => Tag::popular(12),
            'filters' => $filters,
            'heading' => 'All Investigations',
            'subheading' => 'Long-form research across the unknown, the human and the digital.',
        ]);
    }

    public function show(string $slug): void
    {
        $blog = Blog::findBySlug($slug);
        if ($blog === null) {
            abort(404, 'That article could not be found. It may have been unpublished.');
        }

        Blog::recordView((int) $blog['id'], Auth::id());
        $blog['view_count'] = (int) $blog['view_count'] + 1;

        $comments = Comment::forBlog((int) $blog['id']);
        $tags = Tag::forBlog((int) $blog['id']);
        $related = Blog::related($blog, 3);
        $liked = Like::isLiked((int) $blog['id'], Auth::id());

        $author = User::find((int) $blog['author_id']);
        $authorBlogs = $author ? Blog::byAuthor((int) $author['id'], 4) : [];

        $canonical = url('/blog/' . $blog['slug']);

        $this->view('blogs/show', [
            'pageTitle' => ($blog['seo_title'] ?: $blog['title']) . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => $blog['meta_description'] ?: str_limit($blog['excerpt'] ?: $blog['content'], 160),
            'ogImage' => cover_url($blog['featured_image']),
            'canonical' => $canonical,
            'bodyClass' => 'page-article',
            'blog' => $blog,
            'comments' => $comments,
            'tags' => $tags,
            'related' => $related,
            'liked' => $liked,
            'author' => $author,
            'authorBlogs' => array_values(array_filter($authorBlogs, static fn (array $b): bool => (int) $b['id'] !== (int) $blog['id'])),
            'shareUrl' => $canonical,
        ]);
    }

    public function category(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if ($category === null || (int) $category['is_active'] !== 1) {
            abort(404, 'That category does not exist.');
        }

        $sort = (string) input('sort', 'latest');
        $blogs = Blog::paginate(['category' => $slug, 'sort' => $sort], max(1, (int) input('page', 1)), 9);

        $this->view('blogs/index', [
            'pageTitle' => $category['name'] . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => $category['description'] ?: ('Articles about ' . $category['name'] . ' on ObscuraX.'),
            'blogs' => $blogs,
            'categories' => Category::all(true),
            'tags' => Tag::popular(12),
            'filters' => ['category' => $slug, 'sort' => $sort, 'q' => ''],
            'heading' => $category['name'],
            'subheading' => $category['description'] ?: 'All investigations filed under ' . $category['name'] . '.',
            'category' => $category,
            'coverImage' => $category['cover_image'] ? uploaded_url($category['cover_image']) : null,
        ]);
    }

    public function tag(string $slug): void
    {
        $tag = Tag::findBySlug($slug);
        if ($tag === null) {
            abort(404, 'That tag does not exist.');
        }

        $blogs = Blog::paginate(['tag' => $slug, 'sort' => (string) input('sort', 'latest')], max(1, (int) input('page', 1)), 9);

        $this->view('blogs/index', [
            'pageTitle' => '#' . $tag['name'] . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Articles tagged ' . $tag['name'] . ' on ObscuraX.',
            'blogs' => $blogs,
            'categories' => Category::all(true),
            'tags' => Tag::popular(12),
            'filters' => ['tag' => $slug, 'sort' => 'latest', 'q' => ''],
            'heading' => 'Tagged: ' . $tag['name'],
            'subheading' => 'Everything we have filed under this thread.',
        ]);
    }

    public function search(): void
    {
        $query = trim((string) input('q', ''));
        $page = max(1, (int) input('page', 1));
        $blogs = $query === ''
            ? ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 9]
            : Blog::paginate(['q' => $query, 'sort' => (string) input('sort', 'latest')], $page, 9);

        $this->view('blogs/index', [
            'pageTitle' => ($query === '' ? 'Search' : 'Search: ' . $query) . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Search the ObscuraX archive.',
            'blogs' => $blogs,
            'categories' => Category::all(true),
            'tags' => Tag::popular(12),
            'filters' => ['q' => $query, 'sort' => (string) input('sort', 'latest'), 'category' => '', 'tag' => ''],
            'heading' => $query === '' ? 'Search the archive' : 'Results for “' . $query . '”',
            'subheading' => $query === '' ? 'Enter a keyword to search every published investigation.' : $blogs['total'] . ' article(s) matched your search.',
            'searchMode' => true,
        ]);
    }

    public function author(string $username): void
    {
        $author = User::publicProfile($username);
        if ($author === null) {
            abort(404, 'That author profile is not available.');
        }
        $blogs = Blog::byAuthor((int) $author['id'], 30);

        $this->view('blogs/author', [
            'pageTitle' => ($author['full_name'] ?: $author['username']) . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => str_limit((string) ($author['bio'] ?? ''), 160) ?: 'Articles by ' . $author['username'] . '.',
            'author' => $author,
            'blogs' => $blogs,
            'commentCount' => (int) Database::value('SELECT COUNT(*) FROM comments WHERE user_id = ?', [$author['id']], 0),
        ]);
    }
}
