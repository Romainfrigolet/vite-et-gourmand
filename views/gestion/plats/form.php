<?php
/** @var array|null $plat */
/** @var array $allergenes */
/** @var array $selected */
use App\Repositories\PlatRepository;

$p = $plat ?? [];
$val = static fn (string $k): string => old($k, (string) ($p[$k] ?? ''));
$checked = old_array('allergenes', $selected);
$action = $plat ? '/gestion/plats/' . (int) $plat['plat_id'] . '/modifier' : '/gestion/plats/nouveau';
?>
<div class="container py-5">
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4"><?= $plat ? 'Modifier le plat' : 'Nouveau plat' ?></h1>
            <form method="post" action="<?= e($action) ?>" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="titre_plat" class="form-label">Nom du plat</label>
                        <input type="text" id="titre_plat" name="titre_plat" class="form-control<?= invalid('titre_plat') ?>" value="<?= e($val('titre_plat')) ?>" required maxlength="120">
                        <?= field_error('titre_plat') ?>
                    </div>
                    <div class="col-md-4">
                        <label for="type_plat" class="form-label">Type</label>
                        <select id="type_plat" name="type_plat" class="form-select<?= invalid('type_plat') ?>" required>
                            <option value="">— Choisir —</option>
                            <?php foreach (PlatRepository::TYPES as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $val('type_plat') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= field_error('type_plat') ?>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="2" maxlength="500" class="form-control<?= invalid('description') ?>"><?= e($val('description')) ?></textarea>
                        <?= field_error('description') ?>
                    </div>
                </div>
                <fieldset class="mt-4">
                    <legend class="h6">Allergènes présents</legend>
                    <div class="row">
                        <?php foreach ($allergenes as $a): ?>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allergenes[]" id="allergene<?= (int) $a['allergene_id'] ?>" value="<?= (int) $a['allergene_id'] ?>" <?= in_array((int) $a['allergene_id'], $checked, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="allergene<?= (int) $a['allergene_id'] ?>"><?= e($a['libelle']) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="/gestion/plats" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>
