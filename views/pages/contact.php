<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h1 class="mb-2">Nous contacter</h1>
            <p class="lead text-muted mb-4">Une question sur un menu, un devis pour un évènement ? Écrivez-nous, nous vous répondons par e-mail sous 48 heures.</p>

            <form action="/contact" method="post" novalidate class="card p-4 border-0 shadow-sm">
                <?= csrf_field() ?>
                <p class="small text-muted">Tous les champs sont obligatoires.</p>

                <div class="mb-3">
                    <label for="email" class="form-label">Votre adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-control<?= invalid('email') ?>" value="<?= e(old('email', \App\Core\Auth::user()['email'] ?? '')) ?>" required maxlength="180" autocomplete="email">
                    <?= field_error('email') ?>
                </div>

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre de votre demande</label>
                    <input type="text" id="titre" name="titre" class="form-control<?= invalid('titre') ?>" value="<?= e(old('titre')) ?>" required maxlength="150">
                    <?= field_error('titre') ?>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="6" class="form-control<?= invalid('description') ?>" required maxlength="3000"><?= e(old('description')) ?></textarea>
                    <?= field_error('description') ?>
                </div>

                <!-- Champ piège anti-spam, masqué aux humains et aux technologies d'assistance -->
                <div class="visually-hidden" aria-hidden="true">
                    <label for="website">Ne pas remplir ce champ</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <p class="small text-muted">Votre adresse e-mail est utilisée uniquement pour vous répondre (voir notre <a href="/confidentialite">politique de confidentialité</a>).</p>
                <button type="submit" class="btn btn-primary">Envoyer ma demande</button>
            </form>
        </div>
    </div>
</div>
