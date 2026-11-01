<h1>Join the archive</h1>
<p class="auth-sub">Create an account to read, like and discuss investigations across three unusual desks.</p>

<form method="post" action="<?= url('/register') ?>" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="form-label" for="username">Username</label>
            <div class="input-icon">
                <i class="bi bi-at"></i>
                <input class="form-control" id="username" type="text" name="username" value="<?= e(old('username')) ?>" required minlength="3" maxlength="24" pattern="[A-Za-z0-9_.\-]+" autocomplete="username" autofocus>
            </div>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="full_name">Full name <span class="text-muted">(optional)</span></label>
            <input class="form-control" id="full_name" type="text" name="full_name" value="<?= e(old('full_name')) ?>" maxlength="120" autocomplete="name">
        </div>
    </div>

    <div class="form-group mt-3">
        <label class="form-label" for="email">Email address</label>
        <div class="input-icon">
            <i class="bi bi-envelope"></i>
            <input class="form-control" id="email" type="email" name="email" value="<?= e(old('email')) ?>" required maxlength="190" autocomplete="email">
        </div>
        <p class="form-text">We send a verification link. Your account activates once it is confirmed.</p>
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-icon">
            <i class="bi bi-lock"></i>
            <input class="form-control" id="password" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" data-strength="#registerMeter" data-strength-hint="#registerHint" style="padding-right:3rem">
            <button class="reveal" type="button" data-reveal-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
        <div class="password-meter" id="registerMeter" data-score="0"><span></span><span></span><span></span><span></span></div>
        <p class="password-hint" id="registerHint">Use 8+ characters with letters, numbers and a symbol.</p>
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <div class="input-icon">
            <i class="bi bi-shield-lock"></i>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required minlength="8" maxlength="72" autocomplete="new-password">
        </div>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" id="terms" type="checkbox" name="terms" value="1" required>
        <label class="form-check-label" for="terms">I agree to the <a href="<?= url('/terms') ?>">terms of use</a> and <a href="<?= url('/privacy') ?>">privacy policy</a>.</label>
    </div>

    <button class="btn btn-brand w-100 btn-lg" type="submit">Create account <i class="bi bi-arrow-right ms-1"></i></button>
</form>

<p class="auth-foot">Already have an account? <a href="<?= url('/login') ?>">Sign in</a></p>
