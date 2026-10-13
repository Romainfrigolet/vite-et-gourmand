<?php
/** @var array $plats */
use App\Repositories\PlatRepository;
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">Plats</h1>
        <a href="/gestion/plats/nouveau" class="btn btn-primary">Créer un plat</a>
    </div>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <caption class="visually-hidden">Liste des plats</caption>
            <thead><tr><th scope="col">Plat</th><th scope="col">Type</th><th scope="col">Allergènes</th><th scope="col">Menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($plats as $p): ?>
                <tr>
                    <td><?= e($p['titre_plat']) ?></td>
                    <td><?= e(PlatRepository::TYPES[$p['type_plat']]) ?></td>
                    <td class="small"><?= e($p['allergenes'] ?: '—') ?></td>
                    <td><?= (int) $p['nb_menus'] ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="/gestion/plats/<?= (int) $p['plat_id'] ?>/modifier">Modifier<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></a>
                        <form method="post" action="/gestion/plats/<?= (int) $p['plat_id'] ?>/supprimer" class="d-inline" data-confirm="Supprimer le plat « <?= e($p['titre_plat']) ?> » ? Il sera retiré des menus qui le contiennent.">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
