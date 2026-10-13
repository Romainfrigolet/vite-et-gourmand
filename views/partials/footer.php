<?php
use App\Repositories\HoraireRepository;

$horaires = HoraireRepository::all();
?>
<footer class="site-footer mt-5 pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h2>Vite &amp; Gourmand</h2>
                <p class="mb-1">Traiteur à Bordeaux depuis 25 ans.</p>
                <address class="mb-0">
                    12 rue Sainte-Catherine<br>33000 Bordeaux<br>
                    <a href="tel:+33556000000">05 56 00 00 00</a><br>
                    <a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a>
                </address>
            </div>
            <div class="col-md-4">
                <h2>Horaires d'ouverture</h2>
                <dl class="horaires row mb-0">
                    <?php foreach ($horaires as $h): ?>
                        <dt class="col-5"><?= e($h['jour']) ?></dt>
                        <dd class="col-7">
                            <?= (int) $h['ferme'] === 1
                                ? 'Fermé'
                                : e(time_fr($h['heure_ouverture'])) . ' – ' . e(time_fr($h['heure_fermeture'])) ?>
                        </dd>
                    <?php endforeach; ?>
                </dl>
            </div>
            <div class="col-md-4">
                <h2>Informations</h2>
                <ul class="list-unstyled">
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/cgv">Conditions générales de vente</a></li>
                    <li><a href="/confidentialite">Politique de confidentialité</a></li>
                    <li><a href="/contact">Nous contacter</a></li>
                </ul>
            </div>
        </div>
        <p class="text-center small mt-4 mb-0">&copy; <?= date('Y') ?> Vite &amp; Gourmand — Tous droits réservés.</p>
    </div>
</footer>
