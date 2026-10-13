<?php
/** Champs du profil client, partagés entre l'inscription et « Mes informations ». */
/** @var array|null $profile valeurs par défaut (espace client) */
$profile = $profile ?? [];
$val = static fn (string $k): string => old($k, $profile[$k] ?? '');
?>
<div class="row g-3">
    <div class="col-md-6">
        <label for="prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
        <input type="text" id="prenom" name="prenom" class="form-control<?= invalid('prenom') ?>" value="<?= e($val('prenom')) ?>" required maxlength="80" autocomplete="given-name">
        <?= field_error('prenom') ?>
    </div>
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
        <input type="text" id="nom" name="nom" class="form-control<?= invalid('nom') ?>" value="<?= e($val('nom')) ?>" required maxlength="80" autocomplete="family-name">
        <?= field_error('nom') ?>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Adresse e-mail <span aria-hidden="true">*</span></label>
        <input type="email" id="email" name="email" class="form-control<?= invalid('email') ?>" value="<?= e($val('email')) ?>" required maxlength="180" autocomplete="email">
        <?= field_error('email') ?>
    </div>
    <div class="col-md-6">
        <label for="telephone" class="form-label">Numéro de GSM <span aria-hidden="true">*</span></label>
        <input type="tel" id="telephone" name="telephone" class="form-control<?= invalid('telephone') ?>" value="<?= e($val('telephone')) ?>" required autocomplete="tel" placeholder="06 12 34 56 78">
        <?= field_error('telephone') ?>
    </div>
    <div class="col-12">
        <label for="adresse_postale" class="form-label">Adresse postale <span aria-hidden="true">*</span></label>
        <input type="text" id="adresse_postale" name="adresse_postale" class="form-control<?= invalid('adresse_postale') ?>" value="<?= e($val('adresse_postale')) ?>" required maxlength="255" autocomplete="street-address">
        <?= field_error('adresse_postale') ?>
    </div>
    <div class="col-md-4">
        <label for="code_postal" class="form-label">Code postal <span aria-hidden="true">*</span></label>
        <input type="text" id="code_postal" name="code_postal" class="form-control<?= invalid('code_postal') ?>" value="<?= e($val('code_postal')) ?>" required inputmode="numeric" pattern="\d{5}" maxlength="5" autocomplete="postal-code">
        <?= field_error('code_postal') ?>
    </div>
    <div class="col-md-8">
        <label for="ville" class="form-label">Ville <span aria-hidden="true">*</span></label>
        <input type="text" id="ville" name="ville" class="form-control<?= invalid('ville') ?>" value="<?= e($val('ville')) ?>" required maxlength="100" autocomplete="address-level2">
        <?= field_error('ville') ?>
    </div>
</div>
