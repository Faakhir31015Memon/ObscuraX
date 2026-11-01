<?php
/** @var list<array> $blogs */
/** @var array $filters */
/** @var list<array> $categories */
/** @var array $counts */
$activeStatus = (string) ($filters['status'] ?? '');
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Editorial desk</p>
        <h1>Articles</h1>
        <p><?= !empty($isSuper) ? 'Every investigation in the archive, with full editorial control.' : 'Write, publish and refine your own investigations.' ?></p>
    </div>
    <div class="admin-page-actions">
        <?php if (!empty($isSuper)): ?>
            <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/blogs/all') ?>"><i class="bi bi-files"></i> All articles</a>
        <?php endif; ?>
        <a class="btn btn-brand btn-sm" href="<?= url('/admin/blogs/create') ?>"><i class="bi bi-plus-lg"></i> New article</a>
    </div>
</div>

<div class="admin-grid admin-grid-4" style="margin-bottom:1.2rem">
    <article class="admin-stat">
        <div class="top">
            <div>
                <span class="label">All articles</span>
                <p class="value"><?= number_format((int) $counts['total']) ?></p>
                <span class="hint">Your editorial archive</span>
            </div>
            <span class="ico"><i class="bi bi-journals"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-green">
        <div class="top">
            <div>
                <span class="label">Published</span>
                <p class="value"><?= number_format((int) $counts['published']) ?></p>
                <span class="hint">Live on the site</span>
            </div>
            <span class="ico"><i class="bi bi-globe2"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-gold">
        <div class="top">
            <div>
                <span class="label">Drafts</span>
                <p class="value"><?= number_format((int) $counts['draft']) ?></p>
                <span class="hint">Still in progress</span>
            </div>
            <span class="ico"><i class="bi bi-file-earmark-text"></i></span>
        </div>
    </article>
    <article class="admin-stat accent-red">
        <div class="top">
            <div>
                <span class="label">Unpublished</span>
                <p class="value"><?= number_format((int) $counts['unpublished']) ?></p>
                <span class="hint">Taken down temporarily</span>
            </div>
            <span class="ico"><i class="bi bi-eye-slash"></i></span>
        </div>
    </article>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/blogs') ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search titles…" aria-label="Search articles">
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
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/blogs') ?>">Reset</a>
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
                    <th>Updated</th>
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
                    <td><?= e($blog['author_username']) ?></td>
                    <td><span class="badge-status <?= e($blog['status']) ?>"><?= e($blog['status']) ?></span></td>
                    <td class="text-end"><?= number_format((int) $blog['view_count']) ?></td>
                    <td class="text-end"><?= number_format((int) $blog['like_count']) ?></td>
                    <td class="text-end"><?= number_format((int) $blog['comment_count']) ?></td>
                    <td><span class="cell-sub"><?= e(format_date($blog['updated_at'])) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <?php if ($blog['status'] === 'published'): ?>
                                <a class="icon-action" href="<?= url('/blog/' . $blog['slug']) ?>" target="_blank" rel="noopener" title="View live" aria-label="View live"><i class="bi bi-box-arrow-up-right"></i></a>
                            <?php endif; ?>
                            <a class="icon-action" href="<?= url('/admin/blogs/' . $blog['id'] . '/edit') ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                            <?php if (can('blog.publish')): ?>
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
                            <?php endif; ?>
                            <?php if (can('blog.feature')): ?>
                                <form method="post" action="<?= url('/admin/blogs/' . $blog['id'] . '/feature') ?>">
                                    <?= csrf_field() ?>
                                    <button class="icon-action <?= (int) $blog['is_featured'] === 1 ? 'success' : '' ?>" type="submit" title="<?= (int) $blog['is_featured'] === 1 ? 'Remove from featured' : 'Add to featured' ?>" aria-label="Toggle featured"><i class="bi <?= (int) $blog['is_featured'] === 1 ? 'bi-star-fill' : 'bi-star' ?>"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if (can('blog.delete')): ?>
                                <form method="post" action="<?= url('/admin/blogs/' . $blog['id'] . '/delete') ?>" data-confirm="Delete this article permanently? This cannot be undone.">
                                    <?= csrf_field() ?>
                                    <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($blogs === []): ?>
                <tr>
                    <td colspan="8">
                        <div class="admin-empty">
                            <i class="bi bi-journal-plus"></i>
                            <p>No articles match this view. <a href="<?= url('/admin/blogs/create') ?>">Write the first one</a>.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
