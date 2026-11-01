<?php
$title = $pageTitle ?? ($siteName . ' — ' . $siteTagline);
$description = $metaDescription ?? setting('seo_default_description', setting('site_description', ''));
$shareImage = $ogImage ?? asset('assets/img/og-image.jpg');
$canonicalUrl = $canonical ?? url(current_path());
$favicon = setting('site_favicon') !== '' ? uploaded_url(setting('site_favicon')) : asset('assets/img/favicon-32.png');
$touchIcon = setting('site_favicon') !== '' ? uploaded_url(setting('site_favicon')) : asset('assets/img/apple-touch-icon.png');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e(str_limit($description, 300)) ?>">
<meta name="theme-color" content="#06050a">
<script nonce="<?= e(CSP_NONCE) ?>">
    (function () {
        try {
            var saved = localStorage.getItem('obscurax-theme');
            document.documentElement.setAttribute('data-theme', saved === 'light' ? 'light' : 'dark');
        } catch (error) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="base-url" content="<?= e(rtrim(url('/'), '/')) ?>">
<?php if (!empty($noIndex)): ?>
    <meta name="robots" content="noindex, nofollow">
<?php else: ?>
    <meta name="robots" content="index, follow">
<?php endif; ?>
<link rel="canonical" href="<?= e($canonicalUrl) ?>">

<meta property="og:type" content="<?= isset($article) ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e(str_limit($description, 300)) ?>">
<meta property="og:image" content="<?= e($shareImage) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e(str_limit($description, 220)) ?>">
<meta name="twitter:image" content="<?= e($shareImage) ?>">

<link rel="icon" href="<?= url('favicon.ico') ?>" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="<?= e($favicon) ?>">
<link rel="apple-touch-icon" href="<?= e($touchIcon) ?>">
<link rel="manifest" href="<?= url('site.webmanifest') ?>">
<link rel="preload" href="<?= asset('assets/fonts/inter-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= asset('assets/fonts/cinzel-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>

<link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/vendor/icons/bootstrap-icons.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
<?php if (!empty($adminCss)): ?>
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
<?php endif; ?>
<?php if (!empty($needQuill)): ?>
    <link rel="stylesheet" href="<?= asset('assets/vendor/quill/quill.snow.css') ?>">
<?php endif; ?>
