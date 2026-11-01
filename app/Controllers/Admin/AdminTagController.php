<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Tag;

final class AdminTagController extends Controller
{
    public function index(): void
    {
        $this->view('admin/tags', [
            'pageTitle' => 'Tags — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage article tags.',
            'bodyClass' => 'page-admin-tags',
            'tags' => Tag::all(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $validator = new Validator($_POST, ['name' => 'required|min:2|max:64']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/admin/tags');
        }

        $name = trim((string) input('name'));
        $id = Tag::findOrCreate($name);
        Activity::admin((int) Auth::id(), 'tag.create', 'Created tag "' . $name . '"', 'tag', $id);

        flash('success', 'Tag added.');
        redirect('/admin/tags');
    }

    public function update(string $id): void
    {
        $tag = \App\Core\Database::fetch('SELECT * FROM tags WHERE id = ? LIMIT 1', [(int) $id]);
        if ($tag === null) {
            abort(404, 'That tag could not be found.');
        }

        $validator = new Validator($_POST, ['name' => 'required|min:2|max:64']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/admin/tags');
        }

        Tag::update((int) $tag['id'], (string) input('name'));
        Activity::admin((int) Auth::id(), 'tag.update', 'Renamed tag to "' . input('name') . '"', 'tag', (int) $tag['id']);

        flash('success', 'Tag updated.');
        redirect('/admin/tags');
    }

    public function destroy(string $id): void
    {
        $tag = \App\Core\Database::fetch('SELECT * FROM tags WHERE id = ? LIMIT 1', [(int) $id]);
        if ($tag === null) {
            abort(404, 'That tag could not be found.');
        }

        Tag::delete((int) $tag['id']);
        Activity::admin((int) Auth::id(), 'tag.delete', 'Deleted tag "' . $tag['name'] . '"', 'tag', (int) $tag['id']);

        flash('success', 'Tag deleted.');
        redirect('/admin/tags');
    }
}
