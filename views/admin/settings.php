<?php
/** @var array $settings */
/** @var string $tab */
$value = static function (string $key, string $default = '') use ($settings): string {
    return (string) ($settings[$key] ?? $default);
};
$logo = $value('site_logo') !== '' ? uploaded_url($value('site_logo')) : asset('assets/img/logo.png');
$favicon = $value('site_favicon') !== '' ? uploaded_url($value('site_favicon')) : asset('assets/img/favicon-32.png');
$tabs = [
    'general' => ['label' => 'General', 'icon' => 'bi-sliders'],
    'branding' => ['label' => 'Branding', 'icon' => 'bi-palette'],
    'pages' => ['label' => 'Pages', 'icon' => 'bi-file-earmark-text'],
    'social' => ['label' => 'Social', 'icon' => 'bi-share'],
];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Super administration</p>
        <h1>Website settings</h1>
        <p>Identity, branding, public pages and social presence — all applied instantly across the site.</p>
    </div>
</div>

<div class="settings-layout">
    <nav class="settings-nav" aria-label="Settings sections">
        <?php foreach ($tabs as $key => $meta): ?>
            <a class="<?= $tab === $key ? 'active' : '' ?>" href="<?= url('/admin/settings?tab=' . $key) ?>"><i class="bi <?= $meta['icon'] ?> me-2"></i><?= e($meta['label']) ?></a>
        <?php endforeach; ?>
    </nav>

    <div>
        <?php if ($tab === 'general'): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Site identity</h2>
                        <p class="sub">Names, contact details and default search metadata</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <form method="post" action="<?= url('/admin/settings/general') ?>">
                        <?= csrf_field() ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="site_name">Site name</label>
                                <input class="form-control" id="site_name" name="site_name" type="text" value="<?= e(old('site_name', $value('site_name'))) ?>" maxlength="96" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact_email">Contact email</label>
                                <input class="form-control" id="contact_email" name="contact_email" type="email" value="<?= e(old('contact_email', $value('contact_email'))) ?>" maxlength="190" required>
                                <p class="form-text">Contact form submissions are also emailed here.</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="site_tagline">Tagline</label>
                            <input class="form-control" id="site_tagline" name="site_tagline" type="text" value="<?= e(old('site_tagline', $value('site_tagline'))) ?>" maxlength="191">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="site_description">Site description</label>
                            <textarea class="form-control" id="site_description" name="site_description" rows="3" maxlength="500"><?= e(old('site_description', $value('site_description'))) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="footer_about">Footer blurb</label>
                            <textarea class="form-control" id="footer_about" name="footer_about" rows="2" maxlength="300"><?= e(old('footer_about', $value('footer_about'))) ?></textarea>
                        </div>
                        <div class="form-section" style="margin-top:1rem">
                            <h3>Search defaults</h3>
                            <div class="form-group">
                                <label class="form-label" for="seo_default_title">Default SEO title</label>
                                <input class="form-control" id="seo_default_title" name="seo_default_title" type="text" value="<?= e(old('seo_default_title', $value('seo_default_title'))) ?>" maxlength="191">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="seo_default_description">Default meta description</label>
                                <textarea class="form-control" id="seo_default_description" name="seo_default_description" rows="2" maxlength="300"><?= e(old('seo_default_description', $value('seo_default_description'))) ?></textarea>
                            </div>
                        </div>
                        <button class="btn btn-brand mt-3" type="submit"><i class="bi bi-check2"></i> Save general settings</button>
                    </form>
                </div>
            </section>
        <?php elseif ($tab === 'branding'): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Logo &amp; favicon</h2>
                        <p class="sub">PNG, JPG, WEBP or GIF. Square images work best for the favicon.</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="branding-preview">
                        <img class="logo" src="<?= e($logo) ?>" alt="Current logo">
                        <div class="grow">
                            <strong><?= e($value('site_name', 'ObscuraX')) ?></strong>
                            <span>Logo shown in the header, footer and admin sidebar</span>
                        </div>
                    </div>
                    <div class="branding-preview">
                        <img class="favicon" src="<?= e($favicon) ?>" alt="Current favicon">
                        <div class="grow">
                            <strong>Browser tab &amp; app icon</strong>
                            <span>Also used for the apple touch icon</span>
                        </div>
                    </div>

                    <form method="post" action="<?= url('/admin/settings/branding') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="site_logo">Replace logo</label>
                                <input class="form-control" id="site_logo" name="site_logo" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                                <?php if ($value('site_logo') !== ''): ?>
                                    <div class="check-line mt-2">
                                        <input class="form-check-input" id="remove_logo" name="remove_logo" type="checkbox" value="1">
                                        <label for="remove_logo">Revert to the bundled logo</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="site_favicon">Replace favicon</label>
                                <input class="form-control" id="site_favicon" name="site_favicon" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                                <?php if ($value('site_favicon') !== ''): ?>
                                    <div class="check-line mt-2">
                                        <input class="form-check-input" id="remove_favicon" name="remove_favicon" type="checkbox" value="1">
                                        <label for="remove_favicon">Revert to the bundled icon</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="btn btn-brand mt-2" type="submit"><i class="bi bi-upload"></i> Upload branding</button>
                    </form>
                </div>
            </section>
        <?php elseif ($tab === 'pages'): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Public page content</h2>
                        <p class="sub">HTML is rendered directly — keep it semantic and safe.</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <form method="post" action="<?= url('/admin/settings/pages') ?>">
                        <?= csrf_field() ?>
                        <?php
                        $pages = [
                            'page_about' => ['About ObscuraX', 'Who we are and how we work'],
                            'page_privacy' => ['Privacy Policy', 'What we collect and why'],
                            'page_terms' => ['Terms &amp; Conditions', 'The rules of the platform'],
                        ];
                        ?>
                        <?php foreach ($pages as $key => [$label, $hint]): ?>
                            <div class="form-group">
                                <label class="form-label" for="<?= $key ?>"><?= $label ?> <span class="cell-sub d-inline"><?= $hint ?></span></label>
                                <textarea class="form-control" id="<?= $key ?>" name="<?= $key ?>" rows="10" maxlength="200000" required style="font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:.82rem"><?= e(old($key, $value($key))) ?></textarea>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-brand" type="submit"><i class="bi bi-check2"></i> Save page content</button>
                    </form>
                </div>
            </section>
        <?php else: ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Social profiles</h2>
                        <p class="sub">Empty fields are hidden from the footer.</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <form method="post" action="<?= url('/admin/settings/social') ?>">
                        <?= csrf_field() ?>
                        <?php
                        $social = [
                            'social_x' => ['X / Twitter', 'https://x.com/obscurax'],
                            'social_instagram' => ['Instagram', 'https://instagram.com/obscurax'],
                            'social_youtube' => ['YouTube', 'https://youtube.com/@obscurax'],
                            'social_linkedin' => ['LinkedIn', 'https://linkedin.com/company/obscurax'],
                            'social_github' => ['GitHub', 'https://github.com/obscurax'],
                        ];
                        ?>
                        <?php foreach ($social as $key => [$label, $placeholder]): ?>
                            <div class="form-group">
                                <label class="form-label" for="<?= $key ?>"><?= e($label) ?></label>
                                <input class="form-control" id="<?= $key ?>" name="<?= $key ?>" type="url" value="<?= e(old($key, $value($key))) ?>" maxlength="255" placeholder="<?= e($placeholder) ?>">
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-brand" type="submit"><i class="bi bi-check2"></i> Save social links</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
