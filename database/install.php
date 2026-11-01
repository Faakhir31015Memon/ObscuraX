<?php

declare(strict_types=1);

/**
 * ObscuraX installer (CLI only).
 *
 *   php database/install.php            create schema + seed on a fresh database
 *   php database/install.php --fresh    drop and recreate the database first
 *
 * The installer reads database credentials from config/db.php. It never
 * writes passwords to disk.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This installer can only be run from the command line.');
}

$options = getopt('', ['fresh', 'help']);
if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php database/install.php [--fresh]\n");
    exit(0);
}

putenv('OBSCURAX_SKIP_DB=1');
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$dbConfig = config('db');
$dbName = (string) $dbConfig['name'];
$fresh = isset($options['fresh']);

fwrite(STDOUT, "ObscuraX installer\n------------------\n");
fwrite(STDOUT, "Database: {$dbName} @ {$dbConfig['host']}:{$dbConfig['port']}\n");

try {
    $server = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['port']),
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to MySQL: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Check the credentials in config/db.php and make sure MySQL is running.\n");
    exit(1);
}

if ($fresh) {
    fwrite(STDOUT, "Dropping database `{$dbName}` (as requested with --fresh)...\n");
    $server->exec("DROP DATABASE IF EXISTS `{$dbName}`");
}

try {
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    if ($fresh) {
        fwrite(STDERR, "Could not create the database: " . $e->getMessage() . "\n");
        exit(1);
    }
    // Shared hosts such as Hostinger create the database in the control panel
    // and the account usually has no CREATE DATABASE privilege.
    fwrite(STDOUT, "Using the existing database `{$dbName}`.\n");
}
fwrite(STDOUT, "Database ready.\n");

$pdo = Database::pdo();

$existing = (int) $pdo->query(
    'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ' . $pdo->quote($dbName)
)->fetchColumn();

if ($existing > 0 && !$fresh) {
    fwrite(STDERR, "This database already contains {$existing} table(s).\n");
    fwrite(STDERR, "Run with --fresh to drop and rebuild it, or point DB_NAME at an empty database.\n");
    exit(1);
}

// ------------------------------------------------------------- schema
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "schema.sql not found.\n");
    exit(1);
}
$schema = preg_replace('/^\s*--.*$/m', '', $schema) ?? $schema;
$statements = preg_split('/;\s*(?:\r?\n)+/', $schema) ?: [];

$count = 0;
foreach ($statements as $statement) {
    $statement = trim($statement);
    if ($statement === '' || $statement === ';') {
        continue;
    }
    $pdo->exec($statement);
    $count++;
}
fwrite(STDOUT, "Created {$count} schema statements.\n");

// ------------------------------------------------------------- seed
$pdo->beginTransaction();
try {
    require __DIR__ . '/seed.php';
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Seeding failed: " . $e->getMessage() . "\n");
    exit(1);
}

// ------------------------------------------------------------ runtime dirs
foreach (['storage/logs', 'storage/mail', 'uploads/blogs', 'uploads/avatars', 'uploads/branding', 'uploads/hero'] as $dir) {
    $path = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

fwrite(STDOUT, <<<'TXT'

Installation complete.

Super administrator
  Email:    MyMnSaaB@superadmin.com
  Password: MyMnSaaB@accessadmin.com

Administrators
  hamza@admin.com        / H@mza@123.com
  umarbajwa@admin.com    / Um@r@admin123.com

Passwords can be changed at any time from Admin -> Change password.
The archive starts empty: no demo articles, readers, comments or likes.
Sign in and publish the first investigation from the admin panel.

Next: start Apache + MySQL in XAMPP and open the project in your browser.

TXT);
