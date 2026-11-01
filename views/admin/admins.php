<?php
/** @var list<array> $admins */
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Super administration</p>
        <h1>Administrators</h1>
        <p>Editors who can publish articles and moderate content they author. Readers are managed separately.</p>
    </div>
</div>

<div class="admin-grid admin-grid-main">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2><?= count($admins) ?> privileged account<?= count($admins) === 1 ? '' : 's' ?></h2>
                <p class="sub">Disabled administrators cannot sign in until re-enabled.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Administrator</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last login</th>
                        <th>Since</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($admins as $admin): ?>
                    <?php $isSelf = (int) $admin['id'] === (int) $authUser['id']; ?>
                    <tr>
                        <td>
                            <div class="avatar-cell">
                                <img src="<?= e(\App\Models\User::avatar($admin['avatar'])) ?>" alt="">
                                <div>
                                    <div class="name"><?= e($admin['full_name'] ?: $admin['username']) ?><?= $isSelf ? ' (you)' : '' ?></div>
                                    <span class="sub"><?= e($admin['title'] ?: 'Administrator') ?><?= $admin['department'] ? ' · ' . e($admin['department']) : '' ?></span>
                                </div>
                            </div>
                        </td>
                        <td><span class="role-pill <?= e($admin['role_slug']) ?>"><?= e($admin['role_name']) ?></span></td>
                        <td>
                            <?php if ((int) $admin['is_disabled'] === 1 || $admin['status'] !== 'active'): ?>
                                <span class="badge-status inactive">disabled</span>
                            <?php else: ?>
                                <span class="badge-status active">active</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="cell-sub"><?= e($admin['last_login_at'] ? time_ago($admin['last_login_at']) : 'Never') ?></span></td>
                        <td><span class="cell-sub"><?= e(format_date($admin['admin_since'] ?: $admin['created_at'])) ?></span></td>
                        <td>
                            <div class="row-actions">
                                <?php if (!$isSelf): ?>
                                    <button class="icon-action" type="button" data-toggle-target="#admin-edit-<?= (int) $admin['id'] ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></button>
                                    <?php if ($admin['role_slug'] !== 'super_admin'): ?>
                                        <form method="post" action="<?= url('/admin/admins/' . $admin['id'] . '/disable') ?>" data-confirm="<?= (int) $admin['is_disabled'] === 1 ? 'Re-enable' : 'Disable' ?> this administrator?">
                                            <?= csrf_field() ?>
                                            <button class="icon-action <?= (int) $admin['is_disabled'] === 1 ? 'success' : '' ?>" type="submit" title="<?= (int) $admin['is_disabled'] === 1 ? 'Enable' : 'Disable' ?>" aria-label="<?= (int) $admin['is_disabled'] === 1 ? 'Enable' : 'Disable' ?>"><i class="bi <?= (int) $admin['is_disabled'] === 1 ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= url('/admin/admins/' . $admin['id'] . '/remove') ?>" data-confirm="Demote @<?= e($admin['username']) ?> to a reader account?">
                                        <?= csrf_field() ?>
                                        <button class="icon-action danger" type="submit" title="Demote to reader" aria-label="Demote to reader"><i class="bi bi-person-dash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="d-none" id="admin-edit-<?= (int) $admin['id'] ?>">
                        <td colspan="6">
                            <form class="form-section" method="post" action="<?= url('/admin/admins/' . $admin['id'] . '/update') ?>">
                                <?= csrf_field() ?>
                                <h3>Edit @<?= e($admin['username']) ?></h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="u-<?= (int) $admin['id'] ?>">Username</label>
                                        <input class="form-control" id="u-<?= (int) $admin['id'] ?>" name="username" type="text" value="<?= e($admin['username']) ?>" maxlength="24" pattern="[A-Za-z0-9_.\-]+" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="e-<?= (int) $admin['id'] ?>">Email</label>
                                        <input class="form-control" id="e-<?= (int) $admin['id'] ?>" name="email" type="email" value="<?= e($admin['email']) ?>" maxlength="190" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="n-<?= (int) $admin['id'] ?>">Full name</label>
                                        <input class="form-control" id="n-<?= (int) $admin['id'] ?>" name="full_name" type="text" value="<?= e($admin['full_name']) ?>" maxlength="120">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="t-<?= (int) $admin['id'] ?>">Title</label>
                                        <input class="form-control" id="t-<?= (int) $admin['id'] ?>" name="title" type="text" value="<?= e($admin['title']) ?>" maxlength="96">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="d-<?= (int) $admin['id'] ?>">Department</label>
                                    <input class="form-control" id="d-<?= (int) $admin['id'] ?>" name="department" type="text" value="<?= e($admin['department']) ?>" maxlength="96">
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-brand btn-sm" type="submit">Save administrator</button>
                                    <button class="btn btn-ghost btn-sm" type="button" data-toggle-target="#admin-edit-<?= (int) $admin['id'] ?>">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>Create administrator</h2>
        </div>
        <div class="admin-card-body">
            <form method="post" action="<?= url('/admin/admins') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="new-username">Username</label>
                    <input class="form-control" id="new-username" name="username" type="text" value="<?= e(old('username')) ?>" maxlength="24" pattern="[A-Za-z0-9_.\-]+" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-email">Email</label>
                    <input class="form-control" id="new-email" name="email" type="email" value="<?= e(old('email')) ?>" maxlength="190" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-full-name">Full name</label>
                    <input class="form-control" id="new-full-name" name="full_name" type="text" value="<?= e(old('full_name')) ?>" maxlength="120">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="new-title">Title</label>
                        <input class="form-control" id="new-title" name="title" type="text" value="<?= e(old('title', 'Administrator')) ?>" maxlength="96">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new-department">Department</label>
                        <input class="form-control" id="new-department" name="department" type="text" value="<?= e(old('department')) ?>" maxlength="96">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-admin-password">Temporary password</label>
                    <div class="input-group">
                        <input class="form-control" id="new-admin-password" name="password" type="text" minlength="8" maxlength="72" required autocomplete="new-password">
                        <button class="btn btn-outline-soft" type="button" data-generate-password="#new-admin-password" title="Generate a strong password"><i class="bi bi-shuffle"></i></button>
                    </div>
                    <p class="form-text">Share this password securely. The administrator can change it anytime from Change password.</p>
                </div>
                <button class="btn btn-brand w-100" type="submit"><i class="bi bi-person-plus"></i> Create administrator</button>
            </form>
            <div class="admin-note mt-3">
                <i class="bi bi-shield-lock"></i>
                <div>Administrators can publish articles, manage categories, tags, the hero section and comments on their own articles. They cannot manage users or settings.</div>
            </div>
        </div>
    </section>
</div>
