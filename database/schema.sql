-- ============================================================
--  ObscuraX - MySQL / MariaDB schema
--  Charset: utf8mb4 | Engine: InnoDB
--  Run through database/install.php or import into an empty DB
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------ roles
CREATE TABLE IF NOT EXISTS roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(32)  NOT NULL,
    name        VARCHAR(64)  NOT NULL,
    description VARCHAR(191) NOT NULL DEFAULT '',
    level       TINYINT UNSIGNED NOT NULL DEFAULT 10,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ permissions
CREATE TABLE IF NOT EXISTS permissions (
    id         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug       VARCHAR(64) NOT NULL,
    name       VARCHAR(96) NOT NULL,
    group_name VARCHAR(48) NOT NULL DEFAULT 'general',
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_slug (slug),
    KEY idx_permissions_group (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       TINYINT UNSIGNED NOT NULL,
    permission_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ users
CREATE TABLE IF NOT EXISTS users (
    id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id              TINYINT UNSIGNED NOT NULL DEFAULT 1,
    username             VARCHAR(32)  NOT NULL,
    email                VARCHAR(190) NOT NULL,
    password_hash        VARCHAR(255) NOT NULL,
    full_name            VARCHAR(120) DEFAULT NULL,
    bio                  TEXT DEFAULT NULL,
    avatar               VARCHAR(255) DEFAULT NULL,
    website              VARCHAR(190) DEFAULT NULL,
    status               ENUM('active','inactive','banned','removed') NOT NULL DEFAULT 'inactive',
    email_verified_at    DATETIME DEFAULT NULL,
    activated_at         DATETIME DEFAULT NULL,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    password_changed_at  DATETIME DEFAULT NULL,
    last_login_at        DATETIME DEFAULT NULL,
    last_login_ip        VARCHAR(45) DEFAULT NULL,
    last_activity_at     DATETIME DEFAULT NULL,
    banned_at            DATETIME DEFAULT NULL,
    banned_reason        VARCHAR(255) DEFAULT NULL,
    removed_at           DATETIME DEFAULT NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username),
    KEY idx_users_role (role_id),
    KEY idx_users_status (status),
    KEY idx_users_created (created_at),
    KEY idx_users_activity (last_activity_at),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administrative profile data for accounts holding an admin role
CREATE TABLE IF NOT EXISTS admins (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    title       VARCHAR(96) NOT NULL DEFAULT 'Administrator',
    department  VARCHAR(96) DEFAULT NULL,
    created_by  BIGINT UNSIGNED DEFAULT NULL,
    is_disabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_user (user_id),
    CONSTRAINT fk_admins_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_admins_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ categories
CREATE TABLE IF NOT EXISTS categories (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name         VARCHAR(96) NOT NULL,
    slug         VARCHAR(120) NOT NULL,
    description  VARCHAR(500) NOT NULL DEFAULT '',
    accent_color VARCHAR(16) NOT NULL DEFAULT '#8b5cf6',
    icon         VARCHAR(48) NOT NULL DEFAULT 'bi-journal-richtext',
    cover_image  VARCHAR(255) DEFAULT NULL,
    sort_order   SMALLINT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ blogs
CREATE TABLE IF NOT EXISTS blogs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    author_id       BIGINT UNSIGNED NOT NULL,
    category_id     INT UNSIGNED NOT NULL,
    title           VARCHAR(191) NOT NULL,
    slug            VARCHAR(220) NOT NULL,
    excerpt         VARCHAR(500) NOT NULL DEFAULT '',
    content         LONGTEXT NOT NULL,
    featured_image  VARCHAR(255) DEFAULT NULL,
    status          ENUM('draft','published','unpublished') NOT NULL DEFAULT 'draft',
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    allow_comments  TINYINT(1) NOT NULL DEFAULT 1,
    seo_title       VARCHAR(191) DEFAULT NULL,
    meta_description VARCHAR(300) DEFAULT NULL,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    published_at    DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blogs_slug (slug),
    KEY idx_blogs_status (status, published_at),
    KEY idx_blogs_category (category_id, status),
    KEY idx_blogs_author (author_id),
    KEY idx_blogs_featured (is_featured, published_at),
    CONSTRAINT fk_blogs_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blogs_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ tags
CREATE TABLE IF NOT EXISTS tags (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(64) NOT NULL,
    slug       VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
    blog_id BIGINT UNSIGNED NOT NULL,
    tag_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (blog_id, tag_id),
    KEY idx_blog_tags_tag (tag_id),
    CONSTRAINT fk_blog_tags_blog FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
    CONSTRAINT fk_blog_tags_tag FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ engagement
CREATE TABLE IF NOT EXISTS likes (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_id    BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_likes_blog_user (blog_id, user_id),
    KEY idx_likes_user (user_id),
    CONSTRAINT fk_likes_blog FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_id    BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    content    TEXT NOT NULL,
    status     ENUM('published','hidden') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_blog (blog_id, status, created_at),
    KEY idx_comments_user (user_id),
    CONSTRAINT fk_comments_blog FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comment_replies (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    comment_id BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    content    TEXT NOT NULL,
    status     ENUM('published','hidden') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_replies_comment (comment_id, status, created_at),
    KEY idx_replies_user (user_id),
    CONSTRAINT fk_replies_comment FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ auth tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    email      VARCHAR(190) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    used_at    DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_resets_token (token_hash),
    KEY idx_password_resets_user (user_id, expires_at),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verifications (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    email       VARCHAR(190) NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email_verifications_token (token_hash),
    KEY idx_email_verifications_user (user_id, expires_at),
    CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(190) NOT NULL,
    ip_address   VARCHAR(45) NOT NULL,
    successful   TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_attempts_lookup (email, successful, attempted_at),
    KEY idx_login_attempts_ip (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ audit
CREATE TABLE IF NOT EXISTS user_activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED DEFAULT NULL,
    action      VARCHAR(64) NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    ip_address  VARCHAR(45) DEFAULT NULL,
    user_agent  VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_activity_user (user_id, created_at),
    KEY idx_user_activity_action (action),
    CONSTRAINT fk_user_activity_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id    BIGINT UNSIGNED DEFAULT NULL,
    action      VARCHAR(64) NOT NULL,
    target_type VARCHAR(48) DEFAULT NULL,
    target_id   BIGINT UNSIGNED DEFAULT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    ip_address  VARCHAR(45) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_activity_admin (admin_id, created_at),
    KEY idx_admin_activity_action (action),
    CONSTRAINT fk_admin_activity_admin FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ settings
CREATE TABLE IF NOT EXISTS website_settings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key   VARCHAR(96) NOT NULL,
    setting_value LONGTEXT NOT NULL,
    setting_group VARCHAR(48) NOT NULL DEFAULT 'general',
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (setting_key),
    KEY idx_settings_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ audience
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email           VARCHAR(190) NOT NULL,
    token           CHAR(64) NOT NULL,
    status          ENUM('pending','subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
    ip_address      VARCHAR(45) DEFAULT NULL,
    confirmed_at    DATETIME DEFAULT NULL,
    unsubscribed_at DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_newsletter_email (email),
    UNIQUE KEY uq_newsletter_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(96) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    subject    VARCHAR(191) NOT NULL,
    message    TEXT NOT NULL,
    status     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------ analytics
CREATE TABLE IF NOT EXISTS blog_views (
    id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_id   BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED DEFAULT NULL,
    ip_hash   CHAR(40) DEFAULT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_blog_views_blog (blog_id, viewed_at),
    KEY idx_blog_views_date (viewed_at),
    CONSTRAINT fk_blog_views_blog FOREIGN KEY (blog_id) REFERENCES blogs (id) ON DELETE CASCADE,
    CONSTRAINT fk_blog_views_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
