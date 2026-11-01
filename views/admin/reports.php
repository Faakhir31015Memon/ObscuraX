<?php
/** @var int $year */
/** @var list<array> $monthlyRows */
/** @var list<array> $monthly */
/** @var list<array> $years */
/** @var list<array> $yearsAvailable */
$totals = ['users' => 0, 'blogs' => 0, 'likes' => 0, 'comments' => 0, 'views' => 0];
foreach ($monthlyRows as $row) {
    foreach ($totals as $key => $value) {
        $totals[$key] = $value + (int) $row[$key];
    }
}

$monthlyChart = [
    'type' => 'bar',
    'labels' => array_map(static fn (array $r): string => substr($r['label'], 0, 3), $monthlyRows),
    'datasets' => [
        ['label' => 'New readers', 'data' => array_map(static fn (array $r): int => (int) $r['users'], $monthlyRows), 'backgroundColor' => 'rgba(167,139,250,0.55)', 'borderColor' => 'rgba(167,139,250,0.9)', 'borderWidth' => 1, 'borderRadius' => 5],
        ['label' => 'Articles', 'data' => array_map(static fn (array $r): int => (int) $r['blogs'], $monthlyRows), 'backgroundColor' => 'rgba(255,45,75,0.5)', 'borderColor' => 'rgba(255,45,75,0.85)', 'borderWidth' => 1, 'borderRadius' => 5],
        ['label' => 'Comments', 'data' => array_map(static fn (array $r): int => (int) $r['comments'], $monthlyRows), 'backgroundColor' => 'rgba(61,220,151,0.5)', 'borderColor' => 'rgba(61,220,151,0.85)', 'borderWidth' => 1, 'borderRadius' => 5],
    ],
];

$viewsChart = [
    'type' => 'line',
    'labels' => array_map(static fn (array $r): string => substr($r['label'], 0, 3), $monthlyRows),
    'legend' => false,
    'datasets' => [
        ['label' => 'Views', 'data' => array_map(static fn (array $r): int => (int) $r['views'], $monthlyRows), 'borderColor' => '#35b6ff', 'backgroundColor' => 'rgba(53,182,255,0.1)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 2],
    ],
];

$yearlyChart = [
    'type' => 'line',
    'labels' => array_map(static fn (array $r): string => $r['label'], $years),
    'datasets' => [
        ['label' => 'Readers', 'data' => array_map(static fn (array $r): int => (int) $r['users'], $years), 'borderColor' => '#a78bfa', 'backgroundColor' => 'rgba(167,139,250,0.08)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 3],
        ['label' => 'Likes', 'data' => array_map(static fn (array $r): int => (int) $r['likes'], $years), 'borderColor' => '#ff7ac8', 'backgroundColor' => 'rgba(255,122,200,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 3],
        ['label' => 'Views', 'data' => array_map(static fn (array $r): int => (int) $r['views'], $years), 'borderColor' => '#3ddc97', 'backgroundColor' => 'rgba(61,220,151,0.06)', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 2, 'pointRadius' => 3],
    ],
];
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Intelligence</p>
        <h1>Annual report</h1>
        <p>Month-by-month performance for <?= (int) $year ?>, plus the long view across years.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/analytics') ?>"><i class="bi bi-graph-up-arrow"></i> Analytics</a>
        <a class="btn btn-brand btn-sm" href="<?= url('/admin/reports/export?year=' . (int) $year) ?>"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</div>

<div class="analytics-tabs" style="align-items:center">
    <form class="d-flex gap-2 align-items-center" method="get" action="<?= url('/admin/reports') ?>" style="margin:0">
        <label class="form-label mb-0" for="year" style="font-size:.78rem">Report year</label>
        <select class="form-select form-select-sm" id="year" name="year" style="width:auto">
            <?php
            $availableYears = array_map(static fn (array $row): int => (int) $row['y'], $yearsAvailable);
            if (!in_array($year, $availableYears, true)) {
                $availableYears[] = $year;
            }
            if ($availableYears === []) {
                $availableYears[] = (int) date('Y');
            }
            rsort($availableYears);
            ?>
            <?php foreach ($availableYears as $option): ?>
                <option value="<?= $option ?>" <?= $option === $year ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit">Apply</button>
    </form>
</div>

<div class="admin-grid admin-grid-4">
    <article class="admin-stat">
        <div class="top">
            <div>
                <span class="label">New readers · <?= (int) $year ?></span>
                <p class="value"><?= number_format($totals['users']) ?></p>
                <span class="hint">Annual registrations</span>
            </div>
            <span class="ico"><i class="bi bi-person-plus"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-green">
        <div class="top">
            <div>
                <span class="label">Articles published</span>
                <p class="value"><?= number_format($totals['blogs']) ?></p>
                <span class="hint">During <?= (int) $year ?></span>
            </div>
            <span class="ico"><i class="bi bi-journal-check"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-red">
        <div class="top">
            <div>
                <span class="label">Comments &amp; likes</span>
                <p class="value"><?= number_format($totals['comments'] + $totals['likes']) ?></p>
                <span class="hint"><?= number_format($totals['comments']) ?> comments · <?= number_format($totals['likes']) ?> likes</span>
            </div>
            <span class="ico"><i class="bi bi-chat-square-heart"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-blue">
        <div class="top">
            <div>
                <span class="label">Page views</span>
                <p class="value"><?= number_short($totals['views']) ?></p>
                <span class="hint">Recorded in <?= (int) $year ?></span>
            </div>
            <span class="ico"><i class="bi bi-eye"></i></span>
        </div>
    </article>
</div>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Monthly activity · <?= (int) $year ?></h2>
                <p class="sub">Readers, articles and comments</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="reportMonthly" aria-label="Monthly activity" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Monthly views</h2>
                <p class="sub">Readership through the year</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="reportViews" aria-label="Monthly views" role="img"></canvas></div>
        </div>
    </section>
</div>

<section class="admin-card" style="margin-top:1.2rem">
    <div class="admin-card-head">
        <div>
            <h2>Monthly breakdown</h2>
            <p class="sub">A calendar view of <?= (int) $year ?></p>
        </div>
        <a class="small-link" href="<?= url('/admin/reports/export?year=' . (int) $year) ?>">Download CSV</a>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-end">New readers</th>
                    <th class="text-end">Articles</th>
                    <th class="text-end">Likes</th>
                    <th class="text-end">Comments</th>
                    <th class="text-end">Views</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($monthlyRows as $row): ?>
                <tr>
                    <td><span class="cell-title"><?= e($row['label']) ?> <?= (int) $year ?></span></td>
                    <td class="text-end"><?= number_format((int) $row['users']) ?></td>
                    <td class="text-end"><?= number_format((int) $row['blogs']) ?></td>
                    <td class="text-end"><?= number_format((int) $row['likes']) ?></td>
                    <td class="text-end"><?= number_format((int) $row['comments']) ?></td>
                    <td class="text-end"><?= number_format((int) $row['views']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="text-end"><?= number_format($totals['users']) ?></th>
                    <th class="text-end"><?= number_format($totals['blogs']) ?></th>
                    <th class="text-end"><?= number_format($totals['likes']) ?></th>
                    <th class="text-end"><?= number_format($totals['comments']) ?></th>
                    <th class="text-end"><?= number_format($totals['views']) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<div class="admin-grid admin-grid-main" style="margin-top:1.2rem">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Multi-year trend</h2>
                <p class="sub">Six-year view of readers, likes and views</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-box-sm"><canvas data-chart="reportYearly" aria-label="Yearly trend" role="img"></canvas></div>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Year summaries</h2>
                <p class="sub">Platform totals by year</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Year</th><th class="text-end">Readers</th><th class="text-end">Articles</th><th class="text-end">Likes</th><th class="text-end">Views</th></tr></thead>
                <tbody>
                <?php foreach (array_reverse($years) as $row): ?>
                    <tr>
                        <td><span class="cell-title"><?= e($row['label']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $row['users']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['blogs']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['likes']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['views']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script type="application/json" id="reportMonthly" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($monthlyChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="reportViews" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($viewsChart, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="reportYearly" nonce="<?= e(CSP_NONCE) ?>"><?= json_encode($yearlyChart, JSON_UNESCAPED_SLASHES) ?></script>
