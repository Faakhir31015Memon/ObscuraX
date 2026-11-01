<?php

declare(strict_types=1);

/**
 * Copy this file to config/db.php and fill in your database details.
 *
 * Hostinger: hPanel → Databases → MySQL Databases
 *   host  usually "localhost"
 *   name  usually prefixed, e.g. u123456789_obscurax
 *   user  usually the same as the database name
 *   pass  the password you set for that database user
 *
 * Local XAMPP: host 127.0.0.1, name obscurax, user root, empty password.
 *
 * config/db.php is gitignored so production passwords are never committed.
 */
return [
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'u000000000_obscurax',
    'user' => 'u000000000_obscurax',
    'pass' => 'your-database-password',
    'charset' => 'utf8mb4',
];
