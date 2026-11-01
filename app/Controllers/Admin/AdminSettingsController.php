<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Upload;
use App\Core\Validator;

final class AdminSettingsController extends Controller
{
    public function index(): void
    {
        $tab = (string) ($_GET['tab'] ?? 'general');
        if (!in_array($tab, ['general', 'branding', 'pages', 'social'], true)) {
            $tab = 'general';
        }

        $this->view('admin/settings', [
            'pageTitle' => 'Settings — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Website settings and branding.',
            'bodyClass' => 'page-admin-settings',
            'settings' => Settings::all(),
            'tab' => $tab,
        ], 'layouts/admin');
    }

    public function updateGeneral(): void
    {
        $validator = new Validator($_POST, [
            'site_name' => 'required|min:2|max:96',
            'site_tagline' => 'nullable|max:191',
            'site_description' => 'nullable|max:500',
            'contact_email' => 'required|email|max:190',
            'footer_about' => 'nullable|max:300',
            'seo_default_title' => 'nullable|max:191',
            'seo_default_description' => 'nullable|max:300',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/settings?tab=general');
        }

        Settings::setMany([
            'site_name' => trim((string) input('site_name')),
            'site_tagline' => trim((string) input('site_tagline', '')),
            'site_description' => trim((string) input('site_description', '')),
            'contact_email' => mb_strtolower(trim((string) input('contact_email'))),
            'footer_about' => trim((string) input('footer_about', '')),
            'seo_default_title' => trim((string) input('seo_default_title', '')),
            'seo_default_description' => trim((string) input('seo_default_description', '')),
        ], 'general');

        Activity::admin((int) Auth::id(), 'settings.general', 'Updated general website settings', 'settings');
        Session::clearOld();

        flash('success', 'General settings saved.');
        redirect('/admin/settings?tab=general');
    }

    public function updateBranding(): void
    {
        $values = [];

        if (!empty($_FILES['site_logo']) && ($_FILES['site_logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $newLogo = Upload::image($_FILES['site_logo'], 'branding', 3);
                $old = setting('site_logo');
                $values['site_logo'] = $newLogo;
                if ($old !== '' && str_starts_with($old, 'branding/')) {
                    Upload::delete($old);
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                back('/admin/settings?tab=branding');
            }
        } elseif (input('remove_logo') === '1') {
            Upload::delete(setting('site_logo'));
            $values['site_logo'] = '';
        }

        if (!empty($_FILES['site_favicon']) && ($_FILES['site_favicon']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $newIcon = Upload::image($_FILES['site_favicon'], 'branding', 2);
                $old = setting('site_favicon');
                $values['site_favicon'] = $newIcon;
                if ($old !== '' && str_starts_with($old, 'branding/')) {
                    Upload::delete($old);
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                back('/admin/settings?tab=branding');
            }
        } elseif (input('remove_favicon') === '1') {
            Upload::delete(setting('site_favicon'));
            $values['site_favicon'] = '';
        }

        if ($values !== []) {
            Settings::setMany($values, 'branding');
            Activity::admin((int) Auth::id(), 'settings.branding', 'Updated logo or favicon', 'settings');
            flash('success', 'Branding updated.');
        } else {
            flash('info', 'No branding changes were submitted.');
        }

        redirect('/admin/settings?tab=branding');
    }

    public function updatePages(): void
    {
        $validator = new Validator($_POST, [
            'page_about' => 'required|min:100|max:200000',
            'page_privacy' => 'required|min:100|max:200000',
            'page_terms' => 'required|min:100|max:200000',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/settings?tab=pages');
        }

        Settings::setMany([
            'page_about' => (string) input('page_about'),
            'page_privacy' => (string) input('page_privacy'),
            'page_terms' => (string) input('page_terms'),
        ], 'pages');

        Activity::admin((int) Auth::id(), 'settings.pages', 'Updated static page content', 'settings');
        Session::clearOld();

        flash('success', 'Page content saved.');
        redirect('/admin/settings?tab=pages');
    }

    public function updateSocial(): void
    {
        $rules = [];
        foreach (['social_x', 'social_instagram', 'social_youtube', 'social_linkedin', 'social_github'] as $key) {
            $rules[$key] = 'nullable|url|max:255';
        }

        $validator = new Validator($_POST, $rules);
        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/admin/settings?tab=social');
        }

        $values = [];
        foreach (array_keys($rules) as $key) {
            $values[$key] = trim((string) input($key, ''));
        }

        Settings::setMany($values, 'social');
        Activity::admin((int) Auth::id(), 'settings.social', 'Updated social media links', 'settings');
        Session::clearOld();

        flash('success', 'Social links saved.');
        redirect('/admin/settings?tab=social');
    }
}
