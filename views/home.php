<?php
/** @var array $featured */
/** @var array $categories */
/** @var array $popular */
/** @var array $latest */
/** @var array $mostLiked */
/** @var array $tags */
/** @var array $stats */

$heroTitle = setting('hero_title', 'Explore the Unknown. Discover Beyond the Ordinary.');
$heroSplit = strpos($heroTitle, '. ');
$heroLead = $heroSplit !== false ? substr($heroTitle, 0, $heroSplit + 1) : $heroTitle;
$heroTail = $heroSplit !== false ? substr($heroTitle, $heroSplit + 2) : '';
$heroImage = setting('hero_image', 'assets/img/hero-default.jpg');
$heroImage = uploaded_url($heroImage, asset('assets/img/hero-default.jpg'));
$resolve = static function (string $value): string {
    if ($value === '') {
        return '#';
    }
    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '#')) {
        return $value;
    }
    return url($value);
};
$lead = $featured[0] ?? null;
$side = array_slice($featured, 1, 2);
$latestRest = array_slice($latest, 0, 6);
?>
<section class="hero" style="--hero-image: url('<?= e($heroImage) ?>')">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <span class="hero-badge"><span class="dot"></span><?= e(setting('hero_badge', 'Issue 01 — The Unknown')) ?></span>
                <h1 class="hero-title">
                    <?= e($heroLead) ?>
                    <?php if ($heroTail !== ''): ?><span class="text-gradient"><?= e($heroTail) ?></span><?php endif; ?>
                </h1>
                <p class="hero-sub"><?= e(setting('hero_subtitle', 'ObscuraX investigates black magic and the occult, sexual health and education, and ethical hacking — long-form research for people who prefer questions to comfortable answers.')) ?></p>
                <div class="hero-actions">
                    <a class="btn btn-brand btn-lg" href="<?= e($resolve(setting('hero_primary_url', '/blogs'))) ?>">
                        <?= e(setting('hero_primary_label', 'Start reading')) ?> <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a class="btn btn-outline-soft btn-lg" href="<?= e($resolve(setting('hero_secondary_url', '#categories'))) ?>" data-scroll-to="#categories">
                        <?= e(setting('hero_secondary_label', 'Browse categories')) ?>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat"><strong><?= e(number_short($stats['blogs'])) ?></strong><span>Investigations</span></div>
                    <div class="hero-stat"><strong><?= e(number_short($stats['readers'])) ?></strong><span>Verified readers</span></div>
                    <div class="hero-stat"><strong><?= e(number_short($stats['views'])) ?></strong><span>Total reads</span></div>
                    <div class="hero-stat"><strong><?= e(number_short($stats['likes'])) ?></strong><span>Reader likes</span></div>
                </div>
            </div>

            <div class="hero-art" aria-hidden="true">
                <div class="hero-rings"><span></span><span></span><span></span></div>
                <img class="hero-emblem" src="<?= asset('assets/img/logo-mark.png') ?>" alt="">
            </div>
        </div>
    </div>
    <a class="hero-scroll" href="#featured"><span>Scroll</span><i class="bi bi-chevron-down"></i></a>
</section>

<section class="section" id="featured">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="kicker">Editor's selection</p>
                <h2>Featured investigations</h2>
                <p>Long reads chosen by the desk — the pieces we would hand you first.</p>
            </div>
            <a class="link-arrow" href="<?= url('/blogs') ?>">All investigations <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if ($lead !== null): ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <a class="featured-lead" href="<?= url('/blog/' . $lead['slug']) ?>">
                        <img src="<?= e(cover_url($lead['featured_image'])) ?>" alt="<?= e($lead['title']) ?>" loading="lazy">
                        <div class="fl-body">
                            <span class="post-cat" style="position:static;display:inline-flex;--accent: <?= e($lead['accent_color'] ?? '#8b5cf6') ?>"><span class="dot"></span><?= e($lead['category_name']) ?></span>
                            <h3 class="fl-title"><?= e($lead['title']) ?></h3>
                            <p class="fl-excerpt"><?= e(str_limit($lead['excerpt'] ?: strip_tags($lead['content']), 170)) ?></p>
                            <div class="post-meta" style="margin:0">
                                <span><?= e($lead['author_full_name'] ?: $lead['author_username']) ?></span>
                                <span class="sep"></span>
                                <span><?= e(format_date($lead['published_at'])) ?></span>
                                <span class="sep"></span>
                                <span><?= reading_time($lead['content']) ?> min read</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-5">
                    <div class="featured-side">
                        <?php foreach ($side as $post): ?>
                            <a class="featured-mini" href="<?= url('/blog/' . $post['slug']) ?>">
                                <img src="<?= e(cover_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                                <div class="fm-body">
                                    <span class="post-cat" style="position:static;display:inline-flex;--accent: <?= e($post['accent_color'] ?? '#8b5cf6') ?>"><span class="dot"></span><?= e($post['category_name']) ?></span>
                                    <h3><?= e(str_limit($post['title'], 72)) ?></h3>
                                    <div class="post-meta">
                                        <span><?= e(format_date($post['published_at'])) ?></span>
                                        <span class="sep"></span>
                                        <span><?= reading_time($post['content']) ?> min read</span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-line" id="categories">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="kicker">Three desks</p>
                <h2>Where to begin</h2>
                <p>Three subjects that share one quality: they are easy to sensationalise and worth writing about honestly.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $index => $category): ?>
                <div class="col-md-4">
                    <a class="cat-card reveal" data-reveal href="<?= url('/category/' . $category['slug']) ?>" style="--accent: <?= e($category['accent_color']) ?>; --accent-line: <?= e($category['accent_color']) ?>66">
                        <img src="<?= e(cover_url($category['cover_image'])) ?>" alt="" loading="lazy">
                        <span class="cat-icon"><i class="bi <?= e($category['icon'] ?: 'bi-collection') ?>"></i></span>
                        <h3><?= e($category['name']) ?></h3>
                        <p><?= e(str_limit($category['description'], 130)) ?></p>
                        <div class="cat-meta">
                            <span><?= (int) $category['blog_count'] ?> article<?= (int) $category['blog_count'] === 1 ? '' : 's' ?></span>
                            <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="kicker">Fresh from the desk</p>
                <h2>Latest investigations</h2>
                <p>New research, published as it is ready — no schedule, no filler.</p>
            </div>
            <a class="link-arrow" href="<?= url('/blogs') ?>">Browse the archive <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($latestRest as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-line">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="section-head" style="margin-bottom:1.6rem">
                    <div>
                        <p class="kicker">Most read</p>
                        <h2>The pieces readers keep returning to</h2>
                    </div>
                </div>
                <div class="popular-list">
                    <?php foreach ($popular as $index => $post): ?>
                        <div class="popular-item">
                            <span class="popular-rank"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <div>
                                <h3><a href="<?= url('/blog/' . $post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                                <div class="meta">
                                    <span><?= e($post['category_name']) ?></span>
                                    <span><i class="bi bi-eye me-1"></i><?= e(number_short((int) $post['view_count'])) ?> reads</span>
                                    <span><i class="bi bi-heart me-1"></i><?= e(number_short((int) $post['like_count'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="trend-panel h-100">
                    <p class="kicker mb-3">Trending desks</p>
                    <?php foreach ($categories as $category): ?>
                        <a class="d-flex align-items-center justify-content-between py-2 text-decoration-none" href="<?= url('/category/' . $category['slug']) ?>">
                            <span class="d-inline-flex align-items-center gap-2" style="color:#d3cddf">
                                <i class="bi <?= e($category['icon'] ?: 'bi-collection') ?>" style="color: <?= e($category['accent_color']) ?>"></i>
                                <?= e($category['name']) ?>
                            </span>
                            <span class="badge-tag"><?= (int) $category['blog_count'] ?></span>
                        </a>
                        <div class="progress" style="height:3px;background:rgba(255,255,255,.06)">
                            <div class="progress-bar" style="width: <?= max(18, min(100, (int) $category['blog_count'] * 10)) ?>%; background: <?= e($category['accent_color']) ?>"></div>
                        </div>
                    <?php endforeach; ?>

                    <hr class="my-4">
                    <p class="kicker mb-3">Threads</p>
                    <div class="tag-cloud">
                        <?php foreach ($tags as $tag): ?>
                            <a class="tag-chip" href="<?= url('/tag/' . $tag['slug']) ?>">#<?= e($tag['name']) ?> <b><?= (int) ($tag['blog_count'] ?? 0) ?></b></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="kicker">Reader favourites</p>
                <h2>Most liked this season</h2>
            </div>
            <a class="link-arrow" href="<?= url('/blogs?sort=liked') ?>">Most liked <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($mostLiked as $post): ?>
                <div class="col-md-4">
                    <?php require ROOT_PATH . '/views/partials/post-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="newsletter-band">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <p class="kicker mb-3">The dispatch</p>
                    <h2>One email. Only when it is worth your time.</h2>
                    <p>New investigations, occasional field notes, and nothing else. Unsubscribe in one click.</p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form" action="<?= url('/newsletter/subscribe') ?>" method="post" data-newsletter>
                        <?= csrf_field() ?>
                        <input class="form-control" type="email" name="email" required placeholder="your@email.com" aria-label="Email address" autocomplete="email">
                        <button class="btn btn-brand" type="submit">Subscribe <i class="bi bi-arrow-right ms-1"></i></button>
                    </form>
                    <div class="newsletter-note" data-newsletter-msg>No spam. No tracking pixels. Just the work.</div>
                </div>
            </div>
        </div>
    </div>
</section>
