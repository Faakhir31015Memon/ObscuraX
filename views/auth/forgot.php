<?php /** @var string|null $devUrl */ ?>
<h1>Reset your password</h1>
<p class="auth-sub">Enter the email attached to your account. If it exists, we will send a single-use reset link that expires in one hour.</p>

<form method="post" action="<?= url('/forgot-password') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <div class="input-icon">
            <i class="bi bi-envelope"></i>
            <input class="form-control" id="email" type="email" name="email" required maxlength="190" autocomplete="email" autofocus>
        </div>
    </div>
    <button class="btn btn-brand w-100 btn-lg" type="submit">Send reset link <i class="bi bi-send ms-1"></i></button>
</form>

<?php if (!empty($devUrl)): ?>
    <div class="auth-note mt-4">
        <strong>Development mode.</strong> Mail is written to <code>storage/mail</code> instead of being delivered. Use this reset link:
        <br><a href="<?= e($devUrl) ?>"><?= e($devUrl) ?></a>
    </div>
<?php endif; ?>

<p class="auth-foot">Remembered it? <a href="<?= url('/login') ?>">Back to sign in</a></p>
