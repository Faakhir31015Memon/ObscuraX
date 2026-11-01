<?php
/** @var array $post */
$accent = $post['accent_color'] ?? '#8b5cf6';
$link = url('/blog/' . $post['slug']);
$minutes = reading_time((string) ($post['content'] ?? $post['excerpt'] ?? ''));
?>
<article class="post-card reveal" data-reveal>
    <a class="post-media" href="<?= e($link) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(cover_url($post['featured_image'] ?? null)) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
        <span class="post-cat" style="--accent: <?= e($accent) ?>"><span class="dot"></span><?= e($post['category_name'] ?? 'Article') ?></span>
    </a>
    <div class="post-body">
        <div class="post-meta">
            <span><?= e(format_date($post['published_at'] ?? null)) ?></span>
            <span class="sep"></span>
            <span><?= $minutes ?> min read</span>
        </div>
        <h3 class="post-title"><a href="<?= e($link) ?>"><?= e($post['title']) ?></a></h3>
        <p class="post-excerpt"><?= e(str_limit((string) ($post['excerpt'] ?: strip_tags((string) $post['content'])), 150)) ?></p>
        <div class="post-foot">
            <span class="post-author">
                <img src="<?= e(\App\Models\User::avatar($post['author_avatar'] ?? null)) ?>" alt="">
                <?= e($post['author_username'] ?? 'ObscuraX') ?>
            </span>
            <span class="post-stats">
                <span title="Views"><i class="bi bi-eye"></i><?= e(number_short((int) ($post['view_count'] ?? 0))) ?></span>
                <span title="Likes"><i class="bi bi-heart"></i><?= e(number_short((int) ($post['like_count'] ?? 0))) ?></span>
                <?php if (isset($post['comment_count'])): ?>
                    <span title="Comments"><i class="bi bi-chat-square-text"></i><?= e(number_short((int) $post['comment_count'])) ?></span>
                <?php endif; ?>
            </span>
        </div>
    </div>
</article>
