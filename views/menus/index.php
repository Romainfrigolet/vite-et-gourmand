<?php
/** @var array $menus */
/** @var array $themes */
/** @var array $regimes */
/** @var array $filters */
\App\Core\View::script('menus-filter.js');
?>
<div class="container py-5">
    <h1 class="mb-2">Nos menus</h1>
    <p class="lead text-muted mb-4">Tous nos menus sont préparés à la commande et livrés à Bordeaux et en Gironde.</p>

    <div class="row g-4">
        <div class="col-lg-3">
            <form id="menu-filters" class="filters p-4" action="/menus" method="get" role="search" aria-label="Filtrer les menus">
                <h2 class="h5 mb-3">Filtrer</h2>

                <fieldset class="mb-3">
                    <legend class="form-label fw-semibold fs-6">Prix du menu (€)</legend>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="prix_min" class="form-label small">Minimum</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_min" name="prix_min" value="<?= e($filters['prix_min']) ?>" inputmode="numeric">
                        </div>
                        <div class="col-6">
                            <label for="prix_max" class="form-label small">Maximum</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_max" name="prix_max" value="<?= e($filters['prix_max']) ?>" inputmode="numeric">
                        </div>
                    </div>
                    <p class="form-text mb-0">Prix pour le nombre minimum de personnes.</p>
                </fieldset>

                <div class="mb-3">
                    <label for="theme" class="form-label fw-semibold">Thème</label>
                    <select class="form-select" id="theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach ($themes as $t): ?>
                            <option value="<?= (int) $t['theme_id'] ?>" <?= (string) $filters['theme'] === (string) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="regime" class="form-label fw-semibold">Régime</label>
                    <select class="form-select" id="regime" name="regime">
                        <option value="">Tous les régimes</option>
                        <?php foreach ($regimes as $r): ?>
                            <option value="<?= (int) $r['regime_id'] ?>" <?= (string) $filters['regime'] === (string) $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="personnes" class="form-label fw-semibold">Nombre de convives</label>
                    <input type="number" min="1" class="form-control" id="personnes" name="personnes" value="<?= e($filters['personnes']) ?>" aria-describedby="personnes-help" inputmode="numeric">
                    <p id="personnes-help" class="form-text mb-0">Affiche les menus dont le minimum de personnes est compatible.</p>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary js-hide">Appliquer les filtres</button>
                    <a href="/menus" class="btn btn-outline-secondary" id="reset-filters">Réinitialiser</a>
                </div>
            </form>
        </div>

        <div class="col-lg-9">
            <p id="results-count" class="fw-semibold" role="status" aria-live="polite">
                <?= count($menus) ?> menu<?= count($menus) > 1 ? 's' : '' ?> trouvé<?= count($menus) > 1 ? 's' : '' ?>
            </p>
            <div id="menu-results">
                <?= \App\Core\View::capture('menus/_list', ['menus' => $menus]) ?>
            </div>
        </div>
    </div>
</div>
