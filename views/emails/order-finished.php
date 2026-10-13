<?php /** @var array $commande */ ?>
<p>Bonjour <?= e($commande['client_prenom']) ?>,</p>
<p>Votre commande <strong><?= e($commande['numero_commande']) ?></strong> (<?= e($commande['menu_titre']) ?>) est maintenant terminée. Merci d'avoir fait appel à nous !</p>
<p>Votre avis compte beaucoup pour notre petite équipe : connectez-vous à votre compte pour noter votre prestation de 1 à 5 et laisser un commentaire.</p>
<p><a href="<?= e(url('/mon-compte/commandes/' . (int) $commande['commande_id'] . '#avis')) ?>" style="background:#7B1E3A;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">Donner mon avis</a></p>
<p>À bientôt,<br>Julie et José</p>
