<?php /** @var bool $success */ /** @var string $message */ ?>
<div class="auth-emblem <?= $success ? 'is-success' : 'is-error' ?>">
    <i class="bi <?= $success ? 'bi-patch-check' : 'bi-exclamation-triangle' ?>"></i>
</div>
<h1><?= $success ? 'You are verified' : 'Verification failed' ?></h1>
<p class="auth-sub"><?= e($message) ?></p>

<?php if ($success): ?>
    <a class="btn btn-brand w-100 btn-lg" href="<?= url('/login') ?>">Sign in to ObscuraX <i class="bi bi-arrow-right ms-1"></i></a>
    <p class="auth-foot">New here? <a href="<?= url('/') ?>">Explore the archive</a></p>
<?php else: ?>
    <a class="btn btn-brand w-100 btn-lg mb-3" href="<?= url('/verify-email') ?>">Request a new link</a>
    <p class="auth-foot"><a href="<?= url('/login') ?>">Back to sign in</a></p>
<?php endif; ?>
