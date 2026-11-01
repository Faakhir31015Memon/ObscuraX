<?php
/** @var array|null $blog */
/** @var list<array> $categories */
/** @var string $tags */
$isEdit = $blog !== null;
$action = $isEdit ? '/admin/blogs/' . $blog['id'] : '/admin/blogs';
$titleValue = old('title', $blog['title'] ?? '');
$slugValue = old('slug', $blog['slug'] ?? '');
$excerptValue = old('excerpt', $blog['excerpt'] ?? '');
$contentValue = old('content', $blog['content'] ?? '');
$statusValue = old('status', $blog['status'] ?? 'draft');
$image = $blog['featured_image'] ?? null;
$featured = (int) old('is_featured', (string) ($blog['is_featured'] ?? 0)) === 1;
$allowComments = (int) old('allow_comments', (string) ($blog['allow_comments'] ?? 1)) === 1;
$categoryValue = (int) old('category_id', (string) ($selectedCategory ?? ($blog['category_id'] ?? 0)));
?>
<div class="admin-page-head">
    <div>
        <p class="kicker"><?= $isEdit ? 'Editing' : 'New investigation' ?></p>
        <h1><?= $isEdit ? 'Refine article' : 'Write an article' ?></h1>
        <p><?= $isEdit ? 'Last updated ' . e(format_datetime($blog['updated_at'])) . '.' : 'Draft something worth reading. You can publish it when it is ready.' ?></p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline-soft btn-sm" href="<?= url('/admin/blogs') ?>"><i class="bi bi-arrow-left"></i> Articles</a>
        <?php if ($isEdit && $blog['status'] === 'published'): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/blog/' . $blog['slug']) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> View live</a>
        <?php endif; ?>
        <button class="btn btn-brand btn-sm" type="submit" form="blogForm"><i class="bi bi-check2"></i> <?= $isEdit ? 'Save changes' : 'Save article' ?></button>
    </div>
</div>

<form id="blogForm" method="post" action="<?= url($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="admin-grid admin-grid-editor">
        <div>
            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Article content</h2>
                        <p class="sub">Main heading, sub-heading and the full body of the investigation</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="title">Main heading <span class="field-badge">H1</span></label>
                        <input class="form-control" id="title" name="title" type="text" value="<?= e($titleValue) ?>" maxlength="191" required placeholder="A headline that earns attention">
                        <p class="form-text">The article title shown on cards, search results and the article page.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="excerpt">Sub-heading <span class="field-badge">Summary</span></label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500" required placeholder="The one paragraph that makes a reader click."><?= e($excerptValue) ?></textarea>
                        <div class="d-flex justify-content-between gap-2">
                            <p class="form-text mb-0">The short description printed beneath the heading and on every article card.</p>
                            <p class="form-text text-end mb-0"><span data-counter-for="#excerpt"></span></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="blog-editor">Body content <span class="field-badge">Rich text</span></label>
                        <div class="editor-shell">
                            <div id="blog-editor" data-upload-url="<?= url('/admin/upload/image') ?>"></div>
                        </div>
                        <input type="hidden" name="content" value="<?= e($contentValue) ?>">
                        <p class="form-text">Format the article with headings, lists, quotes and links — and use the image button to place pictures inside the body.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="slug">Slug</label>
                        <input class="form-control" id="slug" name="slug" type="text" value="<?= e($slugValue) ?>" maxlength="220" pattern="[a-z0-9-]+" placeholder="auto-generated-from-the-title">
                        <p class="form-text">Lowercase letters, numbers and dashes. Leave blank to generate it from the main heading.</p>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Search engine preview</h2>
                        <p class="sub">Optional overrides for search and social feeds</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="seo_title">SEO title</label>
                        <input class="form-control" id="seo_title" name="seo_title" type="text" maxlength="191" value="<?= e(old('seo_title', $blog['seo_title'] ?? '')) ?>" placeholder="Defaults to the article title">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="meta_description">Meta description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="300" placeholder="Defaults to the short description"><?= e(old('meta_description', $blog['meta_description'] ?? '')) ?></textarea>
                        <p class="form-text text-end"><span data-counter-for="#meta_description"></span></p>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-grid">
            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Publishing</h2>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <?php if (can('blog.publish')): ?>
                                <option value="published" <?= $statusValue === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="unpublished" <?= $statusValue === 'unpublished' ? 'selected' : '' ?>>Unpublished</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php if (can('blog.feature')): ?>
                        <div class="check-line">
                            <input class="form-check-input" id="is_featured" name="is_featured" type="checkbox" value="1" <?= $featured ? 'checked' : '' ?>>
                            <label for="is_featured">Feature on the homepage</label>
                        </div>
                    <?php endif; ?>
                    <div class="check-line">
                        <input class="form-check-input" id="allow_comments" name="allow_comments" type="checkbox" value="1" <?= $allowComments ? 'checked' : '' ?>>
                        <label for="allow_comments">Allow reader comments</label>
                    </div>
                    <hr class="my-3" style="border-color:var(--line)">
                    <div class="detail-list">
                        <div class="row-item"><span>Author</span><span><?= e($blog['author_username'] ?? ($authUser['username'] ?? '')) ?></span></div>
                        <div class="row-item"><span>Created</span><span><?= e($isEdit ? format_date($blog['created_at']) : format_date(date('Y-m-d'))) ?></span></div>
                        <?php if ($isEdit && !empty($blog['published_at'])): ?>
                            <div class="row-item"><span>Published</span><span><?= e(format_date($blog['published_at'])) ?></span></div>
                        <?php endif; ?>
                        <?php if ($isEdit): ?>
                            <div class="row-item"><span>Views</span><span><?= number_format((int) $blog['view_count']) ?></span></div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-brand w-100 mt-3" type="submit"><i class="bi bi-check2"></i> <?= $isEdit ? 'Save changes' : 'Save article' ?></button>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Cover image</h2>
                        <p class="sub">Featured image used on cards and the article hero</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <label class="cover-drop" for="featured_image">
                        <?php if ($image): ?>
                            <img src="<?= e(cover_url($image)) ?>" alt="Current featured image">
                        <?php else: ?>
                            <span class="cover-placeholder"><i class="bi bi-image"></i> Click to upload a cover<br><span class="form-text">JPG, PNG, WEBP or GIF · max 5 MB</span></span>
                        <?php endif; ?>
                        <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </label>
                    <?php if ($image): ?>
                        <div class="check-line mt-2">
                            <input class="form-check-input" id="remove_image" name="remove_image" type="checkbox" value="1">
                            <label for="remove_image">Remove the current cover</label>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <h2>Taxonomy</h2>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Choose a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $categoryValue === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tags">Tags</label>
                        <input class="form-control" id="tags" name="tags" type="text" maxlength="300" value="<?= e(old('tags', $tags)) ?>" placeholder="ritual, folklore, cryptography">
                        <p class="form-text">Comma separated, up to twelve tags.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
