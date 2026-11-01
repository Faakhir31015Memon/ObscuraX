<?php
/** @var array $navCategories */
$logo = setting('site_logo') !== '' ? uploaded_url(setting('site_logo')) : asset('assets/img/logo.png');
$socials = [
    'social_x' => ['bi-twitter-x', 'X'],
    'social_instagram' => ['bi-instagram', 'Instagram'],
    'social_youtube' => ['bi-youtube', 'YouTube'],
    'social_linkedin' => ['bi-linkedin', 'LinkedIn'],
    'social_github' => ['bi-github', 'GitHub'],
];
?>
<footer class="site-footer">
    <div class="container footer-main">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
                    <p><?= e(setting('footer_about', 'An independent archive of the strange, the human and the digital. Research, not rumour.')) ?></p>
                    <div class="footer-social">
                        <?php foreach ($socials as $key => [$icon, $label]): ?>
                            <?php if (setting($key) !== ''): ?>
                                <a href="<?= e(setting($key)) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?>"><i class="bi <?= $icon ?>"></i></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="<?= url('/blogs') ?>">All articles</a></li>
                        <li><a href="<?= url('/search') ?>">Search archive</a></li>
                        <li><a href="<?= url('/about') ?>">About us</a></li>
                        <li><a href="<?= url('/contact') ?>">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="footer-col">
                    <h4>Desks</h4>
                    <ul>
                        <?php foreach ($navCategories as $category): ?>
                            <li><a href="<?= url('/category/' . $category['slug']) ?>"><?= e($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="footer-col">
                    <h4>Stay curious</h4>
                    <p class="text-muted" style="font-size:.86rem">One considered email when a new investigation lands. No noise, ever.</p>
                    <form class="d-flex gap-2" action="<?= url('/newsletter/subscribe') ?>" method="post" data-newsletter>
                        <?= csrf_field() ?>
                        <input class="form-control" type="email" name="email" required placeholder="you@example.com" aria-label="Email address" style="min-height:42px">
                        <button class="btn btn-brand btn-sm" type="submit" aria-label="Subscribe"><i class="bi bi-arrow-right"></i></button>
                    </form>
                    <div class="newsletter-note" data-newsletter-msg></div>
                    <p class="mt-3 mb-0"><a class="link-arrow" href="<?= url('/privacy') ?>" style="font-size:.68rem">Privacy policy <i class="bi bi-arrow-up-right"></i></a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved. Research, not rumour.</span>
            <div class="links">
                <a href="<?= url('/privacy') ?>">Privacy</a>
                <a href="<?= url('/terms') ?>">Terms</a>
                <a href="<?= url('/contact') ?>">Contact</a>
                <a href="<?= url('/sitemap.xml') ?>">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
