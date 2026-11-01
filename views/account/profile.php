<?php /** @var array $user */ ?>
<div class="container">
    <div class="account-layout">
        <?php require ROOT_PATH . '/views/account/_sidebar.php'; ?>

        <div class="account-main">
            <div class="account-welcome">
                <div>
                    <span class="eyebrow">Public profile</span>
                    <h1>Profile details</h1>
                    <p>This information appears beside your comments and on your public author page.</p>
                </div>
            </div>

            <div class="profile-header">
                <img src="<?= e(\App\Models\User::avatar($user['avatar'] ?? null)) ?>" alt="<?= e($user['username']) ?>">
                <div>
                    <h1 style="font-size:1.5rem"><?= e($user['full_name'] ?: $user['username']) ?></h1>
                    <p>@<?= e($user['username']) ?> · joined <?= e(format_date($user['created_at'] ?? null)) ?></p>
                    <p style="font-size:.78rem"><?= e(ucfirst($user['status'])) ?> account · last seen <?= e(time_ago($user['last_activity_at'] ?? $user['last_login_at'] ?? null)) ?></p>
                </div>
            </div>

            <div class="panel mb-4">
                <div class="panel-head">
                    <h2><i class="bi bi-image me-2"></i>Profile picture</h2>
                    <span class="text-muted" style="font-size:.76rem">JPG, PNG or WebP · max 3 MB</span>
                </div>
                <div class="panel-body">
                    <form method="post" action="<?= url('/profile/avatar') ?>" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-3">
                        <?= csrf_field() ?>
                        <div class="flex-grow-1" style="min-width:240px">
                            <label class="form-label" for="avatar">Choose a new avatar</label>
                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" required>
                        </div>
                        <button class="btn btn-brand" type="submit"><i class="bi bi-upload"></i> Upload avatar</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2><i class="bi bi-person-gear me-2"></i>Account details</h2>
                </div>
                <div class="panel-body-lg">
                    <form method="post" action="<?= url('/profile') ?>">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="username">Username <span class="text-muted">*</span></label>
                                <input class="form-control" id="username" name="username" value="<?= e(old('username', $user['username'])) ?>" required minlength="3" maxlength="24" pattern="[A-Za-z0-9_.\-]+" autocomplete="username">
                                <p class="form-text">Letters, numbers, dots, dashes and underscores.</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="full_name">Full name</label>
                                <input class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name', $user['full_name'] ?? '')) ?>" maxlength="120" autocomplete="name">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="email">Email address <span class="text-muted">*</span></label>
                                <input class="form-control" id="email" name="email" type="email" value="<?= e(old('email', $user['email'])) ?>" required maxlength="190" autocomplete="email">
                                <?php if (empty($user['email_verified_at'])): ?>
                                    <p class="form-text" style="color:#ffb39a"><i class="bi bi-exclamation-triangle me-1"></i>Unverified — changing it sends a fresh verification link.</p>
                                <?php else: ?>
                                    <p class="form-text">Changing your email requires your current password.</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="website">Website</label>
                                <input class="form-control" id="website" name="website" type="url" value="<?= e(old('website', $user['website'] ?? '')) ?>" maxlength="190" placeholder="https://">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="600" placeholder="A short introduction shown on your public page."><?= e(old('bio', $user['bio'] ?? '')) ?></textarea>
                            <p class="form-text" data-counter-for="#bio">0 / 600</p>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="Only required when changing your email">
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn btn-brand" type="submit"><i class="bi bi-check2"></i> Save changes</button>
                            <a class="btn btn-outline-soft" href="<?= url('/dashboard') ?>">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="account-meta-grid mt-4">
                <div class="meta-cell"><span class="label">Registered</span><strong><?= e(format_datetime($user['created_at'] ?? null)) ?></strong></div>
                <div class="meta-cell"><span class="label">Activated</span><strong><?= e(format_datetime($user['activated_at'] ?? null)) ?></strong></div>
                <div class="meta-cell"><span class="label">Last login</span><strong><?= e(format_datetime($user['last_login_at'] ?? null)) ?></strong></div>
                <div class="meta-cell"><span class="label">Last IP</span><strong><?= e($user['last_login_ip'] ?? '—') ?></strong></div>
            </div>
        </div>
    </div>
</div>
