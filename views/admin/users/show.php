<?php
/** @var array $user */
/** @var list<array> $activity */
/** @var list<array> $comments */
/** @var list<array> $blogs */
/** @var array $counts */
/** @var bool $isPrivileged */
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Reader profile</p>
        <h1>@<?= e($user['username']) ?></h1>
        <p>Account history, engagement and moderation controls.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/users') ?>"><i class="bi bi-arrow-left"></i> All readers</a>
        <a class="btn btn-ghost btn-sm" href="<?= url('/author/' . $user['username']) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Public profile</a>
    </div>
</div>

<div class="user-detail-hero">
    <img src="<?= e(\App\Models\User::avatar($user['avatar'])) ?>" alt="">
    <div>
        <h1><?= e($user['full_name'] ?: $user['username']) ?></h1>
        <div class="meta">
            <span>@<?= e($user['username']) ?></span>
            <span><?= e($user['email']) ?></span>
            <span class="role-pill <?= e($user['role_slug']) ?>"><?= e($user['role_name']) ?></span>
            <span class="badge-status <?= e($user['status']) ?>"><?= e(\App\Models\User::statusLabel($user['status'])) ?></span>
        </div>
    </div>
    <div class="actions">
        <?php if ($isPrivileged): ?>
            <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/admins') ?>"><i class="bi bi-person-badge"></i> Manage administrator</a>
        <?php else: ?>
            <?php if ($user['status'] === 'banned'): ?>
                <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/unban') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-person-check"></i> Unban account</button>
                </form>
            <?php elseif ($user['status'] !== 'active'): ?>
                <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/activate') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-person-check"></i> Activate</button>
                </form>
            <?php endif; ?>
            <?php if ($user['status'] === 'active'): ?>
                <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/deactivate') ?>" data-confirm="Deactivate this account? They will not be able to sign in.">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-person-dash"></i> Deactivate</button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/remove') ?>" data-confirm="Mark this account as removed? Their comments remain for thread context.">
                <?= csrf_field() ?>
                <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-person-x"></i> Mark removed</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid admin-grid-main">
    <div class="admin-grid">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Account details</h2>
            </div>
            <div class="admin-card-body">
                <div class="form-row">
                    <div class="detail-list">
                        <div class="row-item"><span>Username</span><span>@<?= e($user['username']) ?></span></div>
                        <div class="row-item"><span>Email address</span><span><?= e($user['email']) ?></span></div>
                        <div class="row-item"><span>Email verified</span><span><?= e(format_datetime($user['email_verified_at'])) ?></span></div>
                        <div class="row-item"><span>Registered</span><span><?= e(format_datetime($user['created_at'])) ?></span></div>
                        <div class="row-item"><span>Activated</span><span><?= e(format_datetime($user['activated_at'])) ?></span></div>
                    </div>
                    <div class="detail-list">
                        <div class="row-item"><span>Last sign-in</span><span><?= e(format_datetime($user['last_login_at'])) ?></span></div>
                        <div class="row-item"><span>Last IP</span><span><?= e($user['last_login_ip'] ?: 'Unknown') ?></span></div>
                        <div class="row-item"><span>Status</span><span><?= e(\App\Models\User::statusLabel($user['status'])) ?></span></div>
                        <div class="row-item"><span>Password changed</span><span><?= e(format_datetime($user['password_changed_at'])) ?></span></div>
                        <div class="row-item"><span>Website</span><span><?= $user['website'] ? '<a href="' . e($user['website']) . '" target="_blank" rel="noopener">' . e($user['website']) . '</a>' : '—' ?></span></div>
                    </div>
                </div>
                <?php if (!empty($user['bio'])): ?>
                    <hr style="border-color:var(--line)">
                    <p class="mb-0" style="color:var(--text-soft);font-size:.86rem"><?= nl2br(e($user['bio'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['banned_reason'])): ?>
                    <div class="admin-note mt-3" style="border-color:rgba(255,45,75,.35);background:rgba(255,45,75,.08)">
                        <i class="bi bi-slash-circle" style="color:#ff8fa2"></i>
                        <div><strong>Ban reason:</strong> <?= e($user['banned_reason']) ?><br><span class="cell-sub">Banned <?= e(format_datetime($user['banned_at'])) ?></span></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2>Activity log</h2>
                    <p class="sub">The 20 most recent recorded events</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Event</th><th>Description</th><th>IP</th><th>When</th></tr></thead>
                    <tbody>
                    <?php foreach ($activity as $entry): ?>
                        <tr>
                            <td><span class="cell-title"><?= e($entry['action']) ?></span></td>
                            <td><span class="cell-sub"><?= e($entry['description'] ?: '—') ?></span></td>
                            <td><span class="cell-sub"><?= e($entry['ip_address'] ?: '—') ?></span></td>
                            <td><span class="cell-sub"><?= e(time_ago($entry['created_at'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($activity === []): ?>
                        <tr><td colspan="4"><div class="admin-empty"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2>Recent comments</h2>
                    <p class="sub">Latest contributions by this reader</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Comment</th><th>Article</th><th>Status</th><th>When</th></tr></thead>
                    <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><span class="cell-title"><?= e(str_limit($comment['content'], 90)) ?></span></td>
                            <td><span class="cell-sub"><?= e(str_limit($comment['blog_title'], 40)) ?></span></td>
                            <td><span class="badge-status <?= e($comment['status']) ?>"><?= e($comment['status']) ?></span></td>
                            <td><span class="cell-sub"><?= e(time_ago($comment['created_at'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($comments === []): ?>
                        <tr><td colspan="4"><div class="admin-empty"><i class="bi bi-chat-square"></i><p>No comments yet.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($blogs !== []): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Articles written</h2>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Article</th><th>Status</th><th class="text-end">Views</th><th>Created</th></tr></thead>
                        <tbody>
                        <?php foreach ($blogs as $blog): ?>
                            <tr>
                                <td><span class="cell-title"><a href="<?= url('/admin/blogs/' . $blog['id'] . '/edit') ?>"><?= e($blog['title']) ?></a></span></td>
                                <td><span class="badge-status <?= e($blog['status']) ?>"><?= e($blog['status']) ?></span></td>
                                <td class="text-end"><?= number_format((int) $blog['view_count']) ?></td>
                                <td><span class="cell-sub"><?= e(format_date($blog['created_at'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <div class="admin-grid">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Engagement</h2>
            </div>
            <div class="admin-card-body">
                <div class="admin-grid admin-grid-3">
                    <div class="admin-stat" style="padding:.9rem">
                        <span class="label">Articles</span>
                        <p class="value" style="font-size:1.4rem"><?= number_format((int) $counts['blogs']) ?></p>
                    </div>
                    <div class="admin-stat accent-green" style="padding:.9rem">
                        <span class="label">Comments</span>
                        <p class="value" style="font-size:1.4rem"><?= number_format((int) $counts['comments']) ?></p>
                    </div>
                    <div class="admin-stat accent-red" style="padding:.9rem">
                        <span class="label">Likes</span>
                        <p class="value" style="font-size:1.4rem"><?= number_format((int) $counts['likes']) ?></p>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!$isPrivileged && $user['status'] !== 'banned'): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Ban account</h2>
                </div>
                <div class="admin-card-body">
                    <form method="post" action="<?= url('/admin/users/' . $user['id'] . '/ban') ?>" data-confirm="Ban this reader? They will be signed out and blocked from returning.">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label" for="reason">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" maxlength="255" required placeholder="Explain the ban for the audit log."></textarea>
                            <p class="form-text">The reason is stored with the ban and the admin log.</p>
                        </div>
                        <button class="btn btn-outline-soft w-100" type="submit" style="border-color:rgba(255,45,75,.4);color:#ffb1be"><i class="bi bi-slash-circle"></i> Ban reader</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Metadata</h2>
            </div>
            <div class="admin-card-body">
                <div class="detail-list">
                    <div class="row-item"><span>Account ID</span><span>#<?= (int) $user['id'] ?></span></div>
                    <div class="row-item"><span>Force password change</span><span><?= (int) $user['must_change_password'] === 1 ? 'Yes' : 'No' ?></span></div>
                    <div class="row-item"><span>Registered</span><span><?= e(format_date($user['created_at'])) ?></span></div>
                    <div class="row-item"><span>Last active</span><span><?= e(time_ago($user['last_activity_at'])) ?></span></div>
                </div>
            </div>
        </section>
    </div>
</div>
