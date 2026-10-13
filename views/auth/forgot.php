<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <h1 class="mb-2">Mot de passe oublié</h1>
            <p class="text-muted mb-4">Indiquez l'adresse e-mail de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
            <form action="/mot-de-passe-oublie" method="post" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-control<?= invalid('email') ?>" value="<?= e(old('email')) ?>" required autocomplete="email">
                    <?= field_error('email') ?>
                </div>
                <button type="submit" class="btn btn-primary">Recevoir le lien</button>
            </form>
            <p class="mt-4 text-center"><a href="/connexion">Retour à la connexion</a></p>
        </div>
    </div>
</div>
