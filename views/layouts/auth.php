<?php
/** @var string $content */
/** @var array $flashes */
/** @var string $siteName */
/** @var string $siteTagline */
$noIndex = true;
$logo = setting('site_logo') !== '' ? uploaded_url(setting('site_logo')) : asset('assets/img/logo.png');
$mark = asset('assets/img/logo-mark.png');
?>
<!doctype html>
<html lang="en">
<head>
    <?php require ROOT_PATH . '/views/partials/head.php'; ?>
</head>
<body class="page-auth">
<?php require ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="auth-shell">
    <aside class="auth-visual" aria-hidden="true">
        <a class="brand" href="<?= url('/') ?>">
            <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
        </a>

        <div class="auth-quote">
            <p class="kicker mb-3" style="color:#d6c7ff">An independent archive</p>
            <blockquote>“The unknown is not a threat. It is a subject — and subjects deserve research.”</blockquote>
            <p><?= e($siteName) ?> · Occult · Sexual health · Cybersecurity</p>
        </div>

        <div class="brand-note">
            <img src="<?= e($mark) ?>" alt="" width="32" height="32" style="opacity:.7;margin-bottom:.5rem">
            <p class="mb-0"><?= e($siteTagline) ?></p>
        </div>
    </aside>

    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-topline">
                <a class="link-arrow" href="<?= url('/') ?>"><i class="bi bi-arrow-left"></i> Back to site</a>
                <a class="brand d-lg-none" href="<?= url('/') ?>" aria-label="Home"><img src="<?= e($mark) ?>" alt="" style="height:32px"></a>
                <span class="eyebrow d-none d-lg-inline">Secure access</span>
            </div>
            <?= $content ?>
        </div>
    </div>
</div>

<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
</body>
</html>
