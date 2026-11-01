<?php /** @var list<array> $blogs */ ?>
<div class="container">
    <div class="account-layout">
        <?php require ROOT_PATH . '/views/account/_sidebar.php'; ?>

        <div class="account-main">
            <div class="account-welcome">
                <div>
                    <span class="eyebrow">Your library</span>
                    <h1>Liked articles</h1>
                    <p><?= count($blogs) ?> article<?= count($blogs) === 1 ? '' : 's' ?> you have saved with a like.</p>
                </div>
                <a class="btn btn-outline-soft" href="<?= url('/blogs') ?>"><i class="bi bi-plus-lg"></i> Find more</a>
            </div>

            <?php if ($blogs === []): ?>
                <div class="panel">
                    <div class="empty-state">
                        <i class="bi bi-heart"></i>
                        <h3>Nothing liked yet</h3>
                        <p>When you like an article it is collected here for easy return visits.</p>
                        <a class="btn btn-brand" href="<?= url('/blogs') ?>">Browse the archive</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($blogs as $post): ?>
                        <div class="col-md-6">
                            <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
