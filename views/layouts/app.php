<?php
/** @var string $content */
/** @var string $siteName */
/** @var string $siteTagline */
/** @var array $navCategories */
/** @var array $flashes */
/** @var array|null $authUser */
$bodyClass = $bodyClass ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <?php require ROOT_PATH . '/views/partials/head.php'; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<?php if (str_contains($bodyClass, 'page-article')): ?>
    <div class="reading-progress" data-reading-progress aria-hidden="true"></div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/partials/site-header.php'; ?>
<?php require ROOT_PATH . '/views/partials/flash.php'; ?>

<main id="main">
    <?= $content ?>
</main>

<?php require ROOT_PATH . '/views/partials/site-footer.php'; ?>

<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
</body>
</html>
