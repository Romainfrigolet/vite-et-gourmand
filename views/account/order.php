<?php
/** @var array $commande */
/** @var array $history */
/** @var array|null $avis */
use App\Services\OrderStatus;

$editable = OrderStatus::editableByCustomer($commande['statut']);
?>
<div class="container py-5">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/mon-compte">Mon espace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commande <?= e($commande['numero_commande']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <h1 class="mb-0">Commande <?= e($commande['numero_commande']) ?></h1>
        <p class="fs-5 mb-0"><?= OrderStatus::badge($commande['statut']) ?></p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="detail-title">
                <h2 id="detail-title" class="h5">Détail</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Passée le</dt><dd class="col-sm-7"><?= e(date_fr($commande['date_commande'], true)) ?></dd>
                    <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><a href="/menus/<?= (int) $commande['menu_id'] ?>"><?= e($commande['menu_titre']) ?></a></dd>
                    <dt class="col-sm-5">Nombre de personnes</dt><dd class="col-sm-7"><?= (int) $commande['nombre_personne'] ?></dd>
                    <dt class="col-sm-5">Prestation</dt><dd class="col-sm-7"><?= e(date_fr($commande['date_prestation'])) ?> à <?= e(time_fr($commande['heure_livraison'])) ?></dd>
                    <dt class="col-sm-5">Lieu</dt><dd class="col-sm-7"><?= e($commande['adresse_prestation']) ?><br><?= e($commande['code_postal_prestation'] . ' ' . $commande['ville_prestation']) ?></dd>
                    <dt class="col-sm-5">Prix du menu</dt><dd class="col-sm-7"><?= price($commande['prix_menu']) ?></dd>
                    <dt class="col-sm-5">Remise</dt><dd class="col-sm-7"><?= (float) $commande['remise'] > 0 ? '− ' . price($commande['remise']) : '—' ?></dd>
                    <dt class="col-sm-5">Livraison</dt><dd class="col-sm-7"><?= (float) $commande['prix_livraison'] > 0 ? price($commande['prix_livraison']) . ' (' . e(number_format((float) $commande['distance_km'], 1, ',', '')) . ' km)' : 'Offerte' ?></dd>
                    <dt class="col-sm-5">Total</dt><dd class="col-sm-7 fw-bold"><?= price($commande['prix_total']) ?></dd>
                    <?php if ($commande['statut'] === 'annulee' && $commande['motif_annulation']): ?>
                        <dt class="col-sm-5">Motif d'annulation</dt><dd class="col-sm-7"><?= e($commande['motif_annulation']) ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <?php if ((int) $commande['pret_materiel'] === 1 && in_array($commande['statut'], ['livre', 'attente_retour_materiel'], true)): ?>
                <div class="alert alert-warning">
                    <strong>Matériel prêté :</strong> merci de le restituer sous 10 jours ouvrés en prenant contact avec nous
                    (05 56 00 00 00 ou <a href="/contact">formulaire de contact</a>). Passé ce délai, 600 € de frais seront facturés (<a href="/cgv">CGV</a>).
                </div>
            <?php endif; ?>

            <?php if ($editable): ?>
                <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="actions-title">
                    <h2 id="actions-title" class="h5">Modifier ou annuler</h2>
                    <p class="text-muted">Possible tant que notre équipe n'a pas accepté votre commande.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/mon-compte/commandes/<?= (int) $commande['commande_id'] ?>/modifier" class="btn btn-outline-primary">Modifier la commande</a>
                        <form method="post" action="/mon-compte/commandes/<?= (int) $commande['commande_id'] ?>/annuler" data-confirm="Voulez-vous vraiment annuler cette commande ?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger">Annuler la commande</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="suivi-title">
                <h2 id="suivi-title" class="h5">Suivi de la commande</h2>
                <?php if ($commande['statut'] === 'en_attente'): ?>
                    <p class="mb-0 text-muted">Le suivi détaillé sera disponible dès que notre équipe aura accepté votre commande.</p>
                <?php else: ?>
                    <ol class="timeline mt-3 mb-0">
                        <?php foreach ($history as $h): ?>
                            <li>
                                <strong><?= e(OrderStatus::label($h['statut'])) ?></strong><br>
                                <span class="small text-muted"><?= e(date_fr($h['date_modification'], true)) ?></span>
                                <?php if ($h['commentaire']): ?><br><span class="small"><?= e($h['commentaire']) ?></span><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </section>

            <?php if ($commande['statut'] === 'terminee'): ?>
                <section id="avis" class="card border-0 shadow-sm p-4" aria-labelledby="avis-title">
                    <h2 id="avis-title" class="h5">Votre avis</h2>
                    <?php if ($avis): ?>
                        <p class="stars mb-1" aria-label="Note : <?= (int) $avis['note'] ?> sur 5"><span aria-hidden="true"><?= str_repeat('★', (int) $avis['note']) . str_repeat('☆', 5 - (int) $avis['note']) ?></span></p>
                        <p>« <?= e($avis['commentaire']) ?> »</p>
                        <p class="small text-muted mb-0">Statut : <?= e(\App\Repositories\AvisRepository::STATUTS[$avis['statut']]) ?></p>
                    <?php else: ?>
                        <form method="post" action="/mon-compte/commandes/<?= (int) $commande['commande_id'] ?>/avis" novalidate>
                            <?= csrf_field() ?>
                            <fieldset class="mb-3">
                                <legend class="form-label">Note <span class="visually-hidden">(de 1 à 5)</span></legend>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="note" id="note<?= $i ?>" value="<?= $i ?>" <?= old('note') === (string) $i ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="note<?= $i ?>"><?= $i ?> <span aria-hidden="true">★</span><span class="visually-hidden"> sur 5</span></label>
                                    </div>
                                <?php endfor; ?>
                                <?= field_error('note') ?>
                            </fieldset>
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire</label>
                                <textarea id="commentaire" name="commentaire" rows="4" maxlength="1000" class="form-control<?= invalid('commentaire') ?>" required><?= e(old('commentaire')) ?></textarea>
                                <?= field_error('commentaire') ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
