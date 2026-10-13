<?php
/** @var array|null $menu */
/** @var array $themes */
/** @var array $regimes */
/** @var array $plats */
/** @var array $selected */
/** @var array $images */
use App\Repositories\PlatRepository;

$m = $menu ?? [];
$val = static fn (string $k, string $d = ''): string => old($k, (string) ($m[$k] ?? $d));
$selectedPlats = old_array('plats', $selected);
$actif = has_old() ? old('actif') === '1' : (int) ($m['actif'] ?? 1) === 1;
$action = $menu ? '/gestion/menus/' . (int) $menu['menu_id'] . '/modifier' : '/gestion/menus/nouveau';
?>
<div class="container py-5">
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>
    <h1 class="mb-4"><?= $menu ? 'Modifier « ' . e($menu['titre']) . ' »' : 'Nouveau menu' ?></h1>

    <form method="post" action="<?= e($action) ?>" novalidate class="card p-4 border-0 shadow-sm mb-4">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label for="titre" class="form-label">Titre</label>
                <input type="text" id="titre" name="titre" class="form-control<?= invalid('titre') ?>" value="<?= e($val('titre')) ?>" required maxlength="120">
                <?= field_error('titre') ?>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="actif" name="actif" value="1" <?= $actif ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Visible sur le site</label>
                </div>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" class="form-control<?= invalid('description') ?>" required maxlength="3000"><?= e($val('description')) ?></textarea>
                <?= field_error('description') ?>
            </div>
            <div class="col-md-6">
                <label for="theme_id" class="form-label">Thème</label>
                <select id="theme_id" name="theme_id" class="form-select<?= invalid('theme_id') ?>" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($themes as $t): ?>
                        <option value="<?= (int) $t['theme_id'] ?>" <?= $val('theme_id') === (string) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error('theme_id') ?>
            </div>
            <div class="col-md-6">
                <label for="regime_id" class="form-label">Régime</label>
                <select id="regime_id" name="regime_id" class="form-select<?= invalid('regime_id') ?>" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($regimes as $r): ?>
                        <option value="<?= (int) $r['regime_id'] ?>" <?= $val('regime_id') === (string) $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error('regime_id') ?>
            </div>
            <div class="col-md-3">
                <label for="nombre_personne_minimum" class="form-label">Personnes minimum</label>
                <input type="number" id="nombre_personne_minimum" name="nombre_personne_minimum" min="1" class="form-control<?= invalid('nombre_personne_minimum') ?>" value="<?= e($val('nombre_personne_minimum')) ?>" required>
                <?= field_error('nombre_personne_minimum') ?>
            </div>
            <div class="col-md-3">
                <label for="prix_par_personne" class="form-label">Prix par personne (€)</label>
                <input type="number" id="prix_par_personne" name="prix_par_personne" min="0" step="0.01" class="form-control<?= invalid('prix_par_personne') ?>" value="<?= e($val('prix_par_personne')) ?>" required>
                <?= field_error('prix_par_personne') ?>
            </div>
            <div class="col-md-3">
                <label for="delai_commande_jours" class="form-label">Délai de commande (jours)</label>
                <input type="number" id="delai_commande_jours" name="delai_commande_jours" min="0" class="form-control<?= invalid('delai_commande_jours') ?>" value="<?= e($val('delai_commande_jours', '3')) ?>" required>
                <?= field_error('delai_commande_jours') ?>
            </div>
            <div class="col-md-3">
                <label for="quantite_restante" class="form-label">Stock (commandes possibles)</label>
                <input type="number" id="quantite_restante" name="quantite_restante" min="0" class="form-control<?= invalid('quantite_restante') ?>" value="<?= e($val('quantite_restante', '10')) ?>" required>
                <?= field_error('quantite_restante') ?>
            </div>
            <div class="col-12">
                <label for="conditions" class="form-label">Conditions du menu (délai, conservation, matériel prêté…)</label>
                <textarea id="conditions" name="conditions" rows="3" class="form-control<?= invalid('conditions') ?>" maxlength="3000"><?= e($val('conditions')) ?></textarea>
                <?= field_error('conditions') ?>
            </div>
        </div>

        <fieldset class="mt-4">
            <legend class="h5">Composition du menu</legend>
            <p class="small text-muted">Un même plat peut appartenir à plusieurs menus. <a href="/gestion/plats/nouveau">Créer un nouveau plat</a></p>
            <?= field_error('plats') ?>
            <div class="row">
                <?php foreach (PlatRepository::TYPES as $type => $label): ?>
                    <div class="col-md-4">
                        <h3 class="h6 text-or"><?= e($label) ?>s</h3>
                        <?php foreach ($plats as $p): ?>
                            <?php if ($p['type_plat'] === $type): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="plats[]" id="plat<?= (int) $p['plat_id'] ?>" value="<?= (int) $p['plat_id'] ?>" <?= in_array((int) $p['plat_id'], $selectedPlats, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="plat<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><?= $menu ? 'Enregistrer' : 'Créer le menu' ?></button>
            <a href="/gestion/menus" class="btn btn-outline-secondary">Retour à la liste</a>
        </div>
    </form>

    <?php if ($menu): ?>
        <section id="galerie" class="card p-4 border-0 shadow-sm" aria-labelledby="galerie-title">
            <h2 id="galerie-title" class="h5">Galerie d'images</h2>
            <?php if ($images): ?>
                <ul class="row g-3 list-unstyled">
                    <?php foreach ($images as $img): ?>
                        <li class="col-6 col-md-3">
                            <img src="<?= asset($img['chemin']) ?>" alt="<?= e($img['alt']) ?>" class="img-fluid rounded mb-2" width="800" height="600">
                            <form method="post" action="/gestion/images/<?= (int) $img['image_id'] ?>/supprimer" data-confirm="Supprimer cette image ?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Supprimer<span class="visually-hidden"> l'image « <?= e($img['alt']) ?> »</span></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">Aucune image pour l'instant.</p>
            <?php endif; ?>

            <form method="post" action="/gestion/menus/<?= (int) $menu['menu_id'] ?>/images" enctype="multipart/form-data" class="row g-3 align-items-end mt-1">
                <?= csrf_field() ?>
                <div class="col-md-5">
                    <label for="image" class="form-label">Image (JPEG, PNG ou WebP, 2 Mo max.)</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <div class="col-md-5">
                    <label for="alt" class="form-label">Description de l'image (texte alternatif)</label>
                    <input type="text" id="alt" name="alt" class="form-control" maxlength="255" required placeholder="Ex. : Bûche chocolat sur un plateau">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                </div>
            </form>
        </section>
    <?php endif; ?>
</div>
