<?php /** @var array $menus */ ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">Menus</h1>
        <a href="/gestion/menus/nouveau" class="btn btn-primary">Créer un menu</a>
    </div>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <caption class="visually-hidden">Liste des menus</caption>
            <thead>
                <tr><th scope="col">Titre</th><th scope="col">Thème</th><th scope="col">Régime</th><th scope="col">Min.</th>
                    <th scope="col" class="text-end">Prix / pers.</th><th scope="col">Stock</th><th scope="col">Visible</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($menus as $m): ?>
                <tr>
                    <td><?= e($m['titre']) ?></td>
                    <td><?= e($m['theme']) ?></td>
                    <td><?= e($m['regime']) ?></td>
                    <td><?= (int) $m['nombre_personne_minimum'] ?></td>
                    <td class="text-end"><?= price($m['prix_par_personne']) ?></td>
                    <td><?= (int) $m['quantite_restante'] === 0 ? '<span class="badge text-bg-secondary">Épuisé</span>' : (int) $m['quantite_restante'] ?></td>
                    <td><?= (int) $m['actif'] === 1 ? 'Oui' : '<span class="badge text-bg-light">Masqué</span>' ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="/gestion/menus/<?= (int) $m['menu_id'] ?>/modifier">Modifier<span class="visually-hidden"> <?= e($m['titre']) ?></span></a>
                        <form method="post" action="/gestion/menus/<?= (int) $m['menu_id'] ?>/supprimer" class="d-inline" data-confirm="Supprimer le menu « <?= e($m['titre']) ?> » ?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
