<?php
/** @var array $hero */
$heroImage = setting('hero_image', 'assets/img/hero-default.jpg');
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Homepage</p>
        <h1>Hero section</h1>
        <p>The first thing every visitor sees. Keep it sharp, specific and free of clichés.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Preview homepage</a>
    </div>
</div>

<form method="post" action="<?= url('/admin/hero') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="admin-grid admin-grid-main">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Copy</h2>
            </div>
            <div class="admin-card-body">
                <div class="form-group">
                    <label class="form-label" for="hero_badge">Badge / issue line</label>
                    <input class="form-control" id="hero_badge" name="hero_badge" type="text" value="<?= e(old('hero_badge', $hero['hero_badge'])) ?>" maxlength="96" placeholder="Issue 01 — The Unknown">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hero_title">Headline</label>
                    <input class="form-control" id="hero_title" name="hero_title" type="text" value="<?= e(old('hero_title', $hero['hero_title'])) ?>" maxlength="191" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="hero_subtitle">Subtitle</label>
                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="4" maxlength="500"><?= e(old('hero_subtitle', $hero['hero_subtitle'])) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="hero_primary_label">Primary button label</label>
                        <input class="form-control" id="hero_primary_label" name="hero_primary_label" type="text" value="<?= e(old('hero_primary_label', $hero['hero_primary_label'])) ?>" maxlength="48">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="hero_primary_url">Primary button link</label>
                        <input class="form-control" id="hero_primary_url" name="hero_primary_url" type="text" value="<?= e(old('hero_primary_url', $hero['hero_primary_url'])) ?>" maxlength="191" placeholder="/blogs">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="hero_secondary_label">Secondary button label</label>
                        <input class="form-control" id="hero_secondary_label" name="hero_secondary_label" type="text" value="<?= e(old('hero_secondary_label', $hero['hero_secondary_label'])) ?>" maxlength="48">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="hero_secondary_url">Secondary button link</label>
                        <input class="form-control" id="hero_secondary_url" name="hero_secondary_url" type="text" value="<?= e(old('hero_secondary_url', $hero['hero_secondary_url'])) ?>" maxlength="191" placeholder="#categories">
                    </div>
                </div>
            </div>
        </section>

        <div class="admin-grid">
            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Background image</h2>
                </div>
                <div class="admin-card-body">
                    <label class="cover-drop" for="hero_image">
                        <img src="<?= e(uploaded_url($heroImage)) ?>" alt="Current hero background">
                        <input id="hero_image" name="hero_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </label>
                    <p class="form-text mt-2">Recommended: 1920×1080 or wider, under 6 MB. Click the image to replace it.</p>
                    <div class="check-line">
                        <input class="form-check-input" id="remove_hero_image" name="remove_hero_image" type="checkbox" value="1">
                        <label for="remove_hero_image">Reset to the default background</label>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Save</h2>
                </div>
                <div class="admin-card-body">
                    <button class="btn btn-brand w-100" type="submit"><i class="bi bi-check2"></i> Update hero</button>
                    <div class="admin-note mt-3">
                        <i class="bi bi-lightbulb"></i>
                        <div>Short, declarative copy works best here. The badge is optional; leave it empty to hide it.</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
