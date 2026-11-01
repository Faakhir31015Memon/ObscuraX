<?php
/** @var array $user */
/** @var array $stats */
/** @var list<array> $liked */
/** @var list<array> $comments */
/** @var int $allCommentsCount */
$joined = format_date($user['created_at'] ?? null);
$first = trim(explode(' ', (string) ($user['full_name'] ?: $user['username']))[0]);
?>
<div class="container">
<div class="account-layout">
    <?php require ROOT_PATH . '/views/account/_sidebar.php'; ?>

    <div class="account-main">
        <div class="account-welcome">
            <div>
                <span class="eyebrow">Reader dashboard</span>
                <h1>Welcome back, <?= e($first) ?>.</h1>
                <p>Member since <?= e($joined) ?>. Your likes and comments across the archive live here.</p>
            </div>
            <a class="btn btn-outline-soft" href="<?= url('/blogs') ?>"><i class="bi bi-compass"></i> Explore articles</a>
        </div>

        <div class="stat-grid mb-4">
            <div class="stat-card">
                <span class="icon" style="--icon-bg: rgba(255, 45, 75, 0.14); --icon-fg: #ff7188"><i class="bi bi-heart"></i></span>
                <div class="label">Liked</div>
                <div class="value"><?= (int) $stats['likes'] ?></div>
                <div class="hint"><a href="<?= url('/dashboard/likes') ?>">View liked articles</a></div>
            </div>
            <div class="stat-card">
                <span class="icon"><i class="bi bi-chat-square-text"></i></span>
                <div class="label">Comments</div>
                <div class="value"><?= (int) $stats['comments'] ?></div>
                <div class="hint"><a href="<?= url('/dashboard/comments') ?>">Your discussions</a></div>
            </div>
            <div class="stat-card">
                <span class="icon" style="--icon-bg: rgba(53, 182, 255, 0.14); --icon-fg: #7fd0ff"><i class="bi bi-reply"></i></span>
                <div class="label">Replies received</div>
                <div class="value"><?= (int) $stats['replies'] ?></div>
                <div class="hint">Editor responses to you</div>
            </div>
            <div class="stat-card">
                <span class="icon" style="--icon-bg: rgba(16, 185, 129, 0.14); --icon-fg: #6ee7b7"><i class="bi bi-shield-check"></i></span>
                <div class="label">Account</div>
                <div class="value" style="font-size:1.25rem"><?= e(ucfirst($user['status'] ?? 'active')) ?></div>
                <div class="hint"><a href="<?= url('/account/security') ?>">Security settings</a></div>
            </div>
        </div>

        <section class="panel mb-4">
            <div class="panel-head">
                <h2><i class="bi bi-heart me-2"></i>Recently liked</h2>
                <a class="small-link" href="<?= url('/dashboard/likes') ?>">See all</a>
            </div>
            <div class="panel-body">
                <?php if ($liked === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-heart"></i>
                        <h3>No likes yet</h3>
                        <p>Tap the heart on any article and it will appear here.</p>
                        <a class="btn btn-brand" href="<?= url('/blogs') ?>">Browse articles</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($liked as $post): ?>
                        <div class="list-row">
                            <img class="thumb" src="<?= e(cover_url($post['featured_image'] ?? null)) ?>" alt="" loading="lazy">
                            <div class="grow">
                                <h4><a href="<?= url('/blog/' . $post['slug']) ?>"><?= e($post['title']) ?></a></h4>
                                <div class="sub"><?= e($post['category_name']) ?> · liked <?= e(time_ago($post['liked_at'] ?? null)) ?></div>
                            </div>
                            <span class="post-stats small"><span><i class="bi bi-heart"></i><?= e(number_short((int) ($post['like_count'] ?? 0))) ?></span></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2><i class="bi bi-chat-square-text me-2"></i>Recent comments</h2>
                <a class="small-link" href="<?= url('/dashboard/comments') ?>">All <?= (int) $allCommentsCount ?></a>
            </div>
            <div class="panel-body">
                <?php if ($comments === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-chat-square-text"></i>
                        <h3>You have not commented yet</h3>
                        <p>Join the conversation under any open article.</p>
                        <a class="btn btn-brand" href="<?= url('/blogs') ?>">Find something to read</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-mini">
                            <div class="comment-mini-head">
                                <a href="<?= url('/blog/' . $comment['blog_slug']) ?>"><?= e($comment['blog_title']) ?></a>
                                <span class="sub"><?= e(time_ago($comment['created_at'])) ?></span>
                            </div>
                            <p><?= e(str_limit($comment['content'], 200)) ?></p>
                            <div class="comment-mini-foot">
                                <span class="badge-soft <?= $comment['status'] === 'hidden' ? 'is-warn' : '' ?>"><?= e(ucfirst($comment['status'])) ?></span>
                                <span class="sub"><i class="bi bi-reply me-1"></i><?= (int) $comment['reply_count'] ?> repl<?= (int) $comment['reply_count'] === 1 ? 'y' : 'ies' ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
</div>
