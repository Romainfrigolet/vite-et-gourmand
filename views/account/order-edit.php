<?php /** @var array $commande */ ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-2">Modifier la commande <?= e($commande['numero_commande']) ?></h1>
            <p class="text-muted mb-4">Tous les éléments sont modifiables sauf le menu. Le prix sera recalculé.</p>
            <form method="post" action="/mon-compte/commandes/<?= (int) $commande['commande_id'] ?>/modifier" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <?php require BASE_PATH . '/views/partials/order-fields.php'; ?>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="/mon-compte/commandes/<?= (int) $commande['commande_id'] ?>" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>
