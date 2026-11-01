<?php
/** @var array $author */
/** @var array $blogs */
/** @var int $commentCount */
$name = $author['full_name'] ?: $author['username'];
?>
<section class="page-head pb-0">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/blogs') ?>">Articles</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($name) ?></li>
            </ol>
        </nav>

        <div class="author-hero">
            <img src="<?= e(\App\Models\User::avatar($author['avatar'] ?? null)) ?>" alt="<?= e($name) ?>">
            <div>
                <p class="kicker mb-2">Contributor</p>
                <h1><?= e($name) ?></h1>
                <p><?= e($author['bio'] ?? 'Writes for ObscuraX.') ?></p>
                <div class="author-meta">
                    <span><i class="bi bi-at"></i><?= e($author['username']) ?></span>
                    <span><i class="bi bi-calendar3"></i>Joined <?= e(format_date($author['created_at'])) ?></span>
                    <span><i class="bi bi-journal-text"></i><?= count($blogs) ?> published article<?= count($blogs) === 1 ? '' : 's' ?></span>
                    <span><i class="bi bi-chat-square-text"></i><?= (int) $commentCount ?> comments</span>
                    <?php if (!empty($author['website'])): ?>
                        <a href="<?= e($author['website']) ?>" target="_blank" rel="noopener noreferrer nofollow" style="color:var(--purple-2)"><i class="bi bi-globe2"></i>Website</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="section-head" style="margin-bottom:1.6rem">
            <div>
                <p class="kicker">Published work</p>
                <h2>Investigations by <?= e($name) ?></h2>
            </div>
        </div>

        <?php if ($blogs === []): ?>
            <div class="panel">
                <div class="empty-state">
                    <i class="bi bi-journal-x"></i>
                    <h3>Nothing published yet</h3>
                    <p>This contributor has not published an investigation at this time.</p>
                    <a class="btn btn-outline-soft" href="<?= url('/blogs') ?>">Browse the archive</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($blogs as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
