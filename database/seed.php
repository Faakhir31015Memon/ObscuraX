<?php

declare(strict_types=1);

/**
 * ObscuraX seeder.
 * Called by database/install.php. Safe to run only on a fresh database.
 */

use App\Core\Database;
use App\Models\Tag;

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
        'must_change_password' => 1,
        'last_login_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'last_login_ip' => '127.0.0.1',
        'last_activity_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
    $userIds[$account['username']] = $id;

    if ($account['role'] !== 'user') {
        Database::insert('admins', [
            'user_id' => $id,
            'title' => $account['title'],
            'department' => 'ObscuraX Editorial',
            'created_by' => $userIds['MyMnSaaB'] ?? null,
            'created_at' => $createdAt,
        ]);
    }
}

// Demo readers so engagement and analytics have real data to display.
$readers = [
    ['nina.reader', 'nina@example.com', 'Nina Qureshi', '-9 months', 'active', '-3 days'],
    ['omar.reader', 'omar@example.com', 'Omar Siddiqui', '-8 months', 'active', '-6 days'],
    ['sara.reader', 'sara@example.com', 'Sara Malik', '-6 months', 'active', '-1 day'],
    ['dan.reader', 'dan@example.com', 'Daniel Ferns', '-5 months', 'active', '-11 days'],
    ['lea.reader', 'lea@example.com', 'Lea Ahsan', '-3 months', 'active', '-4 hours'],
    ['taha.reader', 'taha@example.com', 'Taha Raza', '-2 months', 'active', '-20 days'],
    ['noah.pending', 'noah@example.com', 'Noah Iqbal', '-18 days', 'inactive', null],
    ['zara.banned', 'zara@example.com', 'Zara Khan', '-4 months', 'banned', '-30 days'],
    ['imran.removed', 'imran@example.com', 'Imran Shah', '-7 months', 'removed', '-40 days'],
];

$readerIds = [];
foreach ($readers as [$username, $email, $fullName, $created, $status, $lastLogin]) {
    $createdAt = date('Y-m-d H:i:s', strtotime($created));
    $verified = $status === 'active' || $status === 'banned' || $status === 'removed';
    $id = Database::insert('users', [
        'role_id' => $roleIds['user'],
        'username' => $username,
        'email' => $email,
        'password_hash' => $hash('Reader@123'),
        'full_name' => $fullName,
        'bio' => 'Reader of strange things and long articles.',
        'avatar' => null,
        'status' => $status,
        'email_verified_at' => $verified ? $createdAt : null,
        'activated_at' => $verified ? date('Y-m-d H:i:s', strtotime($created . ' +1 day')) : null,
        'must_change_password' => 0,
        'last_login_at' => $lastLogin ? date('Y-m-d H:i:s', strtotime($lastLogin)) : null,
        'last_login_ip' => '127.0.0.1',
        'last_activity_at' => in_array($status, ['active'], true) ? date('Y-m-d H:i:s', strtotime('-2 hours')) : null,
        'banned_at' => $status === 'banned' ? date('Y-m-d H:i:s', strtotime('-25 days')) : null,
        'banned_reason' => $status === 'banned' ? 'Repeated spam in comment threads.' : null,
        'removed_at' => $status === 'removed' ? date('Y-m-d H:i:s', strtotime('-35 days')) : null,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
    $readerIds[$username] = $id;
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
$categoryIds = [];
foreach ($categories as $category) {
    $categoryIds[$category['slug']] = Database::insert('categories', $category);
}

// ------------------------------------------------------------------- blogs
$authorMap = [
    'hamza' => $userIds['hamza'],
    'umar' => $userIds['umar.bajwa'],
    'mymn' => $userIds['MyMnSaaB'],
];

$blogs = require __DIR__ . '/content.php';

$blogIds = [];
foreach ($blogs as $index => $blog) {
    $publishedAt = date('Y-m-d H:i:s', strtotime($blog['date'] . ' 09:30:00'));
    $id = Database::insert('blogs', [
        'author_id' => $authorMap[$blog['author']],
        'category_id' => $categoryIds[$blog['category']],
        'title' => $blog['title'],
        'slug' => $blog['slug'],
        'excerpt' => $blog['excerpt'],
        'content' => $blog['content'],
        'featured_image' => $blog['image'],
        'status' => 'published',
        'is_featured' => $blog['featured'] ? 1 : 0,
        'allow_comments' => 1,
        'seo_title' => $blog['title'] . ' | ObscuraX',
        'meta_description' => $blog['excerpt'],
        'view_count' => 0,
        'published_at' => $publishedAt,
        'created_at' => date('Y-m-d H:i:s', strtotime($blog['date'] . ' -2 days')),
        'updated_at' => $publishedAt,
    ]);
    $blogIds[$blog['slug']] = $id;
    Tag::sync($id, implode(', ', $blog['tags']));
}

// A draft and an unpublished piece so the CMS shows every state.
$draftId = Database::insert('blogs', [
    'author_id' => $authorMap['hamza'],
    'category_id' => $categoryIds['black-magic-occult'],
    'title' => 'The Long Shadow of the Witch Trials (working draft)',
    'slug' => 'long-shadow-witch-trials',
    'excerpt' => 'A working draft mapping local trial records to modern folklore tourism.',
    'content' => '<p>Draft notes. This article is not published yet.</p>',
    'featured_image' => 'assets/img/blogs/occult-3.jpg',
    'status' => 'draft',
    'is_featured' => 0,
    'allow_comments' => 1,
    'seo_title' => null,
    'meta_description' => null,
    'view_count' => 0,
    'published_at' => null,
    'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
    'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
]);
Tag::sync($draftId, 'history, folklore, research');

$unpublishedId = Database::insert('blogs', [
    'author_id' => $authorMap['umar'],
    'category_id' => $categoryIds['hacking-cybersecurity'],
    'title' => 'Zero Trust for One-Person Businesses',
    'slug' => 'zero-trust-one-person-business',
    'excerpt' => 'Unpublished while the threat model is reviewed.',
    'content' => '<p>Under review.</p>',
    'featured_image' => 'assets/img/blogs/cyber-4.jpg',
    'status' => 'unpublished',
    'is_featured' => 0,
    'allow_comments' => 1,
    'seo_title' => null,
    'meta_description' => null,
    'view_count' => 0,
    'published_at' => date('Y-m-d H:i:s', strtotime('-40 days')),
    'created_at' => date('Y-m-d H:i:s', strtotime('-48 days')),
    'updated_at' => date('Y-m-d H:i:s', strtotime('-35 days')),
]);

// ------------------------------------------------------------- engagement
mt_srand(20261101);
$activeReaders = ['nina.reader', 'omar.reader', 'sara.reader', 'dan.reader', 'lea.reader', 'taha.reader'];

$likeRows = 0;
$likeBatch = [];
foreach ($blogIds as $slug => $blogId) {
    $count = mt_rand(4, count($activeReaders));
    $picked = (array) array_rand(array_flip($activeReaders), $count);
    foreach ($picked as $reader) {
        $likeBatch[] = [$blogId, $readerIds[$reader], date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 60) . ' days'))];
    }
}
foreach (array_chunk($likeBatch, 100) as $chunk) {
    $values = [];
    $params = [];
    foreach ($chunk as $row) {
        $values[] = '(?, ?, ?)';
        array_push($params, ...$row);
    }
    Database::query('INSERT IGNORE INTO likes (blog_id, user_id, created_at) VALUES ' . implode(',', $values), $params);
    $likeRows += count($chunk);
}

// views across the last 120 days so charts and counters look lived-in
$viewBatch = [];
foreach ($blogIds as $blogId) {
    $totalViews = mt_rand(120, 480);
    for ($i = 0; $i < $totalViews; $i++) {
        $viewBatch[] = [
            $blogId,
            mt_rand(0, 100) > 55 ? $readerIds[$activeReaders[array_rand($activeReaders)]] : null,
            date('Y-m-d H:i:s', strtotime('-' . mt_rand(0, 120) . ' days -' . mt_rand(0, 900) . ' minutes')),
        ];
    }
}
foreach (array_chunk($viewBatch, 200) as $chunk) {
    $values = [];
    $params = [];
    foreach ($chunk as $row) {
        $values[] = '(?, ?, ?)';
        array_push($params, ...$row);
    }
    Database::query('INSERT INTO blog_views (blog_id, user_id, viewed_at) VALUES ' . implode(',', $values), $params);
}
Database::query('UPDATE blogs SET view_count = (SELECT COUNT(*) FROM blog_views WHERE blog_views.blog_id = blogs.id)');

// comments with replies
$commentPlan = [
    'psychology-of-ritual' => [
        ['nina.reader', 'The point about ritual as a container for anxiety is exactly what I needed to read today. It explains so much of what I see in community practice.', ['hamza' => 'That containment idea is the heart of the piece — thank you for reading it that way.']],
        ['dan.reader', 'I would love a follow-up on how these practices show up in grief rituals specifically.', []],
    ],
    'grimoire-to-archive' => [
        ['omar.reader', 'The printing press as a piracy technology is a framing I had never considered.', ['hamza' => 'That framing is borrowed from book historians — I recommend the sources in the closing section.']],
        ['lea.reader', 'Fascinating that censorship accelerated the spread rather than stopping it.', []],
    ],
    'consent-as-a-skill' => [
        ['sara.reader', 'Consent as a practice rather than a one-time question is such a useful reframe. Sharing this with my partner.', ['hamza' => 'Glad it landed. The script examples near the end are designed to be adapted, not recited.']],
        ['taha.reader', 'The section on checking in mid-way should be taught in schools honestly.', []],
        ['nina.reader', 'Clear, kind and practical. More of this please.', []],
    ],
    'sexual-health-checkups' => [
        ['nina.reader', 'The explanation of window periods finally made testing schedules make sense to me.', []],
        ['dan.reader', 'Would you cover cost and access in a follow-up? That is the part that trips people up locally.', ['hamza' => 'Adding it to the planning board — access barriers deserve their own piece.']],
    ],
    'passkeys-explained' => [
        ['omar.reader', 'Switched my main accounts to passkeys after reading this. The section on recovery keys saved me a headache.', ['umar' => 'Glad to hear it. Keep a copy of that recovery flow somewhere offline.']],
        ['sara.reader', 'The comparison table is the clearest I have seen anywhere.', []],
    ],
    'phishing-economy' => [
        ['dan.reader', 'Business email compromise getting its own article is overdue. The invoice thread example is painfully realistic.', ['umar' => 'It is the attack people still fall for, because it needs no exploit at all.']],
    ],
    'scoping-ethical-hacking' => [
        ['taha.reader', 'So many people skip scoping entirely. The checklist format is genuinely useful.', ['umar' => 'A scope document is a legal shield as much as a technical one.']],
    ],
    'tarot-symbolism-field-guide' => [
        ['lea.reader', 'A sceptical reading that still respects the imagery — this is the tone the whole category needs.', []],
    ],
];

foreach ($commentPlan as $slug => $threads) {
    if (!isset($blogIds[$slug])) {
        continue;
    }
    foreach ($threads as $thread) {
        [$reader, $text, $replies] = $thread;
        $commentId = Database::insert('comments', [
            'blog_id' => $blogIds[$slug],
            'user_id' => $readerIds[$reader],
            'content' => $text,
            'status' => 'published',
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(2, 26) . ' days')),
        ]);
        foreach ($replies as $adminUser => $replyText) {
            Database::insert('comment_replies', [
                'comment_id' => $commentId,
                'user_id' => $authorMap[$adminUser],
                'content' => $replyText,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 20) . ' days')),
            ]);
        }
    }
}

// ------------------------------------------------------------- newsletter
foreach ([
    'nina@example.com', 'omar@example.com', 'sara@example.com',
    'dan@example.com', 'lea@example.com', 'taha@example.com', 'curious@example.com',
] as $index => $email) {
    Database::insert('newsletter_subscribers', [
        'email' => $email,
        'token' => bin2hex(random_bytes(32)),
        'status' => $index === 6 ? 'unsubscribed' : 'subscribed',
        'ip_address' => '127.0.0.1',
        'confirmed_at' => date('Y-m-d H:i:s', strtotime('-' . (10 + $index * 9) . ' days')),
        'unsubscribed_at' => $index === 6 ? date('Y-m-d H:i:s', strtotime('-2 days')) : null,
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . (10 + $index * 9) . ' days')),
    ]);
}

// ---------------------------------------------------------------- activity
foreach ($activeReaders as $index => $reader) {
    Database::insert('user_activity_logs', [
        'user_id' => $readerIds[$reader],
        'action' => 'login',
        'description' => 'Signed in from 127.0.0.1',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Seed data',
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . ($index + 1) . ' hours')),
    ]);
}

foreach ([
    ['hamza', 'blog.publish', 'blog', $blogIds['psychology-of-ritual'] ?? null, 'Published "The Psychology of Ritual"'],
    ['umar', 'blog.publish', 'blog', $blogIds['passkeys-explained'] ?? null, 'Published "Passwordless Is Coming"'],
    ['mymn', 'settings.update', 'settings', null, 'Updated hero section copy'],
    ['hamza', 'comment.reply', 'comment', null, 'Replied to a reader on the occult desk'],
] as [$adminUser, $action, $targetType, $targetId, $description]) {
    Database::insert('admin_activity_logs', [
        'admin_id' => $authorMap[$adminUser],
        'action' => $action,
        'target_type' => $targetType,
        'target_id' => $targetId,
        'description' => $description,
        'ip_address' => '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 20) . ' days')),
    ]);
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
    "Seeded: %d users, %d categories, %d articles, %d likes, %d views.\n",
    count($accounts) + count($readers),
    count($categories),
    count($blogIds) + 2,
    $likeRows,
    count($viewBatch)
));
