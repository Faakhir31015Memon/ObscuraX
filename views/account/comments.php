<?php /** @var list<array> $comments */ ?>
<div class="container">
    <div class="account-layout">
        <?php require ROOT_PATH . '/views/account/_sidebar.php'; ?>

        <div class="account-main">
            <div class="account-welcome">
                <div>
                    <span class="eyebrow">Your voice</span>
                    <h1>Comment history</h1>
                    <p>Every comment you have posted, newest first. Hidden comments are not visible to other readers.</p>
                </div>
            </div>

            <?php if ($comments === []): ?>
                <div class="panel">
                    <div class="empty-state">
                        <i class="bi bi-chat-square-text"></i>
                        <h3>No comments yet</h3>
                        <p>Open an article and join the discussion — thoughtful readers are why this archive exists.</p>
                        <a class="btn btn-brand" href="<?= url('/blogs') ?>">Find an article</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="panel">
                    <div class="panel-body">
                        <?php foreach ($comments as $comment): ?>
                            <article class="comment-history">
                                <div class="comment-history-head">
                                    <a href="<?= url('/blog/' . $comment['blog_slug']) ?>"><?= e($comment['blog_title']) ?></a>
                                    <span class="sub"><?= e(format_datetime($comment['created_at'])) ?></span>
                                </div>
                                <p><?= nl2br(e($comment['content'])) ?></p>
                                <div class="comment-history-foot">
                                    <span class="badge-soft <?= $comment['status'] === 'hidden' ? 'is-warn' : '' ?>">
                                        <i class="bi <?= $comment['status'] === 'hidden' ? 'bi-eye-slash' : 'bi-check-circle' ?> me-1"></i><?= e(ucfirst($comment['status'])) ?>
                                    </span>
                                    <span class="sub"><i class="bi bi-reply me-1"></i><?= (int) $comment['reply_count'] ?> repl<?= (int) $comment['reply_count'] === 1 ? 'y' : 'ies' ?></span>
                                    <a class="small-link ms-auto" href="<?= url('/blog/' . $comment['blog_slug'] . '#comment-' . $comment['id']) ?>">View thread <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
