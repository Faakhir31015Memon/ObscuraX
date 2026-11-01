<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Settings;
use App\Core\Upload;

final class AdminHeroController extends Controller
{
    public function index(): void
    {
        $this->view('admin/hero', [
            'pageTitle' => 'Hero section — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Customise the homepage hero.',
            'bodyClass' => 'page-admin-hero',
            'hero' => [
                'hero_badge' => setting('hero_badge'),
                'hero_title' => setting('hero_title'),
                'hero_subtitle' => setting('hero_subtitle'),
                'hero_primary_label' => setting('hero_primary_label'),
                'hero_primary_url' => setting('hero_primary_url'),
                'hero_secondary_label' => setting('hero_secondary_label'),
                'hero_secondary_url' => setting('hero_secondary_url'),
                'hero_image' => setting('hero_image'),
            ],
        ], 'layouts/admin');
    }

    public function update(): void
    {
        $values = [
            'hero_badge' => trim((string) input('hero_badge', '')),
            'hero_title' => trim((string) input('hero_title', '')),
            'hero_subtitle' => trim((string) input('hero_subtitle', '')),
            'hero_primary_label' => trim((string) input('hero_primary_label', '')),
            'hero_primary_url' => trim((string) input('hero_primary_url', '')),
            'hero_secondary_label' => trim((string) input('hero_secondary_label', '')),
            'hero_secondary_url' => trim((string) input('hero_secondary_url', '')),
        ];

        if ($values['hero_title'] === '') {
            flash('error', 'The hero title is required.');
            back('/admin/hero');
        }

        if (!empty($_FILES['hero_image']) && ($_FILES['hero_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $newImage = Upload::image($_FILES['hero_image'], 'hero', 6);
                $old = setting('hero_image');
                Settings::set('hero_image', $newImage, 'appearance');
                if ($old !== '' && str_starts_with($old, 'hero/')) {
                    Upload::delete($old);
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                back('/admin/hero');
            }
        } elseif (input('remove_hero_image') === '1') {
            Settings::set('hero_image', '', 'appearance');
        }

        Settings::setMany($values, 'appearance');
        Activity::admin((int) Auth::id(), 'hero.update', 'Updated the homepage hero section', 'settings');

        flash('success', 'Hero section updated.');
        redirect('/admin/hero');
    }
}
