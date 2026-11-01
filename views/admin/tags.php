<?php
/** @var list<array> $tags */
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Taxonomy</p>
        <h1>Tags</h1>
        <p>Fine-grained labels used across articles. Renaming a tag keeps it attached to its articles.</p>
    </div>
</div>

<div class="admin-grid admin-grid-main">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2><?= count($tags) ?> tag<?= count($tags) === 1 ? '' : 's' ?></h2>
                <p class="sub">Tags are created automatically when authors add them to an article.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-end">Articles</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td>
                            <form class="d-flex gap-2" method="post" action="<?= url('/admin/tags/' . $tag['id']) ?>">
                                <?= csrf_field() ?>
                                <input class="form-control form-control-sm" name="name" type="text" value="<?= e($tag['name']) ?>" maxlength="64" required aria-label="Tag name">
                                <button class="icon-action success" type="submit" title="Save name" aria-label="Save name"><i class="bi bi-check2"></i></button>
                            </form>
                        </td>
                        <td><span class="cell-sub">/tag/<?= e($tag['slug']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $tag['blog_count']) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="icon-action" href="<?= url('/tag/' . $tag['slug']) ?>" target="_blank" rel="noopener" title="View tag" aria-label="View tag"><i class="bi bi-box-arrow-up-right"></i></a>
                                <form method="post" action="<?= url('/admin/tags/' . $tag['id'] . '/delete') ?>" data-confirm="Delete the tag “<?= e($tag['name']) ?>”? Articles keep their content; only the label is removed.">
                                    <?= csrf_field() ?>
                                    <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($tags === []): ?>
                    <tr><td colspan="4"><div class="admin-empty"><i class="bi bi-tags"></i><p>No tags yet. Add one on the right, or tag an article.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>Add a tag</h2>
        </div>
        <div class="admin-card-body">
            <form method="post" action="<?= url('/admin/tags') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="tag-name">Name</label>
                    <input class="form-control" id="tag-name" name="name" type="text" value="<?= e(old('name')) ?>" maxlength="64" required placeholder="e.g. cryptography">
                </div>
                <button class="btn btn-brand w-100" type="submit"><i class="bi bi-plus-lg"></i> Add tag</button>
            </form>
            <div class="admin-note mt-3">
                <i class="bi bi-info-circle"></i>
                <div>Tag pages are public at <strong>/tag/slug</strong> and are linked from every tagged article.</div>
            </div>
        </div>
    </section>
</div>
