<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\AccountController;
use App\Controllers\Admin\AdminBlogController;
use App\Controllers\Admin\AdminCategoryController;
use App\Controllers\Admin\AdminCommentController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminHeroController;
use App\Controllers\Admin\AdminMessageController;
use App\Controllers\Admin\AdminNewsletterController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminTagController;
use App\Controllers\Admin\AnalyticsController;
use App\Controllers\Admin\LogController;
use App\Controllers\Admin\ManageAdminController;
use App\Controllers\Admin\ManageBlogController;
use App\Controllers\Admin\ManageLikeController;
use App\Controllers\Admin\ManageUserController;
use App\Controllers\Admin\UploadController;
use App\Controllers\BlogController;
use App\Controllers\EngagementController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\SeoController;

// ------------------------------------------------------------------ public
$router->get('/', [HomeController::class, 'index']);
$router->get('/blogs', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'show']);
$router->get('/category/{slug}', [BlogController::class, 'category']);
$router->get('/tag/{slug}', [BlogController::class, 'tag']);
$router->get('/search', [BlogController::class, 'search']);
$router->get('/author/{username}', [BlogController::class, 'author']);

$router->get('/about', [PageController::class, 'about']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [PageController::class, 'send']);
$router->get('/privacy', [PageController::class, 'privacy']);
$router->get('/terms', [PageController::class, 'terms']);

$router->post('/newsletter/subscribe', [HomeController::class, 'subscribe']);
$router->get('/newsletter/unsubscribe/{token}', [HomeController::class, 'unsubscribe']);

$router->get('/sitemap.xml', [SeoController::class, 'sitemap']);
$router->get('/robots.txt', [SeoController::class, 'robots']);

// -------------------------------------------------------------------- auth
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

$router->get('/forgot-password', [AuthController::class, 'showForgot'], ['guest']);
$router->post('/forgot-password', [AuthController::class, 'sendReset'], ['guest']);
$router->get('/reset-password/{token}', [AuthController::class, 'showReset'], ['guest']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], ['guest']);

$router->get('/verify-email', [AuthController::class, 'verificationNotice']);
$router->get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
$router->post('/verify-email/resend', [AuthController::class, 'resendVerification']);

// ----------------------------------------------------------------- account
$router->get('/dashboard', [AccountController::class, 'dashboard'], ['auth', 'password']);
$router->get('/dashboard/likes', [AccountController::class, 'likes'], ['auth', 'password']);
$router->get('/dashboard/comments', [AccountController::class, 'comments'], ['auth', 'password']);
$router->get('/profile', [AccountController::class, 'profile'], ['auth', 'password']);
$router->post('/profile', [AccountController::class, 'updateProfile'], ['auth', 'password']);
$router->post('/profile/avatar', [AccountController::class, 'updateAvatar'], ['auth', 'password']);
$router->get('/account/security', [AccountController::class, 'security'], ['auth']);
$router->post('/account/security', [AccountController::class, 'changePassword'], ['auth']);

// -------------------------------------------------------------- engagement
$router->post('/blog/{id:\d+}/like', [EngagementController::class, 'like'], ['auth']);
$router->post('/blog/{id:\d+}/comment', [EngagementController::class, 'comment'], ['auth', 'password']);
$router->post('/comment/{id:\d+}/reply', [EngagementController::class, 'reply'], ['auth', 'password']);

// ------------------------------------------------------------------- admin
$router->get('/admin', [AdminDashboardController::class, 'index'], ['admin']);
$router->get('/admin/password', [AdminDashboardController::class, 'password'], ['admin']);
$router->post('/admin/password', [AdminDashboardController::class, 'updatePassword'], ['admin']);

$router->get('/admin/blogs', [AdminBlogController::class, 'index'], ['admin', 'can:blog.create']);
$router->get('/admin/blogs/create', [AdminBlogController::class, 'create'], ['admin', 'can:blog.create']);
$router->post('/admin/blogs', [AdminBlogController::class, 'store'], ['admin', 'can:blog.create']);
$router->get('/admin/blogs/{id:\d+}/edit', [AdminBlogController::class, 'edit'], ['admin', 'can:blog.create']);
$router->post('/admin/blogs/{id:\d+}', [AdminBlogController::class, 'update'], ['admin', 'can:blog.create']);
$router->post('/admin/blogs/{id:\d+}/status', [AdminBlogController::class, 'changeStatus'], ['admin', 'can:blog.publish']);
$router->post('/admin/blogs/{id:\d+}/feature', [AdminBlogController::class, 'toggleFeatured'], ['admin', 'can:blog.feature']);
$router->post('/admin/blogs/{id:\d+}/delete', [ManageBlogController::class, 'destroy'], ['admin', 'can:blog.delete']);
$router->post('/admin/upload/image', [UploadController::class, 'image'], ['admin']);

$router->get('/admin/categories', [AdminCategoryController::class, 'index'], ['admin']);
$router->post('/admin/categories', [AdminCategoryController::class, 'store'], ['admin', 'can:category.manage']);
$router->post('/admin/categories/{id:\d+}', [AdminCategoryController::class, 'update'], ['admin', 'can:category.manage']);
$router->post('/admin/categories/{id:\d+}/delete', [AdminCategoryController::class, 'destroy'], ['admin', 'can:category.manage']);

$router->get('/admin/tags', [AdminTagController::class, 'index'], ['admin', 'can:tag.manage']);
$router->post('/admin/tags', [AdminTagController::class, 'store'], ['admin', 'can:tag.manage']);
$router->post('/admin/tags/{id:\d+}', [AdminTagController::class, 'update'], ['admin', 'can:tag.manage']);
$router->post('/admin/tags/{id:\d+}/delete', [AdminTagController::class, 'destroy'], ['admin', 'can:tag.manage']);

$router->get('/admin/comments', [AdminCommentController::class, 'index'], ['admin', 'can:comment.view']);
$router->post('/admin/comments/{id:\d+}/status', [AdminCommentController::class, 'status'], ['admin', 'can:comment.moderate']);
$router->post('/admin/comments/{id:\d+}/reply', [AdminCommentController::class, 'reply'], ['admin', 'can:comment.reply']);
$router->post('/admin/comments/{id:\d+}/delete', [AdminCommentController::class, 'destroy'], ['admin', 'can:comment.delete']);

$router->get('/admin/hero', [AdminHeroController::class, 'index'], ['admin', 'can:hero.manage']);
$router->post('/admin/hero', [AdminHeroController::class, 'update'], ['admin', 'can:hero.manage']);

// --------------------------------------------------------------- super admin
$router->get('/admin/users', [ManageUserController::class, 'index'], ['super']);
$router->get('/admin/users/{id:\d+}', [ManageUserController::class, 'show'], ['super']);
$router->post('/admin/users/{id:\d+}/activate', [ManageUserController::class, 'activate'], ['super']);
$router->post('/admin/users/{id:\d+}/deactivate', [ManageUserController::class, 'deactivate'], ['super']);
$router->post('/admin/users/{id:\d+}/ban', [ManageUserController::class, 'ban'], ['super']);
$router->post('/admin/users/{id:\d+}/remove', [ManageUserController::class, 'remove'], ['super']);

$router->get('/admin/admins', [ManageAdminController::class, 'index'], ['super']);
$router->post('/admin/admins', [ManageAdminController::class, 'store'], ['super']);
$router->post('/admin/admins/{id:\d+}/update', [ManageAdminController::class, 'update'], ['super']);
$router->post('/admin/admins/{id:\d+}/disable', [ManageAdminController::class, 'toggleDisabled'], ['super']);
$router->post('/admin/admins/{id:\d+}/remove', [ManageAdminController::class, 'destroy'], ['super']);

$router->get('/admin/blogs/all', [ManageBlogController::class, 'index'], ['super']);
$router->get('/admin/likes', [ManageLikeController::class, 'index'], ['super', 'can:like.manage']);
$router->post('/admin/likes/{id:\d+}/delete', [ManageLikeController::class, 'destroy'], ['super', 'can:like.manage']);

$router->get('/admin/analytics', [AnalyticsController::class, 'index'], ['super', 'can:analytics.view']);
$router->get('/admin/reports', [AnalyticsController::class, 'reports'], ['super', 'can:analytics.view']);
$router->get('/admin/reports/export', [AnalyticsController::class, 'export'], ['super', 'can:analytics.view']);

$router->get('/admin/settings', [AdminSettingsController::class, 'index'], ['super', 'can:settings.manage']);
$router->post('/admin/settings/general', [AdminSettingsController::class, 'updateGeneral'], ['super', 'can:settings.manage']);
$router->post('/admin/settings/branding', [AdminSettingsController::class, 'updateBranding'], ['super', 'can:settings.manage']);
$router->post('/admin/settings/pages', [AdminSettingsController::class, 'updatePages'], ['super', 'can:settings.manage']);
$router->post('/admin/settings/social', [AdminSettingsController::class, 'updateSocial'], ['super', 'can:settings.manage']);

$router->get('/admin/logs', [LogController::class, 'index'], ['super', 'can:logs.view']);
$router->get('/admin/newsletter', [AdminNewsletterController::class, 'index'], ['super', 'can:newsletter.manage']);
$router->get('/admin/newsletter/export', [AdminNewsletterController::class, 'export'], ['super', 'can:newsletter.manage']);
$router->post('/admin/newsletter/{id:\d+}/delete', [AdminNewsletterController::class, 'destroy'], ['super', 'can:newsletter.manage']);

$router->get('/admin/messages', [AdminMessageController::class, 'index'], ['super', 'can:message.manage']);
$router->post('/admin/messages/{id:\d+}/read', [AdminMessageController::class, 'markRead'], ['super', 'can:message.manage']);
$router->post('/admin/messages/{id:\d+}/archive', [AdminMessageController::class, 'archive'], ['super', 'can:message.manage']);
$router->post('/admin/messages/{id:\d+}/delete', [AdminMessageController::class, 'destroy'], ['super', 'can:message.manage']);
