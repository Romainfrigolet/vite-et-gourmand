<?php
/** @var array $user */
/** @var array $commandes */
use App\Services\OrderStatus;
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="mb-1">Bonjour <?= e($user['prenom']) ?></h1>
            <p class="text-muted mb-0">Retrouvez ici vos commandes et vos informations personnelles.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/menus" class="btn btn-primary">Commander un menu</a>
            <a href="/mon-compte/informations" class="btn btn-outline-primary">Mes informations</a>
        </div>
    </div>

    <h2 class="h4 mb-3">Mes commandes</h2>
    <?php if (!$commandes): ?>
        <p class="alert alert-info">Vous n'avez pas encore passé de commande. <a href="/menus">Découvrez nos menus</a>.</p>
    <?php else: ?>
        <div class="table-responsive card border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Liste de mes commandes</caption>
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Prestation</th>
                        <th scope="col">Personnes</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Statut</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td><?= e($c['numero_commande']) ?></td>
                            <td><?= e($c['menu_titre']) ?></td>
                            <td><?= e(date_fr($c['date_prestation'])) ?> à <?= e(time_fr($c['heure_livraison'])) ?></td>
                            <td><?= (int) $c['nombre_personne'] ?></td>
                            <td class="text-end"><?= price($c['prix_total']) ?></td>
                            <td><?= OrderStatus::badge($c['statut']) ?></td>
                            <td><a href="/mon-compte/commandes/<?= (int) $c['commande_id'] ?>" class="btn btn-sm btn-outline-primary">Détail<span class="visually-hidden"> de la commande <?= e($c['numero_commande']) ?></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
