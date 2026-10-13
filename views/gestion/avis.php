<?php
/** @var array $avis */
/** @var string|null $statut */
use App\Repositories\AvisRepository;
?>
<div class="container py-5">
    <h1 class="mb-4">Avis clients</h1>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <ul class="nav nav-tabs mb-4">
        <?php foreach (AvisRepository::STATUTS + ['tous' => 'Tous'] as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link<?= ($statut ?? 'tous') === $key ? ' active" aria-current="page' : '' ?>" href="/gestion/avis?statut=<?= e($key) ?>"><?= e($label) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (!$avis): ?>
        <p class="alert alert-info">Aucun avis dans cette catégorie.</p>
    <?php endif; ?>
    <div class="row g-3">
        <?php foreach ($avis as $a): ?>
            <div class="col-md-6">
                <article class="card avis-card p-4 h-100">
                    <div class="d-flex justify-content-between">
                        <p class="stars mb-1" aria-label="Note : <?= (int) $a['note'] ?> sur 5"><span aria-hidden="true"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></span></p>
                        <span class="badge <?= $a['statut'] === 'valide' ? 'text-bg-success' : ($a['statut'] === 'refuse' ? 'text-bg-secondary' : 'text-bg-warning') ?>"><?= e(AvisRepository::STATUTS[$a['statut']]) ?></span>
                    </div>
                    <p>« <?= e($a['commentaire']) ?> »</p>
                    <p class="small text-muted"><?= e($a['prenom'] . ' ' . $a['nom']) ?> — <?= e($a['menu']) ?> (<?= e($a['numero_commande']) ?>) — <?= e(date_fr($a['created_at'])) ?></p>
                    <div class="d-flex gap-2 mt-auto">
                        <?php if ($a['statut'] !== 'valide'): ?>
                            <form method="post" action="/gestion/avis/<?= (int) $a['avis_id'] ?>/valider"><?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-primary">Valider<span class="visually-hidden"> l'avis de <?= e($a['prenom']) ?></span></button>
                            </form>
                        <?php endif; ?>
                        <?php if ($a['statut'] !== 'refuse'): ?>
                            <form method="post" action="/gestion/avis/<?= (int) $a['avis_id'] ?>/refuser"><?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Refuser<span class="visually-hidden"> l'avis de <?= e($a['prenom']) ?></span></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
