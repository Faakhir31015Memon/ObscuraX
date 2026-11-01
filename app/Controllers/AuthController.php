<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Newsletter;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [
            'pageTitle' => 'Sign in — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Sign in to ObscuraX to like articles, join discussions and track your reading.',
            'bodyClass' => 'page-auth',
        ], 'layouts/auth');
    }

    public function login(): void
    {
        $validator = new Validator($_POST, [
            'identifier' => 'required|max:190',
            'password' => 'required|max:200',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/login');
        }

        $result = Auth::attempt((string) input('identifier'), (string) input('password'));

        if (!$result['ok']) {
            if ($result['code'] === 'unverified' && isset($result['user'])) {
                Session::put('pending_email', $result['user']['email']);
                flash('error', $result['message']);
                redirect('/verify-email?unverified=1');
            }
            if ($result['code'] === 'deactivated') {
                flash('error', $result['message']);
                redirect('/login');
            }
            Session::flashOld($_POST);
            flash('error', $result['message']);
            back('/login');
        }

        Session::clearOld();
        Session::forget('pending_email');

        $user = Auth::user();
        if ($user !== null && (int) $user['must_change_password'] === 1) {
            flash('info', 'Please choose a new password to finish securing your account.');
            redirect(Auth::isAdmin() ? '/admin/password' : '/account/security');
        }

        flash('success', $result['message']);

        $intended = Session::pull('_intended');
        if (is_string($intended) && str_starts_with($intended, '/') && !str_starts_with($intended, '//')) {
            redirect($intended);
        }
        redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
    }

    public function showRegister(): void
    {
        $this->view('auth/register', [
            'pageTitle' => 'Create account — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Join ObscuraX to read, like and discuss investigations into the occult, health and cybersecurity.',
            'bodyClass' => 'page-auth',
        ], 'layouts/auth');
    }

    public function register(): void
    {
        $validator = new Validator($_POST, [
            'username' => 'required|alpha_dash|min:3|max:24|unique:users,username',
            'full_name' => 'nullable|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|strong_password|min:8|max:72|confirmed',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/register');
        }

        if (input('terms') !== '1') {
            Session::flashOld($_POST);
            flash('error', 'Please accept the terms and privacy policy to continue.');
            back('/register');
        }

        $userId = User::create([
            'role_id' => User::roleId('user'),
            'username' => (string) input('username'),
            'email' => mb_strtolower((string) input('email')),
            'password_hash' => password_hash((string) input('password'), PASSWORD_DEFAULT),
            'full_name' => input('full_name') !== '' ? (string) input('full_name') : null,
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = Verification::issue($userId, (string) input('email'));

        Activity::user($userId, 'register', 'Account created and verification email sent');
        Session::clearOld();
        Session::put('pending_email', (string) input('email'));

        Verification::send((string) input('email'), (string) input('username'), $token);
        if (Verification::devLinkEnabled()) {
            Session::put('dev_verify_url', url('/verify-email/' . $token));
        }

        flash('success', 'Your account was created. Check your inbox to verify your email address.');
        redirect('/verify-email?sent=1');
    }

    public function verificationNotice(): void
    {
        $email = Session::get('pending_email');
        if (Auth::check()) {
            $email = Auth::user()['email'];
        }

        $this->view('auth/verify-notice', [
            'pageTitle' => 'Verify your email — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Confirm your email address to activate your ObscuraX account.',
            'bodyClass' => 'page-auth',
            'email' => $email,
            'devUrl' => $this->devLink('dev_verify_url'),
        ], 'layouts/auth');
    }

    public function verifyEmail(string $token): void
    {
        $hash = hash('sha256', $token);
        $record = Database::fetch('SELECT * FROM email_verifications WHERE token_hash = ? LIMIT 1', [$hash]);

        $valid = $record !== null
            && $record['verified_at'] === null
            && strtotime((string) $record['expires_at']) >= time();

        if (!$valid) {
            $this->view('auth/verify-result', [
                'pageTitle' => 'Verification failed — ' . setting('site_name', 'ObscuraX'),
                'metaDescription' => 'Email verification status.',
                'bodyClass' => 'page-auth',
                'success' => false,
                'message' => 'This verification link is invalid or has expired. Request a new link below.',
            ], 'layouts/auth');
            return;
        }

        Database::transaction(function () use ($record): void {
            Database::update('users', [
                'email_verified_at' => now(),
                'activated_at' => now(),
                'status' => 'active',
            ], 'id = :id', ['id' => (int) $record['user_id']]);

            Database::update('email_verifications', ['verified_at' => now()], 'id = :id', ['id' => (int) $record['id']]);
        });

        Activity::user((int) $record['user_id'], 'email_verified', 'Email address verified');
        Session::forget('pending_email');
        Session::forget('dev_verify_url');

        $user = User::find((int) $record['user_id']);
        if ($user !== null) {
            Mailer::send(
                $user['email'],
                'Welcome to ObscuraX',
                Mailer::render('welcome', ['username' => $user['username'], 'loginUrl' => url('/login')])
            );
        }

        $this->view('auth/verify-result', [
            'pageTitle' => 'Email verified — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Your account is now active.',
            'bodyClass' => 'page-auth',
            'success' => true,
            'message' => 'Your email is verified and your account is active. You can now sign in.',
        ], 'layouts/auth');
    }

    public function resendVerification(): void
    {
        $email = mb_strtolower(trim((string) input('email', '')));
        if ($email === '' && Auth::check()) {
            $email = Auth::user()['email'];
        }
        if ($email === '' && Session::has('pending_email')) {
            $email = (string) Session::get('pending_email');
        }

        $validator = new Validator(['email' => $email], ['email' => 'required|email']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            redirect('/verify-email');
        }

        $user = User::findByEmail($email);
        if ($user !== null && $user['email_verified_at'] === null) {
            $token = Verification::issue((int) $user['id'], $email);
            Verification::send($email, $user['username'], $token);
            if (Verification::devLinkEnabled()) {
                Session::put('dev_verify_url', url('/verify-email/' . $token));
            }
            Session::put('pending_email', $email);
        }

        flash('success', 'If that address is registered and unverified, a fresh verification email is on its way.');
        redirect('/verify-email?resent=1');
    }

    public function showForgot(): void
    {
        $this->view('auth/forgot', [
            'pageTitle' => 'Reset password — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Request a secure password reset link for your ObscuraX account.',
            'bodyClass' => 'page-auth',
            'devUrl' => $this->devLink('dev_reset_url'),
        ], 'layouts/auth');
    }

    public function sendReset(): void
    {
        $validator = new Validator($_POST, ['email' => 'required|email|max:190']);
        if ($validator->fails()) {
            flash('error', (string) $validator->firstError());
            back('/forgot-password');
        }

        $email = mb_strtolower((string) input('email'));
        $user = User::findByEmail($email);

        if ($user !== null && !in_array($user['status'], ['banned', 'removed'], true)) {
            $token = bin2hex(random_bytes(32));
            Database::query('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL', [(int) $user['id']]);
            Database::insert('password_resets', [
                'user_id' => (int) $user['id'],
                'email' => $email,
                'token_hash' => hash('sha256', $token),
                'ip_address' => client_ip(),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => now(),
            ]);

            $resetUrl = url('/reset-password/' . $token);
            Mailer::send(
                $email,
                'Reset your ObscuraX password',
                Mailer::render('reset-password', [
                    'username' => $user['username'],
                    'resetUrl' => $resetUrl,
                    'expiresIn' => '60 minutes',
                ])
            );

            if ($this->mailIsLogged()) {
                Session::put('dev_reset_url', $resetUrl);
            }
        }

        flash('success', 'If an account exists for that email, a password reset link has been sent.');
        redirect('/forgot-password');
    }

    public function showReset(string $token): void
    {
        $record = $this->validReset($token);
        if ($record === null) {
            flash('error', 'That password reset link is invalid or has expired. Please request a new one.');
            redirect('/forgot-password');
        }

        $this->view('auth/reset', [
            'pageTitle' => 'Choose a new password — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Set a new password for your ObscuraX account.',
            'bodyClass' => 'page-auth',
            'token' => $token,
            'email' => $record['email'],
        ], 'layouts/auth');
    }

    public function resetPassword(): void
    {
        $token = (string) input('token', '');
        $record = $this->validReset($token);
        if ($record === null) {
            flash('error', 'That password reset link is invalid or has expired. Please request a new one.');
            redirect('/forgot-password');
        }

        $validator = new Validator($_POST, [
            'password' => 'required|strong_password|min:8|max:72|confirmed',
        ]);
        if ($validator->fails()) {
            Session::flashOld(['password' => '']);
            flash('error', (string) $validator->firstError());
            redirect('/reset-password/' . $token);
        }

        Database::transaction(function () use ($record, $token): void {
            User::updatePassword((int) $record['user_id'], (string) input('password'));
            Database::update('password_resets', ['used_at' => now()], 'id = :id', ['id' => (int) $record['id']]);
            Database::query('DELETE FROM password_resets WHERE user_id = ? AND id <> ?', [(int) $record['user_id'], (int) $record['id']]);
            Database::insert('user_activity_logs', [
                'user_id' => (int) $record['user_id'],
                'action' => 'password_reset',
                'description' => 'Password reset through email link',
                'ip_address' => client_ip(),
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'created_at' => now(),
            ]);
        });

        Session::forget('dev_reset_url');
        flash('success', 'Your password has been reset. Please sign in with your new password.');
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been signed out.');
        redirect('/');
    }

    private function validReset(string $token): ?array
    {
        if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return null;
        }
        $record = Database::fetch('SELECT * FROM password_resets WHERE token_hash = ? LIMIT 1', [hash('sha256', $token)]);
        if ($record === null || $record['used_at'] !== null || strtotime((string) $record['expires_at']) < time()) {
            return null;
        }
        return $record;
    }

    private function mailIsLogged(): bool
    {
        return (bool) config('app.debug') && (string) config('mail.driver') === 'log';
    }

    private function devLink(string $key): ?string
    {
        if (!$this->mailIsLogged()) {
            return null;
        }
        $value = Session::get($key);
        return is_string($value) ? $value : null;
    }
}
