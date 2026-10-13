<?php /** @var array $user */ ?>
<p>Bonjour <?= e($user['prenom']) ?>,</p>
<p>Bienvenue chez <strong>Vite &amp; Gourmand</strong> ! Votre compte a bien été créé avec l'adresse <?= e($user['email']) ?>.</p>
<p>Depuis votre espace, vous pouvez dès maintenant :</p>
<ul>
    <li>commander nos menus pour vos fêtes et évènements ;</li>
    <li>suivre l'avancement de vos commandes ;</li>
    <li>donner votre avis après chaque prestation.</li>
</ul>
<p><a href="<?= e(url('/menus')) ?>" style="background:#7B1E3A;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">Découvrir nos menus</a></p>
<p>À très bientôt,<br>Julie et José</p>
