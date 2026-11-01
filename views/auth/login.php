<h1>Welcome back</h1>
<p class="auth-sub">Sign in to like investigations, join discussions and pick up where you left off.</p>

<form method="post" action="<?= url('/login') ?>" novalidate>
    <?= csrf_field() ?>
    <div class="form-group">
        <label class="form-label" for="identifier">Email or username</label>
        <div class="input-icon">
            <i class="bi bi-person"></i>
            <input class="form-control" id="identifier" type="text" name="identifier" value="<?= e(old('identifier')) ?>" required autocomplete="username" autofocus>
        </div>
    </div>
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label" for="password">Password</label>
            <a href="<?= url('/forgot-password') ?>" style="font-size:.76rem">Forgot password?</a>
        </div>
        <div class="input-icon">
            <i class="bi bi-lock"></i>
            <input class="form-control" id="password" type="password" name="password" required autocomplete="current-password" style="padding-right:3rem">
            <button class="reveal" type="button" data-reveal-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>

    <button class="btn btn-brand w-100 btn-lg mt-2" type="submit">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
</form>

<div class="auth-divider">New here?</div>
<a class="btn btn-outline-soft w-100" href="<?= url('/register') ?>">Create your free account</a>

<p class="auth-foot">By signing in you agree to our <a href="<?= url('/terms') ?>">terms</a> and <a href="<?= url('/privacy') ?>">privacy policy</a>.</p>
