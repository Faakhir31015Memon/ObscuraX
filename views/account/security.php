<?php
/** @var array $user */
/** @var bool $mustChange */
?>
<div class="container">
    <div class="account-layout">
        <?php require ROOT_PATH . '/views/account/_sidebar.php'; ?>

        <div class="account-main">
            <?php if ($mustChange): ?>
                <div class="alert-brand mb-4">
                    <i class="bi bi-shield-exclamation"></i>
                    <div>
                        <strong>A new password is required.</strong>
                        <p class="mb-0">Your account was created with a temporary password. Choose a personal password to unlock the rest of the site.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="account-welcome">
                <div>
                    <span class="eyebrow">Account security</span>
                    <h1>Password &amp; sessions</h1>
                    <p>Keep your account safe with a unique password and a verified email address.</p>
                </div>
            </div>

            <div class="panel mb-4">
                <div class="panel-head">
                    <h2><i class="bi bi-key me-2"></i>Change password</h2>
                </div>
                <div class="panel-body-lg">
                    <form method="post" action="<?= url('/account/security') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password <span class="text-muted">*</span></label>
                            <div class="input-icon">
                                <i class="bi bi-lock"></i>
                                <input class="form-control" id="current_password" name="current_password" type="password" required autocomplete="current-password" style="padding-right:3rem" <?= $mustChange ? 'autofocus' : '' ?>>
                                <button class="reveal" type="button" data-reveal-target="#current_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="password">New password <span class="text-muted">*</span></label>
                                <div class="input-icon">
                                    <i class="bi bi-shield-lock"></i>
                                    <input class="form-control" id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" data-strength="#passwordMeter" data-strength-hint="#passwordHint" style="padding-right:3rem">
                                    <button class="reveal" type="button" data-reveal-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="password-meter" id="passwordMeter" data-score="0"><span></span><span></span><span></span><span></span></div>
                                <p class="password-hint" id="passwordHint">Use 8+ characters with upper and lower case, a number and a symbol.</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="password_confirmation">Confirm new password <span class="text-muted">*</span></label>
                                <div class="input-icon">
                                    <i class="bi bi-shield-check"></i>
                                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-brand mt-4" type="submit"><i class="bi bi-check2-circle"></i> Update password</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2><i class="bi bi-info-circle me-2"></i>Security overview</h2>
                </div>
                <div class="panel-body">
                    <div class="security-row">
                        <span class="security-label"><i class="bi bi-envelope-check"></i> Email verification</span>
                        <?php if (!empty($user['email_verified_at'])): ?>
                            <span class="badge-soft is-ok"><i class="bi bi-check-circle me-1"></i>Verified <?= e(format_date($user['email_verified_at'])) ?></span>
                        <?php else: ?>
                            <span class="badge-soft is-warn"><i class="bi bi-exclamation-circle me-1"></i>Unverified</span>
                        <?php endif; ?>
                    </div>
                    <div class="security-row">
                        <span class="security-label"><i class="bi bi-clock-history"></i> Last sign-in</span>
                        <span class="text-muted" style="font-size:.8rem"><?= e(format_datetime($user['last_login_at'] ?? null)) ?> · <?= e($user['last_login_ip'] ?? 'unknown IP') ?></span>
                    </div>
                    <div class="security-row">
                        <span class="security-label"><i class="bi bi-calendar-check"></i> Password last changed</span>
                        <span class="text-muted" style="font-size:.8rem"><?= e(format_datetime($user['password_changed_at'] ?? null)) ?></span>
                    </div>
                    <div class="security-row">
                        <span class="security-label"><i class="bi bi-person-badge"></i> Account status</span>
                        <span class="badge-soft <?= $user['status'] === 'active' ? 'is-ok' : 'is-warn' ?>"><?= e(ucfirst($user['status'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
