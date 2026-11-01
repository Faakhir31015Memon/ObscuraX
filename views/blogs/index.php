<?php
/** @var array $blogs */
/** @var array $categories */
/** @var array $tags */
/** @var array $filters */
/** @var string $heading */
/** @var string $subheading */
/** @var array|null $category */
/** @var string|null $coverImage */
/** @var bool|null $searchMode */
$category = $category ?? null;
$coverImage = $coverImage ?? null;
$searchMode = $searchMode ?? false;
$activeCategory = $filters['category'] ?? '';
$activeTag = $filters['tag'] ?? '';
$q = $filters['q'] ?? '';
$sort = $filters['sort'] ?? 'latest';
$sorts = ['latest' => 'Newest first', 'popular' => 'Most read', 'liked' => 'Most liked', 'commented' => 'Most discussed', 'oldest' => 'Oldest first'];
?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($searchMode ? 'Search' : ($category['name'] ?? ($activeTag !== '' ? '#' . $activeTag : 'Articles'))) ?></li>
            </ol>
        </nav>
        <p class="kicker mt-3"><?= $searchMode ? 'Archive search' : 'The archive' ?></p>
        <h1><?= e($heading) ?></h1>
        <p><?= e($subheading) ?></p>

        <?php if ($coverImage !== null): ?>
            <div class="page-head-cover">
                <img src="<?= e($coverImage) ?>" alt="<?= e($category['name'] ?? '') ?>">
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 mb-4">
            <form class="filter-bar flex-grow-1" method="get" action="<?= e(url(current_path())) ?>" role="search">
                <?php if ($activeTag !== ''): ?><input type="hidden" name="tag" value="<?= e($activeTag) ?>"><?php endif; ?>
                <input class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Search titles, excerpts and full text…" aria-label="Search articles" style="min-width:200px;flex:1">
                <span class="divider d-none d-md-block"></span>
                <select class="form-select" name="category" aria-label="Category" style="width:auto">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $activeCategory === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select" name="sort" aria-label="Sort" style="width:auto">
                    <?php foreach ($sorts as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-brand btn-sm" type="submit">Filter</button>
            </form>
            <span class="text-muted" style="font-size:.8rem;white-space:nowrap"><?= (int) $blogs['total'] ?> result<?= (int) $blogs['total'] === 1 ? '' : 's' ?></span>
        </div>

        <div class="filter-pills mb-4">
            <a class="filter-pill <?= $activeCategory === '' && $activeTag === '' ? 'active' : '' ?>" href="<?= url($searchMode ? '/blogs' : current_path()) ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a class="filter-pill <?= $activeCategory === $cat['slug'] ? 'active' : '' ?>" href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
            <?php if ($activeTag !== ''): ?>
                <a class="filter-pill active" href="<?= url('/tag/' . $activeTag) ?>">#<?= e($activeTag) ?> <i class="bi bi-x ms-1"></i></a>
            <?php endif; ?>
        </div>

        <?php if ($blogs['items'] === []): ?>
            <div class="panel">
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h3>Nothing filed under that yet</h3>
                    <p>Try a broader search term, a different category, or browse the full archive.</p>
                    <a class="btn btn-brand" href="<?= url('/blogs') ?>">Browse everything</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($blogs['items'] as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php $paginator = $blogs; require ROOT_PATH . '/views/partials/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($tags !== []): ?>
<section class="section-tight section-line">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="kicker mb-2">Browse by thread</p>
                <div class="tag-cloud">
                    <?php foreach ($tags as $tag): ?>
                        <a class="tag-chip" href="<?= url('/tag/' . $tag['slug']) ?>">#<?= e($tag['name']) ?> <b><?= (int) $tag['blog_count'] ?></b></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a class="link-arrow" href="<?= url('/search') ?>">Search the archive <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>
