<?php
/** @var array $stats */
/** @var array $authUser */
$isSuper = !empty($isSuper);
$name = trim((string) ($authUser['full_name'] ?? '')) ?: (string) ($authUser['username'] ?? 'Admin');
$firstName = explode(' ', $name)[0];

$growthLabels = array_map(static fn (array $row): string => $row['label'], $monthly);
$growth = [
    'type' => 'line',
    'labels' => $growthLabels,
    'datasets' => [
        ['label' => 'New readers', 'data' => array_map(static fn (array $r): int => (int) $r['users'], $monthly), 'borderColor' => '#a78bfa', 'backgroundColor' => 'rgba(167,139,250,0.12)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
        ['label' => 'Published', 'data' => array_map(static fn (array $r): int => (int) $r['blogs'], $monthly), 'borderColor' => '#ff2d4b', 'backgroundColor' => 'rgba(255,45,75,0.08)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
        ['label' => 'Comments', 'data' => array_map(static fn (array $r): int => (int) $r['comments'], $monthly), 'borderColor' => '#3ddc97', 'backgroundColor' => 'rgba(61,220,151,0.08)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
    ],
];

$viewsLabels = array_map(static fn (array $row): string => $row['label'], $viewsByDay);
$views = [
    'type' => 'bar',
    'labels' => $viewsLabels,
    'legend' => false,
    'datasets' => [
        ['label' => 'Views', 'data' => array_map(static fn (array $r): int => (int) $r['value'], $viewsByDay), 'backgroundColor' => 'rgba(139,92,246,0.55)', 'borderColor' => 'rgba(167,139,250,0.9)', 'borderWidth' => 1, 'borderRadius' => 5],
    ],
];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Command centre</p>
        <h1>Welcome back, <?= e($firstName) ?></h1>
        <p><?= $isSuper ? 'Full oversight of every reader, article and signal across the archive.' : 'Your desk for writing, publishing and moderating your own investigations.' ?></p>
    </div>
    <div class="admin-page-actions">
        <?php if (can('blog.create')): ?>
            <a class="btn btn-brand btn-sm" href="<?= url('/admin/blogs/create') ?>"><i class="bi bi-plus-lg"></i> New article</a>
        <?php endif; ?>
        <?php if ($isSuper): ?>
            <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/users') ?>"><i class="bi bi-people"></i> Readers <span class="badge-tag ms-1"><?= number_format((int) $stats['readers_total']) ?></span></a>
        <?php endif; ?>
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> View site</a>
    </div>
</div>

<?php if (\App\Core\Auth::mustChangePassword()): ?>
    <div class="pw-change-banner">
        <i class="bi bi-shield-exclamation"></i>
        <span>You are still using the password issued at setup. <a href="<?= url('/admin/password') ?>">Choose a new password</a> before continuing.</span>
    </div>
<?php endif; ?>

<div class="admin-grid admin-grid-4">
    <?php if ($isSuper): ?>
        <article class="admin-stat">
            <div class="top">
                <div>
                    <span class="label">Investigations</span>
                    <p class="value"><?= number_format((int) $stats['blogs_published']) ?></p>
                    <span class="hint"><?= (int) $stats['blogs_draft'] ?> drafts · <?= (int) $stats['blogs_unpublished'] ?> unpublished</span>
                </div>
                <span class="ico"><i class="bi bi-journal-richtext"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-green">
            <div class="top">
                <div>
                    <span class="label">Verified readers</span>
                    <p class="value"><?= number_format((int) $stats['readers_verified']) ?></p>
                    <span class="hint"><?= (int) $stats['readers_pending'] ?> awaiting verification</span>
                </div>
                <span class="ico"><i class="bi bi-patch-check"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-red">
            <div class="top">
                <div>
                    <span class="label">Total readers</span>
                    <p class="value"><?= number_format((int) $stats['readers_total']) ?></p>
                    <span class="hint"><?= (int) $stats['readers_online'] ?> online now · <?= (int) $stats['readers_new_month'] ?> this month</span>
                </div>
                <span class="ico"><i class="bi bi-people"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-blue">
            <div class="top">
                <div>
                    <span class="label">Lifetime views</span>
                    <p class="value"><?= number_short((int) $stats['blog_views']) ?></p>
                    <span class="hint"><?= number_format((int) $stats['daily_views']) ?> in the last 24 hours</span>
                </div>
                <span class="ico"><i class="bi bi-eye"></i></span>
            </div>
        </article>
    <?php else: ?>
        <article class="admin-stat">
            <div class="top">
                <div>
                    <span class="label">My articles</span>
                    <p class="value"><?= number_format((int) ($myBlogsTotal ?? count($myBlogs))) ?></p>
                    <span class="hint"><?= (int) $myPublished ?> published</span>
                </div>
                <span class="ico"><i class="bi bi-journal-richtext"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-blue">
            <div class="top">
                <div>
                    <span class="label">Views on my work</span>
                    <p class="value"><?= number_short((int) $myViews) ?></p>
                    <span class="hint">Across all of your articles</span>
                </div>
                <span class="ico"><i class="bi bi-eye"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-green">
            <div class="top">
                <div>
                    <span class="label">Platform comments</span>
                    <p class="value"><?= number_format((int) $stats['comments_total']) ?></p>
                    <span class="hint"><?= number_format((int) $stats['replies_total']) ?> replies</span>
                </div>
                <span class="ico"><i class="bi bi-chat-square-text"></i></span>
            </div>
        </article>
        <article class="admin-stat accent-red">
            <div class="top">
                <div>
                    <span class="label">Platform likes</span>
                    <p class="value"><?= number_format((int) $stats['likes_total']) ?></p>
                    <span class="hint">Across every published piece</span>
                </div>
                <span class="ico"><i class="bi bi-heart"></i></span>
            </div>
        </article>
    <?php endif; ?>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Twelve-month momentum</h2>
                <p class="sub">Readers, published articles and comments</p>
            </div>
            <a class="small-link" href="<?= url('/admin/analytics') ?>">Full analytics <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="dashboardGrowth" aria-label="Monthly growth" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Readership, last 14 days</h2>
                <p class="sub">Daily page views</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="dashboardViews" aria-label="Daily views" role="img"></canvas></div>
        </div>
    </section>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2><?= $isSuper ? 'Most viewed articles' : 'My latest articles' ?></h2>
                <p class="sub"><?= $isSuper ? 'Every published piece, ranked by readership' : 'Your most recent drafts and publications' ?></p>
            </div>
            <a class="small-link" href="<?= url($isSuper ? '/admin/blogs/all' : '/admin/blogs') ?>">All articles <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Status</th>
                        <th class="text-end">Views</th>
                        <th class="text-end">Likes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($isSuper): ?>
                    <?php foreach ($topBlogs as $row): ?>
                        <tr>
                            <td>
                                <span class="cell-title"><?= e($row['title']) ?></span>
                                <span class="cell-sub"><?= e($row['category_name']) ?></span>
                            </td>
                            <td><span class="badge-status <?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                            <td class="text-end"><?= number_format((int) $row['view_count']) ?></td>
                            <td class="text-end"><?= number_format((int) $row['like_count']) ?></td>
                            <td class="text-end"><a class="icon-action" href="<?= url('/blog/' . $row['slug']) ?>" target="_blank" rel="noopener" aria-label="View article"><i class="bi bi-box-arrow-up-right"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($topBlogs === []): ?>
                        <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-journal-x"></i><p>No published articles yet.</p></div></td></tr>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($myBlogs as $row): ?>
                        <tr>
                            <td>
                                <span class="cell-title"><a href="<?= url('/admin/blogs/' . $row['id'] . '/edit') ?>"><?= e($row['title']) ?></a></span>
                                <span class="cell-sub"><?= e($row['category_name']) ?> · <?= e(format_date($row['updated_at'])) ?></span>
                            </td>
                            <td><span class="badge-status <?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
                            <td class="text-end"><?= number_format((int) $row['view_count']) ?></td>
                            <td class="text-end"><?= number_format((int) $row['like_count']) ?></td>
                            <td class="text-end"><a class="icon-action" href="<?= url('/admin/blogs/' . $row['id'] . '/edit') ?>" aria-label="Edit article"><i class="bi bi-pencil"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($myBlogs === []): ?>
                        <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-pencil-square"></i><p>You have not written anything yet. <a href="<?= url('/admin/blogs/create') ?>">Start your first article</a>.</p></div></td></tr>
                    <?php endif; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="admin-grid">
        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2>Recent comments</h2>
                    <p class="sub">Latest community replies</p>
                </div>
                <a class="small-link" href="<?= url('/admin/comments') ?>">Moderate</a>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <tbody>
                    <?php foreach ($recentComments as $comment): ?>
                        <tr>
                            <td>
                                <span class="cell-title">@<?= e($comment['username']) ?></span>
                                <span class="cell-sub"><?= e(str_limit($comment['content'], 72)) ?></span>
                            </td>
                            <td class="text-end"><span class="cell-sub"><?= e(time_ago($comment['created_at'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($recentComments === []): ?>
                        <tr><td><div class="admin-empty"><i class="bi bi-chat"></i><p>No comments yet.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($isSuper): ?>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Newest readers</h2>
                        <p class="sub">Latest registrations</p>
                    </div>
                    <a class="small-link" href="<?= url('/admin/users') ?>">All readers</a>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <tbody>
                        <?php foreach ($recentRegistrations as $reader): ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <img src="<?= e(\App\Models\User::avatar($reader['avatar'])) ?>" alt="">
                                        <div>
                                            <div class="name"><?= e($reader['username']) ?></div>
                                            <span class="sub"><?= e($reader['role_slug']) ?> · <?= e(time_ago($reader['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end"><a class="icon-action" href="<?= url('/admin/users/' . $reader['id']) ?>" aria-label="View user"><i class="bi bi-arrow-right"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($recentRegistrations === []): ?>
                            <tr><td><div class="admin-empty"><i class="bi bi-person-plus"></i><p>No registrations yet.</p></div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Online now</h2>
                        <p class="sub">Active in the last 5 minutes</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <?php if ($online === []): ?>
                        <div class="admin-empty" style="padding:1rem"><i class="bi bi-moon-stars"></i><p>Nobody else is reading right now.</p></div>
                    <?php else: ?>
                        <div class="detail-list">
                            <?php foreach ($online as $reader): ?>
                                <div class="row-item">
                                    <span><i class="bi bi-circle-fill" style="font-size:0.4rem;color:#3ddc97;margin-right:0.4rem"></i><?= e($reader['username']) ?></span>
                                    <span><?= e(time_ago($reader['last_activity_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script type="application/json" id="dashboardGrowth" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($growth, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="dashboardViews" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($views, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
