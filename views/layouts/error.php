<?php
/** @var int $code */
/** @var string $title */
/** @var string $message */
/** @var Throwable|null $exception */
/** @var bool $debug */
$siteName = function_exists('setting') ? setting('site_name', 'ObscuraX') : 'ObscuraX';
$siteTagline = function_exists('setting') ? setting('site_tagline', 'Explore the Unknown') : 'Explore the Unknown';
$errorTitle = $title;
$pageTitle = $code . ' — ' . $errorTitle . ' · ' . $siteName;
$metaDescription = $errorTitle;
$noIndex = true;
$logo = asset('assets/img/logo-mark.png');
?>
<!doctype html>
<html lang="en">
<head>
    <?php require ROOT_PATH . '/views/partials/head.php'; ?>
</head>
<body class="page-error">
<main class="error-shell">
    <div>
        <a href="<?= url('/') ?>" class="d-inline-block mb-4">
            <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>" style="height:64px">
        </a>
        <p class="error-code"><?= e((string) $code) ?></p>
        <h1><?= e($errorTitle) ?></h1>
        <p><?= e($message !== '' ? $message : 'The page you were looking for could not be delivered. It may have moved, expired, or never existed at all.') ?></p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a class="btn btn-brand" href="<?= url('/') ?>"><i class="bi bi-house me-1"></i> Return home</a>
            <a class="btn btn-outline-soft" href="<?= url('/blogs') ?>">Browse articles</a>
        </div>

        <?php if ($debug && $exception !== null): ?>
            <div class="error-trace">
                <p class="eyebrow mb-2">Developer detail</p>
                <pre><?= e($exception::class . ': ' . $exception->getMessage() . "\n" . $exception->getFile() . ':' . $exception->getLine() . "\n\n" . $exception->getTraceAsString()) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
