<?php
/** @var array|null $menu */
/** @var array $menus */
/** @var array $user */
/** @var array|null $pending */
\App\Core\View::script('order-form.js');
$src = $pending ?? [];
$val = static fn (string $k, string $default = ''): string => old($k, $src[$k] ?? $default);
$minDate = $menu ? (new DateTimeImmutable('today'))->modify('+' . (int) $menu['delai_commande_jours'] . ' days')->format('Y-m-d') : date('Y-m-d');
?>
<div class="container py-5">
    <h1 class="mb-4">Commander un menu</h1>

    <?php if (!$menu): ?>
        <form action="/commander" method="get" class="card p-4 border-0 shadow-sm mb-4">
            <label for="menu" class="form-label fw-semibold">Choisissez votre menu</label>
            <div class="d-flex gap-2 flex-wrap">
                <select id="menu" name="menu" class="form-select w-auto" required>
                    <option value="">— Sélectionner un menu —</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= (int) $m['menu_id'] ?>"><?= e($m['titre']) ?> (<?= price($m['prix_minimum']) ?> pour <?= (int) $m['nombre_personne_minimum'] ?> pers.)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Continuer</button>
            </div>
        </form>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <form action="/commander/recapitulatif" method="post" novalidate id="order-form" class="card p-4 border-0 shadow-sm"
                  data-prix-personne="<?= e($menu['prix_par_personne']) ?>"
                  data-minimum="<?= (int) $menu['nombre_personne_minimum'] ?>">
                <?= csrf_field() ?>

                <fieldset class="mb-4">
                    <legend class="h5">1. Vos coordonnées</legend>
                    <p class="small text-muted">Remplies automatiquement depuis votre compte — <a href="/mon-compte/informations">modifier mes informations</a>.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="client_nom">Nom et prénom</label>
                            <input class="form-control" id="client_nom" value="<?= e($user['prenom'] . ' ' . $user['nom']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="client_email">E-mail</label>
                            <input class="form-control" id="client_email" value="<?= e($user['email']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="client_tel">GSM</label>
                            <input class="form-control" id="client_tel" value="<?= e(phone_fr($user['telephone'])) ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h5">2. La prestation</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date_prestation" class="form-label">Date de la prestation <span aria-hidden="true">*</span></label>
                            <input type="date" id="date_prestation" name="date_prestation" class="form-control<?= invalid('date_prestation') ?>"
                                   min="<?= e($minDate) ?>" value="<?= e($val('date_prestation')) ?>" required aria-describedby="date-help">
                            <p id="date-help" class="form-text mb-0">Au plus tôt le <?= e(date_fr($minDate)) ?> (commande <?= (int) $menu['delai_commande_jours'] ?> jours à l'avance).</p>
                            <?= field_error('date_prestation') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="heure_livraison" class="form-label">Heure de livraison souhaitée <span aria-hidden="true">*</span></label>
                            <input type="time" id="heure_livraison" name="heure_livraison" class="form-control<?= invalid('heure_livraison') ?>"
                                   min="08:00" max="22:00" value="<?= e($val('heure_livraison', '12:00')) ?>" required aria-describedby="heure-help">
                            <p id="heure-help" class="form-text mb-0">Entre 8h00 et 22h00.</p>
                            <?= field_error('heure_livraison') ?>
                        </div>
                        <div class="col-12">
                            <label for="adresse_prestation" class="form-label">Adresse (lieu de la prestation) <span aria-hidden="true">*</span></label>
                            <input type="text" id="adresse_prestation" name="adresse_prestation" class="form-control<?= invalid('adresse_prestation') ?>"
                                   value="<?= e($val('adresse_prestation', $user['adresse_postale'] ?? '')) ?>" required maxlength="255" autocomplete="street-address">
                            <?= field_error('adresse_prestation') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="code_postal_prestation" class="form-label">Code postal <span aria-hidden="true">*</span></label>
                            <input type="text" id="code_postal_prestation" name="code_postal_prestation" class="form-control<?= invalid('code_postal_prestation') ?>"
                                   value="<?= e($val('code_postal_prestation', $user['code_postal'] ?? '')) ?>" required inputmode="numeric" maxlength="5" pattern="\d{5}" autocomplete="postal-code">
                            <?= field_error('code_postal_prestation') ?>
                        </div>
                        <div class="col-md-8">
                            <label for="ville_prestation" class="form-label">Ville <span aria-hidden="true">*</span></label>
                            <input type="text" id="ville_prestation" name="ville_prestation" class="form-control<?= invalid('ville_prestation') ?>"
                                   value="<?= e($val('ville_prestation', $user['ville'] ?? '')) ?>" required maxlength="100" autocomplete="address-level2">
                            <?= field_error('ville_prestation') ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h5">3. Le menu</legend>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="menu_id" class="form-label">Menu choisi</label>
                            <select id="menu_id" name="menu_id" class="form-select" aria-describedby="menu-help">
                                <?php foreach ($menus as $m): ?>
                                    <option value="<?= (int) $m['menu_id'] ?>" <?= (int) $m['menu_id'] === (int) $menu['menu_id'] ? 'selected' : '' ?>><?= e($m['titre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p id="menu-help" class="form-text mb-0">Changer de menu recharge la page avec ses propres conditions.</p>
                        </div>
                        <div class="col-md-4">
                            <label for="nombre_personne" class="form-label">Nombre de personnes <span aria-hidden="true">*</span></label>
                            <input type="number" id="nombre_personne" name="nombre_personne" class="form-control<?= invalid('nombre_personne') ?>"
                                   min="<?= (int) $menu['nombre_personne_minimum'] ?>" max="1000" required inputmode="numeric"
                                   value="<?= e($val('nombre_personne', (string) $menu['nombre_personne_minimum'])) ?>" aria-describedby="personnes-help">
                            <p id="personnes-help" class="form-text mb-0">Minimum <?= (int) $menu['nombre_personne_minimum'] ?> personnes. −10 % dès <?= (int) $menu['nombre_personne_minimum'] + 5 ?>.</p>
                            <?= field_error('nombre_personne') ?>
                        </div>
                    </div>
                    <div class="conditions-box p-3 mt-3 rounded">
                        <h3 class="h6 mb-1"><span aria-hidden="true">⚠ </span>Conditions du menu « <?= e($menu['titre']) ?> »</h3>
                        <p class="mb-0 small"><?= nl2br(e($menu['conditions'] ?: 'Aucune condition particulière.')) ?></p>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary btn-lg">Vérifier le récapitulatif</button>
            </form>
        </div>

        <aside class="col-lg-4" aria-labelledby="recap-title">
            <div class="card border-0 shadow-sm p-4 recap">
                <h2 id="recap-title" class="h5">Estimation du prix</h2>
                <p class="small text-muted"><?= e($menu['titre']) ?> — <?= price($menu['prix_par_personne']) ?> / personne</p>
                <dl aria-live="polite">
                    <dt>Menu (<span id="recap-personnes"><?= (int) $menu['nombre_personne_minimum'] ?></span> pers.)</dt>
                    <dd id="recap-menu">—</dd>
                    <dt>Remise 10 %</dt>
                    <dd id="recap-remise">—</dd>
                    <dt>Livraison</dt>
                    <dd id="recap-livraison">à calculer</dd>
                    <dt class="total">Total</dt>
                    <dd class="total" id="recap-total">—</dd>
                </dl>
                <p class="small text-muted mt-3 mb-0" id="recap-livraison-info">Livraison gratuite à Bordeaux, sinon 5 € + 0,59 €/km.</p>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>
