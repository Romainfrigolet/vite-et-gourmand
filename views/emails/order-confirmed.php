<?php /** @var array $commande */ ?>
<p>Bonjour <?= e($commande['client_prenom']) ?>,</p>
<p>Nous avons bien reçu votre commande <strong><?= e($commande['numero_commande']) ?></strong>. Merci pour votre confiance !</p>
<table role="presentation" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:14px;">
    <tr><td style="color:#5F5A55;">Menu</td><td><strong><?= e($commande['menu_titre']) ?></strong></td></tr>
    <tr><td style="color:#5F5A55;">Nombre de personnes</td><td><?= (int) $commande['nombre_personne'] ?></td></tr>
    <tr><td style="color:#5F5A55;">Prestation</td><td><?= e(date_fr($commande['date_prestation'])) ?> à <?= e(time_fr($commande['heure_livraison'])) ?></td></tr>
    <tr><td style="color:#5F5A55;">Lieu</td><td><?= e($commande['adresse_prestation'] . ', ' . $commande['code_postal_prestation'] . ' ' . $commande['ville_prestation']) ?></td></tr>
    <tr><td style="color:#5F5A55;">Prix du menu</td><td><?= price($commande['prix_menu']) ?></td></tr>
    <?php if ((float) $commande['remise'] > 0): ?>
        <tr><td style="color:#5F5A55;">Remise 10 %</td><td>− <?= price($commande['remise']) ?></td></tr>
    <?php endif; ?>
    <tr><td style="color:#5F5A55;">Livraison</td><td><?= (float) $commande['prix_livraison'] > 0 ? price($commande['prix_livraison']) : 'Offerte' ?></td></tr>
    <tr><td style="color:#5F5A55;"><strong>Total</strong></td><td><strong><?= price($commande['prix_total']) ?></strong></td></tr>
</table>
<?php if ($commande['menu_conditions']): ?>
    <p style="background:#FFF6E5;border-left:4px solid #C8963E;padding:10px;"><strong>Conditions du menu :</strong><br><?= nl2br(e($commande['menu_conditions'])) ?></p>
<?php endif; ?>
<p>Notre équipe va valider votre commande très rapidement. D'ici là, vous pouvez encore la modifier ou l'annuler depuis votre espace.</p>
<p><a href="<?= e(url('/mon-compte/commandes/' . (int) $commande['commande_id'])) ?>" style="background:#7B1E3A;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">Suivre ma commande</a></p>
