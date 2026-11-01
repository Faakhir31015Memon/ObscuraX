<?php
/** @var array $subscribers */
/** @var array $filters */
/** @var array $counts */
$paginator = $subscribers;
$status = (string) $filters['status'];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Audience</p>
        <h1>Newsletter</h1>
        <p>Readers who asked to hear about new investigations.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-brand btn-sm" href="<?= url('/admin/newsletter/export') ?>"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</div>

<div class="admin-grid admin-grid-3" style="margin-bottom:1.2rem">
    <article class="admin-stat">
        <div class="top">
            <div>
                <span class="label">Subscribed</span>
                <p class="value"><?= number_format((int) $counts['subscribed']) ?></p>
                <span class="hint">Receiving the newsletter</span>
            </div>
            <span class="ico"><i class="bi bi-envelope-check"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-gold">
        <div class="top">
            <div>
                <span class="label">Unsubscribed</span>
                <p class="value"><?= number_format((int) $counts['unsubscribed']) ?></p>
                <span class="hint">Opted out</span>
            </div>
            <span class="ico"><i class="bi bi-envelope-slash"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-blue">
        <div class="top">
            <div>
                <span class="label">All records</span>
                <p class="value"><?= number_format((int) $counts['total']) ?></p>
                <span class="hint">Including opt-outs</span>
            </div>
            <span class="ico"><i class="bi bi-envelope-paper"></i></span>
        </div>
    </article>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/newsletter') ?>">
        <select class="form-select" name="status" aria-label="Filter by status" style="max-width:200px">
            <option value="">All subscribers</option>
            <option value="subscribed" <?= $status === 'subscribed' ? 'selected' : '' ?>>Subscribed</option>
            <option value="unsubscribed" <?= $status === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($status !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/newsletter') ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= number_format((int) $subscribers['total']) ?> record<?= (int) $subscribers['total'] === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Subscribed</th>
                    <th>Unsubscribed</th>
                    <th>IP address</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subscribers['items'] as $subscriber): ?>
                <tr>
                    <td><span class="cell-title"><?= e($subscriber['email']) ?></span></td>
                    <td><span class="badge-status <?= e($subscriber['status']) ?>"><?= e($subscriber['status']) ?></span></td>
                    <td><span class="cell-sub"><?= e(format_datetime($subscriber['confirmed_at'] ?: $subscriber['created_at'])) ?></span></td>
                    <td><span class="cell-sub"><?= e($subscriber['unsubscribed_at'] ? format_datetime($subscriber['unsubscribed_at']) : '—') ?></span></td>
                    <td><span class="cell-sub"><?= e($subscriber['ip_address'] ?: '—') ?></span></td>
                    <td>
                        <div class="row-actions">
                            <form method="post" action="<?= url('/admin/newsletter/' . $subscriber['id'] . '/delete') ?>" data-confirm="Permanently delete <?= e($subscriber['email']) ?> from the newsletter list?">
                                <?= csrf_field() ?>
                                <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($subscribers['items'] === []): ?>
                <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-envelope-paper"></i><p>No subscribers match this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
