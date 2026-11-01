<?php
/** @var array $messages */
/** @var array $filters */
/** @var array $counts */
$paginator = $messages;
$status = (string) $filters['status'];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Audience</p>
        <h1>Messages</h1>
        <p>Everything sent through the contact form, with the sender's address kept intact.</p>
    </div>
</div>

<div class="analytics-tabs">
    <a class="<?= $status === '' ? 'active' : '' ?>" href="<?= url('/admin/messages') ?>">All <span class="cell-sub d-inline">(<?= (int) $counts['total'] ?>)</span></a>
    <a class="<?= $status === 'new' ? 'active' : '' ?>" href="<?= url('/admin/messages?status=new') ?>">New <span class="cell-sub d-inline">(<?= (int) $counts['new'] ?>)</span></a>
    <a class="<?= $status === 'read' ? 'active' : '' ?>" href="<?= url('/admin/messages?status=read') ?>">Read <span class="cell-sub d-inline">(<?= (int) $counts['read'] ?>)</span></a>
    <a class="<?= $status === 'archived' ? 'active' : '' ?>" href="<?= url('/admin/messages?status=archived') ?>">Archived <span class="cell-sub d-inline">(<?= (int) $counts['archived'] ?>)</span></a>
</div>

<section class="admin-card">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($messages['items'] as $message): ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <span style="display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border-radius:50%;background:rgba(139,92,246,.16);color:#d9c9ff"><i class="bi bi-person"></i></span>
                            <div>
                                <div class="name"><?= e($message['name']) ?></div>
                                <span class="sub"><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="cell-title"><?= e($message['subject']) ?></span>
                        <span class="cell-sub"><?= e(str_limit($message['message'], 80)) ?></span>
                    </td>
                    <td><span class="badge-status <?= e($message['status']) ?>"><?= e($message['status']) ?></span></td>
                    <td><span class="cell-sub"><?= e(time_ago($message['created_at'])) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-action" type="button" data-toggle-target="#msg-<?= (int) $message['id'] ?>" title="Read message" aria-label="Read message"><i class="bi bi-envelope-open"></i></button>
                            <?php if ($message['status'] === 'new'): ?>
                                <form method="post" action="<?= url('/admin/messages/' . $message['id'] . '/read') ?>">
                                    <?= csrf_field() ?>
                                    <button class="icon-action success" type="submit" title="Mark as read" aria-label="Mark as read"><i class="bi bi-check2"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if ($message['status'] !== 'archived'): ?>
                                <form method="post" action="<?= url('/admin/messages/' . $message['id'] . '/archive') ?>">
                                    <?= csrf_field() ?>
                                    <button class="icon-action" type="submit" title="Archive" aria-label="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('/admin/messages/' . $message['id'] . '/delete') ?>" data-confirm="Delete this message permanently?">
                                <?= csrf_field() ?>
                                <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="d-none" id="msg-<?= (int) $message['id'] ?>">
                    <td colspan="5">
                        <div class="form-section">
                            <h3><?= e($message['subject']) ?></h3>
                            <p style="color:var(--text-soft);white-space:pre-line"><?= e($message['message']) ?></p>
                            <div class="detail-list">
                                <div class="row-item"><span>From</span><span><?= e($message['name']) ?> &lt;<?= e($message['email']) ?>&gt;</span></div>
                                <div class="row-item"><span>Received</span><span><?= e(format_datetime($message['created_at'])) ?></span></div>
                                <div class="row-item"><span>IP address</span><span><?= e($message['ip_address'] ?: '—') ?></span></div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <a class="btn btn-outline-soft btn-sm" href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e(rawurlencode($message['subject'])) ?>"><i class="bi bi-reply"></i> Reply by email</a>
                                <button class="btn btn-ghost btn-sm" type="button" data-toggle-target="#msg-<?= (int) $message['id'] ?>">Close</button>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($messages['items'] === []): ?>
                <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-inbox"></i><p>No messages in this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
