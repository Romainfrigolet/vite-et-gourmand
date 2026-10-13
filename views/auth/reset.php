<?php /** @var string $token */ ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h1 class="mb-4">Choisir un nouveau mot de passe</h1>
            <form action="/reinitialiser-mot-de-passe" method="post" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control<?= invalid('password') ?>" required minlength="10"
                           autocomplete="new-password" data-password-rules="#password-rules" aria-describedby="password-rules">
                    <?= field_error('password') ?>
                    <ul id="password-rules" class="password-rules list-unstyled small mt-2 mb-0">
                        <li data-rule="length">Au moins 10 caractères<span class="visually-hidden"></span></li>
                        <li data-rule="upper">Une lettre majuscule<span class="visually-hidden"></span></li>
                        <li data-rule="lower">Une lettre minuscule<span class="visually-hidden"></span></li>
                        <li data-rule="digit">Un chiffre<span class="visually-hidden"></span></li>
                        <li data-rule="special">Un caractère spécial<span class="visually-hidden"></span></li>
                    </ul>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirmation</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control<?= invalid('password_confirmation') ?>" required autocomplete="new-password">
                    <?= field_error('password_confirmation') ?>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
</div>
