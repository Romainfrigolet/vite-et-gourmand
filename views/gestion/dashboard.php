<?php
/** @var array $counts */
/** @var int $avisCount */
/** @var array $aTraiter */
use App\Core\Auth;
use App\Services\OrderStatus;

$tiles = [
    'en_attente'              => 'À valider',
    'accepte'                 => 'Acceptées',
    'en_preparation'          => 'En préparation',
    'en_livraison'            => 'En livraison',
    'attente_retour_materiel' => 'Matériel à récupérer',
];
?>
<div class="container py-5">
    <h1 class="mb-1">Espace <?= Auth::isAdmin() ? 'administrateur' : 'employé' ?></h1>
    <p class="text-muted mb-4">Bonjour <?= e(Auth::user()['prenom']) ?>, voici l'activité en cours.</p>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <h2 class="h5 mb-3">Commandes par statut</h2>
    <ul class="row g-3 list-unstyled mb-5">
        <?php foreach ($tiles as $statut => $label): ?>
            <li class="col-6 col-md-4 col-xl">
                <a href="/gestion/commandes?statut=<?= e($statut) ?>" class="stat-tile d-block p-3 text-decoration-none text-reset h-100">
                    <span class="value d-block"><?= (int) ($counts[$statut] ?? 0) ?></span>
                    <span class="small"><?= e($label) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="col-6 col-md-4 col-xl">
            <a href="/gestion/avis" class="stat-tile d-block p-3 text-decoration-none text-reset h-100">
                <span class="value d-block"><?= (int) $avisCount ?></span>
                <span class="small">Avis à modérer</span>
            </a>
        </li>
    </ul>

    <h2 class="h5 mb-3">Nouvelles commandes à valider</h2>
    <?php if (!$aTraiter): ?>
        <p class="alert alert-success">Aucune commande en attente de validation.</p>
    <?php else: ?>
        <div class="table-responsive card border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Commandes en attente de validation</caption>
                <thead><tr><th scope="col">N°</th><th scope="col">Client</th><th scope="col">Menu</th><th scope="col">Prestation</th><th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($aTraiter as $c): ?>
                    <tr>
                        <td><?= e($c['numero_commande']) ?></td>
                        <td><?= e($c['client_prenom'] . ' ' . $c['client_nom']) ?></td>
                        <td><?= e($c['menu_titre']) ?></td>
                        <td><?= e(date_fr($c['date_prestation'])) ?></td>
                        <td><?= OrderStatus::badge($c['statut']) ?></td>
                        <td><a class="btn btn-sm btn-primary" href="/gestion/commandes/<?= (int) $c['commande_id'] ?>">Traiter<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
