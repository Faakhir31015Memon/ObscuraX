<?php /** @var bool $done */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Newsletter</li>
            </ol>
        </nav>
        <p class="kicker mt-3">The dispatch</p>
        <h1><?= $done ? 'You are unsubscribed' : 'Link not recognised' ?></h1>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="panel" style="max-width:640px;margin:0 auto">
            <div class="empty-state">
                <i class="bi <?= $done ? 'bi-envelope-check' : 'bi-envelope-exclamation' ?>"></i>
                <h3><?= $done ? 'That address has been removed from the list.' : 'This unsubscribe link is invalid or has already been used.' ?></h3>
                <p><?= $done ? 'No further dispatch emails will be sent. You are always welcome back.' : 'If you keep receiving emails, reply to any dispatch and we will remove you manually.' ?></p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a class="btn btn-brand" href="<?= url('/') ?>">Return home</a>
                    <a class="btn btn-outline-soft" href="<?= url('/blogs') ?>">Keep reading</a>
                </div>
            </div>
        </div>
    </div>
</section>
