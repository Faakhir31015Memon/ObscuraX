<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\Verification;

final class AccountController extends Controller
{
    public function dashboard(): void
    {
        $user = Auth::user();
        $userId = (int) $user['id'];

        $liked = Like::blogsLikedBy($userId, 4);
        $comments = Comment::forUser($userId);
        $recentComments = array_slice($comments, 0, 4);

        $stats = [
            'likes' => (int) Database::value('SELECT COUNT(*) FROM likes WHERE user_id = ?', [$userId], 0),
            'comments' => count($comments),
            'replies' => (int) Database::value(
                'SELECT COUNT(*) FROM comment_replies cr INNER JOIN comments c ON c.id = cr.comment_id WHERE c.user_id = ?',
                [$userId],
                0
            ),
            'unread' => 0,
        ];

        $this->view('account/dashboard', [
            'pageTitle' => 'Your dashboard — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Your ObscuraX reading dashboard.',
            'bodyClass' => 'page-account',
            'user' => $user,
            'stats' => $stats,
            'liked' => $liked,
            'comments' => $recentComments,
            'allCommentsCount' => count($comments),
        ]);
    }

    public function likes(): void
    {
        $userId = (int) Auth::id();
        $this->view('account/likes', [
            'pageTitle' => 'Liked articles — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Every ObscuraX article you have liked.',
            'bodyClass' => 'page-account',
            'blogs' => Like::blogsLikedBy($userId, 100),
        ]);
    }

    public function comments(): void
    {
        $userId = (int) Auth::id();
        $this->view('account/comments', [
            'pageTitle' => 'Your comments — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Your discussion history on ObscuraX.',
            'bodyClass' => 'page-account',
            'comments' => Comment::forUser($userId),
        ]);
    }

    public function profile(): void
    {
        $this->view('account/profile', [
            'pageTitle' => 'Your profile — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage your ObscuraX profile details.',
            'bodyClass' => 'page-account',
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $userId = (int) $user['id'];
        $newEmail = mb_strtolower(trim((string) input('email')));
        $emailChanged = $newEmail !== mb_strtolower($user['email']);

        $validator = new Validator($_POST, [
            'username' => 'required|alpha_dash|min:3|max:24|unique:users,username,' . $userId,
            'full_name' => 'nullable|max:120',
            'email' => 'required|email|max:190|unique:users,email,' . $userId,
            'website' => 'nullable|url|max:190',
            'bio' => 'nullable|max:600',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/profile');
        }

        if ($emailChanged) {
            $current = (string) input('current_password', '');
            if ($current === '' || !password_verify($current, $user['password_hash'])) {
                Session::flashOld($_POST);
                flash('error', 'Enter your current password to change your email address.');
                back('/profile');
            }
        }

        $avatarNote = '';
        $data = [
            'username' => (string) input('username'),
            'full_name' => input('full_name') !== '' ? (string) input('full_name') : null,
            'website' => input('website') !== '' ? (string) input('website') : null,
            'bio' => input('bio') !== '' ? (string) input('bio') : null,
        ];

        if ($emailChanged) {
            $data['email'] = $newEmail;
            $data['email_verified_at'] = null;
        }

        if (!empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $path = Upload::image($_FILES['avatar'], 'avatars', 3);
                Upload::delete($user['avatar']);
                $data['avatar'] = $path;
                $avatarNote = ' Your avatar was updated.';
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                back('/profile');
            }
        }

        User::update($userId, $data);
        Activity::user($userId, 'profile_update', 'Profile details updated');

        if ($emailChanged) {
            $token = Verification::issue($userId, $newEmail);
            Verification::send($newEmail, (string) $data['username'], $token);
            if (Verification::devLinkEnabled()) {
                Session::put('dev_verify_url', url('/verify-email/' . $token));
            }
            flash('success', 'Profile saved. Confirm your new email address using the link we just sent.' . $avatarNote);
        } else {
            flash('success', 'Your profile has been updated.' . $avatarNote);
        }

        Session::clearOld();
        redirect('/profile');
    }

    public function updateAvatar(): void
    {
        $user = Auth::user();
        if (empty($_FILES['avatar']) || ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            flash('error', 'Please choose an image to upload.');
            back('/profile');
        }

        try {
            $path = Upload::image($_FILES['avatar'], 'avatars', 3);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            back('/profile');
        }

        Upload::delete($user['avatar']);
        User::update((int) $user['id'], ['avatar' => $path]);
        Activity::user((int) $user['id'], 'avatar_update', 'Profile picture updated');
        flash('success', 'Your profile picture has been updated.');
        redirect('/profile');
    }

    public function security(): void
    {
        $user = Auth::user();
        $this->view('account/security', [
            'pageTitle' => 'Security — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Manage your ObscuraX password and account security.',
            'bodyClass' => 'page-account',
            'user' => $user,
        ]);
    }

    public function changePassword(): void
    {
        $user = Auth::user();
        $current = (string) input('current_password');
        $new = (string) input('password');

        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
            back('/account/security');
        }

        $validator = new Validator($_POST, [
            'password' => 'required|strong_password|min:8|max:72|confirmed',
        ]);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/account/security');
        }

        if (password_verify($new, $user['password_hash'])) {
            flash('error', 'Choose a password that differs from your current one.');
            back('/account/security');
        }

        User::updatePassword((int) $user['id'], $new);
        Activity::user((int) $user['id'], 'password_change', 'Password changed from account settings');
        Auth::refresh();
        Session::regenerate();

        flash('success', 'Your password has been updated.');
        redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
    }
}
