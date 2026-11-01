<?php
/** @var array|null $authUser */
/** @var array $navCategories */
$logo = setting('site_logo') !== '' ? uploaded_url(setting('site_logo')) : asset('assets/img/logo.png');
$mark = asset('assets/img/logo-mark.png');
$user = $authUser ?? null;
$roleLabel = $user !== null ? ($user['role_slug'] === 'super_admin' ? 'Super Admin' : ($user['role_slug'] === 'admin' ? 'Administrator' : 'Reader')) : '';
?>
<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <a class="brand" href="<?= url('/') ?>" aria-label="<?= e($siteName) ?> home">
                <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
            </a>

            <nav class="main-nav" aria-label="Primary">
                <a class="nav-link <?= is_active('/') ? 'active' : '' ?>" href="<?= url('/') ?>">Home</a>
                <a class="nav-link <?= is_active('/blogs', false) ? 'active' : '' ?>" href="<?= url('/blogs') ?>">All Articles</a>
                <div class="dropdown nav-drop">
                    <a class="nav-link dropdown-toggle <?= is_active('/category', false) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($navCategories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="<?= url('/category/' . $category['slug']) ?>">
                                    <i class="bi <?= e($category['icon'] ?: 'bi-collection') ?>"></i>
                                    <span><?= e($category['name']) ?><br><small class="text-muted"><?= (int) $category['blog_count'] ?> article<?= (int) $category['blog_count'] === 1 ? '' : 's' ?></small></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a class="nav-link <?= is_active('/about') ? 'active' : '' ?>" href="<?= url('/about') ?>">About</a>
                <a class="nav-link <?= is_active('/contact') ? 'active' : '' ?>" href="<?= url('/contact') ?>">Contact</a>
            </nav>

            <div class="header-actions">
                <form class="header-search" action="<?= url('/search') ?>" method="get" role="search">
                    <i class="bi bi-search search-icon"></i>
                    <input class="form-control" type="search" name="q" placeholder="Search the archive…" aria-label="Search" value="<?= e($_GET['q'] ?? '') ?>">
                </form>

                <button class="search-toggle theme-toggle" type="button" data-theme-toggle aria-label="Switch to light mode" title="Switch to light mode">
                    <span class="theme-icon theme-icon-sun"><i class="bi bi-sun"></i></span>
                    <span class="theme-icon theme-icon-moon"><i class="bi bi-moon-stars"></i></span>
                </button>

                <button class="nav-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>

                <?php if ($user !== null): ?>
                    <div class="dropdown user-menu">
                        <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= e(\App\Models\User::avatar($user['avatar'] ?? null)) ?>" alt="">
                            <span class="d-none d-xl-inline"><?= e($user['username']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end nav-drop" style="min-width:240px">
                            <li class="px-3 pt-2 pb-1"><span class="eyebrow"><?= e($roleLabel) ?></span></li>
                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= url('/admin') ?>"><i class="bi bi-speedometer2"></i> Admin panel</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= url('/dashboard') ?>"><i class="bi bi-grid"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="<?= url('/dashboard/likes') ?>"><i class="bi bi-heart"></i> Liked articles</a></li>
                            <li><a class="dropdown-item" href="<?= url('/dashboard/comments') ?>"><i class="bi bi-chat-square-text"></i> My comments</a></li>
                            <li><a class="dropdown-item" href="<?= url('/account/security') ?>"><i class="bi bi-shield-lock"></i> Security</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('/logout') ?>">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-ghost d-none d-md-inline-flex" href="<?= url('/login') ?>">Sign in</a>
                    <a class="btn btn-brand btn-sm" href="<?= url('/register') ?>">Join the archive</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="collapse d-lg-none pb-3" id="mobileNav">
            <nav class="d-grid gap-1" aria-label="Mobile">
                <a class="nav-link <?= is_active('/') ? 'active' : '' ?>" href="<?= url('/') ?>">Home</a>
                <a class="nav-link <?= is_active('/blogs', false) ? 'active' : '' ?>" href="<?= url('/blogs') ?>">All Articles</a>
                <?php foreach ($navCategories as $category): ?>
                    <a class="nav-link" href="<?= url('/category/' . $category['slug']) ?>"><?= e($category['name']) ?></a>
                <?php endforeach; ?>
                <a class="nav-link <?= is_active('/about') ? 'active' : '' ?>" href="<?= url('/about') ?>">About</a>
                <a class="nav-link <?= is_active('/contact') ? 'active' : '' ?>" href="<?= url('/contact') ?>">Contact</a>
                <form class="d-flex gap-2 mt-2" action="<?= url('/search') ?>" method="get">
                    <input class="form-control" type="search" name="q" placeholder="Search…" aria-label="Search">
                    <button class="btn btn-outline-soft" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <?php if ($user === null): ?>
                    <div class="d-flex gap-2 mt-2">
                        <a class="btn btn-outline-soft flex-fill" href="<?= url('/login') ?>">Sign in</a>
                        <a class="btn btn-brand flex-fill" href="<?= url('/register') ?>">Register</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
