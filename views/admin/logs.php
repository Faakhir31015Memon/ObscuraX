<?php
/** @var list<array> $logs */
/** @var string $type */
/** @var string $q */
/** @var array $paginator */
$isAdminLog = $type === 'admin';
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Audit</p>
        <h1>Activity logs</h1>
        <p>Every administrator action and reader event, recorded with its IP address.</p>
    </div>
</div>

<div class="analytics-tabs">
    <a class="<?= $isAdminLog ? 'active' : '' ?>" href="<?= url('/admin/logs?type=admin') ?>"><i class="bi bi-shield-check me-1"></i> Administrator actions</a>
    <a class="<?= !$isAdminLog ? 'active' : '' ?>" href="<?= url('/admin/logs?type=user') ?>"><i class="bi bi-people me-1"></i> Reader activity</a>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/logs') ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Search action, description or actor…" aria-label="Search logs">
        </div>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($q !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/logs?type=' . $type) ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= number_format((int) $paginator['total']) ?> event<?= (int) $paginator['total'] === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Description</th>
                    <?php if ($isAdminLog): ?><th>Target</th><?php endif; ?>
                    <th>IP</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <img src="<?= e(\App\Models\User::avatar($log['avatar'] ?? null)) ?>" alt="">
                            <div>
                                <div class="name"><?= $log['username'] ? '@' . e($log['username']) : 'Deleted account' ?></div>
                                <span class="sub"><?= $isAdminLog ? 'Admin #' . (int) ($log['admin_id'] ?? 0) : 'Reader #' . (int) ($log['user_id'] ?? 0) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="cell-title"><?= e($log['action']) ?></span></td>
                    <td><span class="cell-sub"><?= e($log['description'] ?: '—') ?></span></td>
                    <?php if ($isAdminLog): ?>
                        <td><span class="cell-sub"><?= $log['target_type'] ? e($log['target_type']) . ' #' . (int) $log['target_id'] : '—' ?></span></td>
                    <?php endif; ?>
                    <td><span class="cell-sub"><?= e($log['ip_address'] ?: '—') ?></span></td>
                    <td><span class="cell-sub"><?= e(format_datetime($log['created_at'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($logs === []): ?>
                <tr><td colspan="<?= $isAdminLog ? 6 : 5 ?>"><div class="admin-empty"><i class="bi bi-clock-history"></i><p>No events match this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
