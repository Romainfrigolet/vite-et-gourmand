<?php /** @var array $commande */ ?>
<p>Bonjour <?= e($commande['client_prenom']) ?>,</p>
<p>Votre commande <strong><?= e($commande['numero_commande']) ?></strong> (<?= e($commande['menu_titre']) ?>) a bien été livrée. Nous espérons que votre évènement s'est bien passé !</p>
<p style="background:#FFF6E5;border-left:4px solid #C8963E;padding:10px;">
    Du matériel vous a été prêté pour cette prestation. Merci de nous contacter pour organiser sa restitution
    (05 56 00 00 00 ou via le formulaire de contact du site).<br><br>
    <strong>Conformément à nos conditions générales de vente, si le matériel n'est pas restitué sous 10 jours ouvrés,
    des frais de 600 € vous seront facturés.</strong>
</p>
<p>Merci de votre confiance,<br>L'équipe Vite &amp; Gourmand</p>
