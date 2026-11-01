<?php
/** @var array $paginator */
if (($paginator['pages'] ?? 1) <= 1) {
    return;
}
$page = (int) $paginator['page'];
$pages = (int) $paginator['pages'];
$query = $_GET;
unset($query['page']);
$build = static function (int $target) use ($query): string {
    $query['page'] = $target;
    return url(current_path()) . '?' . http_build_query($query);
};
$start = max(1, $page - 2);
$end = min($pages, $page + 2);
if ($end - $start < 4) {
    $start = max(1, min($start, $end - 4));
}
?>
<nav class="pagination-wrap" aria-label="Pagination">
    <ul class="pagination">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <?php if ($page > 1): ?>
                <a class="page-link" href="<?= e($build($page - 1)) ?>" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
            <?php endif; ?>
        </li>

        <?php if ($start > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= e($build(1)) ?>">1</a></li>
            <?php if ($start > 2): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <?php if ($i === $page): ?>
                    <span class="page-link" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a class="page-link" href="<?= e($build($i)) ?>"><?= $i ?></a>
                <?php endif; ?>
            </li>
        <?php endfor; ?>

        <?php if ($end < $pages): ?>
            <?php if ($end < $pages - 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= e($build($pages)) ?>"><?= $pages ?></a></li>
        <?php endif; ?>

        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <?php if ($page < $pages): ?>
                <a class="page-link" href="<?= e($build($page + 1)) ?>" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
            <?php else: ?>
                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
            <?php endif; ?>
        </li>
    </ul>
</nav>
