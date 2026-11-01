<?php
/** @var array $users */
/** @var array $filters */
/** @var array $stats */
$paginator = $users;
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Community</p>
        <h1>Readers</h1>
        <p>Every registered reader. Activate, deactivate, ban or remove an account from its profile page.</p>
    </div>
</div>

<div class="admin-grid admin-grid-4" style="margin-bottom:1.2rem">
    <article class="admin-stat">
        <div class="top">
            <div>
                <span class="label">Total readers</span>
                <p class="value"><?= number_format((int) $stats['readers_total']) ?></p>
                <span class="hint"><?= (int) $stats['readers_new_month'] ?> new this month</span>
            </div>
            <span class="ico"><i class="bi bi-people"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-green">
        <div class="top">
            <div>
                <span class="label">Active</span>
                <p class="value"><?= number_format((int) $stats['readers_active']) ?></p>
                <span class="hint"><?= (int) $stats['readers_online'] ?> online now</span>
            </div>
            <span class="ico"><i class="bi bi-person-check"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-red">
        <div class="top">
            <div>
                <span class="label">Banned</span>
                <p class="value"><?= number_format((int) $stats['readers_banned']) ?></p>
                <span class="hint"><?= (int) $stats['readers_removed'] ?> removed</span>
            </div>
            <span class="ico"><i class="bi bi-slash-circle"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-gold">
        <div class="top">
            <div>
                <span class="label">Awaiting verification</span>
                <p class="value"><?= number_format((int) $stats['readers_pending']) ?></p>
                <span class="hint"><?= (int) $stats['readers_deactivated'] ?> deactivated</span>
            </div>
            <span class="ico"><i class="bi bi-hourglass-split"></i></span>
        </div>
    </article>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/users') ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search username, name or email…" aria-label="Search readers">
        </div>
        <select class="form-select" name="status" aria-label="Filter by status" style="max-width:150px">
            <option value="">Any status</option>
            <?php foreach (['active' => 'Active', 'inactive' => 'Deactivated', 'banned' => 'Banned', 'removed' => 'Removed'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" name="verified" aria-label="Filter by verification" style="max-width:160px">
            <option value="">Any verification</option>
            <option value="yes" <?= $filters['verified'] === 'yes' ? 'selected' : '' ?>>Verified</option>
            <option value="no" <?= $filters['verified'] === 'no' ? 'selected' : '' ?>>Unverified</option>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['verified'] !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/users') ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= number_format((int) $users['total']) ?> reader<?= (int) $users['total'] === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reader</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Joined</th>
                    <th>Last login</th>
                    <th class="text-end">Activity</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users['items'] as $user): ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <img src="<?= e(\App\Models\User::avatar($user['avatar'])) ?>" alt="">
                            <div>
                                <div class="name"><a href="<?= url('/admin/users/' . $user['id']) ?>"><?= e($user['username']) ?></a></div>
                                <span class="sub"><?= e($user['email']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="role-pill <?= e($user['role_slug']) ?>"><?= e($user['role_name']) ?></span></td>
                    <td>
                        <?php $pending = $user['status'] === 'inactive' && empty($user['email_verified_at']); ?>
                        <span class="badge-status <?= e($pending ? 'pending' : $user['status']) ?>"><?= e($pending ? 'Awaiting verification' : \App\Models\User::statusLabel($user['status'])) ?></span>
                    </td>
                    <td>
                        <?php if (!empty($user['email_verified_at'])): ?>
                            <i class="bi bi-patch-check-fill" style="color:#7ff0c1" title="Verified"></i>
                        <?php else: ?>
                            <i class="bi bi-patch-exclamation" style="color:#f2d99e" title="Unverified"></i>
                        <?php endif; ?>
                    </td>
                    <td><span class="cell-sub"><?= e(format_date($user['created_at'])) ?></span></td>
                    <td><span class="cell-sub"><?= e($user['last_login_at'] ? time_ago($user['last_login_at']) : 'Never') ?></span></td>
                    <td class="text-end">
                        <span class="cell-sub"><?= (int) $user['comment_count'] ?> comments · <?= (int) $user['like_count'] ?> likes</span>
                    </td>
                    <td class="text-end"><a class="icon-action" href="<?= url('/admin/users/' . $user['id']) ?>" aria-label="View reader"><i class="bi bi-arrow-right"></i></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($users['items'] === []): ?>
                <tr><td colspan="8"><div class="admin-empty"><i class="bi bi-people"></i><p><?= ($filters['q'] === '' && $filters['status'] === '' && $filters['verified'] === '') ? 'No readers have joined yet. New registrations appear here in real time.' : 'No readers match this view.' ?></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
