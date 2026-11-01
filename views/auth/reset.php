<?php
/** @var string $token */
/** @var string $email */
?>
<h1>Choose a new password</h1>
<p class="auth-sub">Setting a new password for <strong style="color:var(--text)"><?= e($email) ?></strong>. The link can only be used once.</p>

<form method="post" action="<?= url('/reset-password') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-group">
        <label class="form-label" for="password">New password</label>
        <div class="input-icon">
            <i class="bi bi-lock"></i>
            <input class="form-control" id="password" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" data-strength="#resetMeter" data-strength-hint="#resetHint" style="padding-right:3rem" autofocus>
            <button class="reveal" type="button" data-reveal-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
        <div class="password-meter" id="resetMeter" data-score="0"><span></span><span></span><span></span><span></span></div>
        <p class="password-hint" id="resetHint">Use 8+ characters with letters, numbers and a symbol.</p>
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm new password</label>
        <div class="input-icon">
            <i class="bi bi-shield-lock"></i>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required minlength="8" maxlength="72" autocomplete="new-password">
        </div>
    </div>

    <button class="btn btn-brand w-100 btn-lg" type="submit">Update password <i class="bi bi-check2 ms-1"></i></button>
</form>

<p class="auth-foot"><a href="<?= url('/login') ?>">Back to sign in</a></p>
