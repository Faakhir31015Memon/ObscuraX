<?php /** @var string|null $email */ /** @var string|null $devUrl */ ?>
<div class="auth-emblem"><i class="bi bi-envelope-paper"></i></div>
<h1>Verify your email</h1>
<p class="auth-sub">
    <?php if (!empty($email)): ?>
        We sent a verification link to <strong><?= e($email) ?></strong>. Open it to activate your account.
    <?php else: ?>
        We sent you a verification link. Open it to activate your account.
    <?php endif; ?>
    The link expires after 24 hours.
</p>

<?php if (!empty($devUrl)): ?>
    <div class="auth-note mb-4">
        <strong>Development mode.</strong> Mail is written to <code>storage/mail</code> instead of being delivered. Use this verification link:
        <br><a href="<?= e($devUrl) ?>"><?= e($devUrl) ?></a>
    </div>
<?php endif; ?>

<form method="post" action="<?= url('/verify-email/resend') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <div class="input-icon">
            <i class="bi bi-envelope"></i>
            <input class="form-control" id="email" type="email" name="email" value="<?= e((string) $email) ?>" required maxlength="190" autocomplete="email">
        </div>
    </div>
    <button class="btn btn-brand w-100 btn-lg" type="submit">Resend verification link <i class="bi bi-arrow-repeat ms-1"></i></button>
</form>

<p class="auth-foot">
    Already verified? <a href="<?= url('/login') ?>">Sign in</a>
    <span class="auth-sep">·</span>
    <a href="<?= url('/register') ?>">Use a different account</a>
</p>
