<?php
/** @var array $comments */
/** @var array $replies */
/** @var array $filters */
/** @var bool $canDelete */
$paginator = $comments;
?>
<div class="admin-page-head">
    <div>
        <p class="kicker">Community</p>
        <h1>Comments</h1>
        <p>Moderate discussion, reply in your own voice, and keep threads civil.</p>
    </div>
</div>

<section class="admin-card">
    <form class="admin-toolbar" method="get" action="<?= url('/admin/comments') ?>">
        <div class="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search content, author or article…" aria-label="Search comments">
        </div>
        <select class="form-select" name="status" aria-label="Filter by status" style="max-width:170px">
            <option value="">All comments</option>
            <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="hidden" <?= ($filters['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
        <button class="btn btn-outline-soft btn-sm" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($filters['q'] !== '' || ($filters['status'] ?? '') !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/comments') ?>">Reset</a>
        <?php endif; ?>
        <span class="spacer"></span>
        <span class="count-note"><?= number_format((int) $comments['total']) ?> comment<?= (int) $comments['total'] === 1 ? '' : 's' ?></span>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>Article</th>
                    <th>Visibility</th>
                    <th>Posted</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($comments['items'] as $comment): ?>
                <?php $commentReplies = $replies[(int) $comment['id']] ?? []; ?>
                <tr>
                    <td>
                        <div class="avatar-cell">
                            <img src="<?= e(\App\Models\User::avatar($comment['avatar'])) ?>" alt="">
                            <div>
                                <div class="name">@<?= e($comment['username']) ?></div>
                                <span class="sub"><?= e($comment['email']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="max-width:340px">
                        <span class="cell-title"><?= nl2br(e(str_limit($comment['content'], 180))) ?></span>
                        <span class="cell-sub"><?= (int) $comment['reply_count'] ?> repl<?= (int) $comment['reply_count'] === 1 ? 'y' : 'ies' ?></span>
                    </td>
                    <td>
                        <span class="cell-title"><a href="<?= url('/blog/' . $comment['blog_slug']) ?>#comment-<?= (int) $comment['id'] ?>" target="_blank" rel="noopener"><?= e(str_limit($comment['blog_title'], 44)) ?></a></span>
                    </td>
                    <td><span class="badge-status <?= e($comment['status']) ?>"><?= e($comment['status']) ?></span></td>
                    <td><span class="cell-sub"><?= e(time_ago($comment['created_at'])) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-action" type="button" data-admin-reply="reply-<?= (int) $comment['id'] ?>" title="Reply" aria-label="Reply"><i class="bi bi-reply"></i></button>
                            <form method="post" action="<?= url('/admin/comments/' . $comment['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <button class="icon-action <?= $comment['status'] === 'hidden' ? 'success' : '' ?>" type="submit" title="<?= $comment['status'] === 'hidden' ? 'Publish' : 'Hide' ?>" aria-label="<?= $comment['status'] === 'hidden' ? 'Publish' : 'Hide' ?>"><i class="bi <?= $comment['status'] === 'hidden' ? 'bi-eye' : 'bi-eye-slash' ?>"></i></button>
                            </form>
                            <?php if ($canDelete): ?>
                                <form method="post" action="<?= url('/admin/comments/' . $comment['id'] . '/delete') ?>" data-confirm="Delete this comment and every reply beneath it?">
                                    <?= csrf_field() ?>
                                    <button class="icon-action danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <?php if ($commentReplies !== []): ?>
                    <?php foreach ($commentReplies as $reply): ?>
                        <tr>
                            <td>
                                <div class="avatar-cell" style="padding-left:1.1rem">
                                    <img src="<?= e(\App\Models\User::avatar($reply['avatar'])) ?>" alt="" style="width:26px;height:26px">
                                    <div>
                                        <div class="name">↳ @<?= e($reply['username']) ?></div>
                                        <span class="sub">Reply</span>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width:340px"><span class="cell-sub"><?= nl2br(e(str_limit($reply['content'], 160))) ?></span></td>
                            <td><span class="badge-status <?= e($reply['status']) ?>"><?= e($reply['status']) ?></span></td>
                            <td><span class="cell-sub"><?= e(time_ago($reply['created_at'])) ?></span></td>
                            <td colspan="2">
                                <div class="row-actions">
                                    <form method="post" action="<?= url('/admin/comments/' . $comment['id'] . '/status') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="reply_id" value="<?= (int) $reply['id'] ?>">
                                        <button class="icon-action <?= $reply['status'] === 'hidden' ? 'success' : '' ?>" type="submit" title="<?= $reply['status'] === 'hidden' ? 'Publish reply' : 'Hide reply' ?>" aria-label="<?= $reply['status'] === 'hidden' ? 'Publish reply' : 'Hide reply' ?>"><i class="bi <?= $reply['status'] === 'hidden' ? 'bi-eye' : 'bi-eye-slash' ?>"></i></button>
                                    </form>
                                    <?php if ($canDelete): ?>
                                        <form method="post" action="<?= url('/admin/comments/' . $comment['id'] . '/delete') ?>" data-confirm="Delete this reply?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="reply_id" value="<?= (int) $reply['id'] ?>">
                                            <button class="icon-action danger" type="submit" title="Delete reply" aria-label="Delete reply"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr class="d-none" id="reply-<?= (int) $comment['id'] ?>">
                    <td colspan="6">
                        <form class="form-section" method="post" action="<?= url('/admin/comments/' . $comment['id'] . '/reply') ?>">
                            <?= csrf_field() ?>
                            <h3>Reply as <?= e($authUser['username'] ?? 'admin') ?></h3>
                            <div class="form-group">
                                <textarea class="form-control" name="content" rows="3" maxlength="4000" required placeholder="Respond to @<?= e($comment['username']) ?>…"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-send"></i> Post reply</button>
                                <button class="btn btn-ghost btn-sm" type="button" data-admin-reply="reply-<?= (int) $comment['id'] ?>">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($comments['items'] === []): ?>
                <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-chat-square"></i><p>No comments match this view.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require ROOT_PATH . '/views/partials/pagination.php'; ?>
