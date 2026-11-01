<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Category;

final class AdminCategoryController extends Controller
{
    public function index(): void
    {
        $this->view('admin/categories', [
            'pageTitle' => 'Categories — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage article categories.',
            'bodyClass' => 'page-admin-categories',
            'categories' => Category::withStats(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $data = $this->payload(null);

        $id = Category::create($data);
        Activity::admin((int) Auth::id(), 'category.create', 'Created category "' . $data['name'] . '"', 'category', $id);

        flash('success', 'Category created.');
        redirect('/admin/categories');
    }

    public function update(string $id): void
    {
        $category = Category::find((int) $id);
        if ($category === null) {
            abort(404, 'That category could not be found.');
        }

        $data = $this->payload($category);
        Category::update((int) $category['id'], $data);
        Activity::admin((int) Auth::id(), 'category.update', 'Updated category "' . $data['name'] . '"', 'category', (int) $category['id']);

        flash('success', 'Category updated.');
        redirect('/admin/categories');
    }

    public function destroy(string $id): void
    {
        $category = Category::find((int) $id);
        if ($category === null) {
            abort(404, 'That category could not be found.');
        }

        if (!Category::delete((int) $category['id'])) {
            flash('error', 'Move or delete the articles filed under this category first.');
            redirect('/admin/categories');
        }

        Activity::admin((int) Auth::id(), 'category.delete', 'Deleted category "' . $category['name'] . '"', 'category', (int) $category['id']);
        flash('success', 'Category deleted.');
        redirect('/admin/categories');
    }

    /**
     * @param array|null $existing
     * @return array<string,mixed>
     */
    private function payload(?array $existing): array
    {
        $validator = new Validator($_POST, [
            'name' => 'required|min:2|max:96',
            'slug' => 'nullable|max:120|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|max:500',
            'accent_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'icon' => 'nullable|max:48',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/categories');
        }

        $name = trim((string) input('name'));
        $data = [
            'name' => $name,
            'slug' => Category::uniqueSlug((string) (input('slug') ?: $name), $existing === null ? null : (int) $existing['id']),
            'description' => trim((string) input('description', '')),
            'accent_color' => (string) input('accent_color', '#8b5cf6'),
            'icon' => trim((string) input('icon', 'bi-journal-richtext')) ?: 'bi-journal-richtext',
            'sort_order' => (int) input('sort_order', 0),
            'is_active' => input('is_active') === '1' ? 1 : 0,
        ];

        if (!empty($_FILES['cover_image']) && ($_FILES['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['cover_image'] = Upload::image($_FILES['cover_image'], 'categories', 5);
                if ($existing !== null) {
                    Upload::delete($existing['cover_image'] ?? null);
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                back('/admin/categories');
            }
        } elseif (input('remove_cover') === '1' && $existing !== null) {
            Upload::delete($existing['cover_image'] ?? null);
            $data['cover_image'] = null;
        }

        return $data;
    }
}
