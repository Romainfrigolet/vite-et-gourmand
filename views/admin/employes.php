<?php /** @var array $employes */ ?>
<div class="container py-5">
    <h1 class="mb-4">Employés</h1>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="table-responsive card border-0 shadow-sm">
                <table class="table align-middle mb-0">
                    <caption class="visually-hidden">Comptes employés</caption>
                    <thead><tr><th scope="col">Employé</th><th scope="col">E-mail (identifiant)</th><th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($employes as $emp): ?>
                        <tr>
                            <td><?= e($emp['prenom'] . ' ' . $emp['nom']) ?></td>
                            <td><?= e($emp['email']) ?></td>
                            <td><?= (int) $emp['actif'] === 1 ? '<span class="badge text-bg-success">Actif</span>' : '<span class="badge text-bg-secondary">Désactivé</span>' ?></td>
                            <td>
                                <form method="post" action="/admin/employes/<?= (int) $emp['utilisateur_id'] ?>/statut"
                                      data-confirm="<?= (int) $emp['actif'] === 1 ? 'Désactiver ce compte ? L\'employé ne pourra plus se connecter.' : 'Réactiver ce compte ?' ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= (int) $emp['actif'] === 1 ? 'btn-outline-danger' : 'btn-outline-primary' ?>">
                                        <?= (int) $emp['actif'] === 1 ? 'Désactiver' : 'Réactiver' ?><span class="visually-hidden"> le compte de <?= e($emp['prenom'] . ' ' . $emp['nom']) ?></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-5">
            <form method="post" action="/admin/employes" novalidate class="card border-0 shadow-sm p-4">
                <?= csrf_field() ?>
                <h2 class="h5">Créer un compte employé</h2>
                <p class="small text-muted">L'employé recevra un e-mail l'informant de la création de son compte. Le mot de passe n'y figure pas : communiquez-le-lui directement.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control<?= invalid('prenom') ?>" value="<?= e(old('prenom')) ?>" required maxlength="80">
                        <?= field_error('prenom') ?>
                    </div>
                    <div class="col-6">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control<?= invalid('nom') ?>" value="<?= e(old('nom')) ?>" required maxlength="80">
                        <?= field_error('nom') ?>
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">E-mail (identifiant de connexion)</label>
                        <input type="email" id="email" name="email" class="form-control<?= invalid('email') ?>" value="<?= e(old('email')) ?>" required autocomplete="off">
                        <?= field_error('email') ?>
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control<?= invalid('password') ?>" required autocomplete="new-password" data-password-rules="#password-rules" aria-describedby="password-rules">
                        <?= field_error('password') ?>
                        <ul id="password-rules" class="password-rules list-unstyled small mt-2 mb-0">
                            <li data-rule="length">Au moins 10 caractères<span class="visually-hidden"></span></li>
                            <li data-rule="upper">Une majuscule<span class="visually-hidden"></span></li>
                            <li data-rule="lower">Une minuscule<span class="visually-hidden"></span></li>
                            <li data-rule="digit">Un chiffre<span class="visually-hidden"></span></li>
                            <li data-rule="special">Un caractère spécial<span class="visually-hidden"></span></li>
                        </ul>
                    </div>
                    <div class="col-12">
                        <label for="password_confirmation" class="form-label">Confirmation</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control<?= invalid('password_confirmation') ?>" required autocomplete="new-password">
                        <?= field_error('password_confirmation') ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4">Créer le compte</button>
            </form>
        </div>
    </div>
</div>
