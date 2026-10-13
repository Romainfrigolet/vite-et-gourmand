<?php
/** @var array $avis */
/** @var float|null $moyenne */
/** @var array $highlights */
?>
<section class="hero py-5" aria-labelledby="hero-title">
    <div class="container py-lg-5">
        <div class="row">
            <div class="col-lg-8">
                <p class="text-uppercase fw-semibold mb-2">Traiteur à Bordeaux depuis 25 ans</p>
                <h1 id="hero-title" class="display-5 mb-3">Vos repas de fêtes et d'évènements, cuisinés par Julie et José</h1>
                <p class="lead mb-4">Noël, Pâques, anniversaires, mariages ou simples repas de famille : choisissez votre menu en ligne, nous le préparons et le livrons chez vous.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/menus" class="btn btn-or btn-lg">Découvrir nos menus</a>
                    <a href="/contact" class="btn btn-outline-light btn-lg">Demander un conseil</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" aria-labelledby="presentation-title">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 id="presentation-title" class="mb-3">Une maison bordelaise, une cuisine de saison</h2>
                <p>Depuis 1999, <strong>Vite &amp; Gourmand</strong> accompagne les Bordelais dans tous leurs moments de partage. Nos menus évoluent au fil des saisons et des fêtes, avec des produits du Sud-Ouest choisis auprès de producteurs locaux.</p>
                <p>Chaque menu est préparé le jour de votre prestation dans notre cuisine de la rue Sainte-Catherine, puis livré à l'heure convenue, à Bordeaux et dans toute la Gironde.</p>
                <a href="/menus" class="btn btn-primary">Voir tous les menus</a>
            </div>
            <div class="col-lg-6">
                <h2 class="h3 mb-3">Une équipe de professionnels</h2>
                <ul class="list-unstyled d-grid gap-3 mb-0">
                    <li class="d-flex gap-3">
                        <span class="badge bg-bordeaux fs-6 align-self-start" aria-hidden="true">25</span>
                        <div><strong>25 ans d'expérience</strong><br><span class="text-muted">Plus de 8 000 prestations réalisées pour les particuliers et les entreprises.</span></div>
                    </li>
                    <li class="d-flex gap-3">
                        <span class="badge bg-bordeaux fs-6 align-self-start" aria-hidden="true">✓</span>
                        <div><strong>Hygiène et traçabilité</strong><br><span class="text-muted">Plan de maîtrise sanitaire, chaîne du froid respectée, allergènes indiqués pour chaque plat.</span></div>
                    </li>
                    <li class="d-flex gap-3">
                        <span class="badge bg-bordeaux fs-6 align-self-start" aria-hidden="true">⏱</span>
                        <div><strong>Ponctualité</strong><br><span class="text-muted">Livraison à l'heure choisie, suivi de votre commande étape par étape depuis votre espace.</span></div>
                    </li>
                    <li class="d-flex gap-3">
                        <span class="badge bg-bordeaux fs-6 align-self-start" aria-hidden="true">♥</span>
                        <div><strong>Fait maison</strong><br><span class="text-muted">Du foie gras aux canelés, tout est cuisiné par notre équipe.</span></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php if ($highlights): ?>
<section class="py-5 bg-sable" aria-labelledby="highlights-title">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <h2 id="highlights-title" class="mb-0">Nos menus du moment</h2>
            <a href="/menus">Voir tous les menus <span aria-hidden="true">→</span></a>
        </div>
        <?= \App\Core\View::capture('menus/_list', ['menus' => $highlights]) ?>
    </div>
</section>
<?php endif; ?>

<section class="py-5" aria-labelledby="avis-title">
    <div class="container">
        <h2 id="avis-title" class="mb-1">Ils nous ont fait confiance</h2>
        <?php if ($moyenne !== null): ?>
            <p class="text-muted mb-4">Note moyenne : <strong><?= e(number_format($moyenne, 1, ',', '')) ?>/5</strong> d'après les avis vérifiés de nos clients.</p>
        <?php endif; ?>
        <?php if (!$avis): ?>
            <p>Aucun avis publié pour le moment.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($avis as $a): ?>
                    <div class="col-md-6 col-lg-4">
                        <figure class="card avis-card h-100 p-4 mb-0">
                            <p class="stars mb-2" aria-label="Note : <?= (int) $a['note'] ?> sur 5">
                                <span aria-hidden="true"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></span>
                            </p>
                            <blockquote class="mb-3"><p class="mb-0">« <?= e($a['commentaire']) ?> »</p></blockquote>
                            <figcaption class="mt-auto small text-muted">
                                <?= e($a['prenom']) ?> <?= e($a['initiale']) ?>. — <?= e($a['menu']) ?>
                            </figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
