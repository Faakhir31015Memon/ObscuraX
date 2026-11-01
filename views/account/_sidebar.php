<?php
/** @var array $authUser */
$me = $authUser;
$roleLabel = $me['role_slug'] === 'super_admin' ? 'Super Administrator' : ($me['role_slug'] === 'admin' ? 'Administrator' : 'Reader');
$items = [
    ['/dashboard', 'bi-grid', 'Dashboard'],
    ['/dashboard/likes', 'bi-heart', 'Liked articles'],
    ['/dashboard/comments', 'bi-chat-square-text', 'My comments'],
    ['/profile', 'bi-person', 'Profile'],
    ['/account/security', 'bi-shield-lock', 'Security'],
];
?>
<aside class="account-sidebar">
    <div class="account-user">
        <img src="<?= e(\App\Models\User::avatar($me['avatar'] ?? null)) ?>" alt="<?= e($me['username']) ?>">
        <h2><?= e($me['full_name'] ?: $me['username']) ?></h2>
        <span><?= e($roleLabel) ?> · @<?= e($me['username']) ?></span>
    </div>
    <nav class="account-menu" aria-label="Account">
        <?php foreach ($items as [$path, $icon, $label]): ?>
            <a class="<?= is_active($path, $path === '/dashboard') ? 'active' : '' ?>" href="<?= url($path) ?>">
                <i class="bi <?= $icon ?>"></i> <?= e($label) ?>
            </a>
        <?php endforeach; ?>
        <?php if (\App\Core\Auth::isAdmin()): ?>
            <div class="divider"></div>
            <a href="<?= url('/admin') ?>"><i class="bi bi-speedometer2"></i> Admin panel</a>
        <?php endif; ?>
        <div class="divider"></div>
        <form method="post" action="<?= url('/logout') ?>">
            <?= csrf_field() ?>
            <button type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button>
        </form>
    </nav>
</aside>
