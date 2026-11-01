<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;

final class AdminBlogController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $isSuper = Auth::isSuperAdmin();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => (string) ($_GET['status'] ?? ''),
            'category' => (int) ($_GET['category'] ?? 0),
        ];

        $blogs = Blog::forAdmin($filters, (int) $user['id'], $isSuper, 200);

        $this->view('admin/blogs/index', [
            'pageTitle' => 'Articles — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage articles.',
            'bodyClass' => 'page-admin-blogs',
            'blogs' => $blogs,
            'filters' => $filters,
            'categories' => Category::all(),
            'counts' => Blog::countsByStatus(),
            'isSuper' => $isSuper,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->view('admin/blogs/form', [
            'pageTitle' => 'New article — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Write a new article.',
            'bodyClass' => 'page-admin-blog-edit',
            'needQuill' => true,
            'blog' => null,
            'categories' => Category::all(true),
            'tags' => '',
            'selectedCategory' => (int) ($_GET['category'] ?? 0),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $user = Auth::user();
        $data = $this->validatePayload();
        $status = (string) input('status', 'draft');

        if ($status === 'published' && !Auth::can('blog.publish')) {
            $status = 'draft';
        }

        $now = now();
        $payload = [
            'author_id' => (int) $user['id'],
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'slug' => Blog::uniqueSlug((string) ($data['slug'] ?: $data['title'])),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'status' => $status,
            'is_featured' => Auth::can('blog.feature') && input('is_featured') === '1' ? 1 : 0,
            'allow_comments' => input('allow_comments') === '1' ? 1 : 0,
            'seo_title' => $data['seo_title'] !== '' ? $data['seo_title'] : null,
            'meta_description' => $data['meta_description'] !== '' ? $data['meta_description'] : null,
            'view_count' => 0,
            'published_at' => $status === 'published' ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (!empty($_FILES['featured_image']) && ($_FILES['featured_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['featured_image'] = Upload::image($_FILES['featured_image'], 'blogs', 5);
            } catch (\RuntimeException $e) {
                Session::flashOld($_POST);
                flash('error', $e->getMessage());
                back('/admin/blogs/create');
            }
        } else {
            $payload['featured_image'] = null;
        }

        $id = Blog::create($payload);
        Tag::sync($id, (string) $data['tags']);
        Activity::admin((int) $user['id'], 'blog.create', 'Created "' . $data['title'] . '"', 'blog', $id);

        flash('success', 'Article saved as ' . $status . '.');
        redirect('/admin/blogs/' . $id . '/edit');
    }

    public function edit(string $id): void
    {
        $blog = Blog::find((int) $id);
        if ($blog === null) {
            abort(404, 'That article could not be found.');
        }
        if (!Blog::canManage($blog, Auth::user())) {
            abort(403, 'You can only edit articles you wrote.');
        }

        $tagList = array_map(static fn (array $t): string => $t['name'], Tag::forBlog((int) $blog['id']));

        $this->view('admin/blogs/form', [
            'pageTitle' => 'Edit: ' . $blog['title'] . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Edit article.',
            'bodyClass' => 'page-admin-blog-edit',
            'needQuill' => true,
            'blog' => $blog,
            'categories' => Category::all(true),
            'tags' => implode(', ', $tagList),
            'selectedCategory' => (int) $blog['category_id'],
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $user = Auth::user();
        $blog = Blog::find((int) $id);
        if ($blog === null) {
            abort(404, 'That article could not be found.');
        }
        if (!Blog::canManage($blog, $user)) {
            abort(403, 'You can only edit articles you wrote.');
        }

        $data = $this->validatePayload();
        $status = (string) input('status', 'draft');
        if ($status === 'published' && !Auth::can('blog.publish')) {
            $status = 'draft';
        }

        $payload = [
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'slug' => Blog::uniqueSlug((string) ($data['slug'] ?: $data['title']), (int) $blog['id']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'status' => $status,
            'allow_comments' => input('allow_comments') === '1' ? 1 : 0,
            'seo_title' => $data['seo_title'] !== '' ? $data['seo_title'] : null,
            'meta_description' => $data['meta_description'] !== '' ? $data['meta_description'] : null,
            'updated_at' => now(),
        ];

        if (Auth::can('blog.feature')) {
            $payload['is_featured'] = input('is_featured') === '1' ? 1 : 0;
        }

        if ($status === 'published' && empty($blog['published_at'])) {
            $payload['published_at'] = now();
        }

        if (!empty($_FILES['featured_image']) && ($_FILES['featured_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['featured_image'] = Upload::image($_FILES['featured_image'], 'blogs', 5);
                Upload::delete($blog['featured_image']);
            } catch (\RuntimeException $e) {
                Session::flashOld($_POST);
                flash('error', $e->getMessage());
                back('/admin/blogs/' . $blog['id'] . '/edit');
            }
        } elseif (input('remove_image') === '1') {
            Upload::delete($blog['featured_image']);
            $payload['featured_image'] = null;
        }

        Blog::update((int) $blog['id'], $payload);
        Tag::sync((int) $blog['id'], (string) $data['tags']);
        Activity::admin((int) $user['id'], 'blog.update', 'Updated "' . $data['title'] . '"', 'blog', (int) $blog['id']);

        flash('success', 'Article updated.');
        redirect('/admin/blogs/' . $blog['id'] . '/edit');
    }

    public function changeStatus(string $id): void
    {
        $user = Auth::user();
        $blog = Blog::find((int) $id);
        if ($blog === null) {
            abort(404, 'That article could not be found.');
        }
        if (!Blog::canManage($blog, $user)) {
            abort(403, 'You can only manage articles you wrote.');
        }

        $status = (string) input('status');
        if (!in_array($status, ['draft', 'published', 'unpublished'], true)) {
            flash('error', 'Unknown article status.');
            back('/admin/blogs');
        }
        if ($status === 'published' && !Auth::can('blog.publish')) {
            abort(403, 'You do not have permission to publish articles.');
        }

        $payload = ['status' => $status, 'updated_at' => now()];
        if ($status === 'published' && empty($blog['published_at'])) {
            $payload['published_at'] = now();
        }
        Blog::update((int) $blog['id'], $payload);
        Activity::admin((int) $user['id'], 'blog.status', 'Set "' . $blog['title'] . '" to ' . $status, 'blog', (int) $blog['id']);

        flash('success', 'Article is now ' . $status . '.');
        back('/admin/blogs');
    }

    public function toggleFeatured(string $id): void
    {
        $user = Auth::user();
        $blog = Blog::find((int) $id);
        if ($blog === null) {
            abort(404, 'That article could not be found.');
        }
        if (!Blog::canManage($blog, $user)) {
            abort(403, 'You can only manage articles you wrote.');
        }

        $featured = (int) $blog['is_featured'] === 1 ? 0 : 1;
        Blog::update((int) $blog['id'], ['is_featured' => $featured]);
        Activity::admin((int) $user['id'], 'blog.feature', ($featured ? 'Featured "' : 'Unfeatured "') . $blog['title'], 'blog', (int) $blog['id']);

        flash('success', $featured ? 'Article added to the featured shelf.' : 'Article removed from the featured shelf.');
        back('/admin/blogs');
    }

    /**
     * @return array<string,string>
     */
    private function validatePayload(): array
    {
        $validator = new Validator($_POST, [
            'title' => 'required|min:4|max:191',
            'slug' => 'nullable|max:220|regex:/^[a-z0-9-]+$/',
            'excerpt' => 'required|min:20|max:500',
            'content' => 'required|min:40',
            'category_id' => 'required|integer',
            'tags' => 'nullable|max:300',
            'seo_title' => 'nullable|max:191',
            'meta_description' => 'nullable|max:300',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back($_SERVER['HTTP_REFERER'] ?? '/admin/blogs');
        }

        $category = Database::fetch('SELECT id FROM categories WHERE id = ? LIMIT 1', [(int) input('category_id')]);
        if ($category === null) {
            Session::flashOld($_POST);
            flash('error', 'Choose a valid category.');
            back($_SERVER['HTTP_REFERER'] ?? '/admin/blogs');
        }

        return [
            'title' => trim((string) input('title')),
            'slug' => trim((string) input('slug', '')),
            'excerpt' => trim((string) input('excerpt')),
            'content' => (string) input('content'),
            'category_id' => (string) (int) input('category_id'),
            'tags' => trim((string) input('tags', '')),
            'seo_title' => trim((string) input('seo_title', '')),
            'meta_description' => trim((string) input('meta_description', '')),
        ];
    }
}
