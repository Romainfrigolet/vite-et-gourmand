<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <h1 class="mb-4">Connexion</h1>
            <form action="/connexion" method="post" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-control<?= invalid('email') ?>" value="<?= e(old('email')) ?>" required autocomplete="username" autofocus>
                    <?= field_error('email') ?>
                </div>
                <div class="mb-2">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary" data-toggle-password="#password" aria-pressed="false">Afficher</button>
                    </div>
                </div>
                <p class="mb-4"><a href="/mot-de-passe-oublie">Mot de passe oublié ?</a></p>
                <button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
            </form>
            <p class="mt-4 text-center">Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>
        </div>
    </div>
</div>
