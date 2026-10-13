<?php /** @var array $horaires */ ?>
<div class="container py-5">
    <h1 class="mb-4">Horaires d'ouverture</h1>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>
    <form method="post" action="/gestion/horaires" class="card p-4 border-0 shadow-sm">
        <?= csrf_field() ?>
        <p class="text-muted">Ces horaires sont affichés dans le pied de page de toutes les pages du site.</p>
        <div class="table-responsive">
            <table class="table align-middle">
                <caption class="visually-hidden">Horaires par jour</caption>
                <thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
                <tbody>
                <?php foreach ($horaires as $h): $id = (int) $h['horaire_id']; ?>
                    <tr>
                        <th scope="row"><?= e($h['jour']) ?></th>
                        <td>
                            <label class="visually-hidden" for="ouv<?= $id ?>">Ouverture le <?= e($h['jour']) ?></label>
                            <input type="time" id="ouv<?= $id ?>" name="horaires[<?= $id ?>][ouverture]" class="form-control" value="<?= e(substr((string) $h['heure_ouverture'], 0, 5)) ?>">
                        </td>
                        <td>
                            <label class="visually-hidden" for="ferm<?= $id ?>">Fermeture le <?= e($h['jour']) ?></label>
                            <input type="time" id="ferm<?= $id ?>" name="horaires[<?= $id ?>][fermeture]" class="form-control" value="<?= e(substr((string) $h['heure_fermeture'], 0, 5)) ?>">
                        </td>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ferme<?= $id ?>" name="horaires[<?= $id ?>][ferme]" value="1" <?= (int) $h['ferme'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ferme<?= $id ?>"><span class="visually-hidden"><?= e($h['jour']) ?> : </span>fermé</label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary align-self-start">Enregistrer les horaires</button>
    </form>
</div>
