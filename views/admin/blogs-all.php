<?php
/** @var list<array> $blogs */
/** @var array $filters */
/** @var list<array> $categories */
/** @var array $counts */
$activeStatus = (string) ($filters['status'] ?? '');
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Super administration</p>
        <h1>All articles</h1>
        <p>Complete editorial oversight of every article by every author on the platform.</p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/blogs') ?>"><i class="bi bi-pencil-square"></i> My articles</a>
        <a class="btn btn-brand btn-sm" href="<?= url('/admin/blogs/create') ?>"><i class="bi bi-plus-lg"></i> New article</a>
    </div>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/blogs/all') ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search every title…" aria-label="Search articles">
        </div>
        <select class="form-select" name="status" aria-label="Filter by status" style="max-width:160px">
            <option value="">All statuses</option>
            <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublished'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $activeStatus === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" name="category" aria-label="Filter by category" style="max-width:190px">
            <option value="0">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($filters['q'] !== '' || $activeStatus !== '' || (int) $filters['category'] > 0): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/blogs/all') ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= count($blogs) ?> article<?= count($blogs) === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th class="text-end">Views</th>
                    <th class="text-end">Likes</th>
                    <th class="text-end">Comments</th>
                    <th>Published</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($blogs as $blog): ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <img class="thumb" src="<?= e(cover_url($blog['featured_image'])) ?>" alt="" style="width:46px;height:42px;border-radius:8px">
                            <div>
                                <div class="name"><a href="<?= url('/admin/blogs/' . $blog['id'] . '/edit') ?>"><?= e($blog['title']) ?></a></div>
                                <span class="sub"><?= e($blog['category_name']) ?><?= (int) $blog['is_featured'] === 1 ? ' · Featured' : '' ?></span>
                            </div>
                        </div>
                    </td>
                    <td>@<?= e($blog['author_username']) ?></td>
                    <td><span class="badge-status <?= e($blog['status']) ?>"><?= e($blog['status']) ?></span></td>
                    <td class="text-end"><?= number_format((int) $blog['view_count']) ?></td>
                    <td class="text-end"><?= number_format((int) $blog['like_count']) ?></td>
                    <td class="text-end"><?= number_format((int) $blog['comment_count']) ?></td>
                    <td><span class="cell-sub"><?= e(format_date($blog['published_at'])) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <?php if ($blog['status'] === 'published'): ?>
                                <a class="icon-action" href="<?= url('/blog/' . $blog['slug']) ?>" target="_blank" rel="noopener" title="View live" aria-label="View live"><i class="bi bi-box-arrow-up-right"></i></a>
                            <?php endif; ?>
                            <a class="icon-action" href="<?= url('/admin/blogs/' . $blog['id'] . '/edit') ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= url('/admin/blogs/' . $blog['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <?php if ($blog['status'] === 'published'): ?>
                                    <input type="hidden" name="status" value="unpublished">
                                    <button class="icon-action" type="submit" title="Unpublish" aria-label="Unpublish"><i class="bi bi-eye-slash"></i></button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="published">
                                    <button class="icon-action success" type="submit" title="Publish" aria-label="Publish"><i class="bi bi-send-check"></i></button>
                                <?php endif; ?>
                            </form>
                            <form method="post" action="<?= url('/admin/blogs/' . $blog['id'] . '/feature') ?>">
                                <?= csrf_field() ?>
                                <button class="icon-action <?= (int) $blog['is_featured'] === 1 ? 'success' : '' ?>" type="submit" title="<?= (int) $blog['is_featured'] === 1 ? 'Remove from featured' : 'Add to featured' ?>" aria-label="Toggle featured"><i class="bi <?= (int) $blog['is_featured'] === 1 ? 'bi-star-fill' : 'bi-star' ?>"></i></button>
                            </form>
                            <form method="post" action="<?= url('/admin/blogs/' . $blog['id'] . '/delete') ?>" data-confirm="Delete “<?= e($blog['title']) ?>” permanently? This removes its likes, comments and views.">
                                <?= csrf_field() ?>
                                <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($blogs === []): ?>
                <tr><td colspan="8"><div class="admin-empty"><i class="bi bi-files"></i><p>No articles match this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
