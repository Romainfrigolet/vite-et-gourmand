<?php /** @var array $commande */ ?>
<div class="container py-5">
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Modifier la commande <?= e($commande['numero_commande']) ?></h1>
            <p class="text-muted mb-4">Client : <?= e($commande['client_prenom'] . ' ' . $commande['client_nom']) ?> — <?= e(phone_fr($commande['client_telephone'])) ?> — <?= e($commande['client_email']) ?></p>
            <form method="post" action="/gestion/commandes/<?= (int) $commande['commande_id'] ?>/modifier" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <div class="alert alert-warning">Contactez le client avant de modifier sa commande, puis indiquez ci-dessous le mode de contact et le motif.</div>
                <fieldset class="mb-4">
                    <legend class="h6 fs-6 fw-semibold">Contact préalable avec le client</legend>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="contact_tel" value="telephone" <?= old('mode_contact') === 'telephone' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="contact_tel">Appel GSM</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="contact_mail" value="email" <?= old('mode_contact') === 'email' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="contact_mail">E-mail</label>
                    </div>
                    <?= field_error('mode_contact') ?>
                    <div class="mt-3">
                        <label for="motif" class="form-label">Motif de la modification</label>
                        <input type="text" id="motif" name="motif" maxlength="450" class="form-control<?= invalid('motif') ?>" value="<?= e(old('motif')) ?>" required>
                        <?= field_error('motif') ?>
                    </div>
                </fieldset>
                <?php require BASE_PATH . '/views/partials/order-fields.php'; ?>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="/gestion/commandes/<?= (int) $commande['commande_id'] ?>" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>
