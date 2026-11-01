# ObscuraX

ObscuraX is a complete blogging and knowledge platform exploring **Black Magic & Occult**, **Sexual Health & Education**, and **Hacking & Cybersecurity**. It ships with a premium dark theme (black / purple / red / silver), a full CMS, reader engagement tools, a three-tier administration system, and interactive analytics.

Built with **Core PHP 8, MySQL/MariaDB, HTML5, CSS3, JavaScript, and Bootstrap 5** — no framework, no build step.

---

## Highlights

- **Public site** — home, all articles, article detail, categories, tags, search, author pages, about, contact, privacy, terms, sitemap, robots.
- **Reader accounts** — registration with email verification, secure login, likes, threaded comments/replies, profile editing, avatar upload, liked-articles and comment history, password change.
- **Editorial CMS** — Quill rich-text editor with inline image upload, slug management, featured images, categories, tags, SEO title/meta, draft/publish/unpublish, featured toggle, search/filter/pagination, related articles.
- **Admin panel (2 editors)** — create and manage *their own* articles, reply to and moderate comments, manage categories and tags, customise the homepage hero. They cannot manage users or other administrators.
- **Super admin panel** — user lifecycle (activate/deactivate/ban/unban/mark removed), administrator management, all articles/likes/comments, categories, tags, newsletter, contact messages, branding (logo & favicon), hero, static pages, social links, full activity logs, analytics and CSV report exports.
- **Analytics** — users by status, registrations, monthly/yearly series, most viewed/liked/commented articles, category breakdown, most active readers, last logins, interactive Chart.js charts, annual reports and exports.
- **Security** — PDO prepared statements everywhere, CSRF tokens on every POST, output escaping, bcrypt password hashing, secure single-use reset/verification tokens, login throttling, session fingerprinting and regeneration, RBAC, upload MIME validation, strict CSP with per-request nonces, administrative audit trails.

---

## Requirements

| Component | Version |
|-----------|---------|
| PHP | 8.1+ (tested on PHP 8.5) with `pdo_mysql`, `fileinfo`, `mbstring`, `openssl` |
| MySQL / MariaDB | MySQL 8 or MariaDB 10.4+ |
| Web server | Apache (XAMPP recommended) or PHP's built-in server |

---

## Installation with XAMPP

1. **Copy the project** into your XAMPP web root:
   ```
   C:\xampp\htdocs\ObscuraX
   ```

2. **Create the environment file** (if `.env` is missing):
   ```bash
   copy .env.example .env
   ```
   Then set a unique `APP_KEY` (any random 32+ character string) and your database credentials.

3. **Create and seed the database** from a terminal:
   ```bash
   C:\xampp\php\php.exe database\install.php --fresh
   ```
   This drops and recreates the `obscurax` database, applies [database/schema.sql](database/schema.sql), and seeds roles, permissions, settings, categories, tags, demo articles, and demo readers.
   *(The schema file can also be imported directly through phpMyAdmin, but the installer is required to seed the default accounts.)*

4. **Start Apache and MySQL** in the XAMPP control panel, then open:
   ```
   http://localhost/ObscuraX/
   ```

5. Ensure PHP can write to `uploads/` and `storage/` (these hold uploaded images and the mail log).

### Alternative: PHP built-in server

```bash
C:\xampp\php\php.exe -S 127.0.0.1:8000 router.php
```

---

## Default accounts

All three administrator accounts are flagged **`must_change_password = 1`**. After their first sign-in they are redirected to a mandatory password-change screen before they can open any other admin page.

| Role | Name | Email | Initial password |
|------|------|-------|------------------|
| Super Admin | MyMn SaaB | `MyMnSaaB@superadmin.com` | `MyMnSaaB@accessadmin.com` |
| Admin | Hamza | `hamza@admin.com` | `H@mza@123.com` |
| Admin | Umar Bajwa | `umarbajwa@admin.com` | `Um@r@admin123.com` |
| Reader (demo) | Nina Qureshi | `nina@example.com` | `Reader@123` |

Demo reader accounts: `nina.reader`, `omar.reader`, `sara.reader`, `dan.reader`, `lea.reader`, `taha.reader` (all `Reader@123`). Seeded articles are sample editorial content and can be edited or deleted from the admin panel.

---

## Mail & verification links

The default mail driver is `log`, which writes each message as an HTML file under `storage/mail/`. No SMTP server is required for local testing.

When `APP_DEBUG=true` and `MAIL_DRIVER=log`, password-reset and email-verification links are also displayed on the relevant screen (and stored in the session), so you can complete those flows without opening the mail log.

To send real mail, set the `MAIL_*` values in `.env` (`MAIL_DRIVER=smtp`) and point them at your SMTP provider.

---

## Roles at a glance

| Capability | Reader | Admin | Super Admin |
|------------|:------:|:-----:|:-----------:|
| Read, like, comment, reply | ✓ | ✓ | ✓ |
| Edit own profile / password | ✓ | ✓ | ✓ |
| Create articles | — | ✓ | ✓ |
| Edit articles | — | own only | all |
| Publish / unpublish / feature | — | publish own | all |
| Reply to & moderate comments | — | ✓ | ✓ |
| Manage categories, tags | — | ✓ | ✓ |
| Homepage hero | — | ✓ | ✓ |
| Manage readers (activate/deactivate/ban/unban/remove) | — | — | ✓ |
| Manage administrators | — | — | ✓ |
| Branding, pages, social, settings | — | — | ✓ |
| Analytics, reports, activity logs | — | — | ✓ |

Authorization is enforced server-side through middleware and permission checks; editing URLs or POSTing directly does not grant extra access.

---

## Project structure

```
ObscuraX/
├── app/
│   ├── Controllers/          # Public, account and admin controllers
│   ├── Core/                 # Router, Database, Auth, Csrf, Session, Upload, Mailer, ...
│   ├── Middleware/           # Auth / admin / super / permission / password guards
│   ├── Models/               # User, Blog, Comment, Like, Category, Tag, Stat, ...
│   ├── bootstrap.php         # Autoloader, config, security headers, error handling
│   └── helpers.php
├── config/config.php         # Reads .env
├── database/
│   ├── schema.sql            # Full table structure
│   ├── seed.php              # Demo data
│   └── install.php           # CLI installer (--fresh)
├── routes/web.php            # All application routes
├── views/                    # Layouts, public pages, auth, admin, emails
├── assets/
│   ├── css/ js/ img/         # Theme, admin styles, scripts, logo & favicon
│   └── vendor/               # Local Bootstrap 5, Chart.js, Quill, Bootstrap Icons
├── uploads/                  # User uploads (images)
├── storage/                  # Mail log and application logs
├── index.php                 # Front controller
└── router.php                # Dev server router
```

---

## Branding

- The bundled logo lives at `assets/img/logo-mark.png`.
- The browser-tab favicon and app icons live at `assets/img/favicon-32.png`, `assets/img/apple-touch-icon.png`, and `favicon.ico`, with `site.webmanifest`.
- The super admin can upload replacement branding under **Admin → Settings → Branding**; uploads are validated and applied site-wide instantly.

---

## Security notes

- Every SQL query uses PDO prepared statements with emulated prepares disabled.
- All POST requests require a CSRF token (`_token` or `X-CSRF-Token`); failures return 419.
- Passwords are stored with `password_hash()` (bcrypt) and rehashed on verification when needed.
- Password-reset and email-verification tokens are stored as SHA-256 hashes, expire (60 minutes / 24 hours), and are single-use.
- Sessions use HttpOnly, SameSite=Lax cookies, a user-agent fingerprint keyed with `APP_KEY`, periodic regeneration, and login throttling.
- Uploads are validated by real MIME type and image dimensions, renamed randomly, and served from `uploads/` with script execution disabled.
- A strict Content-Security-Policy uses a per-request nonce for scripts; there are no inline event handlers.
- Administrator actions are recorded in `admin_activity_logs`; reader actions in `user_activity_logs`.

For production, set `APP_DEBUG=false`, use HTTPS, set a strong `APP_KEY`, create a dedicated database user, and switch mail to SMTP.

---

## Resetting the demo

To return to a clean, fully seeded state:

```bash
C:\xampp\php\php.exe database\install.php --fresh
```

This restores the original accounts and passwords, the forced first-login password change flags, and all sample content.
