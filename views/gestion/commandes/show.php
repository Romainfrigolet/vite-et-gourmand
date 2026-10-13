<?php
/** @var array $commande */
/** @var array $history */
/** @var array $next */
use App\Services\OrderStatus;

$id = (int) $commande['commande_id'];
$canEdit = OrderStatus::editableByStaff($commande['statut']);
$canCancel = OrderStatus::cancellableByStaff($commande['statut']);
?>
<div class="container py-5">
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <h1 class="mb-0">Commande <?= e($commande['numero_commande']) ?></h1>
        <p class="fs-5 mb-0"><?= OrderStatus::badge($commande['statut']) ?></p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="client-title">
                <h2 id="client-title" class="h5">Client</h2>
                <p class="mb-0">
                    <?= e($commande['client_prenom'] . ' ' . $commande['client_nom']) ?><br>
                    GSM : <a href="tel:<?= e($commande['client_telephone']) ?>"><?= e(phone_fr($commande['client_telephone'])) ?></a><br>
                    E-mail : <a href="mailto:<?= e($commande['client_email']) ?>"><?= e($commande['client_email']) ?></a>
                </p>
            </section>

            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="presta-title">
                <h2 id="presta-title" class="h5">Prestation</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Commandée le</dt><dd class="col-sm-7"><?= e(date_fr($commande['date_commande'], true)) ?></dd>
                    <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><?= e($commande['menu_titre']) ?></dd>
                    <dt class="col-sm-5">Personnes</dt><dd class="col-sm-7"><?= (int) $commande['nombre_personne'] ?> (minimum <?= (int) $commande['nombre_personne_minimum'] ?>)</dd>
                    <dt class="col-sm-5">Date et heure</dt><dd class="col-sm-7"><?= e(date_fr($commande['date_prestation'])) ?> à <?= e(time_fr($commande['heure_livraison'])) ?></dd>
                    <dt class="col-sm-5">Lieu</dt><dd class="col-sm-7"><?= e($commande['adresse_prestation']) ?>, <?= e($commande['code_postal_prestation'] . ' ' . $commande['ville_prestation']) ?></dd>
                    <dt class="col-sm-5">Prix du menu</dt><dd class="col-sm-7"><?= price($commande['prix_menu']) ?><?= (float) $commande['remise'] > 0 ? ' − ' . price($commande['remise']) . ' de remise' : '' ?></dd>
                    <dt class="col-sm-5">Livraison</dt><dd class="col-sm-7"><?= (float) $commande['prix_livraison'] > 0 ? price($commande['prix_livraison']) . ' (' . e(number_format((float) $commande['distance_km'], 1, ',', '')) . ' km)' : 'Offerte (Bordeaux)' ?></dd>
                    <dt class="col-sm-5">Total</dt><dd class="col-sm-7 fw-bold"><?= price($commande['prix_total']) ?></dd>
                    <?php if ($commande['motif_annulation']): ?>
                        <dt class="col-sm-5">Motif d'annulation</dt><dd class="col-sm-7"><?= e($commande['motif_annulation']) ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <?php if ($canEdit || $canCancel): ?>
                <section id="annulation" class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="modif-title">
                    <h2 id="modif-title" class="h5">Modifier ou annuler</h2>
                    <p class="small text-muted">Obligatoire : contacter le client (GSM ou e-mail) avant toute modification ou annulation, puis indiquer le mode de contact et le motif.</p>
                    <?php if ($canEdit): ?>
                        <a href="/gestion/commandes/<?= $id ?>/modifier" class="btn btn-outline-primary mb-3">Modifier la commande</a>
                    <?php endif; ?>
                    <?php if ($canCancel): ?>
                        <form method="post" action="/gestion/commandes/<?= $id ?>/annuler" novalidate data-confirm="Confirmer l'annulation de cette commande ?">
                            <?= csrf_field() ?>
                            <fieldset class="mb-3">
                                <legend class="form-label">Client contacté par</legend>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode_contact" id="contact_tel" value="telephone" <?= old('mode_contact') === 'telephone' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="contact_tel">Appel GSM</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode_contact" id="contact_mail" value="email" <?= old('mode_contact') === 'email' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contact_mail">E-mail</label>
                                </div>
                                <?= field_error('mode_contact') ?>
                            </fieldset>
                            <div class="mb-3">
                                <label for="motif" class="form-label">Motif de l'annulation</label>
                                <textarea id="motif" name="motif" rows="2" maxlength="450" class="form-control<?= invalid('motif') ?>" required><?= e(old('motif')) ?></textarea>
                                <?= field_error('motif') ?>
                            </div>
                            <button type="submit" class="btn btn-outline-danger">Annuler la commande</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="statut-title">
                <h2 id="statut-title" class="h5">Mettre à jour le statut</h2>
                <?php if (!in_array($commande['statut'], ['attente_retour_materiel', 'terminee', 'annulee'], true)): ?>
                    <form method="post" action="/gestion/commandes/<?= $id ?>/materiel" class="mb-3">
                        <?= csrf_field() ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="pret_materiel" name="pret_materiel" value="1" <?= (int) $commande['pret_materiel'] === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pret_materiel">Du matériel est prêté au client</label>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-secondary mt-2">Enregistrer</button>
                    </form>
                <?php elseif ((int) $commande['pret_materiel'] === 1): ?>
                    <p class="small">Matériel prêté : <?= (int) $commande['restitution_materiel'] === 1 ? 'restitué' : '<strong>non restitué</strong>' ?></p>
                <?php endif; ?>

                <?php if ($next): ?>
                    <form method="post" action="/gestion/commandes/<?= $id ?>/statut">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label for="statut" class="form-label">Nouveau statut</label>
                            <select id="statut" name="statut" class="form-select">
                                <?php foreach ($next as $s): ?>
                                    <option value="<?= e($s) ?>"><?= e(OrderStatus::label($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="commentaire" class="form-label">Commentaire (facultatif)</label>
                            <input type="text" id="commentaire" name="commentaire" maxlength="500" class="form-control">
                        </div>
                        <?php if (in_array('attente_retour_materiel', $next, true)): ?>
                            <p class="small text-muted">Le client recevra un e-mail lui rappelant de restituer le matériel sous 10 jours ouvrés (600 € de frais sinon).</p>
                        <?php elseif (in_array('terminee', $next, true)): ?>
                            <p class="small text-muted">Le client recevra un e-mail l'invitant à donner son avis.</p>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted mb-0">Cette commande est clôturée.</p>
                <?php endif; ?>
            </section>

            <section class="card border-0 shadow-sm p-4" aria-labelledby="histo-title">
                <h2 id="histo-title" class="h5">Historique</h2>
                <ol class="timeline mt-3 mb-0">
                    <?php foreach ($history as $h): ?>
                        <li>
                            <strong><?= e(OrderStatus::label($h['statut'])) ?></strong><br>
                            <span class="small text-muted"><?= e(date_fr($h['date_modification'], true)) ?><?= $h['prenom'] ? ' — ' . e($h['prenom'] . ' ' . $h['nom']) : '' ?></span>
                            <?php if ($h['commentaire']): ?><br><span class="small"><?= e($h['commentaire']) ?></span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        </div>
    </div>
</div>
