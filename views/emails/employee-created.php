<?php /** @var array $employe */ ?>
<p>Bonjour <?= e($employe['prenom']) ?>,</p>
<p>Un compte employé vient d'être créé pour vous sur l'application Vite &amp; Gourmand.</p>
<p><strong>Identifiant :</strong> <?= e($employe['email']) ?></p>
<p>Pour des raisons de sécurité, votre mot de passe ne vous est pas communiqué par e-mail :
   <strong>rapprochez-vous de l'administrateur pour l'obtenir.</strong></p>
<p><a href="<?= e(url('/connexion')) ?>" style="background:#7B1E3A;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">Se connecter</a></p>
