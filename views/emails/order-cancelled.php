<?php /** @var array $commande */ ?>
<p>Bonjour <?= e($commande['client_prenom']) ?>,</p>
<p>Comme convenu lors de notre échange, votre commande <strong><?= e($commande['numero_commande']) ?></strong> (<?= e($commande['menu_titre']) ?>) a été annulée.</p>
<p><strong>Motif :</strong> <?= e($commande['motif_annulation']) ?></p>
<p>Nous restons à votre disposition pour toute nouvelle prestation.<br>L'équipe Vite &amp; Gourmand</p>
