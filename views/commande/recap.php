<?php
/** @var array $menu */
/** @var array $order */
/** @var array $prices */
/** @var array $user */
?>
<div class="container py-5">
    <h1 class="mb-2">Récapitulatif de votre commande</h1>
    <p class="lead text-muted mb-4">Vérifiez le détail avant de valider. Vous pourrez modifier ou annuler votre commande tant qu'elle n'a pas été acceptée par notre équipe.</p>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="prestation-title">
                <h2 id="prestation-title" class="h5">Prestation</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Client</dt><dd class="col-sm-7"><?= e($user['prenom'] . ' ' . $user['nom']) ?><br><?= e($user['email']) ?><br><?= e(phone_fr($user['telephone'])) ?></dd>
                    <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><?= e($menu['titre']) ?></dd>
                    <dt class="col-sm-5">Nombre de personnes</dt><dd class="col-sm-7"><?= (int) $order['nombre_personne'] ?></dd>
                    <dt class="col-sm-5">Date et heure</dt><dd class="col-sm-7"><?= e(date_fr($order['date_prestation'])) ?> à <?= e(time_fr($order['heure_livraison'])) ?></dd>
                    <dt class="col-sm-5">Lieu</dt><dd class="col-sm-7"><?= e($order['adresse_prestation']) ?><br><?= e($order['code_postal_prestation'] . ' ' . $order['ville_prestation']) ?></dd>
                </dl>
            </section>

            <section class="conditions-box p-3 rounded mb-4" aria-labelledby="cond-title">
                <h2 id="cond-title" class="h5 mb-2"><span aria-hidden="true">⚠ </span>Conditions du menu</h2>
                <p class="mb-0"><?= nl2br(e($menu['conditions'] ?: 'Aucune condition particulière.')) ?></p>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="card border-0 shadow-sm p-4 recap" aria-labelledby="prix-title">
                <h2 id="prix-title" class="h5">Détail du prix</h2>
                <dl>
                    <dt><?= (int) $order['nombre_personne'] ?> × <?= price($menu['prix_par_personne']) ?></dt>
                    <dd><?= price($prices['prix_menu']) ?></dd>
                    <dt>Remise 10 %<?= $prices['remise_appliquee'] ? '' : ' (non applicable)' ?></dt>
                    <dd><?= $prices['remise'] > 0 ? '− ' . price($prices['remise']) : price(0) ?></dd>
                    <dt>Livraison<?= $prices['bordeaux'] ? ' (Bordeaux)' : ' (' . e(number_format($prices['distance_km'], 1, ',', '')) . ' km)' ?></dt>
                    <dd><?= $prices['bordeaux'] ? 'Offerte' : price($prices['prix_livraison']) ?></dd>
                    <dt class="total">Total TTC</dt>
                    <dd class="total"><?= price($prices['prix_total']) ?></dd>
                </dl>
                <?php if (!$prices['bordeaux']): ?>
                    <p class="small text-muted mt-2 mb-0">Livraison hors Bordeaux : 5,00 € + 0,59 € × <?= e(number_format($prices['distance_km'], 1, ',', '')) ?> km.</p>
                <?php endif; ?>

                <form action="/commander/confirmer" method="post" class="mt-4">
                    <?= csrf_field() ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="accept_conditions" name="accept_conditions" value="1" required>
                        <label class="form-check-label" for="accept_conditions">J'ai lu les conditions du menu et les <a href="/cgv">conditions générales de vente</a>.</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Valider ma commande</button>
                        <a href="/commander/<?= (int) $menu['menu_id'] ?>" class="btn btn-outline-secondary">Modifier</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
