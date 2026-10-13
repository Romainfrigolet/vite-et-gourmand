<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-2">Créer un compte</h1>
            <p class="text-muted mb-4">Déjà client ? <a href="/connexion">Connectez-vous</a>. Les champs marqués d'un astérisque (<span aria-hidden="true">*</span>) sont obligatoires.</p>

            <form action="/inscription" method="post" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <fieldset>
                    <legend class="h5">Vos informations</legend>
                    <?php require BASE_PATH . '/views/partials/profile-fields.php'; ?>
                </fieldset>

                <fieldset class="mt-3">
                    <legend class="h5">Votre mot de passe</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Mot de passe <span aria-hidden="true">*</span></label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control<?= invalid('password') ?>" required minlength="10"
                                       autocomplete="new-password" data-password-rules="#password-rules" aria-describedby="password-rules">
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="#password" aria-pressed="false">Afficher</button>
                            </div>
                            <?= field_error('password') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmation du mot de passe <span aria-hidden="true">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control<?= invalid('password_confirmation') ?>" required autocomplete="new-password">
                            <?= field_error('password_confirmation') ?>
                        </div>
                    </div>
                    <ul id="password-rules" class="password-rules list-unstyled small mt-2 mb-0">
                        <li data-rule="length">Au moins 10 caractères<span class="visually-hidden"></span></li>
                        <li data-rule="upper">Une lettre majuscule<span class="visually-hidden"></span></li>
                        <li data-rule="lower">Une lettre minuscule<span class="visually-hidden"></span></li>
                        <li data-rule="digit">Un chiffre<span class="visually-hidden"></span></li>
                        <li data-rule="special">Un caractère spécial (ex. ! # @ ?)<span class="visually-hidden"></span></li>
                    </ul>
                </fieldset>

                <div class="form-check mt-4">
                    <input class="form-check-input<?= invalid('rgpd') ?>" type="checkbox" id="rgpd" name="rgpd" value="1" required>
                    <label class="form-check-label" for="rgpd">
                        J'accepte que mes données soient utilisées pour gérer mon compte et mes commandes, conformément à la
                        <a href="/confidentialite">politique de confidentialité</a>. <span aria-hidden="true">*</span>
                    </label>
                    <?= field_error('rgpd') ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg mt-4">Créer mon compte</button>
            </form>
        </div>
    </div>
</div>
