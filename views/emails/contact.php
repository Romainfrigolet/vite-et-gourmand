<p><strong>Nouveau message reçu depuis le formulaire de contact.</strong></p>
<p><strong>De :</strong> <?= e($email) ?><br>
<strong>Titre :</strong> <?= e($titre) ?></p>
<p style="white-space:pre-line;border-left:4px solid #C8963E;padding-left:12px;"><?= e($description) ?></p>
<p>Répondre directement à : <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
