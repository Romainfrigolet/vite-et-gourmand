<?php
/** @var array $menu */
/** @var array $images */
/** @var array $plats */
use App\Core\Auth;

\App\Core\View::script('gallery.js');
$typeLabels = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$soldOut = (int) $menu['quantite_restante'] === 0;
?>
<div class="container py-5">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/menus">Nos menus</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-6">
            <?php if ($images): ?>
                <figure class="mb-3">
                    <img id="gallery-main" src="<?= asset($images[0]['chemin']) ?>" alt="<?= e($images[0]['alt']) ?>" class="gallery-main" width="800" height="600">
                    <figcaption id="gallery-caption" class="small text-muted mt-2"><?= e($images[0]['alt']) ?></figcaption>
                </figure>
                <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs d-flex flex-wrap gap-2" aria-label="Galerie d'images du menu">
                        <?php foreach ($images as $i => $img): ?>
                            <button type="button" class="gallery-thumb-btn" data-src="<?= asset($img['chemin']) ?>" data-alt="<?= e($img['alt']) ?>"
                                    aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Afficher l'image : <?= e($img['alt']) ?>">
                                <img src="<?= asset($img['chemin']) ?>" alt="" class="gallery-thumb" width="96" height="72">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-6">
            <div class="mb-2 d-flex flex-wrap gap-1">
                <span class="badge badge-theme">Thème : <?= e($menu['theme']) ?></span>
                <span class="badge badge-regime">Régime : <?= e($menu['regime']) ?></span>
            </div>
            <h1 class="mb-3"><?= e($menu['titre']) ?></h1>
            <p class="lead"><?= nl2br(e($menu['description'])) ?></p>

            <dl class="row mb-4">
                <dt class="col-sm-6">Prix</dt>
                <dd class="col-sm-6"><strong class="text-or fs-5"><?= price($menu['prix_minimum']) ?></strong> pour <?= (int) $menu['nombre_personne_minimum'] ?> personnes</dd>
                <dt class="col-sm-6">Prix par personne</dt>
                <dd class="col-sm-6"><?= price($menu['prix_par_personne']) ?></dd>
                <dt class="col-sm-6">Nombre de personnes minimum</dt>
                <dd class="col-sm-6"><?= (int) $menu['nombre_personne_minimum'] ?></dd>
                <dt class="col-sm-6">Délai de commande</dt>
                <dd class="col-sm-6"><?= (int) $menu['delai_commande_jours'] ?> jours avant la prestation</dd>
                <dt class="col-sm-6">Disponibilité</dt>
                <dd class="col-sm-6">
                    <?php if ($soldOut): ?>
                        <span class="badge text-bg-secondary">Épuisé</span>
                    <?php else: ?>
                        Encore <?= (int) $menu['quantite_restante'] ?> commande<?= (int) $menu['quantite_restante'] > 1 ? 's' : '' ?> possible<?= (int) $menu['quantite_restante'] > 1 ? 's' : '' ?>
                    <?php endif; ?>
                </dd>
            </dl>

            <section class="conditions-box p-3 mb-4 rounded" aria-labelledby="conditions-title">
                <h2 id="conditions-title" class="h5 mb-2"><span aria-hidden="true">⚠ </span>Conditions de ce menu — à lire avant de commander</h2>
                <p class="mb-0"><?= nl2br(e($menu['conditions'] ?: 'Aucune condition particulière.')) ?></p>
            </section>

            <p class="small text-muted">Une remise de 10 % est appliquée à partir de <?= (int) $menu['nombre_personne_minimum'] + 5 ?> personnes. Livraison gratuite à Bordeaux, 5 € + 0,59 €/km en dehors.</p>

            <?php if ($soldOut): ?>
                <button type="button" class="btn btn-primary btn-lg" disabled>Menu épuisé</button>
            <?php elseif (Auth::check() && !Auth::isCustomer()): ?>
                <p class="alert alert-info">Les commandes sont réservées aux comptes clients.</p>
            <?php else: ?>
                <a href="/commander/<?= (int) $menu['menu_id'] ?>" class="btn btn-primary btn-lg">Commander ce menu</a>
                <?php if (!Auth::check()): ?>
                    <p class="small text-muted mt-2">Vous devrez vous connecter ou créer un compte pour finaliser votre commande.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <section class="mt-5" aria-labelledby="composition-title">
        <h2 id="composition-title" class="mb-4">Composition du menu</h2>
        <div class="row g-4">
            <?php foreach ($typeLabels as $type => $label): ?>
                <?php if (!empty($plats[$type])): ?>
                    <div class="col-md-4">
                        <h3 class="h5 text-or"><?= e($label) ?></h3>
                        <ul class="list-unstyled">
                            <?php foreach ($plats[$type] as $plat): ?>
                                <li class="mb-3">
                                    <strong><?= e($plat['titre_plat']) ?></strong>
                                    <?php if ($plat['description']): ?><br><span class="text-muted"><?= e($plat['description']) ?></span><?php endif; ?>
                                    <br><span class="allergenes">Allergènes : <?= e($plat['allergenes'] ?: 'aucun allergène majeur') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
