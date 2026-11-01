<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Upload;
use App\Models\Blog;
use App\Models\Category;

final class ManageBlogController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => (string) ($_GET['status'] ?? ''),
            'category' => (int) ($_GET['category'] ?? 0),
        ];

        $this->view('admin/blogs-all', [
            'pageTitle' => 'All articles — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Every article on the platform.',
            'bodyClass' => 'page-admin-all-blogs',
            'blogs' => Blog::forAdmin($filters, (int) Auth::id(), true, 300),
            'filters' => $filters,
            'categories' => Category::all(),
            'counts' => Blog::countsByStatus(),
        ], 'layouts/admin');
    }

    public function destroy(string $id): void
    {
        if (!Auth::can('blog.delete')) {
            abort(403, 'You do not have permission to delete articles.');
        }

        $blog = Blog::find((int) $id);
        if ($blog === null) {
            abort(404, 'That article could not be found.');
        }

        Upload::delete($blog['featured_image']);
        Blog::delete((int) $blog['id']);
        Activity::admin((int) Auth::id(), 'blog.delete', 'Deleted "' . $blog['title'] . '"', 'blog', (int) $blog['id']);

        flash('success', 'Article deleted.');
        back('/admin/blogs/all');
    }
}
