<?php /** @var array $menus */ ?>
<?php if (!$menus): ?>
    <p class="alert alert-info">Aucun menu ne correspond à vos critères. Essayez d'élargir votre recherche.</p>
<?php else: ?>
    <ul class="row g-4 list-unstyled mb-0">
        <?php foreach ($menus as $menu): ?>
            <li class="col-sm-6 col-lg-4">
                <article class="card menu-card h-100">
                    <?php if ($menu['image']): ?>
                        <img src="<?= asset($menu['image']) ?>" class="card-img-top" alt="" loading="lazy" width="800" height="600">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            <span class="badge badge-theme"><?= e($menu['theme']) ?></span>
                            <span class="badge badge-regime"><?= e($menu['regime']) ?></span>
                            <?php if ((int) $menu['quantite_restante'] === 0): ?>
                                <span class="badge text-bg-secondary">Épuisé</span>
                            <?php elseif ((int) $menu['quantite_restante'] <= 5): ?>
                                <span class="badge text-bg-warning">Plus que <?= (int) $menu['quantite_restante'] ?> commandes possibles</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5 card-title"><?= e($menu['titre']) ?></h3>
                        <p class="card-text text-muted"><?= e(mb_strimwidth($menu['description'], 0, 140, '…')) ?></p>
                        <p class="mb-3 mt-auto">
                            <span class="price"><?= price($menu['prix_minimum']) ?></span>
                            <span class="text-muted">pour <?= (int) $menu['nombre_personne_minimum'] ?> personnes minimum</span><br>
                            <small class="text-muted">soit <?= price($menu['prix_par_personne']) ?> par personne</small>
                        </p>
                        <a href="/menus/<?= (int) $menu['menu_id'] ?>" class="btn btn-outline-primary">
                            Voir le détail<span class="visually-hidden"> du menu <?= e($menu['titre']) ?></span>
                        </a>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
