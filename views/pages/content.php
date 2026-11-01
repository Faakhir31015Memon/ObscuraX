<?php
/** @var string $heading */
/** @var string $content */
?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($heading) ?></li>
            </ol>
        </nav>
        <p class="kicker mt-3">ObscuraX</p>
        <h1><?= e($heading) ?></h1>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="prose-page">
            <?= $content ?>
        </div>
        <div class="divider-ornament">✦</div>
        <div class="text-center">
            <a class="btn btn-outline-soft" href="<?= url('/contact') ?>">Questions? Contact the desk</a>
        </div>
    </div>
</section>
