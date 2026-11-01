<?php
/** @var list<array> $categories */
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Taxonomy</p>
        <h1>Categories</h1>
        <p>The three pillars of the archive — and any new shelves you create.</p>
    </div>
</div>

<div class="admin-grid admin-grid-main">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>All categories</h2>
                <p class="sub">A category can only be deleted once it has no articles.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th class="text-end">Articles</th>
                        <th class="text-end">Views</th>
                        <th>Visible</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <div class="avatar-cell">
                                <span class="ico" style="display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border-radius:9px;background:<?= e($category['accent_color']) ?>22;color:<?= e($category['accent_color']) ?>"><i class="bi <?= e($category['icon'] ?: 'bi-journal-richtext') ?>"></i></span>
                                <div>
                                    <div class="name"><?= e($category['name']) ?></div>
                                    <span class="sub"><?= e(str_limit($category['description'] ?? '', 48)) ?: 'No description' ?></span>
                                </div>
                            </div>
                        </td>
                        <td><span class="cell-sub">/<?= e($category['slug']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $category['blog_count']) ?></td>
                        <td class="text-end"><?= number_format((int) $category['views']) ?></td>
                        <td><span class="badge-status <?= (int) $category['is_active'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $category['is_active'] === 1 ? 'active' : 'hidden' ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a class="icon-action" href="<?= url('/category/' . $category['slug']) ?>" target="_blank" rel="noopener" title="View archive" aria-label="View archive"><i class="bi bi-box-arrow-up-right"></i></a>
                                <button class="icon-action" type="button" data-toggle-target="#cat-edit-<?= (int) $category['id'] ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></button>
                                <form method="post" action="<?= url('/admin/categories/' . $category['id'] . '/delete') ?>" data-confirm="Delete “<?= e($category['name']) ?>”? Only empty categories can be removed.">
                                    <?= csrf_field() ?>
                                    <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="d-none" id="cat-edit-<?= (int) $category['id'] ?>">
                        <td colspan="6">
                            <form method="post" action="<?= url('/admin/categories/' . $category['id']) ?>" enctype="multipart/form-data" class="form-section">
                                <?= csrf_field() ?>
                                <h3>Edit <?= e($category['name']) ?></h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="name-<?= (int) $category['id'] ?>">Name</label>
                                        <input class="form-control" id="name-<?= (int) $category['id'] ?>" name="name" type="text" value="<?= e($category['name']) ?>" maxlength="96" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="slug-<?= (int) $category['id'] ?>">Slug</label>
                                        <input class="form-control" id="slug-<?= (int) $category['id'] ?>" name="slug" type="text" value="<?= e($category['slug']) ?>" maxlength="120" pattern="[a-z0-9-]+">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="description-<?= (int) $category['id'] ?>">Description</label>
                                    <textarea class="form-control" id="description-<?= (int) $category['id'] ?>" name="description" rows="2" maxlength="500"><?= e($category['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-row-3">
                                    <div class="form-group">
                                        <label class="form-label" for="accent-<?= (int) $category['id'] ?>">Accent colour</label>
                                        <input class="form-control form-control-color" id="accent-<?= (int) $category['id'] ?>" name="accent_color" type="color" value="<?= e($category['accent_color']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="icon-<?= (int) $category['id'] ?>">Bootstrap icon class</label>
                                        <input class="form-control" id="icon-<?= (int) $category['id'] ?>" name="icon" type="text" value="<?= e($category['icon'] ?? '') ?>" maxlength="48" placeholder="bi-moon-stars">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="sort-<?= (int) $category['id'] ?>">Sort order</label>
                                        <input class="form-control" id="sort-<?= (int) $category['id'] ?>" name="sort_order" type="number" value="<?= (int) ($category['sort_order'] ?? 0) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="cover-<?= (int) $category['id'] ?>">Cover image</label>
                                    <input class="form-control" id="cover-<?= (int) $category['id'] ?>" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <?php if (!empty($category['cover_image'])): ?>
                                        <div class="check-line mt-2">
                                            <input class="form-check-input" id="remove-cover-<?= (int) $category['id'] ?>" name="remove_cover" type="checkbox" value="1">
                                            <label for="remove-cover-<?= (int) $category['id'] ?>">Remove the current cover (<?= e(basename($category['cover_image'])) ?>)</label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="check-line">
                                    <input class="form-check-input" id="active-<?= (int) $category['id'] ?>" name="is_active" type="checkbox" value="1" <?= (int) $category['is_active'] === 1 ? 'checked' : '' ?>>
                                    <label for="active-<?= (int) $category['id'] ?>">Visible in navigation</label>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-brand btn-sm" type="submit">Save category</button>
                                    <button class="btn btn-ghost btn-sm" type="button" data-toggle-target="#cat-edit-<?= (int) $category['id'] ?>">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($categories === []): ?>
                    <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-collection"></i><p>No categories yet. Create the first one.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>New category</h2>
        </div>
        <div class="admin-card-body">
            <form method="post" action="<?= url('/admin/categories') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="new-name">Name</label>
                    <input class="form-control" id="new-name" name="name" type="text" value="<?= e(old('name')) ?>" maxlength="96" required placeholder="e.g. Folklore &amp; Myth">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-slug">Slug</label>
                    <input class="form-control" id="new-slug" name="slug" type="text" value="<?= e(old('slug')) ?>" maxlength="120" pattern="[a-z0-9-]+" placeholder="auto-generated">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-description">Description</label>
                    <textarea class="form-control" id="new-description" name="description" rows="3" maxlength="500" placeholder="What belongs on this shelf?"><?= e(old('description')) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="new-accent">Accent colour</label>
                        <input class="form-control form-control-color" id="new-accent" name="accent_color" type="color" value="<?= e(old('accent_color', '#8b5cf6')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new-icon">Icon</label>
                        <input class="form-control" id="new-icon" name="icon" type="text" value="<?= e(old('icon', 'bi-journal-richtext')) ?>" maxlength="48">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="new-sort">Sort order</label>
                        <input class="form-control" id="new-sort" name="sort_order" type="number" value="<?= e(old('sort_order', '0')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new-cover">Cover image</label>
                        <input class="form-control" id="new-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>
                </div>
                <div class="check-line">
                    <input class="form-check-input" id="new-active" name="is_active" type="checkbox" value="1" checked>
                    <label for="new-active">Visible in navigation</label>
                </div>
                <button class="btn btn-brand w-100 mt-3" type="submit"><i class="bi bi-plus-lg"></i> Create category</button>
            </form>
        </div>
    </section>
</div>
