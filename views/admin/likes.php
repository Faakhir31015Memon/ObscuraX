<?php
/** @var array $likes */
/** @var array $filters */
/** @var list<array> $blogs */
$paginator = $likes;
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Community</p>
        <h1>Likes</h1>
        <p>Who liked what. Removing a like corrects the count on the article immediately.</p>
    </div>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/likes') ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search reader or article…" aria-label="Search likes">
        </div>
        <select class="form-select" name="blog_id" aria-label="Filter by article" style="max-width:280px">
            <option value="0">All articles</option>
            <?php foreach ($blogs as $blog): ?>
                <option value="<?= (int) $blog['id'] ?>" <?= (int) $filters['blog_id'] === (int) $blog['id'] ? 'selected' : '' ?>><?= e(str_limit($blog['title'], 60)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($filters['q'] !== '' || (int) $filters['blog_id'] > 0): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/likes') ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= number_format((int) $likes['total']) ?> like<?= (int) $likes['total'] === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reader</th>
                    <th>Article</th>
                    <th>Category</th>
                    <th>Liked</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($likes['items'] as $like): ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <img src="<?= e(\App\Models\User::avatar($like['avatar'])) ?>" alt="">
                            <div>
                                <div class="name">@<?= e($like['username']) ?></div>
                                <span class="sub">Like #<?= (int) $like['id'] ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="cell-title"><a href="<?= url('/blog/' . $like['blog_slug']) ?>" target="_blank" rel="noopener"><?= e(str_limit($like['blog_title'], 60)) ?></a></span></td>
                    <td><span class="cell-sub"><?= e($like['category_name']) ?></span></td>
                    <td><span class="cell-sub"><?= e(format_datetime($like['created_at'])) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <form method="post" action="<?= url('/admin/likes/' . $like['id'] . '/delete') ?>" data-confirm="Remove this like from the article?">
                                <?= csrf_field() ?>
                                <button class="icon-action danger" type="submit" title="Remove like" aria-label="Remove like"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($likes['items'] === []): ?>
                <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-heart"></i><p>No likes match this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
