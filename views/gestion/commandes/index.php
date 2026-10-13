<?php
/** @var array $commandes */
/** @var string $statut */
/** @var string $client */
use App\Services\OrderStatus;
?>
<div class="container py-5">
    <h1 class="mb-4">Commandes</h1>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <form method="get" action="/gestion/commandes" class="filters p-3 mb-4 row g-3 align-items-end" role="search" aria-label="Filtrer les commandes">
        <div class="col-md-4">
            <label for="statut" class="form-label">Statut</label>
            <select id="statut" name="statut" class="form-select">
                <option value="">Tous les statuts</option>
                <?php foreach (OrderStatus::LABELS as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $statut === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label for="client" class="form-label">Client ou n° de commande</label>
            <input type="search" id="client" name="client" class="form-control" value="<?= e($client) ?>" placeholder="Nom, prénom, e-mail ou VG-2026-…">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
            <a href="/gestion/commandes" class="btn btn-outline-secondary">Effacer</a>
        </div>
    </form>

    <p role="status" class="fw-semibold"><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <caption class="visually-hidden">Liste des commandes filtrées</caption>
            <thead>
                <tr>
                    <th scope="col">N°</th><th scope="col">Client</th><th scope="col">Menu</th><th scope="col">Prestation</th>
                    <th scope="col">Pers.</th><th scope="col" class="text-end">Total</th><th scope="col">Statut</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($commandes as $c): ?>
                <tr>
                    <td><?= e($c['numero_commande']) ?></td>
                    <td><?= e($c['client_prenom'] . ' ' . $c['client_nom']) ?><br><span class="small text-muted"><?= e($c['client_email']) ?></span></td>
                    <td><?= e($c['menu_titre']) ?></td>
                    <td><?= e(date_fr($c['date_prestation'])) ?> <?= e(time_fr($c['heure_livraison'])) ?><br><span class="small text-muted"><?= e($c['ville_prestation']) ?></span></td>
                    <td><?= (int) $c['nombre_personne'] ?></td>
                    <td class="text-end"><?= price($c['prix_total']) ?></td>
                    <td><?= OrderStatus::badge($c['statut']) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="/gestion/commandes/<?= (int) $c['commande_id'] ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$commandes): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Aucune commande ne correspond à ces critères.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
