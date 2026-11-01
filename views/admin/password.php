<?php
/** @var array $user */
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Account security</p>
        <h1>Change password</h1>
        <p>Use a unique password you do not use anywhere else. You will stay signed in on this device.</p>
    </div>
</div>

<div class="admin-grid admin-grid-main">
    <section class="admin-card">
        <div class="admin-card-head">
            <h2>New password</h2>
        </div>
        <div class="admin-card-body">
            <form method="post" action="<?= url('/admin/password') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="current_password">Current password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input class="form-control" id="current_password" name="current_password" type="password" required autocomplete="current-password" style="padding-right:3rem">
                        <button class="reveal" type="button" data-reveal-target="#current_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">New password</label>
                        <div class="input-icon">
                            <i class="bi bi-shield-lock"></i>
                            <input class="form-control" id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" data-strength="#passwordMeter" data-strength-hint="#passwordHint" style="padding-right:3rem">
                            <button class="reveal" type="button" data-reveal-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="password-meter" id="passwordMeter" data-score="0"><span></span><span></span><span></span><span></span></div>
                        <p class="password-hint" id="passwordHint">Use 8+ characters with upper and lower case, a number and a symbol.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm new password</label>
                        <div class="input-icon">
                            <i class="bi bi-shield-check"></i>
                            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <button class="btn btn-brand" type="submit"><i class="bi bi-check2-circle"></i> Update password</button>
            </form>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>Account</h2>
        </div>
        <div class="admin-card-body">
            <div class="detail-list">
                <div class="row-item"><span>Signed in as</span><span>@<?= e($user['username']) ?></span></div>
                <div class="row-item"><span>Email</span><span><?= e($user['email']) ?></span></div>
                <div class="row-item"><span>Role</span><span><?= e($user['role_name'] ?? 'Administrator') ?></span></div>
                <div class="row-item"><span>Last sign-in</span><span><?= e(format_datetime($user['last_login_at'] ?? null)) ?></span></div>
                <div class="row-item"><span>Password changed</span><span><?= e(format_datetime($user['password_changed_at'] ?? null)) ?></span></div>
            </div>
            <div class="admin-note mt-3">
                <i class="bi bi-info-circle"></i>
                <div>Changing your password signs out no one else. If you suspect misuse, change it immediately and contact the super administrator.</div>
            </div>
        </div>
    </section>
</div>
