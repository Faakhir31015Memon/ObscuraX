<?php
/** @var array<string,list<string>> $flashes */
$icons = [
    'success' => 'bi-check-circle',
    'error' => 'bi-exclamation-octagon',
    'danger' => 'bi-exclamation-octagon',
    'info' => 'bi-info-circle',
    'warning' => 'bi-exclamation-triangle',
];
?>
<?php if (!empty($flashes)): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="flash flash-<?= e($type) ?>" role="status">
                    <i class="bi <?= e($icons[$type] ?? 'bi-info-circle') ?>"></i>
                    <div><?= e($message) ?></div>
                    <button type="button" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
