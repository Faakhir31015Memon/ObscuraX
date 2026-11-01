<?php
/** @var string $contactEmail */
?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact</li>
            </ol>
        </nav>
        <p class="kicker mt-3">Open channel</p>
        <h1>Talk to the desk</h1>
        <p>Story tips, corrections, sensitive disclosures and collaborations all arrive in the same inbox. Tell us how you would like to be credited.</p>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="contact-grid">
            <aside class="contact-card">
                <p class="kicker mb-3">Direct lines</p>
                <div class="contact-point">
                    <i class="bi bi-envelope"></i>
                    <div><strong>Editorial inbox</strong><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></div>
                </div>
                <div class="contact-point">
                    <i class="bi bi-clock-history"></i>
                    <div><strong>Response window</strong><span>Usually within two working days</span></div>
                </div>
                <div class="contact-point">
                    <i class="bi bi-shield-lock"></i>
                    <div><strong>Sensitive tips</strong><span>Tell us how to reach you safely; we will follow your lead.</span></div>
                </div>
                <div class="contact-point">
                    <i class="bi bi-people"></i>
                    <div><strong>Community issues</strong><span>Report comments or account problems and include the relevant link.</span></div>
                </div>
                <hr>
                <p class="text-muted mb-0" style="font-size:.82rem">Please do not send medical records, credentials or exploitation material. Security disclosures should include a safe way to reproduce the issue.</p>
            </aside>

            <div class="contact-card">
                <h2 class="h4 mb-1">Send a message</h2>
                <p class="text-muted mb-4" style="font-size:.88rem">Fields marked with <span class="text-danger">*</span> are required.</p>
                <form method="post" action="<?= url('/contact') ?>">
                    <?= csrf_field() ?>
                    <div class="position-absolute" style="left:-9999px;top:auto" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Your name <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" name="name" type="text" required maxlength="96" value="<?= e(old('name')) ?>" autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                            <input class="form-control" id="email" name="email" type="email" required maxlength="190" value="<?= e(old('email')) ?>" autocomplete="email">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="subject">Subject <span class="text-danger">*</span></label>
                            <input class="form-control" id="subject" name="subject" type="text" required maxlength="191" value="<?= e(old('subject')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="7" required minlength="10" maxlength="5000" placeholder="Context, links and how we can verify the details."><?= e(old('message')) ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="form-text">Plain text only. 10–5000 characters.</span>
                                <span class="form-text" data-counter-for="#message">0</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" id="consent" type="checkbox" required>
                                <label class="form-check-label" for="consent">I understand this message is sent to the editorial inbox and that replies are by email.</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-brand btn-lg" type="submit">Send message <i class="bi bi-arrow-right ms-1"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
