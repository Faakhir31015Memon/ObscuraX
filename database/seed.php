<?php

declare(strict_types=1);

/**
 * ObscuraX seeder.
 * Called by database/install.php. Seeds the platform skeleton only:
 * roles, permissions, administrator accounts, the three desks and site settings.
 * No articles, readers or engagement rows are created — every statistic on the
 * site is computed from real activity from the first login onward.
 */

use App\Core\Database;

/** @var PDO $pdo */
$pdo = Database::pdo();

$hash = static fn (string $plain): string => password_hash($plain, PASSWORD_DEFAULT);

// ------------------------------------------------------------------ roles
$roles = [
    ['slug' => 'user', 'name' => 'Reader', 'description' => 'Can read, like and comment on articles.', 'level' => 10],
    ['slug' => 'admin', 'name' => 'Administrator', 'description' => 'Publishes and manages their own articles.', 'level' => 50],
    ['slug' => 'super_admin', 'name' => 'Super Administrator', 'description' => 'Full control over the platform.', 'level' => 100],
];
foreach ($roles as $role) {
    Database::insert('roles', $role);
}

// ------------------------------------------------------------ permissions
$permissions = [
    ['blog.create', 'Create articles', 'content'],
    ['blog.edit.own', 'Edit own articles', 'content'],
    ['blog.edit.any', 'Edit any article', 'content'],
    ['blog.publish', 'Publish / unpublish articles', 'content'],
    ['blog.feature', 'Feature articles', 'content'],
    ['blog.delete', 'Delete articles', 'content'],
    ['category.manage', 'Manage categories', 'taxonomy'],
    ['tag.manage', 'Manage tags', 'taxonomy'],
    ['comment.view', 'View comments', 'community'],
    ['comment.reply', 'Reply to comments', 'community'],
    ['comment.moderate', 'Hide / restore comments', 'community'],
    ['comment.delete', 'Delete comments', 'community'],
    ['hero.manage', 'Manage hero section', 'appearance'],
    ['user.manage', 'Manage users', 'administration'],
    ['admin.manage', 'Manage administrators', 'administration'],
    ['settings.manage', 'Manage website settings', 'administration'],
    ['analytics.view', 'View analytics and reports', 'administration'],
    ['logs.view', 'View audit logs', 'administration'],
    ['newsletter.manage', 'Manage newsletter subscribers', 'administration'],
    ['message.manage', 'Manage contact messages', 'administration'],
    ['like.manage', 'Manage likes', 'community'],
];
foreach ($permissions as [$slug, $name, $group]) {
    Database::insert('permissions', ['slug' => $slug, 'name' => $name, 'group_name' => $group]);
}

$roleIds = [];
foreach (Database::fetchAll('SELECT id, slug FROM roles') as $row) {
    $roleIds[$row['slug']] = (int) $row['id'];
}

$adminPermissions = [
    'blog.create', 'blog.edit.own', 'blog.publish', 'blog.feature',
    'category.manage', 'tag.manage',
    'comment.view', 'comment.reply', 'comment.moderate',
    'hero.manage',
];

foreach (Database::fetchAll('SELECT id, slug FROM permissions') as $permission) {
    if (in_array($permission['slug'], $adminPermissions, true)) {
        Database::insert('role_permissions', [
            'role_id' => $roleIds['admin'],
            'permission_id' => (int) $permission['id'],
        ]);
    }
    Database::insert('role_permissions', [
        'role_id' => $roleIds['super_admin'],
        'permission_id' => (int) $permission['id'],
    ]);
}

// ---------------------------------------------------------------- accounts
$accounts = [
    [
        'role' => 'super_admin', 'username' => 'MyMnSaaB', 'email' => 'MyMnSaaB@superadmin.com',
        'password' => 'MyMnSaaB@accessadmin.com', 'full_name' => 'MyMn SaaB',
        'bio' => 'Founder and super administrator of ObscuraX. Custodian of the archive.',
        'title' => 'Founder / Super Administrator', 'created' => '-14 months',
    ],
    [
        'role' => 'admin', 'username' => 'hamza', 'email' => 'hamza@admin.com',
        'password' => 'H@mza@123.com', 'full_name' => 'Hamza',
        'bio' => 'Investigates ritual, folklore and the psychology of belief. Writes the occult desk.',
        'title' => 'Editor — Occult & Folklore', 'created' => '-13 months',
    ],
    [
        'role' => 'admin', 'username' => 'umar.bajwa', 'email' => 'umarbajwa@admin.com',
        'password' => 'Um@r@admin123.com', 'full_name' => 'Umar Bajwa',
        'bio' => 'Security researcher and educator. Covers ethical hacking, privacy and digital hygiene.',
        'title' => 'Editor — Cybersecurity', 'created' => '-13 months',
    ],
];

$userIds = [];
foreach ($accounts as $account) {
    $createdAt = date('Y-m-d H:i:s', strtotime($account['created']));
    $id = Database::insert('users', [
        'role_id' => $roleIds[$account['role']],
        'username' => $account['username'],
        'email' => $account['email'],
        'password_hash' => $hash($account['password']),
        'full_name' => $account['full_name'],
        'bio' => $account['bio'],
        'avatar' => null,
        'status' => 'active',
        'email_verified_at' => $createdAt,
        'activated_at' => $createdAt,
        'last_login_at' => null,
        'last_login_ip' => null,
        'last_activity_at' => null,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
    $userIds[$account['username']] = $id;

    Database::insert('admins', [
        'user_id' => $id,
        'title' => $account['title'],
        'department' => 'ObscuraX Editorial',
        'created_by' => $userIds['MyMnSaaB'] ?? null,
        'created_at' => $createdAt,
    ]);
}

// -------------------------------------------------------------- categories
$categories = [
    [
        'name' => 'Black Magic & Occult', 'slug' => 'black-magic-occult', 'accent_color' => '#ff2d4b',
        'icon' => 'bi-moon-stars', 'sort_order' => 1,
        'description' => 'Ritual, folklore, grimoires and the psychology of belief — examined with curiosity and care, never with sensationalism.',
        'cover_image' => 'assets/img/categories/black-magic-occult.jpg',
    ],
    [
        'name' => 'Sexual Health & Education', 'slug' => 'sexual-health-education', 'accent_color' => '#ff5fa8',
        'icon' => 'bi-heart-pulse', 'sort_order' => 2,
        'description' => 'Evidence-based, judgement-free writing on intimacy, consent, relationships and sexual wellbeing.',
        'cover_image' => 'assets/img/categories/sexual-health-education.jpg',
    ],
    [
        'name' => 'Hacking & Cybersecurity', 'slug' => 'hacking-cybersecurity', 'accent_color' => '#35b6ff',
        'icon' => 'bi-shield-lock', 'sort_order' => 3,
        'description' => 'Ethical hacking, digital privacy and the defensive craft of staying ahead of attackers.',
        'cover_image' => 'assets/img/categories/hacking-cybersecurity.jpg',
    ],
];
foreach ($categories as $category) {
    Database::insert('categories', $category);
}

// ---------------------------------------------------------------- settings
$settings = require __DIR__ . '/settings.php';
foreach ($settings as $key => $value) {
    Database::insert('website_settings', [
        'setting_key' => $key,
        'setting_value' => $value,
        'setting_group' => 'general',
    ]);
}

fwrite(STDOUT, sprintf(
    "Seeded: %d administrator accounts, %d desks, 0 articles. The archive is ready for its first investigation.\n",
    count($accounts),
    count($categories)
));
