<?php
/** @var array $overview */
/** @var list<array> $monthly */
/** @var list<array> $yearly */
/** @var list<array> $viewsByDay */
/** @var list<array> $topViewed */
/** @var list<array> $topLiked */
/** @var list<array> $topCommented */
/** @var list<array> $topUsers */
/** @var list<array> $recentRegistrations */
/** @var list<array> $usersByStatus */
/** @var list<array> $categories */
/** @var list<array> $roles */
/** @var int $months */
$labels = array_map(static fn (array $row): string => $row['label'], $monthly);
$palette = ['#a78bfa', '#ff2d4b', '#3ddc97', '#35b6ff', '#d9b96a', '#ff7ac8'];
$statusColors = ['active' => '#3ddc97', 'inactive' => '#d9b96a', 'banned' => '#ff2d4b', 'removed' => '#6f6880'];

$growthChart = [
    'type' => 'line',
    'labels' => $labels,
    'datasets' => [
        ['label' => 'New readers', 'data' => array_map(static fn (array $r): int => (int) $r['users'], $monthly), 'borderColor' => '#a78bfa', 'backgroundColor' => 'rgba(167,139,250,0.1)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
        ['label' => 'Published articles', 'data' => array_map(static fn (array $r): int => (int) $r['blogs'], $monthly), 'borderColor' => '#ff2d4b', 'backgroundColor' => 'rgba(255,45,75,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
        ['label' => 'Comments', 'data' => array_map(static fn (array $r): int => (int) $r['comments'], $monthly), 'borderColor' => '#3ddc97', 'backgroundColor' => 'rgba(61,220,151,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
        ['label' => 'Likes', 'data' => array_map(static fn (array $r): int => (int) $r['likes'], $monthly), 'borderColor' => '#35b6ff', 'backgroundColor' => 'rgba(53,182,255,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
    ],
];

$engagementChart = [
    'type' => 'bar',
    'labels' => $labels,
    'datasets' => [
        ['label' => 'Page views', 'data' => array_map(static fn (array $r): int => (int) $r['views'], $monthly), 'backgroundColor' => 'rgba(139,92,246,0.55)', 'borderColor' => 'rgba(167,139,250,0.9)', 'borderWidth' => 1, 'borderRadius' => 5],
        ['label' => 'Replies', 'data' => array_map(static fn (array $r): int => (int) $r['replies'], $monthly), 'backgroundColor' => 'rgba(61,220,151,0.5)', 'borderColor' => 'rgba(61,220,151,0.8)', 'borderWidth' => 1, 'borderRadius' => 5],
    ],
];

$dailyChart = [
    'type' => 'bar',
    'labels' => array_map(static fn (array $r): string => $r['label'], $viewsByDay),
    'legend' => false,
    'datasets' => [
        ['label' => 'Views', 'data' => array_map(static fn (array $r): int => (int) $r['value'], $viewsByDay), 'backgroundColor' => 'rgba(53,182,255,0.45)', 'borderColor' => 'rgba(53,182,255,0.85)', 'borderWidth' => 1, 'borderRadius' => 4],
    ],
];

$statusChart = [
    'type' => 'doughnut',
    'labels' => array_map(static fn (array $r): string => ucfirst($r['status']), $usersByStatus),
    'datasets' => [[
        'data' => array_map(static fn (array $r): int => (int) $r['total'], $usersByStatus),
        'backgroundColor' => array_map(static fn (array $r): string => $statusColors[$r['status']] ?? '#6f6880', $usersByStatus),
        'borderColor' => '#0b0812',
        'borderWidth' => 2,
    ]],
];

$categoryChart = [
    'type' => 'doughnut',
    'labels' => array_map(static fn (array $r): string => $r['name'], $categories),
    'datasets' => [[
        'data' => array_map(static fn (array $r): int => (int) $r['blogs'], $categories),
        'backgroundColor' => array_map(static fn (array $r, int $i): string => $palette[$i % count($palette)], $categories, array_keys($categories)),
        'borderColor' => '#0b0812',
        'borderWidth' => 2,
    ]],
];

$yearlyChart = [
    'type' => 'line',
    'labels' => array_map(static fn (array $r): string => $r['label'], $yearly),
    'datasets' => [
        ['label' => 'Readers', 'data' => array_map(static fn (array $r): int => (int) $r['users'], $yearly), 'borderColor' => '#a78bfa', 'backgroundColor' => 'rgba(167,139,250,0.08)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 3],
        ['label' => 'Views', 'data' => array_map(static fn (array $r): int => (int) $r['views'], $yearly), 'borderColor' => '#3ddc97', 'backgroundColor' => 'rgba(61,220,151,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 3],
    ],
];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Intelligence</p>
        <h1>Analytics</h1>
        <p>How the archive grows, what gets read, and who keeps coming back.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/reports') ?>"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
    </div>
</div>

<div class="analytics-tabs">
    <?php foreach ([3 => '3 months', 6 => '6 months', 12 => '12 months', 24 => '24 months'] as $value => $label): ?>
        <a class="<?= $months === $value ? 'active' : '' ?>" href="<?= url('/admin/analytics?months=' . $value) ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-grid admin-grid-4">
    <article class="admin-stat">
        <div class="top">
            <div>
                <span class="label">Readers</span>
                <p class="value"><?= number_format((int) $overview['users_total']) ?></p>
                <span class="hint"><?= number_format((int) $overview['users_active']) ?> active · <?= (int) $overview['users_online'] ?> online</span>
            </div>
            <span class="ico"><i class="bi bi-people"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-green">
        <div class="top">
            <div>
                <span class="label">Published articles</span>
                <p class="value"><?= number_format((int) $overview['blogs_published']) ?></p>
                <span class="hint"><?= (int) $overview['blogs_draft'] ?> draft<?= (int) $overview['blogs_draft'] === 1 ? '' : 's' ?> waiting</span>
            </div>
            <span class="ico"><i class="bi bi-journal-richtext"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-blue">
        <div class="top">
            <div>
                <span class="label">Lifetime views</span>
                <p class="value"><?= number_short((int) $overview['blog_views']) ?></p>
                <span class="hint"><?= number_format((int) $overview['daily_views']) ?> in the last day</span>
            </div>
            <span class="ico"><i class="bi bi-eye"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-red">
        <div class="top">
            <div>
                <span class="label">Conversation</span>
                <p class="value"><?= number_format((int) $overview['comments_total']) ?></p>
                <span class="hint"><?= number_format((int) $overview['replies_total']) ?> replies · <?= number_format((int) $overview['likes_total']) ?> likes</span>
            </div>
            <span class="ico"><i class="bi bi-chat-square-heart"></i></span>
        </div>
    </article>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Growth</h2>
                <p class="sub">Readers, publishing, comments and likes</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box"><canvas data-chart="analyticsGrowth" aria-label="Growth chart" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Account states</h2>
                <p class="sub">Every registered account</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="analyticsStatus" aria-label="Account status" role="img"></canvas></div>
        </div>
    </section>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Engagement by month</h2>
                <p class="sub">Page views and replies</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="analyticsEngagement" aria-label="Engagement chart" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Category share</h2>
                <p class="sub">Published articles per category</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="analyticsCategories" aria-label="Category share" role="img"></canvas></div>
        </div>
    </section>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Views, last 30 days</h2>
                <p class="sub">Daily readership</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="analyticsDaily" aria-label="Daily views" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Yearly trajectory</h2>
                <p class="sub">Readers and views per year</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="analyticsYearly" aria-label="Yearly trajectory" role="img"></canvas></div>
        </div>
    </section>
</div>

<div class="admin-grid admin-grid-3" style="margin-top:1.2rem">
    <?php
    $leaderboards = [
        ['title' => 'Most viewed', 'rows' => $topViewed, 'metric' => 'view_count', 'label' => 'views'],
        ['title' => 'Most liked', 'rows' => $topLiked, 'metric' => 'like_count', 'label' => 'likes'],
        ['title' => 'Most discussed', 'rows' => $topCommented, 'metric' => 'comment_count', 'label' => 'comments'],
    ];
    ?>
    <?php foreach ($leaderboards as $board): ?>
        <section class="admin-card" style="margin-top:0">
            <div class="admin-card-head">
                <h2><?= e($board['title']) ?></h2>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <tbody>
                    <?php foreach ($board['rows'] as $index => $row): ?>
                        <tr>
                            <td style="width:2rem"><span class="cell-sub"><?= $index + 1 ?></span></td>
                            <td>
                                <span class="cell-title"><a href="<?= url('/blog/' . $row['slug']) ?>" target="_blank" rel="noopener"><?= e(str_limit($row['title'], 52)) ?></a></span>
                                <span class="cell-sub"><?= e($row['category_name']) ?></span>
                            </td>
                            <td class="text-end"><span class="cell-title"><?= number_format((int) $row[$board['metric']]) ?></span><span class="cell-sub"><?= e($board['label']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($board['rows'] === []): ?>
                        <tr><td><div class="admin-empty"><i class="bi bi-bar-chart"></i><p>Not enough data yet.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Most active readers</h2>
                <p class="sub">Comments plus likes</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Reader</th><th>Role</th><th class="text-end">Comments</th><th class="text-end">Likes</th><th class="text-end">Engagement</th></tr></thead>
                <tbody>
                <?php foreach ($topUsers as $reader): ?>
                    <tr>
                        <td>
                            <div class="avatar-cell">
                                <img src="<?= e(\App\Models\User::avatar($reader['avatar'])) ?>" alt="">
                                <div><div class="name">@<?= e($reader['username']) ?></div><span class="sub">Member since <?= e(format_date($reader['created_at'])) ?></span></div>
                            </div>
                        </td>
                        <td><span class="role-pill <?= e($reader['role_slug']) ?>"><?= e($reader['role_slug']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $reader['comment_count']) ?></td>
                        <td class="text-end"><?= number_format((int) $reader['like_count']) ?></td>
                        <td class="text-end"><span class="cell-title"><?= number_format((int) $reader['engagement']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($topUsers === []): ?>
                    <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-person-lines-fill"></i><p>No engagement yet.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Category deep dive</h2>
                <p class="sub">Readership and conversation by shelf</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Category</th><th class="text-end">Articles</th><th class="text-end">Views</th><th class="text-end">Likes</th><th class="text-end">Comments</th></tr></thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><span class="cell-title" style="color:<?= e($category['accent_color']) ?>"><?= e($category['name']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $category['blogs']) ?></td>
                        <td class="text-end"><?= number_format((int) $category['views']) ?></td>
                        <td class="text-end"><?= number_format((int) $category['likes']) ?></td>
                        <td class="text-end"><?= number_format((int) $category['comments']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script type="application/json" id="analyticsGrowth" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($growthChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="analyticsEngagement" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($engagementChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="analyticsDaily" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($dailyChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="analyticsStatus" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($statusChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="analyticsCategories" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($categoryChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="analyticsYearly" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($yearlyChart, JSON_UNESCAPED_SLASHES) ?></script>
