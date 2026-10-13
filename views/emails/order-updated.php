<?php /** @var array $commande */ /** @var string $motif */ ?>
<p>Bonjour <?= e($commande['client_prenom']) ?>,</p>
<p>Suite à notre échange, votre commande <strong><?= e($commande['numero_commande']) ?></strong> a été modifiée par notre équipe.</p>
<p><strong>Motif :</strong> <?= e($motif) ?></p>
<p>Nouvelle prestation : <?= (int) $commande['nombre_personne'] ?> personnes, le <?= e(date_fr($commande['date_prestation'])) ?> à <?= e(time_fr($commande['heure_livraison'])) ?>,
   <?= e($commande['adresse_prestation'] . ', ' . $commande['code_postal_prestation'] . ' ' . $commande['ville_prestation']) ?>.<br>
   Nouveau total : <strong><?= price($commande['prix_total']) ?></strong></p>
<p><a href="<?= e(url('/mon-compte/commandes/' . (int) $commande['commande_id'])) ?>">Voir ma commande</a></p>
