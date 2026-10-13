<?php
/** @var int $code */
/** @var string $message */
?>
<div class="container py-5 text-center">
    <p class="display-1 fw-bold text-or mb-0" aria-hidden="true"><?= (int) $code ?></p>
    <h1 class="h2 mb-3"><?= e($message) ?></h1>
    <a href="/" class="btn btn-primary">Retour à l'accueil</a>
</div>
