<?php
/** @var array $blog */
/** @var array $comments */
/** @var array $tags */
/** @var array $related */
/** @var bool $liked */
/** @var array|null $author */
/** @var array $authorBlogs */
/** @var string $shareUrl */
$user = auth_user();
$accent = $blog['accent_color'] ?? '#8b5cf6';
$minutes = reading_time($blog['content']);
$shares = share_links($shareUrl, $blog['title']);
$canComment = (int) $blog['allow_comments'] === 1;
$canReply = $user !== null && !\App\Core\Auth::mustChangePassword();
$commentCount = 0;
foreach ($comments as $comment) {
    $commentCount += 1 + count($comment['replies'] ?? []);
}
?>
<article>
    <header class="article-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/blogs') ?>">Articles</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/category/' . $blog['category_slug']) ?>"><?= e($blog['category_name']) ?></a></li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-9">
                    <a class="badge-tag mt-3" href="<?= url('/category/' . $blog['category_slug']) ?>" style="border-color: <?= e($accent) ?>55;color:<?= e($accent) ?>">
                        <i class="bi bi-collection"></i> <?= e($blog['category_name']) ?>
                    </a>
                    <h1 class="article-title"><?= e($blog['title']) ?></h1>
                    <p class="article-deck"><?= e($blog['excerpt'] ?: str_limit(strip_tags($blog['content']), 220)) ?></p>
                </div>
            </div>

            <div class="article-meta">
                <a class="article-author" href="<?= url('/author/' . $blog['author_username']) ?>">
                    <img src="<?= e(\App\Models\User::avatar($blog['author_avatar'])) ?>" alt="">
                    <span>
                        <strong><?= e($blog['author_full_name'] ?: $blog['author_username']) ?></strong>
                        <span><?= e($author['bio'] ?? 'ObscuraX contributor') ?></span>
                    </span>
                </a>
                <span class="meta-item"><i class="bi bi-calendar3"></i> <?= e(format_date($blog['published_at'])) ?></span>
                <span class="meta-item"><i class="bi bi-clock"></i> <?= $minutes ?> min read</span>
                <span class="meta-item"><i class="bi bi-eye"></i> <?= e(number_short((int) $blog['view_count'])) ?> reads</span>
                <span class="meta-item"><i class="bi bi-chat-square-text"></i> <?= $commentCount ?> comment<?= $commentCount === 1 ? '' : 's' ?></span>
            </div>

            <div class="article-cover">
                <img src="<?= e(cover_url($blog['featured_image'])) ?>" alt="<?= e($blog['title']) ?>">
            </div>
        </div>
    </header>

    <div class="container">
        <div class="article-layout">
            <div class="share-rail">
                <span class="label">Share</span>
                <button class="share-btn <?= $liked ? 'is-liked' : '' ?>" type="button"
                        data-like
                        data-url="<?= url('/blog/' . $blog['id'] . '/like') ?>"
                        data-auth="<?= $user !== null ? '1' : '0' ?>"
                        data-login-url="<?= url('/login') ?>"
                        aria-pressed="<?= $liked ? 'true' : 'false' ?>"
                        title="<?= $user !== null ? 'Like this article' : 'Sign in to like' ?>">
                    <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
                <button class="share-btn" type="button" data-copy="<?= e($shareUrl) ?>" title="Copy link"><i class="bi bi-link-45deg"></i></button>
                <a class="share-btn" href="<?= e($shares['x']) ?>" target="_blank" rel="noopener noreferrer" title="Share on X"><i class="bi bi-twitter-x"></i></a>
                <a class="share-btn" href="<?= e($shares['facebook']) ?>" target="_blank" rel="noopener noreferrer" title="Share on Facebook"><i class="bi bi-facebook"></i></a>
                <a class="share-btn" href="<?= e($shares['linkedin']) ?>" target="_blank" rel="noopener noreferrer" title="Share on LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a class="share-btn" href="<?= e($shares['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" title="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
            </div>

            <div>
                <div class="article-body">
                    <?= $blog['content'] ?>
                </div>

                <div class="like-callout">
                    <div>
                        <p class="eyebrow mb-1">Did this land?</p>
                        <p class="mb-0 text-muted" style="font-size:.86rem">Likes help us decide which threads to follow next.</p>
                    </div>
                    <button class="btn btn-outline-soft <?= $liked ? 'is-liked' : '' ?>" type="button"
                            data-like
                            data-url="<?= url('/blog/' . $blog['id'] . '/like') ?>"
                            data-auth="<?= $user !== null ? '1' : '0' ?>"
                            data-login-url="<?= url('/login') ?>"
                            aria-pressed="<?= $liked ? 'true' : 'false' ?>">
                        <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i>
                        <span data-like-label><?= $liked ? 'Liked' : 'Like' ?></span>
                        <span class="text-muted ms-1">· <span data-like-count><?= (int) $blog['like_count'] ?></span></span>
                    </button>
                </div>

                <?php if ($tags !== []): ?>
                    <div class="article-tags">
                        <span class="eyebrow me-2">Filed under</span>
                        <?php foreach ($tags as $tag): ?>
                            <a class="tag-chip" href="<?= url('/tag/' . $tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="aside-card mt-4 d-flex flex-wrap align-items-center gap-3" style="position:static">
                    <img src="<?= e(\App\Models\User::avatar($blog['author_avatar'])) ?>" alt="" style="width:56px;height:56px;border-radius:50%;object-fit:cover">
                    <div class="flex-grow-1">
                        <p class="eyebrow mb-1">Written by</p>
                        <h3 class="mb-1" style="font-size:1.05rem"><a href="<?= url('/author/' . $blog['author_username']) ?>"><?= e($blog['author_full_name'] ?: $blog['author_username']) ?></a></h3>
                        <p class="mb-0 text-muted" style="font-size:.84rem"><?= e(str_limit($author['bio'] ?? 'Contributor at ObscuraX.', 150)) ?></p>
                    </div>
                    <a class="btn btn-outline-soft btn-sm" href="<?= url('/author/' . $blog['author_username']) ?>">More by this author</a>
                </div>
            </div>

            <aside class="article-aside">
                <div class="aside-card aside-author">
                    <img src="<?= e(\App\Models\User::avatar($blog['author_avatar'])) ?>" alt="">
                    <h3><?= e($blog['author_full_name'] ?: $blog['author_username']) ?></h3>
                    <p class="role"><?= e($author['role_name'] ?? 'Contributor') ?></p>
                    <p><?= e(str_limit($author['bio'] ?? 'Writes for ObscuraX.', 160)) ?></p>
                    <a class="btn btn-outline-soft btn-sm w-100" href="<?= url('/author/' . $blog['author_username']) ?>">View profile</a>
                </div>

                <?php if ($related !== []): ?>
                    <div class="aside-card">
                        <h4>Related investigations</h4>
                        <?php foreach (array_slice($related, 0, 3) as $item): ?>
                            <div class="mini-post">
                                <img src="<?= e(cover_url($item['featured_image'])) ?>" alt="" loading="lazy">
                                <div>
                                    <h5><a href="<?= url('/blog/' . $item['slug']) ?>"><?= e(str_limit($item['title'], 64)) ?></a></h5>
                                    <span><?= e(format_date($item['published_at'])) ?> · <?= reading_time($item['content']) ?> min</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="aside-card">
                    <h4>The dispatch</h4>
                    <p class="text-muted" style="font-size:.84rem">One email when a new investigation lands.</p>
                    <form action="<?= url('/newsletter/subscribe') ?>" method="post" data-newsletter>
                        <?= csrf_field() ?>
                        <input class="form-control mb-2" type="email" name="email" required placeholder="you@example.com" aria-label="Email address">
                        <button class="btn btn-brand btn-sm w-100" type="submit">Subscribe</button>
                    </form>
                    <div class="newsletter-note" data-newsletter-msg></div>
                </div>
            </aside>
        </div>
    </div>

    <section class="comments-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="comments-head">
                        <h2>Discussion</h2>
                        <span><?= $commentCount ?> contribution<?= $commentCount === 1 ? '' : 's' ?></span>
                    </div>

                    <?php if ($canComment): ?>
                        <?php if ($user !== null): ?>
                            <?php if (\App\Core\Auth::mustChangePassword()): ?>
                                <div class="comment-closed mb-4">
                                    You must <a href="<?= url('/account/security') ?>">set a new password</a> before joining discussions.
                                </div>
                            <?php else: ?>
                                <form class="comment-form mb-4" method="post" action="<?= url('/blog/' . $blog['id'] . '/comment') ?>">
                                    <?= csrf_field() ?>
                                    <div class="d-flex gap-3">
                                        <img src="<?= e(\App\Models\User::avatar($user['avatar'] ?? null)) ?>" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0">
                                        <div class="flex-grow-1">
                                            <textarea class="form-control" name="content" required minlength="2" maxlength="2000" placeholder="Add to the discussion…" aria-label="Your comment"></textarea>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="form-text">Be specific, be kind. Comments are moderated.</span>
                                                <button class="btn btn-brand btn-sm" type="submit">Post comment</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="comment-closed mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <span>Have something to add? Readers can join the discussion after signing in.</span>
                                <span class="d-flex gap-2">
                                    <a class="btn btn-outline-soft btn-sm" href="<?= url('/login') ?>">Sign in</a>
                                    <a class="btn btn-brand btn-sm" href="<?= url('/register') ?>">Create account</a>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="comment-closed mb-4">Comments are closed for this article.</div>
                    <?php endif; ?>

                    <?php if ($comments === []): ?>
                        <div class="panel">
                            <div class="empty-state">
                                <i class="bi bi-chat-square"></i>
                                <h3>No comments yet</h3>
                                <p>Start the conversation — thoughtful first contributions set the tone.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="panel">
                            <div class="panel-body">
                                <?php foreach ($comments as $comment): ?>
                                    <?php
                                    $name = $comment['full_name'] ?: $comment['username'];
                                    $isAdminAuthor = in_array($comment['role_slug'] ?? '', ['admin', 'super_admin'], true);
                                    ?>
                                    <div class="comment" id="comment-<?= (int) $comment['id'] ?>">
                                        <div class="comment-avatar">
                                            <img src="<?= e(\App\Models\User::avatar($comment['avatar'])) ?>" alt="">
                                        </div>
                                        <div class="comment-main">
                                            <div class="comment-head">
                                                <strong><?= e($name) ?></strong>
                                                <?php if ($isAdminAuthor): ?><span class="badge-admin">ObscuraX</span><?php endif; ?>
                                                <time datetime="<?= e($comment['created_at']) ?>"><?= e(time_ago($comment['created_at'])) ?></time>
                                            </div>
                                            <div class="comment-body"><p><?= nl2br(e($comment['content'])) ?></p></div>
                                            <?php if ($canReply && $canComment): ?>
                                                <div class="comment-actions">
                                                    <button type="button" data-reply-toggle="reply-form-<?= (int) $comment['id'] ?>">Reply</button>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($comment['replies'])): ?>
                                                <div class="reply-list">
                                                    <?php foreach ($comment['replies'] as $reply): ?>
                                                        <?php $replyName = $reply['full_name'] ?: $reply['username']; ?>
                                                        <div class="reply" id="reply-<?= (int) $reply['id'] ?>">
                                                            <img src="<?= e(\App\Models\User::avatar($reply['avatar'])) ?>" alt="">
                                                            <div class="flex-grow-1">
                                                                <div class="comment-head">
                                                                    <strong><?= e($replyName) ?></strong>
                                                                    <?php if (in_array($reply['role_slug'] ?? '', ['admin', 'super_admin'], true)): ?><span class="badge-admin">ObscuraX</span><?php endif; ?>
                                                                    <time datetime="<?= e($reply['created_at']) ?>"><?= e(time_ago($reply['created_at'])) ?></time>
                                                                </div>
                                                                <div class="comment-body"><p><?= nl2br(e($reply['content'])) ?></p></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($canReply && $canComment): ?>
                                                <form class="reply-form" id="reply-form-<?= (int) $comment['id'] ?>" method="post" action="<?= url('/comment/' . $comment['id'] . '/reply') ?>">
                                                    <?= csrf_field() ?>
                                                    <div class="d-flex gap-2 mt-2">
                                                        <input class="form-control" type="text" name="content" required minlength="2" maxlength="2000" placeholder="Reply to <?= e($name) ?>…" aria-label="Reply">
                                                        <button class="btn btn-brand btn-sm" type="submit">Reply</button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($related !== []): ?>
        <section class="section-tight section-line">
            <div class="container">
                <div class="section-head">
                    <div>
                        <p class="kicker">Keep digging</p>
                        <h2>More from <?= e($blog['category_name']) ?></h2>
                    </div>
                    <a class="link-arrow" href="<?= url('/category/' . $blog['category_slug']) ?>">All in this desk <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4">
                    <?php foreach ($related as $post): ?>
                        <div class="col-md-4">
                            <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</article>

<script type="application/ld+json" nonce="<?= e(CSP_NONCE) ?>">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $blog['title'],
    'description' => $blog['meta_description'] ?: str_limit($blog['excerpt'], 160),
    'image' => [cover_url($blog['featured_image'])],
    'datePublished' => $blog['published_at'],
    'dateModified' => $blog['updated_at'],
    'author' => ['@type' => 'Person', 'name' => $blog['author_full_name'] ?: $blog['author_username']],
    'publisher' => ['@type' => 'Organization', 'name' => $siteName],
    'mainEntityOfPage' => $shareUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>
