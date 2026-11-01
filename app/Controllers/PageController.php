<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contact;
use App\Core\Activity;
use App\Core\Auth;

final class PageController extends Controller
{
    public function about(): void
    {
        $this->renderPage('about', 'About ObscuraX', 'About Us');
    }

    public function privacy(): void
    {
        $this->renderPage('privacy', 'Privacy Policy', 'Privacy Policy');
    }

    public function terms(): void
    {
        $this->renderPage('terms', 'Terms & Conditions', 'Terms & Conditions');
    }

    public function contact(): void
    {
        $this->view('pages/contact', [
            'pageTitle' => 'Contact — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Contact the ObscuraX editorial team with story tips, corrections or partnership requests.',
            'bodyClass' => 'page-static',
            'contactEmail' => setting('contact_email', 'hello@obscurax.local'),
        ]);
    }

    public function send(): void
    {
        if (trim((string) input('website', '')) !== '') {
            // honeypot filled by bots - pretend success
            flash('success', 'Thank you. Your message has been received.');
            redirect('/contact');
        }

        $validator = new Validator($_POST, [
            'name' => 'required|min:2|max:96',
            'email' => 'required|email|max:190',
            'subject' => 'required|min:3|max:191',
            'message' => 'required|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            Session::flashOld($_POST);
            flash('error', (string) $validator->firstError());
            back('/contact');
        }

        $id = Contact::create([
            'name' => (string) input('name'),
            'email' => (string) input('email'),
            'subject' => (string) input('subject'),
            'message' => (string) input('message'),
        ]);

        $inbox = setting('contact_email', (string) config('mail.from', 'hello@obscurax.local'));
        Mailer::send(
            $inbox,
            'New contact message: ' . (string) input('subject'),
            Mailer::render('contact-message', [
                'name' => (string) input('name'),
                'email' => (string) input('email'),
                'subject' => (string) input('subject'),
                'body' => (string) input('message'),
                'ip' => client_ip(),
            ])
        );

        if (Auth::check()) {
            Activity::user(Auth::id(), 'contact_message', 'Contact message #' . $id . ' submitted');
        }

        Session::clearOld();
        flash('success', 'Thank you. Your message has been received and our team will reply by email.');
        redirect('/contact');
    }

    private function renderPage(string $slug, string $fallbackTitle, string $heading): void
    {
        $content = setting('page_' . $slug, '');
        if (trim($content) === '') {
            http_response_code(404);
            abort(404, 'This page has not been published yet.');
        }

        $this->view('pages/content', [
            'pageTitle' => $fallbackTitle . ' — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => str_limit($content, 160),
            'bodyClass' => 'page-static',
            'heading' => $heading,
            'content' => $content,
        ]);
    }
}
