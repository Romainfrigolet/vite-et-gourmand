<?php
/** Champs modifiables d'une commande existante (client ou équipe). Le menu n'est pas modifiable. */
/** @var array $commande */
$val = static fn (string $k): string => old($k, (string) ($commande[$k] ?? ''));
?>
<div class="row g-3">
    <div class="col-md-6">
        <label for="date_prestation" class="form-label">Date de la prestation</label>
        <input type="date" id="date_prestation" name="date_prestation" class="form-control<?= invalid('date_prestation') ?>" value="<?= e($val('date_prestation')) ?>" required>
        <?= field_error('date_prestation') ?>
    </div>
    <div class="col-md-6">
        <label for="heure_livraison" class="form-label">Heure de livraison</label>
        <input type="time" id="heure_livraison" name="heure_livraison" class="form-control<?= invalid('heure_livraison') ?>" min="08:00" max="22:00" value="<?= e(substr($val('heure_livraison'), 0, 5)) ?>" required>
        <?= field_error('heure_livraison') ?>
    </div>
    <div class="col-12">
        <label for="adresse_prestation" class="form-label">Adresse de la prestation</label>
        <input type="text" id="adresse_prestation" name="adresse_prestation" class="form-control<?= invalid('adresse_prestation') ?>" value="<?= e($val('adresse_prestation')) ?>" required maxlength="255">
        <?= field_error('adresse_prestation') ?>
    </div>
    <div class="col-md-4">
        <label for="code_postal_prestation" class="form-label">Code postal</label>
        <input type="text" id="code_postal_prestation" name="code_postal_prestation" class="form-control<?= invalid('code_postal_prestation') ?>" value="<?= e($val('code_postal_prestation')) ?>" required inputmode="numeric" maxlength="5" pattern="\d{5}">
        <?= field_error('code_postal_prestation') ?>
    </div>
    <div class="col-md-8">
        <label for="ville_prestation" class="form-label">Ville</label>
        <input type="text" id="ville_prestation" name="ville_prestation" class="form-control<?= invalid('ville_prestation') ?>" value="<?= e($val('ville_prestation')) ?>" required maxlength="100">
        <?= field_error('ville_prestation') ?>
    </div>
    <div class="col-md-6">
        <label for="menu_affiche" class="form-label">Menu (non modifiable)</label>
        <input type="text" id="menu_affiche" class="form-control" value="<?= e($commande['menu_titre']) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="nombre_personne" class="form-label">Nombre de personnes</label>
        <input type="number" id="nombre_personne" name="nombre_personne" class="form-control<?= invalid('nombre_personne') ?>" min="<?= (int) $commande['nombre_personne_minimum'] ?>" max="1000" value="<?= e($val('nombre_personne')) ?>" required aria-describedby="nb-help">
        <p id="nb-help" class="form-text mb-0">Minimum <?= (int) $commande['nombre_personne_minimum'] ?> personnes. Le prix est recalculé automatiquement.</p>
        <?= field_error('nombre_personne') ?>
    </div>
</div>
