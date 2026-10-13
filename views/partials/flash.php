<?php
use App\Core\Session;

$flashes = Session::pullFlashes();
$classes = ['success' => 'alert-success', 'error' => 'alert-danger', 'info' => 'alert-info', 'warning' => 'alert-warning'];
?>
<?php if ($flashes): ?>
    <div class="container mt-3" role="status" aria-live="polite">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert <?= $classes[$flash['type']] ?? 'alert-info' ?> alert-dismissible fade show">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer ce message"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
