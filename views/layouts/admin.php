<?php
/** @var string $content */
/** @var array $authUser */
$isSuper = \App\Core\Auth::isSuperAdmin();
$unread = 0;
$readerCount = 0;
if ($isSuper) {
    try {
        $unread = \App\Models\Contact::unreadCount();
        $readerCount = \App\Models\Stat::readersTotal();
    } catch (\Throwable) {
        $unread = 0;
        $readerCount = 0;
    }
}
$logo = setting('site_logo') !== '' ? uploaded_url(setting('site_logo')) : asset('assets/img/logo.png');
$sections = [
    'blogs' => 'Articles',
    'categories' => 'Categories',
    'tags' => 'Tags',
    'comments' => 'Comments',
    'hero' => 'Hero section',
    'users' => 'Readers',
    'admins' => 'Administrators',
    'likes' => 'Likes',
    'analytics' => 'Analytics',
    'reports' => 'Reports',
    'logs' => 'Activity logs',
    'newsletter' => 'Newsletter',
    'messages' => 'Messages',
    'settings' => 'Settings',
    'password' => 'Change password',
    'upload' => 'Uploads',
];
$segments = array_values(array_filter(explode('/', current_path())));
$sectionKey = $segments[1] ?? '';
$sectionLabel = $segments === [] || $segments === ['admin'] ? 'Overview' : ($sections[$sectionKey] ?? 'Overview');
$adminNav = static function (string $path, string $icon, string $label, bool $exact = false, int $count = 0): string {
    $active = is_active($path, $exact) ? 'active' : '';
    $badge = $count > 0 ? '<span class="count">' . $count . '</span>' : '';
    return '<a class="' . $active . '" href="' . e(url($path)) . '"><i class="bi ' . $icon . '"></i> ' . e($label) . $badge . '</a>';
};
?>
<!doctype html>
<html lang="en">
<head>
<?php $adminCss = true; $noIndex = true; require ROOT_PATH . '/views/partials/head.php'; ?>
</head>
<body class="admin-body <?= e($bodyClass ?? '') ?>">
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a class="admin-brand" href="<?= url('/admin') ?>">
            <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
            <span class="badge-admin <?= $isSuper ? 'is-super' : '' ?>"><?= $isSuper ? 'Super' : 'Admin' ?></span>
        </a>

        <nav class="admin-nav" aria-label="Admin">
            <span class="admin-nav-label">Overview</span>
            <?= $adminNav('/admin', 'bi-speedometer2', 'Dashboard', true) ?>

            <span class="admin-nav-label">Content</span>
            <?php if (can('blog.create')): ?>
                <?= $adminNav('/admin/blogs', 'bi-journal-richtext', 'Articles', true) ?>
            <?php endif; ?>
            <?php if (can('category.manage')): ?>
                <?= $adminNav('/admin/categories', 'bi-collection', 'Categories') ?>
            <?php endif; ?>
            <?php if (can('tag.manage')): ?>
                <?= $adminNav('/admin/tags', 'bi-tags', 'Tags') ?>
            <?php endif; ?>
            <?php if (can('comment.view')): ?>
                <?= $adminNav('/admin/comments', 'bi-chat-square-text', 'Comments') ?>
            <?php endif; ?>
            <?php if (can('hero.manage')): ?>
                <?= $adminNav('/admin/hero', 'bi-easel', 'Hero section') ?>
            <?php endif; ?>

            <?php if ($isSuper): ?>
                <span class="admin-nav-label">Community</span>
                <?= $adminNav('/admin/users', 'bi-people', 'Readers', false, $readerCount) ?>
                <?= $adminNav('/admin/admins', 'bi-person-badge', 'Administrators') ?>
                <?= $adminNav('/admin/blogs/all', 'bi-files', 'All articles') ?>
                <?= $adminNav('/admin/likes', 'bi-heart', 'Likes') ?>

                <span class="admin-nav-label">Intelligence</span>
                <?= $adminNav('/admin/analytics', 'bi-graph-up-arrow', 'Analytics') ?>
                <?= $adminNav('/admin/reports', 'bi-file-earmark-bar-graph', 'Reports') ?>
                <?= $adminNav('/admin/logs', 'bi-clock-history', 'Activity logs') ?>

                <span class="admin-nav-label">Audience</span>
                <?= $adminNav('/admin/newsletter', 'bi-envelope-paper', 'Newsletter') ?>
                <?= $adminNav('/admin/messages', 'bi-inbox', 'Messages', false, $unread) ?>
                <?= $adminNav('/admin/settings', 'bi-sliders', 'Settings') ?>
            <?php endif; ?>

            <span class="admin-nav-label">Account</span>
            <?= $adminNav('/admin/password', 'bi-key', 'Change password') ?>
        </nav>

        <div class="admin-sidebar-foot">
            <div class="admin-side-user">
                <img src="<?= e(\App\Models\User::avatar($authUser['avatar'] ?? null)) ?>" alt="">
                <div class="grow">
                    <strong><?= e($authUser['full_name'] ?: $authUser['username']) ?></strong>
                    <span><?= e($isSuper ? 'Super administrator' : 'Administrator') ?></span>
                </div>
                <form method="post" action="<?= url('/logout') ?>" title="Sign out">
                    <?= csrf_field() ?>
                    <button type="submit" aria-label="Sign out"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="admin-sidebar-backdrop" id="adminBackdrop" data-sidebar-close></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-icon-btn admin-menu-toggle" type="button" data-sidebar-toggle aria-label="Open menu"><i class="bi bi-list"></i></button>
            <p class="crumb"><a href="<?= url('/admin') ?>">Admin</a><span class="sep">/</span><?= e($sectionLabel) ?></p>
            <div class="admin-topbar-actions">
                <button class="admin-icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Switch to light mode" title="Switch to light mode">
                    <span class="theme-icon theme-icon-sun"><i class="bi bi-sun"></i></span>
                    <span class="theme-icon theme-icon-moon"><i class="bi bi-moon-stars"></i></span>
                </button>
                <a class="admin-icon-btn" href="<?= url('/') ?>" title="View site" aria-label="View site"><i class="bi bi-box-arrow-up-right"></i></a>
                <?php if ($isSuper): ?>
                    <a class="admin-icon-btn" href="<?= url('/admin/messages') ?>" title="Messages" aria-label="Messages">
                        <i class="bi bi-envelope"></i>
                        <?php if ($unread > 0): ?><span class="dot"></span><?php endif; ?>
                    </a>
                <?php endif; ?>
                <?php if (can('blog.create')): ?>
                    <a class="btn btn-brand btn-sm d-none d-md-inline-flex" href="<?= url('/admin/blogs/create') ?>"><i class="bi bi-plus-lg"></i> New article</a>
                <?php endif; ?>
            </div>
        </header>

        <?php require ROOT_PATH . '/views/partials/flash.php'; ?>

        <main class="admin-content" id="main">
            <?= $content ?>
        </main>
    </div>
</div>

<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<?php if (!empty($needQuill)): ?>
    <script src="<?= asset('assets/vendor/quill/quill.js') ?>" defer></script>
<?php endif; ?>
<?php if (!empty($charts)): ?>
    <script src="<?= asset('assets/vendor/chartjs/chart.umd.min.js') ?>" defer></script>
<?php endif; ?>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
<script src="<?= asset('assets/js/admin.js') ?>" defer></script>
</body>
</html>
