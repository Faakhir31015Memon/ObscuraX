-- ============================================================
--  ObscuraX - full install for Hostinger / phpMyAdmin
--  Import this ONE file into the empty database you created.
--  It creates all tables and seeds roles, permissions, the three
--  administrator accounts, categories and default settings.
--  Archive content (articles, readers, comments, likes) stays empty.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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

-- ------------------------------------------------------------ seed data
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `slug`, `name`, `description`, `level`, `created_at`) VALUES (1,'user','Reader','Can read, like and comment on articles.',10,'2026-11-01 20:28:51'),
(2,'admin','Administrator','Publishes and manages their own articles.',50,'2026-11-01 20:28:51'),
(3,'super_admin','Super Administrator','Full control over the platform.',100,'2026-11-01 20:28:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `slug`, `name`, `group_name`) VALUES (1,'blog.create','Create articles','content'),
(2,'blog.edit.own','Edit own articles','content'),
(3,'blog.edit.any','Edit any article','content'),
(4,'blog.publish','Publish / unpublish articles','content'),
(5,'blog.feature','Feature articles','content'),
(6,'blog.delete','Delete articles','content'),
(7,'category.manage','Manage categories','taxonomy'),
(8,'tag.manage','Manage tags','taxonomy'),
(9,'comment.view','View comments','community'),
(10,'comment.reply','Reply to comments','community'),
(11,'comment.moderate','Hide / restore comments','community'),
(12,'comment.delete','Delete comments','community'),
(13,'hero.manage','Manage hero section','appearance'),
(14,'user.manage','Manage users','administration'),
(15,'admin.manage','Manage administrators','administration'),
(16,'settings.manage','Manage website settings','administration'),
(17,'analytics.view','View analytics and reports','administration'),
(18,'logs.view','View audit logs','administration'),
(19,'newsletter.manage','Manage newsletter subscribers','administration'),
(20,'message.manage','Manage contact messages','administration'),
(21,'like.manage','Manage likes','community');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;

/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (2,1),
(2,2),
(2,4),
(2,5),
(2,7),
(2,8),
(2,9),
(2,10),
(2,11),
(2,13),
(3,1),
(3,2),
(3,3),
(3,4),
(3,5),
(3,6),
(3,7),
(3,8),
(3,9),
(3,10),
(3,11),
(3,12),
(3,13),
(3,14),
(3,15),
(3,16),
(3,17),
(3,18),
(3,19),
(3,20),
(3,21);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `bio`, `avatar`, `website`, `status`, `email_verified_at`, `activated_at`, `must_change_password`, `password_changed_at`, `last_login_at`, `last_login_ip`, `last_activity_at`, `banned_at`, `banned_reason`, `removed_at`, `created_at`, `updated_at`) VALUES (1,3,'MyMnSaaB','MyMnSaaB@superadmin.com','$2y$12$7CxKQkLykKSzw0HQ5zFtq.tvyvfg2bpUFoXTw7WvNDE/z.O241RUm','MyMn SaaB','Founder and super administrator of ObscuraX. Custodian of the archive.',NULL,NULL,'active','2025-09-01 20:28:51','2025-09-01 20:28:51',0,NULL,'2026-11-01 21:26:30','::1','2026-11-01 21:26:30',NULL,NULL,NULL,'2025-09-01 20:28:51','2026-11-01 21:26:30'),
(2,2,'hamza','hamza@admin.com','$2y$12$Zm.kQLEkXTA56sBj4Xx31.QMD4h7w/824JQb1PsFcut.eua0xdzzi','Hamza','Investigates ritual, folklore and the psychology of belief. Writes the occult desk.',NULL,NULL,'active','2025-10-01 20:28:51','2025-10-01 20:28:51',0,NULL,'2026-11-01 21:27:47','::1','2026-11-01 21:27:47',NULL,NULL,NULL,'2025-10-01 20:28:51','2026-11-01 21:27:47'),
(3,2,'umar.bajwa','umarbajwa@admin.com','$2y$12$9NDZJ8QXWMotiSjt/N2EruP3Fb0ZIZqSF/jwSHi8u1Ig7KR0bDroC','Umar Bajwa','Security researcher and educator. Covers ethical hacking, privacy and digital hygiene.',NULL,NULL,'active','2025-10-01 20:28:52','2025-10-01 20:28:52',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-01 20:28:52','2026-11-01 21:12:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` (`id`, `user_id`, `title`, `department`, `created_by`, `is_disabled`, `created_at`) VALUES (1,1,'Founder / Super Administrator','ObscuraX Editorial',1,0,'2025-09-01 20:28:51'),
(2,2,'Editor — Occult & Folklore','ObscuraX Editorial',1,0,'2025-10-01 20:28:51'),
(3,3,'Editor — Cybersecurity','ObscuraX Editorial',1,0,'2025-10-01 20:28:52');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;

/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon`, `cover_image`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (1,'Black Magic & Occult','black-magic-occult','Ritual, folklore, grimoires and the psychology of belief — examined with curiosity and care, never with sensationalism.','#ff2d4b','bi-moon-stars','assets/img/categories/black-magic-occult.jpg',1,1,'2026-11-01 20:28:52','2026-11-01 20:28:52'),
(2,'Sexual Health & Education','sexual-health-education','Evidence-based, judgement-free writing on intimacy, consent, relationships and sexual wellbeing.','#ff5fa8','bi-heart-pulse','assets/img/categories/sexual-health-education.jpg',2,1,'2026-11-01 20:28:52','2026-11-01 20:28:52'),
(3,'Hacking & Cybersecurity','hacking-cybersecurity','Ethical hacking, digital privacy and the defensive craft of staying ahead of attackers.','#35b6ff','bi-shield-lock','assets/img/categories/hacking-cybersecurity.jpg',3,1,'2026-11-01 20:28:52','2026-11-01 20:28:52');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

/*!40000 ALTER TABLE `website_settings` DISABLE KEYS */;
INSERT INTO `website_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `updated_at`) VALUES (1,'site_name','ObscuraX','general','2026-11-01 20:28:52'),
(2,'site_tagline','Explore the Unknown. Discover Beyond the Ordinary.','general','2026-11-01 20:28:52'),
(3,'site_description','ObscuraX publishes long-form investigations into black magic and the occult, sexual health and education, and ethical hacking and cybersecurity.','general','2026-11-01 20:28:52'),
(4,'site_logo','','general','2026-11-01 20:28:52'),
(5,'site_favicon','','general','2026-11-01 20:28:52'),
(6,'contact_email','hello@obscurax.local','general','2026-11-01 20:28:52'),
(7,'footer_about','An independent archive of the strange, the human and the digital. Research, not rumour.','general','2026-11-01 20:28:52'),
(8,'hero_badge','Issue 01 — The Unknown','general','2026-11-01 20:28:52'),
(9,'hero_title','Explore the Unknown. Discover Beyond the Ordinary.','general','2026-11-01 20:28:52'),
(10,'hero_subtitle','ObscuraX investigates black magic and the occult, sexual health and education, and ethical hacking — long-form research for people who prefer questions to comfortable answers.','general','2026-11-01 20:28:52'),
(11,'hero_primary_label','Start reading','general','2026-11-01 20:28:52'),
(12,'hero_primary_url','/blogs','general','2026-11-01 20:28:52'),
(13,'hero_secondary_label','Browse categories','general','2026-11-01 20:28:52'),
(14,'hero_secondary_url','#categories','general','2026-11-01 20:28:52'),
(15,'hero_image','assets/img/hero-default.jpg','general','2026-11-01 20:28:52'),
(16,'social_x','https://x.com/','general','2026-11-01 20:28:52'),
(17,'social_instagram','','general','2026-11-01 20:28:52'),
(18,'social_youtube','','general','2026-11-01 20:28:52'),
(19,'social_linkedin','','general','2026-11-01 20:28:52'),
(20,'social_github','','general','2026-11-01 20:28:52'),
(21,'seo_default_title','ObscuraX — Explore the Unknown','general','2026-11-01 20:28:52'),
(22,'seo_default_description','Long-form investigations into the occult, sexual health and cybersecurity.','general','2026-11-01 20:28:52'),
(23,'page_about','<p class=\"lead\">ObscuraX is an independent knowledge platform for people who prefer questions to comfortable answers. We publish long-form research across three deliberately unusual fields: black magic and the occult, sexual health and education, and ethical hacking and cybersecurity.</p>\n\n<h2>Why these three subjects?</h2>\n<p>They share a quality that makes them easy to sensationalise and difficult to write about honestly. Each sits at the edge of mainstream conversation, each attracts an enormous amount of misinformation, and each matters more than polite company usually admits. Put together, they are a study of how fear, desire and control actually work.</p>\n<p>We are not here to make you believe anything. We are here to make the subject legible — with sources, context and a clear line between what is documented and what is claimed.</p>\n\n<h2>How we work</h2>\n<ul>\n<li><strong>Evidence first.</strong> Claims about research, medicine or security are checked against primary sources. Where evidence is thin, we say so.</li>\n<li><strong>No fear marketing.</strong> We do not trade in panic, \"ancient secrets\" or \"what they don\'t want you to know\".</li>\n<li><strong>Practical value.</strong> Every article should leave you able to do something differently: test more thoughtfully, communicate more clearly, secure a system more sensibly.</li>\n<li><strong>Respect for people.</strong> We write about communities and readers with care. We do not turn living traditions into props.</li>\n</ul>\n\n<h2>Editorial standards</h2>\n<p>Articles are written and edited by our small editorial team. Corrections are published openly and editors are named on every piece. We do not accept payment for coverage, and we clearly label any future sponsored work.</p>\n\n<h2>Health and security disclaimers</h2>\n<p>Nothing on ObscuraX is medical advice, a diagnosis, or a substitute for a qualified professional. Sexual health content is educational and inclusive, and clinical questions belong with a clinician. Cybersecurity content is for defenders, students and authorised testing only — never test a system you do not own or have written permission to assess.</p>\n\n<h2>Get in touch</h2>\n<p>Story tips, corrections and collaborations are welcome through the contact page. If you are reporting something sensitive, tell us how you would like to be credited.</p>','general','2026-11-01 20:28:52'),
(24,'page_privacy','<p class=\"lead\">This policy explains what ObscuraX collects, why, and how you can control it. It is written to be read, not to be survived.</p>\n\n<h2>What we collect</h2>\n<ul>\n<li><strong>Account data.</strong> Your username, email address, hashed password, optional profile details and avatar.</li>\n<li><strong>Activity data.</strong> Articles you like, comments and replies you post, and the dates of account creation, activation and sign-in. This data powers your dashboard and our moderation.</li>\n<li><strong>Technical data.</strong> IP address, browser user agent, and aggregated page views. Page views are stored with a hashed IP so we can measure readership without keeping a raw log.</li>\n<li><strong>Messages.</strong> Anything you send through the contact form or newsletter signup.</li>\n</ul>\n\n<h2>What we do not do</h2>\n<p>We do not sell personal data. We do not run advertising trackers. We do not use your email address for anything other than account notices and the newsletter you explicitly requested.</p>\n\n<h2>Cookies and sessions</h2>\n<p>We use a single first-party session cookie to keep you signed in and to protect forms against cross-site request forgery. It is HTTP-only and SameSite protected. There is no analytics or advertising cookie.</p>\n\n<h2>Passwords and security</h2>\n<p>Passwords are stored only as salted hashes using PHP\'s password_hash(). We never store or transmit plain-text passwords. Password reset tokens are single-use, hashed in the database, and expire after one hour.</p>\n\n<h2>Your rights</h2>\n<p>You can review and update your profile at any time, change your password, and ask us to remove your account. To request removal or an export of your account data, contact us using the email published on the contact page.</p>\n\n<h2>Retention</h2>\n<p>Account data is retained while your account exists. Security and moderation logs are retained for up to 12 months. Removed accounts are soft-deleted so that comment threads remain readable, with identifying details retained only as required for audit.</p>\n\n<h2>Changes</h2>\n<p>If this policy changes materially, the update date will change and signed-in readers will see a notice. Continued use after a change means you accept the updated policy.</p>\n\n<h2>Contact</h2>\n<p>Privacy questions can be sent through the contact form, marked “Privacy”.</p>','general','2026-11-01 20:28:52'),
(25,'page_terms','<p class=\"lead\">By using ObscuraX you agree to these terms. They exist to keep the platform safe, readable and honest.</p>\n\n<h2>Accounts</h2>\n<ul>\n<li>You must provide accurate information and a working email address, and verify it before signing in.</li>\n<li>You are responsible for keeping your password secure. Do not share account access.</li>\n<li>Administrators may deactivate, suspend or remove accounts that break these terms. You may appeal by contacting us.</li>\n</ul>\n\n<h2>Acceptable use</h2>\n<p>You may not use ObscuraX to post spam, harassment, hate speech, sexual content involving minors, or private information about others. You may not attempt to access accounts or administrative areas you are not authorised to use, probe the platform for vulnerabilities without permission, or interfere with other readers\' access.</p>\n\n<h2>Comments and community</h2>\n<p>Comments are moderated. We may hide or remove content that is abusive, off-topic, promotional, or that shares personal data. Repeated violations can lead to permanent loss of commenting privileges.</p>\n\n<h2>Intellectual property</h2>\n<p>Articles, artwork and branding on ObscuraX are owned by the platform or licensed to it. You may quote short excerpts with attribution and a link. Republishing full articles requires written permission.</p>\n\n<h2>Educational disclaimers</h2>\n<p>Content about the occult is cultural and historical commentary, not supernatural assurance. Content about sexual health is educational and is not medical advice, diagnosis or treatment. Content about security is for authorised, defensive and educational use only. You are responsible for complying with the laws of your jurisdiction.</p>\n\n<h2>Newsletter</h2>\n<p>Subscribing is optional. Every email includes a one-click unsubscribe link, and you can also unsubscribe from any newsletter email.</p>\n\n<h2>Availability and changes</h2>\n<p>The platform is provided “as is”. We aim for high availability but do not guarantee uninterrupted service. We may update these terms; continued use after an update constitutes acceptance.</p>\n\n<h2>Contact</h2>\n<p>Questions about these terms can be sent through the contact page.</p>','general','2026-11-01 20:28:52');
/*!40000 ALTER TABLE `website_settings` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;
-- End of ObscuraX install
