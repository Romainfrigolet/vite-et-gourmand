<?php /** @var array $profile */ ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="Fil d'Ariane">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/mon-compte">Mon espace</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mes informations</li>
                </ol>
            </nav>
            <h1 class="mb-4">Mes informations</h1>

            <form action="/mon-compte/informations" method="post" novalidate class="card p-4 border-0 shadow-sm mb-4">
                <?= csrf_field() ?>
                <fieldset>
                    <legend class="h5">Coordonnées</legend>
                    <?php require BASE_PATH . '/views/partials/profile-fields.php'; ?>
                </fieldset>

                <fieldset class="mt-4">
                    <legend class="h5">Changer de mot de passe <span class="fs-6 fw-normal text-muted">(facultatif)</span></legend>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" class="form-control<?= invalid('current_password') ?>" autocomplete="current-password">
                            <?= field_error('current_password') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" class="form-control<?= invalid('password') ?>" autocomplete="new-password" data-password-rules="#password-rules" aria-describedby="password-rules">
                            <?= field_error('password') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="password_confirmation" class="form-label">Confirmation</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control<?= invalid('password_confirmation') ?>" autocomplete="new-password">
                            <?= field_error('password_confirmation') ?>
                        </div>
                    </div>
                    <ul id="password-rules" class="password-rules list-unstyled small mt-2 mb-0">
                        <li data-rule="length">Au moins 10 caractères<span class="visually-hidden"></span></li>
                        <li data-rule="upper">Une majuscule<span class="visually-hidden"></span></li>
                        <li data-rule="lower">Une minuscule<span class="visually-hidden"></span></li>
                        <li data-rule="digit">Un chiffre<span class="visually-hidden"></span></li>
                        <li data-rule="special">Un caractère spécial<span class="visually-hidden"></span></li>
                    </ul>
                </fieldset>
                <button type="submit" class="btn btn-primary mt-4">Enregistrer</button>
            </form>

            <section class="card p-4 border-danger" aria-labelledby="delete-title">
                <h2 id="delete-title" class="h5 text-danger">Supprimer mon compte</h2>
                <p>Vos données personnelles seront définitivement effacées. Les commandes passées sont conservées de façon anonyme pour nos obligations comptables.</p>
                <form method="post" action="/mon-compte/supprimer" data-confirm="Cette action est définitive. Supprimer votre compte ?" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-sm-7">
                        <label for="password_delete" class="form-label">Confirmez avec votre mot de passe</label>
                        <input type="password" id="password_delete" name="password_delete" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="col-sm-5">
                        <button type="submit" class="btn btn-outline-danger w-100">Supprimer mon compte</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
