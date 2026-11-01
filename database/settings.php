<?php

declare(strict_types=1);

$about = <<<'HTML'
<p class="lead">ObscuraX is an independent knowledge platform for people who prefer questions to comfortable answers. We publish long-form research across three deliberately unusual fields: black magic and the occult, sexual health and education, and ethical hacking and cybersecurity.</p>

<h2>Why these three subjects?</h2>
<p>They share a quality that makes them easy to sensationalise and difficult to write about honestly. Each sits at the edge of mainstream conversation, each attracts an enormous amount of misinformation, and each matters more than polite company usually admits. Put together, they are a study of how fear, desire and control actually work.</p>
<p>We are not here to make you believe anything. We are here to make the subject legible — with sources, context and a clear line between what is documented and what is claimed.</p>

<h2>How we work</h2>
<ul>
<li><strong>Evidence first.</strong> Claims about research, medicine or security are checked against primary sources. Where evidence is thin, we say so.</li>
<li><strong>No fear marketing.</strong> We do not trade in panic, "ancient secrets" or "what they don't want you to know".</li>
<li><strong>Practical value.</strong> Every article should leave you able to do something differently: test more thoughtfully, communicate more clearly, secure a system more sensibly.</li>
<li><strong>Respect for people.</strong> We write about communities and readers with care. We do not turn living traditions into props.</li>
</ul>

<h2>Editorial standards</h2>
<p>Articles are written and edited by our small editorial team. Corrections are published openly and editors are named on every piece. We do not accept payment for coverage, and we clearly label any future sponsored work.</p>

<h2>Health and security disclaimers</h2>
<p>Nothing on ObscuraX is medical advice, a diagnosis, or a substitute for a qualified professional. Sexual health content is educational and inclusive, and clinical questions belong with a clinician. Cybersecurity content is for defenders, students and authorised testing only — never test a system you do not own or have written permission to assess.</p>

<h2>Get in touch</h2>
<p>Story tips, corrections and collaborations are welcome through the contact page. If you are reporting something sensitive, tell us how you would like to be credited.</p>
HTML;

$privacy = <<<'HTML'
<p class="lead">This policy explains what ObscuraX collects, why, and how you can control it. It is written to be read, not to be survived.</p>

<h2>What we collect</h2>
<ul>
<li><strong>Account data.</strong> Your username, email address, hashed password, optional profile details and avatar.</li>
<li><strong>Activity data.</strong> Articles you like, comments and replies you post, and the dates of account creation, activation and sign-in. This data powers your dashboard and our moderation.</li>
<li><strong>Technical data.</strong> IP address, browser user agent, and aggregated page views. Page views are stored with a hashed IP so we can measure readership without keeping a raw log.</li>
<li><strong>Messages.</strong> Anything you send through the contact form or newsletter signup.</li>
</ul>

<h2>What we do not do</h2>
<p>We do not sell personal data. We do not run advertising trackers. We do not use your email address for anything other than account notices and the newsletter you explicitly requested.</p>

<h2>Cookies and sessions</h2>
<p>We use a single first-party session cookie to keep you signed in and to protect forms against cross-site request forgery. It is HTTP-only and SameSite protected. There is no analytics or advertising cookie.</p>

<h2>Passwords and security</h2>
<p>Passwords are stored only as salted hashes using PHP's password_hash(). We never store or transmit plain-text passwords. Password reset tokens are single-use, hashed in the database, and expire after one hour.</p>

<h2>Your rights</h2>
<p>You can review and update your profile at any time, change your password, and ask us to remove your account. To request removal or an export of your account data, contact us using the email published on the contact page.</p>

<h2>Retention</h2>
<p>Account data is retained while your account exists. Security and moderation logs are retained for up to 12 months. Removed accounts are soft-deleted so that comment threads remain readable, with identifying details retained only as required for audit.</p>

<h2>Changes</h2>
<p>If this policy changes materially, the update date will change and signed-in readers will see a notice. Continued use after a change means you accept the updated policy.</p>

<h2>Contact</h2>
<p>Privacy questions can be sent through the contact form, marked “Privacy”.</p>
HTML;

$terms = <<<'HTML'
<p class="lead">By using ObscuraX you agree to these terms. They exist to keep the platform safe, readable and honest.</p>

<h2>Accounts</h2>
<ul>
<li>You must provide accurate information and a working email address, and verify it before signing in.</li>
<li>You are responsible for keeping your password secure. Do not share account access.</li>
<li>Administrators may deactivate, suspend or remove accounts that break these terms. You may appeal by contacting us.</li>
</ul>

<h2>Acceptable use</h2>
<p>You may not use ObscuraX to post spam, harassment, hate speech, sexual content involving minors, or private information about others. You may not attempt to access accounts or administrative areas you are not authorised to use, probe the platform for vulnerabilities without permission, or interfere with other readers' access.</p>

<h2>Comments and community</h2>
<p>Comments are moderated. We may hide or remove content that is abusive, off-topic, promotional, or that shares personal data. Repeated violations can lead to permanent loss of commenting privileges.</p>

<h2>Intellectual property</h2>
<p>Articles, artwork and branding on ObscuraX are owned by the platform or licensed to it. You may quote short excerpts with attribution and a link. Republishing full articles requires written permission.</p>

<h2>Educational disclaimers</h2>
<p>Content about the occult is cultural and historical commentary, not supernatural assurance. Content about sexual health is educational and is not medical advice, diagnosis or treatment. Content about security is for authorised, defensive and educational use only. You are responsible for complying with the laws of your jurisdiction.</p>

<h2>Newsletter</h2>
<p>Subscribing is optional. Every email includes a one-click unsubscribe link, and you can also unsubscribe from any newsletter email.</p>

<h2>Availability and changes</h2>
<p>The platform is provided “as is”. We aim for high availability but do not guarantee uninterrupted service. We may update these terms; continued use after an update constitutes acceptance.</p>

<h2>Contact</h2>
<p>Questions about these terms can be sent through the contact page.</p>
HTML;

return [
    'site_name' => 'ObscuraX',
    'site_tagline' => 'Explore the Unknown. Discover Beyond the Ordinary.',
    'site_description' => 'ObscuraX publishes long-form investigations into black magic and the occult, sexual health and education, and ethical hacking and cybersecurity.',
    'site_logo' => '',
    'site_favicon' => '',
    'contact_email' => 'hello@obscurax.local',
    'footer_about' => 'An independent archive of the strange, the human and the digital. Research, not rumour.',
    'hero_badge' => 'Issue 01 — The Unknown',
    'hero_title' => 'Explore the Unknown. Discover Beyond the Ordinary.',
    'hero_subtitle' => 'ObscuraX investigates black magic and the occult, sexual health and education, and ethical hacking — long-form research for people who prefer questions to comfortable answers.',
    'hero_primary_label' => 'Start reading',
    'hero_primary_url' => '/blogs',
    'hero_secondary_label' => 'Browse categories',
    'hero_secondary_url' => '#categories',
    'hero_image' => 'assets/img/hero-default.jpg',
    'social_x' => 'https://x.com/',
    'social_instagram' => '',
    'social_youtube' => '',
    'social_linkedin' => '',
    'social_github' => '',
    'seo_default_title' => 'ObscuraX — Explore the Unknown',
    'seo_default_description' => 'Long-form investigations into the occult, sexual health and cybersecurity.',
    'page_about' => $about,
    'page_privacy' => $privacy,
    'page_terms' => $terms,
];
